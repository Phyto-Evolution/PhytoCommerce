<?php
/**
 * Frontend class for Phyto Dispatch Logger.
 *
 * Displays a "Dispatch Conditions" summary card on the customer-facing
 * WooCommerce Order Details page when a dispatch log exists.
 *
 * @package PhytoDispatchLogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_DL_Frontend
 */
class Phyto_DL_Frontend {

	/**
	 * Register frontend hooks.
	 */
	public function register_hooks() {
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'render_dispatch_card' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render the dispatch conditions card on the order details page.
	 *
	 * @param WC_Order $order Current WooCommerce order.
	 */
	public function render_dispatch_card( $order ) {
		$log = Phyto_DL_DB::get_by_order( $order->get_id() );

		if ( ! $log ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$photo_url  = '';
		if ( ! empty( $log->photo_filename ) ) {
			$photo_url = trailingslashit( $upload_dir['baseurl'] ) . 'phyto-dispatch/' . esc_attr( $log->photo_filename );
		}
		?>
		<section class="phyto-dl-card">
			<h2 class="phyto-dl-card__title">
				<?php esc_html_e( '📦 Dispatch Conditions', 'phyto-dispatch-logger' ); ?>
			</h2>
			<table class="phyto-dl-card__table">
				<tr>
					<th><?php esc_html_e( 'Dispatch Date', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo esc_html( $log->dispatch_date ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Temperature', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo esc_html( $log->temp_celsius ); ?> °C</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Humidity', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo esc_html( $log->humidity_pct ); ?> %</td>
				</tr>
				<?php if ( ! empty( $log->packing_method ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Packing Method', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo esc_html( $log->packing_method ); ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'Packs Included', 'phyto-dispatch-logger' ); ?></th>
					<td>
						<?php
						$packs = array();
						if ( $log->gel_pack )  { $packs[] = esc_html__( 'Gel Pack', 'phyto-dispatch-logger' ); }
						if ( $log->heat_pack ) { $packs[] = esc_html__( 'Heat Pack', 'phyto-dispatch-logger' ); }
						echo $packs ? esc_html( implode( ', ', $packs ) ) : esc_html__( 'None', 'phyto-dispatch-logger' );
						?>
					</td>
				</tr>
				<?php if ( $log->transit_days ) : ?>
				<tr>
					<th><?php esc_html_e( 'Estimated Transit', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo esc_html( $log->transit_days ); ?> <?php esc_html_e( 'days', 'phyto-dispatch-logger' ); ?></td>
				</tr>
				<?php endif; ?>
				<?php if ( ! empty( $log->notes ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Notes', 'phyto-dispatch-logger' ); ?></th>
					<td><?php echo nl2br( esc_html( $log->notes ) ); ?></td>
				</tr>
				<?php endif; ?>
			</table>
			<?php if ( $photo_url ) : ?>
			<div class="phyto-dl-card__photo">
				<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php esc_attr_e( 'Dispatch photo', 'phyto-dispatch-logger' ); ?>">
			</div>
			<?php endif; ?>
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
			'phyto-dl-frontend',
			PHYTO_DL_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_DL_VERSION
		);
	}
}
