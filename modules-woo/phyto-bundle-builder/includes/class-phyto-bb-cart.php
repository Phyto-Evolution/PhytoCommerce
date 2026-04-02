<?php
/**
 * Cart integration — apply bundle discount when all slots are filled.
 *
 * Items are tagged with cart item data: phyto_bundle_id + phyto_bundle_disc
 * The discount is applied as a negative fee once per bundle group when the
 * number of items in that bundle matches the template's slot_count.
 *
 * @package PhytoBundleBuilder
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_BB_Cart {

	public function register_hooks() {
		add_filter( 'woocommerce_get_item_data',        array( $this, 'display_bundle_label' ), 10, 2 );
		add_action( 'woocommerce_cart_calculate_fees',  array( $this, 'apply_bundle_discount' ) );
	}

	/**
	 * Show a "Bundle" badge on cart line items.
	 */
	public function display_bundle_label( $item_data, $cart_item ) {
		if ( empty( $cart_item['phyto_bundle_id'] ) ) { return $item_data; }
		$template = Phyto_BB_DB::get_template( $cart_item['phyto_bundle_id'] );
		if ( $template ) {
			$item_data[] = array(
				'key'   => __( 'Bundle', 'phyto-bundle-builder' ),
				'value' => esc_html( $template->name ),
			);
		}
		return $item_data;
	}

	/**
	 * Apply discount fee when a full bundle is in the cart.
	 */
	public function apply_bundle_discount( $cart ) {
		$bundles = array(); // [ template_id => [ line_total, count ] ]

		foreach ( $cart->get_cart() as $item ) {
			if ( empty( $item['phyto_bundle_id'] ) || empty( $item['phyto_bundle_disc'] ) ) { continue; }
			$tid  = (int) $item['phyto_bundle_id'];
			$disc = (int) $item['phyto_bundle_disc'];
			if ( ! isset( $bundles[ $tid ] ) ) {
				$bundles[ $tid ] = array( 'total' => 0, 'count' => 0, 'disc' => $disc );
			}
			$bundles[ $tid ]['total'] += $item['line_total'];
			$bundles[ $tid ]['count']++;
		}

		foreach ( $bundles as $tid => $data ) {
			if ( $data['disc'] <= 0 ) { continue; }

			$template = Phyto_BB_DB::get_template( $tid );
			if ( ! $template ) { continue; }

			// Only apply discount when all slots are filled.
			if ( $data['count'] < (int) $template->slot_count ) { continue; }

			$amount = -( $data['total'] * $data['disc'] / 100 );
			$label  = sprintf(
				/* translators: 1: bundle name, 2: discount % */
				__( '%1$s Bundle Discount (%2$d%%)', 'phyto-bundle-builder' ),
				$template->name,
				$data['disc']
			);
			$cart->add_fee( $label, $amount );
		}
	}
}
