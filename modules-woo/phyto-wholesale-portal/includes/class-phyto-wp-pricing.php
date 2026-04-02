<?php
/**
 * Per-product MOQ and tiered pricing for wholesale customers.
 *
 * Post meta keys:
 *   _phyto_ws_enabled  — '1' if wholesale pricing is active for this product
 *   _phyto_ws_moq      — minimum order quantity (int)
 *   _phyto_ws_tiers    — JSON array of {qty, price} objects, ascending qty
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_Pricing {

	public function register_hooks() {
		// Show wholesale price on product page
		add_filter( 'woocommerce_get_price_html',     array( $this, 'filter_price_html' ), 20, 2 );
		// Apply wholesale price in cart
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'apply_cart_prices' ), 20 );
		// Enforce MOQ
		add_filter( 'woocommerce_add_to_cart_validation',  array( $this, 'enforce_moq' ), 10, 5 );
		// Meta box
		add_action( 'woocommerce_product_options_pricing', array( $this, 'render_pricing_fields' ) );
		add_action( 'woocommerce_process_product_meta',    array( $this, 'save_pricing_fields' ) );
	}

	public function filter_price_html( $price_html, $product ) {
		if ( ! Phyto_WP_Roles::is_wholesale() ) { return $price_html; }
		$ws_price = $this->get_ws_price( $product->get_id(), 1 );
		if ( $ws_price === null ) { return $price_html; }
		/* translators: %s: formatted price */
		return '<span class="phyto-ws-price">' . sprintf( __( 'Wholesale: %s', 'phyto-wholesale-portal' ), wc_price( $ws_price ) ) . '</span>';
	}

	public function apply_cart_prices( $cart ) {
		if ( ! Phyto_WP_Roles::is_wholesale() ) { return; }
		foreach ( $cart->get_cart() as $item ) {
			$ws_price = $this->get_ws_price( $item['product_id'], $item['quantity'] );
			if ( $ws_price !== null ) {
				$item['data']->set_price( $ws_price );
			}
		}
	}

	public function enforce_moq( $passed, $product_id, $qty, $variation_id = 0, $variations = array() ) {
		if ( ! Phyto_WP_Roles::is_wholesale() ) { return $passed; }
		$enabled = get_post_meta( $product_id, '_phyto_ws_enabled', true );
		if ( ! $enabled ) { return $passed; }
		$moq = (int) get_post_meta( $product_id, '_phyto_ws_moq', true );
		if ( $moq > 1 && $qty < $moq ) {
			wc_add_notice( sprintf(
				/* translators: %d: minimum quantity */
				__( 'Minimum wholesale order quantity for this product is %d.', 'phyto-wholesale-portal' ),
				$moq
			), 'error' );
			return false;
		}
		return $passed;
	}

	/**
	 * Get the wholesale price for a product at a given quantity.
	 *
	 * @param int $product_id
	 * @param int $qty
	 * @return float|null Null if no wholesale pricing configured.
	 */
	private function get_ws_price( $product_id, $qty ) {
		if ( ! get_post_meta( $product_id, '_phyto_ws_enabled', true ) ) { return null; }
		$tiers_json = get_post_meta( $product_id, '_phyto_ws_tiers', true );
		if ( ! $tiers_json ) { return null; }
		$tiers = json_decode( $tiers_json, true );
		if ( ! is_array( $tiers ) || empty( $tiers ) ) { return null; }

		$applicable = null;
		foreach ( $tiers as $tier ) {
			if ( $qty >= (int) $tier['qty'] ) {
				$applicable = (float) $tier['price'];
			}
		}
		return $applicable;
	}

	public function render_pricing_fields() {
		global $post;
		$enabled = get_post_meta( $post->ID, '_phyto_ws_enabled', true );
		$moq     = get_post_meta( $post->ID, '_phyto_ws_moq', true ) ?: 1;
		$tiers   = get_post_meta( $post->ID, '_phyto_ws_tiers', true ) ?: '[]';
		?>
		<div class="options_group phyto-ws-pricing">
			<p class="form-field">
				<label><?php esc_html_e( 'Wholesale Pricing', 'phyto-wholesale-portal' ); ?></label>
				<input type="checkbox" id="_phyto_ws_enabled" name="_phyto_ws_enabled" value="1" <?php checked( $enabled, '1' ); ?> />
				<span><?php esc_html_e( 'Enable wholesale pricing for this product', 'phyto-wholesale-portal' ); ?></span>
			</p>
			<p class="form-field">
				<label for="_phyto_ws_moq"><?php esc_html_e( 'Min. Order Qty (MOQ)', 'phyto-wholesale-portal' ); ?></label>
				<input type="number" id="_phyto_ws_moq" name="_phyto_ws_moq" value="<?php echo esc_attr( $moq ); ?>" min="1" class="short" />
			</p>
			<p class="form-field">
				<label><?php esc_html_e( 'Price Tiers (qty → price)', 'phyto-wholesale-portal' ); ?></label>
				<span class="description"><?php esc_html_e( 'JSON array: [{"qty":1,"price":10},{"qty":10,"price":8}]', 'phyto-wholesale-portal' ); ?></span>
				<textarea id="_phyto_ws_tiers" name="_phyto_ws_tiers" style="width:100%;height:80px"><?php echo esc_textarea( $tiers ); ?></textarea>
			</p>
		</div>
		<?php
	}

	public function save_pricing_fields( $post_id ) {
		$enabled = isset( $_POST['_phyto_ws_enabled'] ) ? '1' : '';
		update_post_meta( $post_id, '_phyto_ws_enabled', $enabled );
		update_post_meta( $post_id, '_phyto_ws_moq', absint( $_POST['_phyto_ws_moq'] ?? 1 ) );

		$tiers_raw = wp_unslash( $_POST['_phyto_ws_tiers'] ?? '[]' );
		$tiers     = json_decode( $tiers_raw, true );
		update_post_meta( $post_id, '_phyto_ws_tiers', $tiers ? wp_json_encode( $tiers ) : '[]' );
	}
}
