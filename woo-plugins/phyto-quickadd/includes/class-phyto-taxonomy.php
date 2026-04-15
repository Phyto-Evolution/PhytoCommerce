<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Fetches taxonomy packs from the PhytoCommerce GitHub taxonomy directory.
 * Uses WP transients as cache (same logic as PS Configuration cache in phytoquickadd).
 */
class Phyto_Taxonomy {

    const GITHUB_BASE = 'https://raw.githubusercontent.com/Phyto-Evolution/PhytoCommerce/main/taxonomy/';
    const CACHE_TTL   = 3600;

    public static function fetch_index(): ?array {
        return self::fetch_json( self::GITHUB_BASE . 'index.json' );
    }

    public static function fetch_category_index( string $category_id ): ?array {
        return self::fetch_json( self::GITHUB_BASE . $category_id . '/index.json' );
    }

    public static function fetch_pack( string $file_path ): ?array {
        return self::fetch_json( self::GITHUB_BASE . $file_path );
    }

    private static function fetch_json( string $url ): ?array {
        $transient_key = 'phyto_tax_' . md5( $url );
        $cached = get_transient( $transient_key );
        if ( $cached !== false ) return $cached;

        $response = wp_remote_get( $url, [
            'timeout'    => 15,
            'user-agent' => 'PhytoCommerce-WooPlugin/' . PHYTO_QA_VERSION,
            'sslverify'  => true,
        ] );

        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) return null;
        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! $data ) return null;

        set_transient( $transient_key, $data, self::CACHE_TTL );
        return $data;
    }

    /**
     * Import a taxonomy pack: create WC product_cat terms mirroring family → genus → species → cultivar.
     */
    public static function import_pack( string $pack_file ): array {
        $pack = self::fetch_pack( $pack_file );
        if ( ! $pack ) return [ 'success' => false, 'error' => 'Could not fetch pack.' ];

        $imported = 0;
        $log      = [];

        foreach ( $pack['genera'] as $genus_data ) {
            // Family
            $family_id = self::ensure_term( $pack['family'], 0, [
                'description'   => $pack['description']       ?? $pack['common_name'] ?? '',
                'meta_title'    => $pack['meta_title']         ?? '',
                'meta_desc'     => $pack['meta_description']   ?? '',
                'meta_keywords' => $pack['meta_keywords']      ?? '',
            ] );
            if ( is_wp_error( $family_id ) ) continue;

            // Genus
            $genus_id = self::ensure_term( $genus_data['genus'], $family_id, [
                'description'   => $genus_data['description']       ?? $genus_data['common_name'] ?? '',
                'meta_title'    => $genus_data['meta_title']         ?? '',
                'meta_desc'     => $genus_data['meta_description']   ?? '',
                'meta_keywords' => $genus_data['meta_keywords']      ?? '',
            ] );
            if ( is_wp_error( $genus_id ) ) continue;
            $log[] = 'Genus: ' . $genus_data['genus'];
            $imported++;

            foreach ( $genus_data['species'] ?? [] as $species ) {
                $species_id = self::ensure_term( $species['full_name'], $genus_id, [
                    'description'   => $species['description']       ?? $species['full_name'],
                    'meta_title'    => $species['meta_title']         ?? '',
                    'meta_desc'     => $species['meta_description']   ?? '',
                    'meta_keywords' => $species['meta_keywords']      ?? '',
                ] );
                if ( is_wp_error( $species_id ) ) continue;
                $log[] = '  Species: ' . $species['full_name'];
                $imported++;

                foreach ( $species['cultivars'] ?? [] as $cultivar ) {
                    $cname = $species['full_name'] . " '" . $cultivar['cultivar'] . "'";
                    $cid   = self::ensure_term( $cname, $species_id, [
                        'description' => $cultivar['description'] ?? $cname,
                    ] );
                    if ( ! is_wp_error( $cid ) ) { $log[] = '    Cultivar: ' . $cname; $imported++; }
                }
            }
        }

        return [ 'success' => true, 'imported' => $imported, 'log' => $log ];
    }

    /**
     * Ensure a WooCommerce product category term exists.
     * @return int|WP_Error  term_id
     */
    private static function ensure_term( string $name, int $parent_id, array $meta = [] ): int|\WP_Error {
        $existing = term_exists( $name, 'product_cat', $parent_id );
        if ( $existing ) return (int) ( $existing['term_id'] ?? $existing );

        $args = [
            'parent'      => $parent_id,
            'description' => wp_kses_post( $meta['description'] ?? '' ),
            'slug'        => sanitize_title( $name ),
        ];

        $result = wp_insert_term( $name, 'product_cat', $args );
        if ( is_wp_error( $result ) ) return $result;

        $term_id = (int) $result['term_id'];
        if ( ! empty( $meta['meta_title'] ) )    update_term_meta( $term_id, 'rank_math_title',            $meta['meta_title'] );
        if ( ! empty( $meta['meta_desc'] ) )     update_term_meta( $term_id, 'rank_math_description',      $meta['meta_desc'] );
        if ( ! empty( $meta['meta_keywords'] ) ) update_term_meta( $term_id, 'rank_math_focus_keyword',    $meta['meta_keywords'] );
        // Yoast SEO fallback
        if ( ! empty( $meta['meta_title'] ) )    update_term_meta( $term_id, '_yoast_wpseo_title',         $meta['meta_title'] );
        if ( ! empty( $meta['meta_desc'] ) )     update_term_meta( $term_id, '_yoast_wpseo_metadesc',      $meta['meta_desc'] );

        return $term_id;
    }
}
