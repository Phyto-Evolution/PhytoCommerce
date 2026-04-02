<?php
/**
 * Admin class for Phyto Dispatch Logger.
 *
 * Registers the WooCommerce order meta box, the admin list-table page,
 * handles photo uploads, and provides CSV export.
 *
 * @package PhytoDispatchLogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_DL_Admin
 */
class Phyto_DL_Admin {

	/**
	 * Register all admin hooks.
	 */
	public function register_hooks() {
		// Meta box on order edit screen (classic and HPOS).
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_meta_box' ) );
		// HPOS order save hook.
		add_action( 'woocommerce_after_order_object_save', array( $this, 'save_meta_box_hpos' ) );

		// Admin menu page.
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );

		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// CSV export.
		add_action( 'admin_post_phyto_dl_export_csv', array( $this, 'export_csv' ) );
	}

	// ── Meta box ──────────────────────────────────────────────────────────────

	/**
	 * Register the Dispatch Conditions meta box on WooCommerce order pages.
	 */
	public function add_order_meta_box() {
		$screens = array( 'shop_order', 'woocommerce_page_wc-orders' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'phyto_dispatch_logger',
				__( 'Dispatch Conditions', 'phyto-dispatch-logger' ),
				array( $this, 'render_meta_box' ),
				$screen,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Render the Dispatch Conditions meta box content.
	 *
	 * @param WP_Post|WC_Order $post_or_order Post object (classic) or order (HPOS).
	 */
	public function render_meta_box( $post_or_order ) {
		$order_id = is_a( $post_or_order, 'WC_Order' )
			? $post_or_order->get_id()
			: $post_or_order->ID;

		$log = Phyto_DL_DB::get_by_order( $order_id );
		wp_nonce_field( 'phyto_dl_save_' . $order_id, 'phyto_dl_nonce' );

		$date     = $log ? esc_attr( $log->dispatch_date )  : '';
		$temp     = $log ? esc_attr( $log->temp_celsius )   : '';
		$hum      = $log ? esc_attr( $log->humidity_pct )   : '';
		$method   = $log ? esc_attr( $log->packing_method ) : '';
		$gel      = $log && $log->gel_pack  ? 'checked' : '';
		$heat     = $log && $log->heat_pack ? 'checked' : '';
		$transit  = $log ? esc_attr( $log->transit_days )   : '';
		$staff    = $log ? esc_attr( $log->staff_name )     : '';
		$notes    = $log ? esc_textarea( $log->notes )      : '';
		$photo    = $log ? esc_attr( $log->photo_filename ) : '';

		$upload_dir = wp_upload_dir();
		$photo_url  = '';
		if ( $photo ) {
			$photo_url = trailingslashit( $upload_dir['baseurl'] ) . 'phyto-dispatch/' . $photo;
		}
		?>
		<div class="phyto-dl-metabox">
			<div class="phyto-dl-row">
				<label><?php esc_html_e( 'Dispatch Date', 'phyto-dispatch-logger' ); ?></label>
				<input type="date" name="phyto_dl[dispatch_date]" value="<?php echo $date; ?>">
			</div>
			<div class="phyto-dl-row phyto-dl-row--two">
				<div>
					<label><?php esc_html_e( 'Temp (°C)', 'phyto-dispatch-logger' ); ?></label>
					<input type="number" step="0.1" name="phyto_dl[temp_celsius]" value="<?php echo $temp; ?>">
				</div>
				<div>
					<label><?php esc_html_e( 'Humidity (%)', 'phyto-dispatch-logger' ); ?></label>
					<input type="number" step="0.1" min="0" max="100" name="phyto_dl[humidity_pct]" value="<?php echo $hum; ?>">
				</div>
			</div>
			<div class="phyto-dl-row">
				<label><?php esc_html_e( 'Packing Method', 'phyto-dispatch-logger' ); ?></label>
				<input type="text" name="phyto_dl[packing_method]" value="<?php echo $method; ?>" placeholder="e.g. Box with moisture pack">
			</div>
			<div class="phyto-dl-row phyto-dl-row--checks">
				<label>
					<input type="checkbox" name="phyto_dl[gel_pack]" value="1" <?php echo $gel; ?>>
					<?php esc_html_e( 'Gel Pack', 'phyto-dispatch-logger' ); ?>
				</label>
				<label>
					<input type="checkbox" name="phyto_dl[heat_pack]" value="1" <?php echo $heat; ?>>
					<?php esc_html_e( 'Heat Pack', 'phyto-dispatch-logger' ); ?>
				</label>
			</div>
			<div class="phyto-dl-row phyto-dl-row--two">
				<div>
					<label><?php esc_html_e( 'Transit Days (est.)', 'phyto-dispatch-logger' ); ?></label>
					<input type="number" min="0" name="phyto_dl[transit_days]" value="<?php echo $transit; ?>">
				</div>
				<div>
					<label><?php esc_html_e( 'Staff Name', 'phyto-dispatch-logger' ); ?></label>
					<input type="text" name="phyto_dl[staff_name]" value="<?php echo $staff; ?>">
				</div>
			</div>
			<div class="phyto-dl-row">
				<label><?php esc_html_e( 'Notes', 'phyto-dispatch-logger' ); ?></label>
				<textarea name="phyto_dl[notes]" rows="3"><?php echo $notes; ?></textarea>
			</div>
			<div class="phyto-dl-row">
				<label><?php esc_html_e( 'Dispatch Photo', 'phyto-dispatch-logger' ); ?></label>
				<?php if ( $photo_url ) : ?>
					<img src="<?php echo esc_url( $photo_url ); ?>" style="max-width:160px;display:block;margin-bottom:6px;">
					<small><?php esc_html_e( 'Upload a new image to replace.', 'phyto-dispatch-logger' ); ?></small>
				<?php endif; ?>
				<input type="file" name="phyto_dl_photo" accept="image/*">
			</div>
		</div>
		<?php
	}

	/**
	 * Save meta box — classic orders.
	 *
	 * @param int $post_id WP post ID.
	 */
	public function save_meta_box( $post_id ) {
		$this->process_save( $post_id );
	}

	/**
	 * Save meta box — HPOS orders.
	 *
	 * @param WC_Order $order Order object.
	 */
	public function save_meta_box_hpos( $order ) {
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->process_save( $order->get_id() );
		}
	}

	/**
	 * Core save logic (shared by classic and HPOS paths).
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	private function process_save( $order_id ) {
		if ( ! isset( $_POST['phyto_dl_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phyto_dl_nonce'] ) ), 'phyto_dl_save_' . $order_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		$data = isset( $_POST['phyto_dl'] ) ? (array) $_POST['phyto_dl'] : array();
		$data['order_id'] = $order_id;

		// Handle photo upload.
		if ( ! empty( $_FILES['phyto_dl_photo']['tmp_name'] ) ) {
			$filename = $this->handle_photo_upload( $_FILES['phyto_dl_photo'] );
			if ( $filename ) {
				$data['photo_filename'] = $filename;
			}
		}

		Phyto_DL_DB::upsert( $data );
	}

	/**
	 * Move uploaded photo to the phyto-dispatch uploads folder.
	 *
	 * @param array $file $_FILES entry.
	 * @return string|false Filename (without path) on success, false on failure.
	 */
	private function handle_photo_upload( array $file ) {
		$upload_dir = wp_upload_dir();
		$dest_dir   = trailingslashit( $upload_dir['basedir'] ) . 'phyto-dispatch';

		if ( ! is_dir( $dest_dir ) ) {
			wp_mkdir_p( $dest_dir );
		}

		$ext      = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		$allowed  = array( 'jpg', 'jpeg', 'png', 'webp', 'gif' );

		if ( ! in_array( $ext, $allowed, true ) ) {
			return false;
		}

		$filename = 'dispatch-' . time() . '-' . wp_rand( 1000, 9999 ) . '.' . $ext;
		$dest     = $dest_dir . '/' . $filename;

		if ( move_uploaded_file( $file['tmp_name'], $dest ) ) {
			return $filename;
		}

		return false;
	}

	// ── Admin page ────────────────────────────────────────────────────────────

	/**
	 * Register the Dispatch Logs menu page under WooCommerce.
	 */
	public function add_menu_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Dispatch Logs', 'phyto-dispatch-logger' ),
			__( 'Dispatch Logs', 'phyto-dispatch-logger' ),
			'manage_woocommerce',
			'phyto-dispatch-logs',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Render the Dispatch Logs admin list page.
	 */
	public function render_admin_page() {
		$logs = Phyto_DL_DB::get_all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Dispatch Logs', 'phyto-dispatch-logger' ); ?></h1>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=phyto_dl_export_csv&_wpnonce=' . wp_create_nonce( 'phyto_dl_export' ) ) ); ?>" class="button">
					<?php esc_html_e( '⬇ Export CSV', 'phyto-dispatch-logger' ); ?>
				</a>
			</p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Order', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Date', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Staff', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Temp °C', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Humidity %', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Packing', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Gel', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Heat', 'phyto-dispatch-logger' ); ?></th>
						<th><?php esc_html_e( 'Transit', 'phyto-dispatch-logger' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $logs ) ) : ?>
					<tr><td colspan="9"><?php esc_html_e( 'No dispatch logs yet.', 'phyto-dispatch-logger' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $logs as $log ) :
						$edit_url = admin_url( 'post.php?post=' . absint( $log->order_id ) . '&action=edit' );
					?>
					<tr>
						<td><a href="<?php echo esc_url( $edit_url ); ?>">#<?php echo esc_html( $log->order_id ); ?></a></td>
						<td><?php echo esc_html( $log->dispatch_date ); ?></td>
						<td><?php echo esc_html( $log->staff_name ); ?></td>
						<td><?php echo esc_html( $log->temp_celsius ); ?></td>
						<td><?php echo esc_html( $log->humidity_pct ); ?></td>
						<td><?php echo esc_html( $log->packing_method ); ?></td>
						<td><?php echo $log->gel_pack  ? '✓' : '—'; ?></td>
						<td><?php echo $log->heat_pack ? '✓' : '—'; ?></td>
						<td><?php echo esc_html( $log->transit_days ); ?> days</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Stream all dispatch logs as a CSV file download.
	 */
	public function export_csv() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'phyto-dispatch-logger' ) );
		}

		check_admin_referer( 'phyto_dl_export' );

		$logs = Phyto_DL_DB::get_all();

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="dispatch-logs-' . gmdate( 'Y-m-d' ) . '.csv"' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'Order ID', 'Dispatch Date', 'Temp °C', 'Humidity %', 'Packing Method', 'Gel Pack', 'Heat Pack', 'Transit Days', 'Staff', 'Notes', 'Photo' ) );

		foreach ( $logs as $log ) {
			fputcsv( $out, array(
				$log->order_id,
				$log->dispatch_date,
				$log->temp_celsius,
				$log->humidity_pct,
				$log->packing_method,
				$log->gel_pack  ? 'Yes' : 'No',
				$log->heat_pack ? 'Yes' : 'No',
				$log->transit_days,
				$log->staff_name,
				$log->notes,
				$log->photo_filename,
			) );
		}

		fclose( $out );
		exit;
	}

	/**
	 * Enqueue admin CSS on relevant screens.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		$screens = array( 'post.php', 'post-new.php', 'woocommerce_page_wc-orders', 'woocommerce_page_phyto-dispatch-logs' );
		if ( ! in_array( $hook, $screens, true ) ) {
			return;
		}

		wp_enqueue_style(
			'phyto-dl-admin',
			PHYTO_DL_URL . 'assets/css/admin.css',
			array(),
			PHYTO_DL_VERSION
		);
	}
}
