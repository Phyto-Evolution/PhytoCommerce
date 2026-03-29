<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Restock_Frontend {

    public static function init(): void {
        // Show subscription form on out-of-stock single product pages
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'render_form' ], 31 );
        // Handle AJAX subscription (logged-in + guest)
        add_action( 'wp_ajax_phyto_restock_subscribe',        [ __CLASS__, 'handle_subscribe' ] );
        add_action( 'wp_ajax_nopriv_phyto_restock_subscribe', [ __CLASS__, 'handle_subscribe' ] );
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }

    public static function enqueue(): void {
        if ( ! is_product() ) return;
        wp_enqueue_style(
            'phyto-restock-front',
            PHYTO_RESTOCK_URL . 'assets/css/front.css',
            [], PHYTO_RESTOCK_VERSION
        );
        wp_enqueue_script(
            'phyto-restock-front',
            PHYTO_RESTOCK_URL . 'assets/js/front.js',
            [ 'jquery' ], PHYTO_RESTOCK_VERSION, true
        );
        wp_localize_script( 'phyto-restock-front', 'phytoRestock', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_restock' ),
            'i18n'    => [
                'success' => __( "You'll be notified when this product is back in stock.", 'phyto-restock-alert' ),
                'error'   => __( 'Something went wrong. Please try again.', 'phyto-restock-alert' ),
            ],
        ] );
    }

    public static function render_form(): void {
        global $product;
        if ( ! $product || $product->is_in_stock() ) return;

        $settings = get_option( 'phyto_restock_settings', [] );
        if ( empty( $settings['show_form'] ) ) return;

        wc_get_template( 'restock-form.php', [], '', PHYTO_RESTOCK_DIR . 'templates/' );
    }

    public static function handle_subscribe(): void {
        check_ajax_referer( 'phyto_restock', 'nonce' );

        $product_id   = absint( $_POST['product_id']   ?? 0 );
        $variation_id = absint( $_POST['variation_id'] ?? 0 );
        $email        = sanitize_email( $_POST['email']     ?? '' );
        $firstname    = sanitize_text_field( $_POST['firstname'] ?? '' );

        if ( ! $product_id || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid request.', 'phyto-restock-alert' ) ] );
        }

        $user_id = get_current_user_id();
        $result  = Phyto_Restock_DB::subscribe( $product_id, $variation_id, $email, $firstname, $user_id );

        if ( $result ) {
            wp_send_json_success( [ 'message' => __( "You'll be notified when this product is back in stock.", 'phyto-restock-alert' ) ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'You are already subscribed for this product.', 'phyto-restock-alert' ) ] );
        }
    }
}
