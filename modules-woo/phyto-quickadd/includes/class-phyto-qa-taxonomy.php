<?php
/**
 * Taxonomy pack importer for Phyto Quick Add.
 *
 * @package PhytoQuickAdd
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_QA_Taxonomy {

	const INDEX_URL = 'https://raw.githubusercontent.com/kshivaramakrishnan/PhytoCommerce/main/taxonomy/index.json';

	/**
	 * Fetch the taxonomy index from GitHub.
	 *
	 * @return array|WP_Error
	 */
	public static function fetch_index() {
		$response = wp_remote_get( self::INDEX_URL, array( 'timeout' => 20 ) );
		if ( is_wp_error( $response ) ) { return $response; }

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return new WP_Error( 'fetch_failed', "HTTP {$code}" );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! $data ) {
			return new WP_Error( 'parse_failed', 'Could not parse taxonomy index JSON.' );
		}
		return $data;
	}

	/**
	 * Fetch a specific pack JSON from GitHub.
	 *
	 * @param string $path Relative path, e.g. "taxonomy/cacti/cactaceae.json"
	 * @return array|WP_Error
	 */
	public static function fetch_pack( $path ) {
		$base_url = 'https://raw.githubusercontent.com/kshivaramakrishnan/PhytoCommerce/main/';
		$response = wp_remote_get( $base_url . ltrim( $path, '/' ), array( 'timeout' => 30 ) );
		if ( is_wp_error( $response ) ) { return $response; }

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return new WP_Error( 'fetch_failed', "HTTP {$code} for {$path}" );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! $data ) {
			return new WP_Error( 'parse_failed', "Could not parse JSON for {$path}" );
		}
		return $data;
	}

	/**
	 * Import a pack as WooCommerce product categories.
	 * Creates parent category (family) → child categories (genera).
	 *
	 * @param array $pack_data Decoded pack JSON.
	 * @return array { imported: int, skipped: int, errors: string[] }
	 */
	public static function import_pack( $pack_data ) {
		$result = array( 'imported' => 0, 'skipped' => 0, 'errors' => array() );

		if ( empty( $pack_data['family'] ) ) {
			$result['errors'][] = 'Pack has no family field.';
			return $result;
		}

		$family_name = sanitize_text_field( $pack_data['family'] );
		$family_term = self::ensure_term( $family_name, 'product_cat', 0 );

		if ( is_wp_error( $family_term ) ) {
			$result['errors'][] = "Family term error: " . $family_term->get_error_message();
			return $result;
		}

		$family_id = $family_term['term_id'];

		if ( isset( $pack_data['genera'] ) && is_array( $pack_data['genera'] ) ) {
			foreach ( $pack_data['genera'] as $genus_entry ) {
				$genus_name = isset( $genus_entry['genus'] ) ? sanitize_text_field( $genus_entry['genus'] ) : '';
				if ( ! $genus_name ) { $result['skipped']++; continue; }

				$genus_term = self::ensure_term( $genus_name, 'product_cat', $family_id );
				if ( is_wp_error( $genus_term ) ) {
					$result['errors'][] = "Genus '{$genus_name}': " . $genus_term->get_error_message();
				} else {
					$result['imported']++;
				}
			}
		}

		return $result;
	}

	/**
	 * Find or create a term under a parent.
	 *
	 * @param string $name      Term name.
	 * @param string $taxonomy  Taxonomy slug.
	 * @param int    $parent_id Parent term ID (0 for root).
	 * @return array|WP_Error Term array with term_id.
	 */
	private static function ensure_term( $name, $taxonomy, $parent_id ) {
		$existing = get_term_by( 'name', $name, $taxonomy );
		if ( $existing && (int) $existing->parent === $parent_id ) {
			return array( 'term_id' => $existing->term_id );
		}

		$args = array( 'parent' => $parent_id );
		$term = wp_insert_term( $name, $taxonomy, $args );
		if ( is_wp_error( $term ) && $term->get_error_code() === 'term_exists' ) {
			return array( 'term_id' => $term->get_error_data() );
		}
		return $term;
	}
}
