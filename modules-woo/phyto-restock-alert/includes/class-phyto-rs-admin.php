<?php
/**
 * Admin features for Phyto Restock Alert.
 *
 * - Meta box on product edit page listing all subscribers with timestamps,
 *   individual delete links, and a "Notify All Now" button.
 * - CSV export action for per-product subscriber list.
 * - "Subscribers" column on the Products list-table.
 *
 * @package PhytoRestockAlert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_RS_Admin
 */
class Phyto_RS_Admin {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		// Meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );

		// Products list-table column.
		add_filter( 'manage_product_posts_columns', array( $this, 'add_subscriber_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_subscriber_column' ), 10, 2 );

		// Admin AJAX: notify all.
		add_action( 'wp_ajax_phyto_rs_notify_now', array( $this, 'ajax_notify_now' ) );

		// Admin AJAX: delete subscriber.
		add_action( 'wp_ajax_phyto_rs_delete_subscriber', array( $this, 'ajax_delete_subscriber' ) );

		// Admin AJAX: CSV export.
		add_action( 'wp_ajax_phyto_rs_export_csv', array( $this, 'ajax_export_csv' ) );

		// Enqueue admin scripts on product edit screen.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin inline script for AJAX actions.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		global $post;
		if ( ! $post || 'product' !== $post->post_type ) {
			return;
		}

