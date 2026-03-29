<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_WS_Frontend {

    public static function init(): void {
        add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'add_menu_item' ] );
        add_action( 'init',                           [ __CLASS__, 'register_endpoint' ] );
        add_action( 'woocommerce_account_wholesale_endpoint', [ __CLASS__, 'render_account_page' ] );
        add_action( 'wp_ajax_phyto_ws_apply',         [ __CLASS__, 'handle_apply' ] );
        add_action( 'wp_enqueue_scripts',             [ __CLASS__, 'enqueue' ] );
    }

    public static function register_endpoint(): void {
        add_rewrite_endpoint( 'wholesale', EP_ROOT | EP_PAGES );
    }

    public static function add_menu_item( array $items ): array {
        $items['wholesale'] = __( 'Wholesale', 'phyto-wholesale' );
        return $items;
    }

    public static function enqueue(): void {
        if ( ! is_account_page() ) return;
        wp_enqueue_style( 'phyto-ws-front', PHYTO_WS_URL . 'assets/css/front.css', [], PHYTO_WS_VERSION );
        wp_enqueue_script( 'phyto-ws-front', PHYTO_WS_URL . 'assets/js/front.js', [ 'jquery' ], PHYTO_WS_VERSION, true );
        wp_localize_script( 'phyto-ws-front', 'phytoWS', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_ws' ),
        ] );
    }

    public static function render_account_page(): void {
        $user_id = get_current_user_id();
        $app     = Phyto_WS_DB::get_application( $user_id );
        include PHYTO_WS_DIR . 'templates/account-wholesale.php';
    }

    public static function handle_apply(): void {
        check_ajax_referer( 'phyto_ws', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => __( 'Please log in.', 'phyto-wholesale' ) ] );

        $user_id = get_current_user_id();
        $app     = Phyto_WS_DB::get_application( $user_id );
        if ( $app && $app->status === 'Pending' ) {
            wp_send_json_error( [ 'message' => __( 'Your application is already under review.', 'phyto-wholesale' ) ] );
        }

        $id = Phyto_WS_DB::submit_application( $user_id, $_POST );
        if ( $id ) {
            wp_send_json_success( [ 'message' => __( 'Application submitted. We will review and respond within 2 business days.', 'phyto-wholesale' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Could not submit application. Please try again.', 'phyto-wholesale' ) ] );
        }
    }
}
