<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Restock_Notifier {

    public static function init(): void {
        // Fires when WooCommerce stock transitions from 0 → positive
        add_action( 'woocommerce_product_set_stock',           [ __CLASS__, 'on_stock_change' ] );
        add_action( 'woocommerce_variation_set_stock',         [ __CLASS__, 'on_variation_stock_change' ] );
    }

    public static function on_stock_change( WC_Product $product ): void {
        if ( $product->get_stock_quantity() <= 0 ) return;
        self::dispatch( $product->get_id(), 0 );
    }

    public static function on_variation_stock_change( WC_Product $variation ): void {
        if ( $variation->get_stock_quantity() <= 0 ) return;
        self::dispatch( $variation->get_parent_id(), $variation->get_id() );
    }

    private static function dispatch( int $product_id, int $variation_id ): void {
        $settings    = get_option( 'phyto_restock_settings', [] );
        $max         = (int) ( $settings['max_per_run'] ?? 50 );
        $subscribers = Phyto_Restock_DB::get_subscribers( $product_id, $variation_id );

        if ( empty( $subscribers ) ) return;

        $product   = wc_get_product( $variation_id ?: $product_id );
        $shop_name = get_bloginfo( 'name' );
        $from_name = ! empty( $settings['from_name'] ) ? $settings['from_name'] : $shop_name;
        $from_mail = get_option( 'woocommerce_email_from_address' ) ?: get_option( 'admin_email' );

        $notified = [];
        $count    = 0;

        foreach ( $subscribers as $sub ) {
            if ( $count >= $max ) break;

            $subject = sprintf( __( '[%s] Back in stock: %s', 'phyto-restock-alert' ), $shop_name, $product->get_name() );
            $body    = self::build_email( $sub, $product, $shop_name );

            $headers = [
                "Content-Type: text/html; charset=UTF-8",
                "From: {$from_name} <{$from_mail}>",
            ];

            wp_mail( $sub->email, $subject, $body, $headers );
            $notified[] = (int) $sub->id_alert;
            $count++;
        }

        Phyto_Restock_DB::mark_notified( $notified );
    }

    private static function build_email( object $sub, WC_Product $product, string $shop_name ): string {
        $name    = ! empty( $sub->firstname ) ? esc_html( $sub->firstname ) : __( 'there', 'phyto-restock-alert' );
        $link    = esc_url( get_permalink( $product->get_id() ) );
        $img     = esc_url( wp_get_attachment_url( $product->get_image_id() ) );
        $pname   = esc_html( $product->get_name() );
        $price   = wc_price( $product->get_price() );

        ob_start();
        include PHYTO_RESTOCK_DIR . 'templates/email-restock.php';
        return ob_get_clean();
    }
}
