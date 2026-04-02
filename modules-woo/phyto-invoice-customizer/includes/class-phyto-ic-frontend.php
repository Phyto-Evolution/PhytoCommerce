<?php
/**
 * Frontend class for Phyto Invoice Customizer.
 *
 * Injects the LAG banner on the customer-facing Order Details page.
 *
 * @package PhytoInvoiceCustomizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_IC_Frontend
 */
class Phyto_IC_Frontend {

	/**
	 * Register frontend hooks.
	 */
	public function register_hooks() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'render_lag_banner' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render the LAG banner on the order details page.
	 *
	 * @param WC_Order $order Current order.
	 */
	public function render_lag_banner( $order ) {
		if ( ! Phyto_IC_Settings::show_lag() ) {
			return;
		}

		?>
		<section class="phyto-ic-lag-banner">
			<div class="phyto-ic-lag-banner__icon">🌿</div>
			<div class="phyto-ic-lag-banner__body">
				<strong><?php esc_html_e( 'Live Arrival Guarantee', 'phyto-invoice-customizer' ); ?></strong>
				<p><?php echo esc_html( Phyto_IC_Settings::get_lag_text() ); ?></p>
			</div>
		</section>
		<?php
	}

	/**
	 * Enqueue frontend stylesheet on order details pages.
	 */
	public function enqueue_assets() {
		if ( ! is_wc_endpoint_url( 'view-order' ) ) {
			return;
		}

		wp_enqueue_style(
			'phyto-ic-frontend',
			PHYTO_IC_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_IC_VERSION
		);
	}
}
