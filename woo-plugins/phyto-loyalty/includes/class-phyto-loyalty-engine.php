<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Core earn/redeem logic + WooCommerce hook wiring.
 */
class Phyto_Loyalty_Engine {

    public static function init(): void {
        // Earn points when order completes
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'earn_on_order' ] );

        // Apply redemption discount at cart
        add_action( 'woocommerce_cart_calculate_fees', [ __CLASS__, 'apply_redemption' ] );

        // Store redemption intent in session
        add_action( 'wp_ajax_phyto_loyalty_redeem', [ __CLASS__, 'handle_redeem_ajax' ] );
    }

    public static function earn_on_order( int $order_id ): void {
        $settings = get_option( 'phyto_loyalty_settings', [] );
        if ( empty( $settings['enabled'] ) ) return;

        $order   = wc_get_order( $order_id );
        if ( ! $order ) return;

        $user_id = $order->get_user_id();
        if ( ! $user_id ) return; // guest — no loyalty

        $earn_rate = (float) ( $settings['earn_rate'] ?? 1 ); // INR per 1 point
        $subtotal  = (float) $order->get_subtotal();
        $points    = (int) floor( $subtotal / $earn_rate );

        // Apply tier multiplier
        $account    = Phyto_Loyalty_DB::ensure_account( $user_id );
        $tiers      = $settings['tiers'] ?? [];
        $multiplier = 1.0;
        foreach ( $tiers as $t ) {
            if ( $account->tier === $t['name'] ) { $multiplier = (float) $t['multiplier']; break; }
        }
        $points = (int) floor( $points * $multiplier );
        if ( $points <= 0 ) return;

        Phyto_Loyalty_DB::add_transaction( $user_id, 'earn', $points, $order_id,
            sprintf( __( 'Earned on order #%d', 'phyto-loyalty' ), $order_id )
        );
    }

    public static function apply_redemption( WC_Cart $cart ): void {
        $points = WC()->session ? (int) WC()->session->get( 'phyto_loyalty_redeem', 0 ) : 0;
        if ( $points <= 0 ) return;

        $settings    = get_option( 'phyto_loyalty_settings', [] );
        $redeem_rate = (float) ( $settings['redeem_rate'] ?? 1 );  // discount per 1 point (INR)
        $max_pct     = (float) ( $settings['max_redeem_pct'] ?? 20 );
        $discount    = $points * $redeem_rate;
        $max_allowed = ( $cart->get_subtotal() * $max_pct ) / 100;
        $discount    = min( $discount, $max_allowed );

        $cart->add_fee( __( 'Loyalty Points Discount', 'phyto-loyalty' ), -$discount, false );
    }

    public static function handle_redeem_ajax(): void {
        check_ajax_referer( 'phyto_loyalty', 'nonce' );
        if ( ! is_user_logged_in() ) wp_send_json_error( [ 'message' => __( 'Please log in.', 'phyto-loyalty' ) ] );

        $points   = absint( $_POST['points'] ?? 0 );
        $settings = get_option( 'phyto_loyalty_settings', [] );
        $min      = (int) ( $settings['min_redeem'] ?? 100 );
        $account  = Phyto_Loyalty_DB::get_account( get_current_user_id() );

        if ( ! $account || $account->points_balance < $points ) {
            wp_send_json_error( [ 'message' => __( 'Insufficient points.', 'phyto-loyalty' ) ] );
        }
        if ( $points < $min ) {
            wp_send_json_error( [ 'message' => sprintf( __( 'Minimum redemption is %d points.', 'phyto-loyalty' ), $min ) ] );
        }

        WC()->session->set( 'phyto_loyalty_redeem', $points );
        WC()->cart->calculate_totals();
        wp_send_json_success( [ 'message' => sprintf( __( '%d points applied to your cart.', 'phyto-loyalty' ), $points ) ] );
    }
}
