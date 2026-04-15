<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Bundle_Frontend {

    public static function init(): void {
        add_action( 'init',                  [ __CLASS__, 'register_endpoint' ] );
        add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_menu_item' ] );
        add_action( 'woocommerce_account_bundles_endpoint', [ __CLASS__, 'render_page' ] );
        // Also expose bundles via a shortcode: [phyto_bundle id="1"]
        add_shortcode( 'phyto_bundle', [ __CLASS__, 'shortcode' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
        // AJAX: fetch products for a slot's category
        add_action( 'wp_ajax_phyto_bundle_products',        [ __CLASS__, 'ajax_products' ] );
        add_action( 'wp_ajax_nopriv_phyto_bundle_products', [ __CLASS__, 'ajax_products' ] );
    }

    public static function register_endpoint(): void {
        add_rewrite_endpoint( 'bundles', EP_ROOT | EP_PAGES );
    }

    public static function add_menu_item( array $items ): array {
        $items['bundles'] = __( 'Build a Bundle', 'phyto-bundle' );
        return $items;
    }

    public static function enqueue(): void {
        if ( ! is_account_page() && ! is_page() ) return;
        wp_enqueue_style(  'phyto-bundle-front', PHYTO_BUNDLE_URL . 'assets/css/front.css', [], PHYTO_BUNDLE_VERSION );
        wp_enqueue_script( 'phyto-bundle-front', PHYTO_BUNDLE_URL . 'assets/js/front.js',  [ 'jquery' ], PHYTO_BUNDLE_VERSION, true );
        wp_localize_script( 'phyto-bundle-front', 'phytoBundle', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_bundle' ),
            'cart_url' => wc_get_cart_url(),
        ] );
    }

    public static function render_page(): void {
        $bundles = Phyto_Bundle_DB::get_bundles();
        $bundle  = null;
        $slots   = [];
        if ( ! empty( $_GET['bundle'] ) ) {
            $bundle = Phyto_Bundle_DB::get_bundle( absint( $_GET['bundle'] ) );
            if ( $bundle ) $slots = Phyto_Bundle_DB::get_slots( (int) $bundle->id_bundle );
        }
        include PHYTO_BUNDLE_DIR . 'templates/bundle-page.php';
    }

    public static function shortcode( array $atts ): string {
        $atts   = shortcode_atts( [ 'id' => 0 ], $atts );
        $bundle = Phyto_Bundle_DB::get_bundle( absint( $atts['id'] ) );
        if ( ! $bundle || ! $bundle->active ) return '';
        $slots  = Phyto_Bundle_DB::get_slots( (int) $bundle->id_bundle );
        ob_start();
        include PHYTO_BUNDLE_DIR . 'templates/bundle-builder.php';
        return ob_get_clean();
    }

    public static function ajax_products(): void {
        check_ajax_referer( 'phyto_bundle', 'nonce' );
        $cat_id = absint( $_POST['category_id'] ?? 0 );
        if ( ! $cat_id ) wp_send_json_error();

        $products = wc_get_products( [
            'status'   => 'publish',
            'category' => [ get_term( $cat_id, 'product_cat' )->slug ?? '' ],
            'limit'    => 50,
            'stock_status' => 'instock',
        ] );

        $data = array_map( fn( WC_Product $p ) => [
            'id'    => $p->get_id(),
            'name'  => $p->get_name(),
            'price' => wc_price( $p->get_price() ),
            'img'   => wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) ?: wc_placeholder_img_src(),
        ], $products );

        wp_send_json_success( $data );
    }
}
