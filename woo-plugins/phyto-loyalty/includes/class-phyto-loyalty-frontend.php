<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Loyalty_Frontend {

    public static function init(): void {
        add_action( 'woocommerce_after_cart_totals',      [ __CLASS__, 'render_cart_widget' ] );
        add_action( 'woocommerce_account_dashboard',      [ __CLASS__, 'render_account_block' ] );
        add_action( 'wp_enqueue_scripts',                 [ __CLASS__, 'enqueue' ] );
        add_filter( 'woocommerce_account_menu_items',     [ __CLASS__, 'add_menu_item' ] );
        add_action( 'woocommerce_account_loyalty_endpoint', [ __CLASS__, 'render_account_page' ] );
        add_action( 'init',                               [ __CLASS__, 'register_endpoint' ] );
    }

    public static function register_endpoint(): void {
        add_rewrite_endpoint( 'loyalty', EP_ROOT | EP_PAGES );
    }

    public static function add_menu_item( array $items ): array {
        $items['loyalty'] = __( 'My Points', 'phyto-loyalty' );
        return $items;
    }

    public static function enqueue(): void {
        if ( ! is_cart() && ! is_account_page() ) return;
        wp_enqueue_style( 'phyto-loyalty-front', PHYTO_LOYALTY_URL . 'assets/css/front.css', [], PHYTO_LOYALTY_VERSION );
        wp_enqueue_script( 'phyto-loyalty-front', PHYTO_LOYALTY_URL . 'assets/js/front.js', [ 'jquery' ], PHYTO_LOYALTY_VERSION, true );
        wp_localize_script( 'phyto-loyalty-front', 'phytoLoyalty', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_loyalty' ),
        ] );
    }

    public static function render_cart_widget(): void {
        if ( ! is_user_logged_in() ) return;
        $account = Phyto_Loyalty_DB::get_account( get_current_user_id() );
        if ( ! $account ) return;
        $settings    = get_option( 'phyto_loyalty_settings', [] );
        $redeem_rate = (float) ( $settings['redeem_rate'] ?? 1 );
        include PHYTO_LOYALTY_DIR . 'templates/cart-widget.php';
    }

    public static function render_account_block(): void {
        if ( ! is_user_logged_in() ) return;
        $account = Phyto_Loyalty_DB::get_account( get_current_user_id() );
        include PHYTO_LOYALTY_DIR . 'templates/account-block.php';
    }

    public static function render_account_page(): void {
        $user_id      = get_current_user_id();
        $account      = Phyto_Loyalty_DB::ensure_account( $user_id );
        $transactions = Phyto_Loyalty_DB::get_transactions( $user_id );
        include PHYTO_LOYALTY_DIR . 'templates/account-page.php';
    }
}
