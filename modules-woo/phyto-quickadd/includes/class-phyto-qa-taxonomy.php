<?php
/**
 * Taxonomy pack importer for Phyto Quick Add.
 *
 * @package PhytoQuickAdd
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_QA_Taxonomy {

	const BASE_RAW = 'https://raw.githubusercontent.com/Phyto-Evolution/PhytoCommerce/main/';
	const INDEX_PATH = 'taxonomy/index.json';

	/**
	 * Fetch the full taxonomy index from GitHub, expanding each category to
	 * include its pack list by fetching the per-category sub-index.
	 *
	 * Returns an array shaped like:
	 *   [
	 *     'categories' => [
	 *       [ 'id', 'name', 'packs' => [ [ 'id', 'name', 'file', 'genera_count' ], ... ] ],
	 *       ...
	 *     ],
	 *     'total_packs' => int,
	 *   ]
	 *
	 * @return array|WP_Error
	 */
	public static function fetch_index() {
		// Step 1: top-level taxonomy/index.json
		$top = self::fetch_raw( self::INDEX_PATH );
		if ( is_wp_error( $top ) ) { return $top; }

		if ( empty( $top['categories'] ) ) {
			return new WP_Error( 'parse_failed', 'No categories found in taxonomy index.' );
		}

		$result      = array( 'categories' => array(), 'total_packs' => 0 );
		$errors      = array();

		// Step 2: fetch per-category sub-index for each category
		foreach ( $top['categories'] as $cat ) {
			$cat_index_path = 'taxonomy/' . ltrim( $cat['index'] ?? '', '/' );
			$cat_data       = self::fetch_raw( $cat_index_path );

			if ( is_wp_error( $cat_data ) ) {
				$errors[] = $cat['name'] . ': ' . $cat_data->get_error_message();
				continue;
			}

			$packs = array();
			foreach ( (array) ( $cat_data['packs'] ?? array() ) as $pack ) {
				$packs[] = array(
					'id'          => $pack['id']   ?? '',
					'name'        => $pack['name']  ?? $pack['id'],
					'file'        => 'taxonomy/' . ltrim( $pack['file'] ?? '', '/' ),
					'genera_count' => count( (array) ( $pack['genera'] ?? array() ) ),
				);
			}

			$result['categories'][] = array(
				'id'    => $cat['id']   ?? '',
				'name'  => $cat['name'] ?? '',
				'packs' => $packs,
			);
			$result['total_packs'] += count( $packs );
		}

		if ( ! empty( $errors ) ) {
			$result['warnings'] = $errors;
		}

		return $result;
	}

	/**
	 * Fetch a specific pack JSON from GitHub.
	 *
	 * @param string $path Relative path from repo root, e.g. "taxonomy/carnivorous/nepenthaceae.json"
	 * @return array|WP_Error
	 */
	public static function fetch_pack( $path ) {
		return self::fetch_raw( ltrim( $path, '/' ) );
	}

	/**
	 * Fetch and JSON-decode a file from the GitHub repo.
	 *
	 * @param string $path Path relative to repo root.
	 * @return array|WP_Error
	 */
	private static function fetch_raw( $path ) {
		$url      = self::BASE_RAW . ltrim( $path, '/' );
		$response = wp_remote_get( $url, array( 'timeout' => 20 ) );

		if ( is_wp_error( $response ) ) { return $response; }

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code !== 200 ) {
			return new WP_Error( 'fetch_failed', "HTTP {$code} fetching {$path}" );
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $data ) ) {
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
