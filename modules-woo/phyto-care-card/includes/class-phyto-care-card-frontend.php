<?php
/**
 * Frontend display for Phyto Care Card.
 *
 * Adds the "Download Care Guide" button to the single product page and serves
 * the PDF via the custom /care-card/{id}/ rewrite endpoint.
 *
 * @package PhytoCareCard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Care_Card_Frontend
 */
class Phyto_Care_Card_Frontend {

	/**
	 * Meta keys that indicate a care card has meaningful content.
	 *
	 * @var array
	 */
	private $care_meta_keys = array(
		'_phyto_cc_light_req',
		'_phyto_cc_watering',
		'_phyto_cc_humidity',
		'_phyto_cc_temp_min',
		'_phyto_cc_temp_max',
		'_phyto_cc_potting_media',
		'_phyto_cc_fertilisation',
		'_phyto_cc_dormancy_notes',
		'_phyto_cc_special_tips',
	);

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		add_action( 'woocommerce_single_product_summary', array( $this, 'add_download_button' ), 35 );
		add_action( 'template_redirect', array( $this, 'serve_pdf' ) );
	}

	/**
	 * Output the "Download Care Guide" button on the single product page.
	 *
	 * Hooked to `woocommerce_single_product_summary` at priority 35 (after price/add-to-cart).
	 * Fires only when at least one care meta key is populated.
	 * Also enqueues the frontend stylesheet.
	 */
	public function add_download_button() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();

		if ( ! $this->has_care_data( $product_id ) ) {
			return;
		}

		wp_enqueue_style(
			'phyto-cc-frontend',
			PHYTO_CC_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_CC_VERSION
		);

		$url = home_url( '/care-card/' . $product_id . '/' );

		printf(
			'<a class="phyto-cc-btn button" href="%s" target="_blank" rel="noopener">%s</a>',
			esc_url( $url ),
			esc_html__( "\xe2\xac\x87 Download Care Guide (PDF)", 'phyto-care-card' )
		);
	}

	/**
	 * Serve the PDF when the /care-card/{id}/ endpoint is requested.
	 *
	 * Hooked to `template_redirect`. Exits after output to prevent WordPress
	 * from rendering a theme template.
	 */
	public function serve_pdf() {
		$product_id = (int) get_query_var( 'phyto_care_card_id', 0 );

		if ( ! $product_id ) {
			return;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_die( esc_html__( 'Product not found.', 'phyto-care-card' ), '', array( 'response' => 404 ) );
		}

		$generator = new Phyto_Care_Card_Generator();
		$pdf       = $generator->generate( $product_id );

		$slug = $product->get_slug();

		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: inline; filename="care-guide-' . sanitize_file_name( $slug ) . '.pdf"' );
		header( 'Content-Length: ' . strlen( $pdf ) );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Pragma: no-cache' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — binary PDF output.
		echo $pdf;
		exit;
	}

	/**
	 * Check whether the product has at least one care meta value populated.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return bool True when at least one care field is non-empty.
	 */
	private function has_care_data( $product_id ) {
		foreach ( $this->care_meta_keys as $key ) {
			$val = get_post_meta( $product_id, $key, true );
			if ( '' !== (string) $val ) {
				return true;
			}
		}
		return false;
	}
}
