<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_KYC_Frontend {

    public static function init(): void {
        // Blur prices for unverified users
        add_filter( 'woocommerce_get_price_html',                [ __CLASS__, 'blur_price' ], 20, 2 );
        // Block add-to-cart
        add_filter( 'woocommerce_add_to_cart_validation',        [ __CLASS__, 'block_unverified_cart' ], 10, 2 );
        // Banner
        add_action( 'woocommerce_before_single_product_summary', [ __CLASS__, 'kyc_banner' ], 5 );
        // My Account menu + endpoint
        add_filter( 'woocommerce_account_menu_items',            [ __CLASS__, 'add_menu_item' ] );
        add_action( 'init',                                      [ __CLASS__, 'register_endpoint' ] );
        add_action( 'woocommerce_account_kyc_endpoint',          [ __CLASS__, 'render_account_page' ] );
        // AJAX submission
        add_action( 'wp_ajax_phyto_kyc_submit_pan',  [ __CLASS__, 'handle_pan_submit' ] );
        add_action( 'wp_ajax_phyto_kyc_submit_gst',  [ __CLASS__, 'handle_gst_submit' ] );
        add_action( 'wp_enqueue_scripts',             [ __CLASS__, 'enqueue' ] );
    }

    public static function register_endpoint(): void {
        add_rewrite_endpoint( 'kyc', EP_ROOT | EP_PAGES );
    }

    public static function add_menu_item( array $items ): array {
        $items['kyc'] = __( 'Identity Verification', 'phyto-kyc' );
        return $items;
    }

    public static function enqueue(): void {
        if ( ! is_account_page() && ! is_product() && ! is_shop() ) return;
        wp_enqueue_style( 'phyto-kyc-front', PHYTO_KYC_URL . 'assets/css/front.css', [], PHYTO_KYC_VERSION );
        wp_enqueue_script( 'phyto-kyc-front', PHYTO_KYC_URL . 'assets/js/front.js', [ 'jquery' ], PHYTO_KYC_VERSION, true );
        wp_localize_script( 'phyto-kyc-front', 'phytoKYC', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_kyc' ),
        ] );
    }

    private static function needs_kyc(): bool {
        $settings = get_option( 'phyto_kyc_settings', [] );
        if ( empty( $settings['enabled'] ) ) return false;
        if ( ! is_user_logged_in() ) return true;
        return ! Phyto_KYC_DB::is_verified( get_current_user_id() );
    }

    public static function blur_price( string $price_html, WC_Product $product ): string {
        if ( ! self::needs_kyc() ) return $price_html;
        $kyc_url = wc_get_account_endpoint_url( 'kyc' );
        return '<span class="phyto-kyc-blurred" title="' . esc_attr__( 'Complete KYC to view price', 'phyto-kyc' ) . '">' . $price_html . '</span>'
             . '<a href="' . esc_url( $kyc_url ) . '" class="phyto-kyc-unlock-link">' . esc_html__( 'Verify to view price', 'phyto-kyc' ) . '</a>';
    }

    public static function block_unverified_cart( bool $passed, int $product_id ): bool {
        if ( self::needs_kyc() ) {
            wc_add_notice( sprintf(
                __( 'Please <a href="%s">complete identity verification</a> before purchasing.', 'phyto-kyc' ),
                esc_url( wc_get_account_endpoint_url( 'kyc' ) )
            ), 'error' );
            return false;
        }
        return $passed;
    }

    public static function kyc_banner(): void {
        if ( ! self::needs_kyc() ) return;
        $kyc_url = wc_get_account_endpoint_url( 'kyc' );
        echo '<div class="phyto-kyc-banner"><p>'
           . sprintf( esc_html__( 'Identity verification required to view prices and purchase. %s', 'phyto-kyc' ),
                '<a href="' . esc_url( $kyc_url ) . '">' . esc_html__( 'Verify now →', 'phyto-kyc' ) . '</a>' )
           . '</p></div>';
    }

    public static function render_account_page(): void {
        if ( ! is_user_logged_in() ) { echo '<p>' . esc_html__( 'Please log in.', 'phyto-kyc' ) . '</p>'; return; }
        $user_id = get_current_user_id();
        $profile = Phyto_KYC_DB::ensure_profile( $user_id );
        $settings = get_option( 'phyto_kyc_settings', [] );
        include PHYTO_KYC_DIR . 'templates/account-kyc.php';
    }

    public static function handle_pan_submit(): void {
        check_ajax_referer( 'phyto_kyc', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => __( 'Please log in.', 'phyto-kyc' ) ] );

        $pan     = strtoupper( sanitize_text_field( $_POST['pan'] ?? '' ) );
        if ( ! preg_match( '/^[A-Z]{5}[0-9]{4}[A-Z]$/', $pan ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid PAN format.', 'phyto-kyc' ) ] );
        }

        $result = Phyto_KYC_Sandbox::verify_pan( $pan );
        $status = $result['success'] ? 'Verified' : 'Rejected';

        global $wpdb;
        $user_id = get_current_user_id();
        Phyto_KYC_DB::ensure_profile( $user_id );
        $wpdb->update( $wpdb->prefix . 'phyto_kyc_profile', [
            'pan_number'      => $pan,
            'pan_name'        => $result['name'] ?? '',
            'level1_status'   => $status,
            'api_response_l1' => wp_json_encode( $result['raw'] ?? [] ),
            'kyc_level'       => $result['success'] ? 1 : 0,
            'date_upd'        => current_time( 'mysql' ),
        ], [ 'user_id' => $user_id ] );

        if ( $result['success'] ) {
            wp_send_json_success( [ 'message' => sprintf( __( 'PAN verified. Name on record: %s', 'phyto-kyc' ), esc_html( $result['name'] ?? '' ) ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'PAN could not be verified. Please check the number and try again.', 'phyto-kyc' ) ] );
        }
    }

    public static function handle_gst_submit(): void {
        check_ajax_referer( 'phyto_kyc', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => __( 'Please log in.', 'phyto-kyc' ) ] );

        $gst = strtoupper( sanitize_text_field( $_POST['gst'] ?? '' ) );
        if ( strlen( $gst ) !== 15 ) wp_send_json_error( [ 'message' => __( 'Invalid GSTIN format.', 'phyto-kyc' ) ] );

        $result = Phyto_KYC_Sandbox::verify_gst( $gst );
        $status = $result['success'] ? 'Verified' : 'Rejected';

        global $wpdb;
        $user_id = get_current_user_id();
        Phyto_KYC_DB::ensure_profile( $user_id );
        $wpdb->update( $wpdb->prefix . 'phyto_kyc_profile', [
            'gst_number'      => $gst,
            'business_name'   => $result['business_name'] ?? '',
            'level2_status'   => $status,
            'api_response_l2' => wp_json_encode( $result['raw'] ?? [] ),
            'kyc_level'       => $result['success'] ? 2 : 1,
            'date_upd'        => current_time( 'mysql' ),
        ], [ 'user_id' => $user_id ] );

        if ( $result['success'] ) {
            wp_send_json_success( [ 'message' => sprintf( __( 'GST verified. Business: %s', 'phyto-kyc' ), esc_html( $result['business_name'] ?? '' ) ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'GSTIN could not be verified.', 'phyto-kyc' ) ] );
        }
    }
}
