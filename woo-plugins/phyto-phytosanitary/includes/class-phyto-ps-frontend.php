<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Front-end: show compliance document list on single product pages.
 * Documents marked public are shown to all; non-public require login.
 */
class Phyto_PS_Frontend {

    public static function init(): void {
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_documents' ], 25 );
        add_action( 'wp_enqueue_scripts',                 [ __CLASS__, 'enqueue' ] );
    }

    public static function enqueue(): void {
        if ( ! is_product() ) return;
        wp_enqueue_style( 'phyto-ps-front', PHYTO_PS_URL . 'assets/css/front.css', [], PHYTO_PS_VERSION );
    }

    public static function render_documents(): void {
        global $product;
        if ( ! $product ) return;

        $show_private = is_user_logged_in();
        $docs = Phyto_PS_DB::get_by_product( $product->get_id(), ! $show_private );
        if ( empty( $docs ) ) return;

        // Separate expired from valid
        $today = date( 'Y-m-d' );
        include PHYTO_PS_DIR . 'templates/product-docs.php';
    }

    /** Human-readable doc type labels. */
    public static function doc_type_label( string $type ): string {
        $labels = [
            'phytosanitary'  => __( 'Phytosanitary Certificate', 'phyto-phytosanitary' ),
            'cites'          => __( 'CITES Permit', 'phyto-phytosanitary' ),
            'import_permit'  => __( 'Import Permit', 'phyto-phytosanitary' ),
            'quality_cert'   => __( 'Quality Certificate', 'phyto-phytosanitary' ),
            'origin_cert'    => __( 'Certificate of Origin', 'phyto-phytosanitary' ),
            'nursery_cert'   => __( 'Nursery Registration', 'phyto-phytosanitary' ),
            'other'          => __( 'Document', 'phyto-phytosanitary' ),
        ];
        return $labels[ $type ] ?? $labels['other'];
    }

    /** CSS class for expiry status badge. */
    public static function expiry_status( ?string $expiry_date ): string {
        if ( ! $expiry_date ) return 'none';
        $today = date( 'Y-m-d' );
        $diff  = ( strtotime( $expiry_date ) - strtotime( $today ) ) / 86400;
        if ( $diff < 0 )  return 'expired';
        if ( $diff < 30 ) return 'expiring';
        return 'valid';
    }
}