		wp_localize_script(
			'jquery',
			'phytoRsAdmin',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'phyto_rs_admin' ),
				'confirmDel' => __( 'Remove this subscriber?', 'phyto-restock-alert' ),
				'notifying'  => __( 'Sending notifications…', 'phyto-restock-alert' ),
				'done'       => __( 'Done! Notifications sent.', 'phyto-restock-alert' ),
				'error'      => __( 'Error — please try again.', 'phyto-restock-alert' ),
			)
		);
	}

	/**
	 * Register the "Restock Subscribers" meta box on the product edit screen.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto-restock-alert',
			__( 'Restock Subscribers', 'phyto-restock-alert' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the subscribers meta box.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_meta_box( $post ) {
		$product_id  = absint( $post->ID );
		$subscribers = Phyto_RS_DB::get_subscribers( $product_id );
		$count       = count( $subscribers );
		?>
		<div id="phyto-rs-meta-box" style="font-family:sans-serif;">

			<p>
				<strong>
					<?php
					/* translators: %d: subscriber count */
					echo esc_html( sprintf( _n( '%d subscriber', '%d subscribers', $count, 'phyto-restock-alert' ), $count ) );
					?>
				</strong>
			</p>

			<?php if ( $count > 0 ) : ?>
				<table class="widefat striped" style="margin-bottom:12px;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Email', 'phyto-restock-alert' ); ?></th>
							<th><?php esc_html_e( 'Subscribed', 'phyto-restock-alert' ); ?></th>
							<th><?php esc_html_e( 'Notified', 'phyto-restock-alert' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'phyto-restock-alert' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $subscribers as $row ) : ?>
							<tr id="phyto-rs-row-<?php echo absint( $row->id ); ?>">
								<td><?php echo esc_html( $row->email ); ?></td>
								<td><?php echo esc_html( $row->subscribed_at ); ?></td>
								<td>
									<?php
									if ( $row->notified_at ) {
										echo esc_html( $row->notified_at );
									} else {
										esc_html_e( '—', 'phyto-restock-alert' );
									}
									?>
								</td>
								<td>
									<a href="#"
										class="phyto-rs-delete"
										data-id="<?php echo absint( $row->id ); ?>"
										style="color:#b32d2e;"
									><?php esc_html_e( 'Delete', 'phyto-restock-alert' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<p>
				<button type="button"
					id="phyto-rs-notify-now"
					class="button button-primary"
					data-product="<?php echo absint( $product_id ); ?>"
					<?php disabled( 0, $count ); ?>
				>
					<?php esc_html_e( 'Notify All Now', 'phyto-restock-alert' ); ?>
				</button>

				&nbsp;

				<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=phyto_rs_export_csv&product_id=' . $product_id . '&_wpnonce=' . wp_create_nonce( 'phyto_rs_export_csv_' . $product_id ) ) ); ?>"
					class="button"
					<?php if ( 0 === $count ) { echo 'style="pointer-events:none;opacity:.5;"'; } ?>
				>
					<?php esc_html_e( 'Export CSV', 'phyto-restock-alert' ); ?>
				</a>
			</p>

			<span id="phyto-rs-notify-status" style="margin-left:8px;"></span>

		</div>

		<script>
		(function($){
			$(document).on('click', '.phyto-rs-delete', function(e){
				e.preventDefault();
				if ( ! confirm( phytoRsAdmin.confirmDel ) ) { return; }
				var $btn = $(this);
				var id   = $btn.data('id');
				$.post( phytoRsAdmin.ajaxUrl, {
					action: 'phyto_rs_delete_subscriber',
					id:      id,
					nonce:   phytoRsAdmin.nonce
				}, function( res ){
					if ( res.success ) {
						$('#phyto-rs-row-' + id).remove();
					}
				});
			});

			$('#phyto-rs-notify-now').on('click', function(){
				var $btn = $(this);
				$btn.prop('disabled', true);
				$('#phyto-rs-notify-status').text( phytoRsAdmin.notifying );
				$.post( phytoRsAdmin.ajaxUrl, {
					action:     'phyto_rs_notify_now',
					product_id: $btn.data('product'),
					nonce:      phytoRsAdmin.nonce
				}, function( res ){
					if ( res.success ) {
						$('#phyto-rs-notify-status').text( phytoRsAdmin.done );
					} else {
						$('#phyto-rs-notify-status').text( phytoRsAdmin.error );
						$btn.prop('disabled', false);
					}
				}).fail(function(){
					$('#phyto-rs-notify-status').text( phytoRsAdmin.error );
					$btn.prop('disabled', false);
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Add "Subscribers" column to the Products list-table.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_subscriber_column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'price' === $key ) {
				$new['phyto_rs_subscribers'] = __( 'Restock Subs', 'phyto-restock-alert' );
			}
		}
		if ( ! isset( $new['phyto_rs_subscribers'] ) ) {
			$new['phyto_rs_subscribers'] = __( 'Restock Subs', 'phyto-restock-alert' );
		}
		return $new;
	}

	/**
	 * Render the subscriber count badge in the Products list-table.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post / product ID.
	 */
	public function render_subscriber_column( $column, $post_id ) {
		if ( 'phyto_rs_subscribers' !== $column ) {
			return;
		}

		$count = Phyto_RS_DB::get_subscriber_count( $post_id );

		if ( $count > 0 ) {
			echo '<span style="display:inline-block;background:#2271b1;color:#fff;border-radius:10px;padding:2px 8px;font-size:11px;font-weight:700;">'
				. absint( $count )
				. '</span>';
		} else {
			echo '<span style="color:#999;">0</span>';
		}
	}

	/**
	 * AJAX: Trigger notification for all subscribers of a product.
	 */
	public function ajax_notify_now() {
		check_ajax_referer( 'phyto_rs_admin', 'nonce' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'phyto-restock-alert' ) ) );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'phyto-restock-alert' ) ) );
		}

		require_once PHYTO_RS_PATH . 'includes/class-phyto-rs-frontend.php';
		$frontend = new Phyto_RS_Frontend();
		$sent     = $frontend->notify_subscribers( $product_id );

		wp_send_json_success( array( 'sent' => $sent ) );
	}

	/**
	 * AJAX: Delete a single subscriber row.
	 */
	public function ajax_delete_subscriber() {
		check_ajax_referer( 'phyto_rs_admin', 'nonce' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'phyto-restock-alert' ) ) );
		}

		$id = absint( $_POST['id'] ?? 0 );
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID.', 'phyto-restock-alert' ) ) );
		}

		$result = Phyto_RS_DB::delete_subscriber( $id );
		if ( $result ) {
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Could not delete subscriber.', 'phyto-restock-alert' ) ) );
		}
	}

	/**
	 * AJAX: Stream a CSV export of all subscribers for a product.
	 */
	public function ajax_export_csv() {
		$product_id = absint( $_GET['product_id'] ?? 0 );

		if ( ! $product_id || ! check_admin_referer( 'phyto_rs_export_csv_' . $product_id ) ) {
			wp_die( esc_html__( 'Forbidden.', 'phyto-restock-alert' ), 403 );
		}

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'phyto-restock-alert' ), 403 );
		}

		$subscribers = Phyto_RS_DB::get_subscribers( $product_id );
		$product     = wc_get_product( $product_id );
		$product_name = $product ? $product->get_name() : 'product-' . $product_id;
		$filename    = 'restock-subscribers-' . sanitize_title( $product_name ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Excel compatibility.
		fputs( $out, "\xEF\xBB\xBF" );

		fputcsv( $out, array(
			__( 'ID', 'phyto-restock-alert' ),
			__( 'Product ID', 'phyto-restock-alert' ),
			__( 'Email', 'phyto-restock-alert' ),
			__( 'Subscribed At', 'phyto-restock-alert' ),
			__( 'Notified At', 'phyto-restock-alert' ),
		) );

		foreach ( $subscribers as $row ) {
			fputcsv( $out, array(
				$row->id,
				$row->product_id,
				$row->email,
				$row->subscribed_at,
				$row->notified_at ?? '',
			) );
		}

		fclose( $out );
		exit;
	}
}
