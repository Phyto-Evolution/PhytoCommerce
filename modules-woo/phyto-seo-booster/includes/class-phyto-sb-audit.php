<?php
/**
 * Audit & AI meta generation for Phyto SEO Booster.
 *
 * @package PhytoSeoBooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_SB_Audit {

	/** Prevent recursive save loops. */
	private static $saving = false;

	public function register_hooks() {
		add_action( 'save_post_product', array( $this, 'auto_generate_meta' ), 20 );
	}

	/**
	 * Auto-generate Yoast/RankMath meta if empty and API key available.
	 */
	public function auto_generate_meta( $post_id ) {
		if ( self::$saving ) {
			return;
		}

		$api_key = get_option( 'phyto_sb_api_key', '' );
		if ( ! $api_key ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		// Check Yoast first, then RankMath.
		$has_yoast_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
		$has_rank_title  = get_post_meta( $post_id, 'rank_math_title', true );

		if ( $has_yoast_title || $has_rank_title ) {
			return; // Already has meta — skip.
		}

		$result = $this->call_claude( $product->get_name(), $api_key );
		if ( ! $result ) {
			return;
		}

		self::$saving = true;

		if ( ! empty( $result['meta_title'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( $result['meta_title'] ) );
			update_post_meta( $post_id, 'rank_math_title', sanitize_text_field( $result['meta_title'] ) );
		}

		if ( ! empty( $result['meta_description'] ) ) {
			update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_text_field( $result['meta_description'] ) );
			update_post_meta( $post_id, 'rank_math_description', sanitize_text_field( $result['meta_description'] ) );
		}

		self::$saving = false;
	}

	/**
	 * Score a single product (0–100).
	 *
	 * @param  int $product_id WC product ID.
	 * @return array ['score' => int, 'issues' => string[]]
	 */
	public static function score_product( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return array( 'score' => 0, 'issues' => array( 'invalid_product' ) );
		}

		$checks = array(
			'meta_title'    => (bool) ( get_post_meta( $product_id, '_yoast_wpseo_title', true ) || get_post_meta( $product_id, 'rank_math_title', true ) ),
			'meta_desc'     => (bool) ( get_post_meta( $product_id, '_yoast_wpseo_metadesc', true ) || get_post_meta( $product_id, 'rank_math_description', true ) ),
			'description'   => strlen( wp_strip_all_tags( $product->get_description() ) ) >= 50,
			'has_image'     => (bool) $product->get_image_id(),
			'has_sku'       => '' !== $product->get_sku(),
			'has_price'     => '' !== $product->get_price(),
		);

		$passed = array_filter( $checks );
		$score  = (int) round( count( $passed ) / count( $checks ) * 100 );
		$issues = array_keys( array_filter( $checks, fn( $v ) => ! $v ) );

		return compact( 'score', 'issues' );
	}

	/**
	 * Run audit for all published products and store results.
	 *
	 * @return int Number of products audited.
	 */
	public static function run_full_audit() {
		$ids = get_posts( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		foreach ( $ids as $id ) {
			$result = self::score_product( $id );
			Phyto_SB_DB::upsert( $id, $result['score'], $result['issues'] );
		}

		return count( $ids );
	}

	/**
	 * Generate meta title + description via Claude API.
	 *
	 * @param  string $product_name Plant/product name.
	 * @param  string $api_key      Claude API key.
	 * @return array|false Associative array with meta_title, meta_description keys, or false.
	 */
	public static function call_claude( $product_name, $api_key ) {
		$prompt = 'Write SEO meta for a WooCommerce product listing for the plant "' . $product_name . '". Return ONLY valid JSON: {"meta_title":"...(max 60 chars)","meta_description":"...(max 160 chars)"}';

		$response = wp_remote_post(
			'https://api.anthropic.com/v1/messages',
			array(
				'timeout' => 30,
				'headers' => array(
					'x-api-key'         => $api_key,
					'anthropic-version' => '2023-06-01',
					'content-type'      => 'application/json',
				),
				'body' => wp_json_encode( array(
					'model'      => 'claude-haiku-4-5-20251001',
					'max_tokens' => 200,
					'messages'   => array( array( 'role' => 'user', 'content' => $prompt ) ),
				) ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		$text = $body['content'][0]['text'] ?? '';
		$text = preg_replace( '/^```(?:json)?\n?|\n?```$/', '', trim( $text ) );

		$data = json_decode( $text, true );
		if ( ! is_array( $data ) ) {
			return false;
		}

		return $data;
	}
}
