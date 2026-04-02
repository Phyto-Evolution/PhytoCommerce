<?php
/**
 * Frontend class for Phyto Collection Widget.
 *
 * @package PhytoCollectionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_CW_Frontend {

	public function register_hooks() {
		add_action( 'woocommerce_order_status_completed', array( $this, 'auto_add_on_purchase' ), 10, 1 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'render_product_badge' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Auto-add purchased products to collection when order completes.
	 */
	public function auto_add_on_purchase( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$customer_id   = $order->get_customer_id();
		$date_acquired = $order->get_date_created() ? $order->get_date_created()->format( 'Y-m-d' ) : gmdate( 'Y-m-d' );

		foreach ( $order->get_items() as $item ) {
			Phyto_CW_DB::add_or_update( $customer_id, $item->get_product_id(), $order_id, $date_acquired );
		}
	}

	/**
	 * Show "In your collection since [date]" badge on product page for owner.
	 */
	public function render_product_badge() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		global $product;
		if ( ! $product ) {
			return;
		}

		$customer_id = get_current_user_id();
		$item        = Phyto_CW_DB::get_by_product_customer( $customer_id, $product->get_id() );

		if ( ! $item ) {
			return;
		}

		$since = $item->date_acquired ? date_i18n( get_option( 'date_format' ), strtotime( $item->date_acquired ) ) : '';
		$url   = wc_get_account_endpoint_url( 'plant-collection' );
		?>
		<div class="phyto-cw-owned-badge">
			<span class="phyto-cw-owned-badge__icon">🌿</span>
			<span>
				<?php
				printf(
					/* translators: %s: date */
					esc_html__( 'In your collection since %s', 'phyto-collection-widget' ),
					'<strong>' . esc_html( $since ) . '</strong>'
				);
				?>
			</span>
			<a href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'View Collection →', 'phyto-collection-widget' ); ?></a>
		</div>
		<?php
	}

	public function enqueue_assets() {
		if ( ! is_product() && ! is_account_page() ) {
			return;
		}
		wp_enqueue_style( 'phyto-cw-frontend', PHYTO_CW_URL . 'assets/css/frontend.css', array(), PHYTO_CW_VERSION );
	}
}
