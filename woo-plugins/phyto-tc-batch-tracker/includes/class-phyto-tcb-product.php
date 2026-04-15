<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Product-side: show TC batch info on product pages + product edit screen.
 */
class Phyto_TCB_Product {

    public static function init(): void {
        // Show batch badge on single product page
        add_action( 'woocommerce_single_product_summary', [ __CLASS__, 'show_batch_badge' ], 35 );
        // Decrement batch units_remaining when order is processed
        add_action( 'woocommerce_order_status_processing', [ __CLASS__, 'decrement_on_order' ] );
        // Product edit: add batch linking field (General tab)
        add_action( 'woocommerce_product_options_general_product_data', [ __CLASS__, 'add_batch_field' ] );
        add_action( 'woocommerce_process_product_meta',                 [ __CLASS__, 'save_batch_field' ] );
    }

    public static function show_batch_badge(): void {
        global $product;
        if ( ! $product ) return;
        $batch = Phyto_TCB_DB::get_batch_by_product( $product->get_id() );
        if ( ! $batch ) return;
        echo '<p class="phyto-tcb-badge"><strong>' . esc_html__( 'TC Batch:', 'phyto-tc-batch' ) . '</strong> '
           . esc_html( $batch->batch_code ) . ' — ' . esc_html( $batch->generation ) . ' — ' . esc_html( $batch->batch_status ) . '</p>';
    }

    public static function decrement_on_order( int $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;
        global $wpdb;
        foreach ( $order->get_items() as $item ) {
            $product_id   = (int) $item->get_product_id();
            $variation_id = (int) $item->get_variation_id();
            $qty          = (int) $item->get_quantity();
            $batch        = Phyto_TCB_DB::get_batch_by_product( $product_id, $variation_id );
            if ( ! $batch ) continue;
            $new_remaining = max( 0, (int) $batch->units_remaining - $qty );
            $wpdb->update( $wpdb->prefix . 'phyto_tc_batch', [
                'units_remaining' => $new_remaining,
                'date_upd'        => current_time( 'mysql' ),
            ], [ 'id_batch' => $batch->id_batch ] );
        }
    }

    public static function add_batch_field(): void {
        global $post;
        $link = null;
        global $wpdb;
        $link = $wpdb->get_row( $wpdb->prepare(
            "SELECT id_batch FROM `{$wpdb->prefix}phyto_tc_batch_product` WHERE product_id=%d AND variation_id=0",
            $post->ID
        ) );
        $batches = Phyto_TCB_DB::get_batches( 'Active' );
        echo '<div class="options_group"><p class="form-field"><label>' . esc_html__( 'TC Batch', 'phyto-tc-batch' ) . '</label>';
        echo '<select name="phyto_tcb_batch_id"><option value="">' . esc_html__( '— None —', 'phyto-tc-batch' ) . '</option>';
        foreach ( $batches as $b ) {
            printf( '<option value="%d" %s>%s (%s)</option>',
                $b->id_batch, selected( $link ? $link->id_batch : 0, $b->id_batch, false ),
                esc_html( $b->batch_code ), esc_html( $b->species_name )
            );
        }
        echo '</select></p></div>';
    }

    public static function save_batch_field( int $post_id ): void {
        $batch_id = absint( $_POST['phyto_tcb_batch_id'] ?? 0 );
        if ( $batch_id ) {
            Phyto_TCB_DB::link_product( $post_id, 0, $batch_id );
        }
    }
}
