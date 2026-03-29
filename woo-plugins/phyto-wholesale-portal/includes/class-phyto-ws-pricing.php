<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_WS_Pricing {

    public static function init(): void {
        // Apply tiered pricing for wholesalers
        add_filter( 'woocommerce_product_get_price',         [ __CLASS__, 'get_price' ], 20, 2 );
        add_filter( 'woocommerce_product_get_regular_price', [ __CLASS__, 'get_price' ], 20, 2 );
        // Enforce MOQ
        add_filter( 'woocommerce_add_to_cart_validation',    [ __CLASS__, 'validate_moq' ], 10, 4 );
        // Hide product from non-wholesalers if wholesale_only
        add_filter( 'woocommerce_product_is_visible',        [ __CLASS__, 'check_visibility' ], 10, 2 );
        // Show MOQ note on product page
        add_action( 'woocommerce_single_product_summary',    [ __CLASS__, 'show_moq_notice' ], 25 );
    }

    public static function is_wholesaler(): bool {
        return is_user_logged_in() && current_user_can( 'phyto_wholesaler' )
               || ( is_user_logged_in() && in_array( 'phyto_wholesaler', wp_get_current_user()->roles, true ) );
    }

    public static function get_price( $price, WC_Product $product ) {
        if ( ! self::is_wholesaler() ) return $price;
        $config = Phyto_WS_DB::get_product_config( $product->get_id() );
        if ( ! $config || empty( $config->price_tiers ) ) return $price;

        $tiers = json_decode( $config->price_tiers, true );
        if ( empty( $tiers ) ) return $price;

        // Find cart qty for this product
        $qty = 1;
        foreach ( WC()->cart ? WC()->cart->get_cart() : [] as $item ) {
            if ( (int) $item['product_id'] === $product->get_id() ) {
                $qty = (int) $item['quantity'];
                break;
            }
        }

        $best_price = $price;
        foreach ( $tiers as $tier ) {
            if ( $qty >= (int) $tier['min_qty'] ) {
                $best_price = (float) $tier['price'];
            }
        }
        return $best_price;
    }

    public static function validate_moq( bool $passed, int $product_id, int $quantity ): bool {
        if ( ! self::is_wholesaler() ) return $passed;
        $config = Phyto_WS_DB::get_product_config( $product_id );
        if ( ! $config || $config->moq <= 0 ) return $passed;
        if ( $quantity < (int) $config->moq ) {
            wc_add_notice( sprintf(
                __( 'Minimum order quantity for wholesale is %d.', 'phyto-wholesale' ),
                $config->moq
            ), 'error' );
            return false;
        }
        return $passed;
    }

    public static function check_visibility( bool $visible, int $product_id ): bool {
        $config = Phyto_WS_DB::get_product_config( $product_id );
        if ( $config && $config->wholesale_only && ! self::is_wholesaler() ) return false;
        return $visible;
    }

    public static function show_moq_notice(): void {
        global $product;
        if ( ! self::is_wholesaler() || ! $product ) return;
        $config = Phyto_WS_DB::get_product_config( $product->get_id() );
        if ( ! $config || $config->moq <= 0 ) return;
        echo '<p class="phyto-ws-moq-notice">' . sprintf( esc_html__( 'Wholesale minimum order: %d units', 'phyto-wholesale' ), (int) $config->moq ) . '</p>';
    }
}
