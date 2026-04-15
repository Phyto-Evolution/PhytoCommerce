<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles adding bundle selections to the WooCommerce cart
 * and applying the bundle discount as a cart fee.
 */
class Phyto_Bundle_Cart {

    public static function init(): void {
        add_action( 'wp_ajax_phyto_bundle_add_to_cart',        [ __CLASS__, 'handle_add' ] );
        add_action( 'wp_ajax_nopriv_phyto_bundle_add_to_cart', [ __CLASS__, 'handle_add' ] );
        add_action( 'woocommerce_cart_calculate_fees',         [ __CLASS__, 'apply_discount' ] );
    }

    public static function handle_add(): void {
        check_ajax_referer( 'phyto_bundle', 'nonce' );

        $bundle_id  = absint( $_POST['bundle_id'] ?? 0 );
        $selections = (array) ( $_POST['selections'] ?? [] ); // [ slot_id => product_id ]

        $bundle = Phyto_Bundle_DB::get_bundle( $bundle_id );
        if ( ! $bundle ) wp_send_json_error( [ 'message' => __( 'Bundle not found.', 'phyto-bundle' ) ] );

        $slots    = Phyto_Bundle_DB::get_slots( $bundle_id );
        $added    = [];
        $subtotal = 0.0;

        foreach ( $slots as $slot ) {
            $product_id = absint( $selections[ $slot->id_slot ] ?? 0 );
            if ( ! $product_id ) {
                if ( $slot->required ) {
                    wp_send_json_error( [ 'message' => sprintf(
                        __( 'Please select a product for "%s".', 'phyto-bundle' ), $slot->slot_name
                    ) ] );
                }
                continue;
            }
            $product = wc_get_product( $product_id );
            if ( ! $product || ! $product->is_purchasable() ) continue;

            $cart_item_key = WC()->cart->add_to_cart( $product_id, 1, 0, [], [
                'phyto_bundle_id'   => $bundle_id,
                'phyto_bundle_name' => $bundle->name,
            ] );
            if ( $cart_item_key ) {
                $added[]   = $product_id;
                $subtotal += (float) $product->get_price();
            }
        }

        if ( empty( $added ) ) {
            wp_send_json_error( [ 'message' => __( 'No products could be added.', 'phyto-bundle' ) ] );
        }

        // Store bundle discount info in session
        $bundles_in_cart   = WC()->session->get( 'phyto_bundles_in_cart', [] );
        $bundles_in_cart[] = [ 'bundle_id' => $bundle_id, 'subtotal' => $subtotal ];
        WC()->session->set( 'phyto_bundles_in_cart', $bundles_in_cart );

        wp_send_json_success( [
            'message'  => sprintf( __( '%d items added to your cart!', 'phyto-bundle' ), count( $added ) ),
            'cart_url' => wc_get_cart_url(),
        ] );
    }

    public static function apply_discount( WC_Cart $cart ): void {
        $bundles = WC()->session ? WC()->session->get( 'phyto_bundles_in_cart', [] ) : [];
        if ( empty( $bundles ) ) return;

        foreach ( $bundles as $entry ) {
            $bundle   = Phyto_Bundle_DB::get_bundle( (int) ( $entry['bundle_id'] ?? 0 ) );
            if ( ! $bundle ) continue;
            $discount = Phyto_Bundle_DB::calculate_discount( $bundle, (float) ( $entry['subtotal'] ?? 0 ) );
            if ( $discount > 0 ) {
                $cart->add_fee(
                    sprintf( __( 'Bundle Discount: %s', 'phyto-bundle' ), $bundle->name ),
                    -$discount, false
                );
            }
        }
    }
}
