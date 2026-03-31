<?php
/**
 * Admin class: product meta box for seasonal availability months + subscriber management page.
 *
 * @package PhytoSeasonalAvailability
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Seasonal_Admin
 *
 * Adds a "Seasonal Availability" meta box to the WooCommerce product editor
 * and a subscriber management sub-page under the WooCommerce admin menu.
 */
class Phyto_Seasonal_Admin {

	/**
	 * Month labels keyed by month number (1–12).
	 *
	 * @var array<int,string>
	 */
	private $months = array(
		1  => 'January',
		2  => 'February',
		3  => 'March',
		4  => 'April',
		5  => 'May',
		6  => 'June',
		7  => 'July',
		8  => 'August',
		9  => 'September',
		10 => 'October',
		11 => 'November',
		12 => 'December',
	);

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 1 );
		add_action( 'admin_menu', array( $this, 'subscribers_page' ) );
		add_action( 'admin_init', array( $this, 'export_csv' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Register the "Seasonal Availability" meta box on the product edit screen.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_sa_seasonal_availability',
			__( 'Seasonal Availability', 'phyto-seasonal-availability' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param WP_Post $post Current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_sa_save_meta', 'phyto_sa_nonce' );

		$selected_months = get_post_meta( $post->ID, '_phyto_sa_months', true );
		if ( ! is_array( $selected_months ) ) {
			$selected_months = array();
		}
		// Ensure integer comparison works correctly.
		$selected_months = array_map( 'intval', $selected_months );

		$message    = get_post_meta( $post->ID, '_phyto_sa_message', true );
		$year_round = (bool) get_post_meta( $post->ID, '_phyto_sa_year_round', true );

		if ( '' === $message ) {
			$message = __( 'This plant is not available for shipping this month.', 'phyto-seasonal-availability' );
		}
		?>
		<style>
			.phyto-sa-metabox { padding: 8px 0; }
			.phyto-sa-months { display: flex; flex-wrap: wrap; gap: 6px 12px; margin: 10px 0 16px; }
			.phyto-sa-months label { display: flex; align-items: center; gap: 4px; font-weight: normal; }
			.phyto-sa-year-round-row { margin-bottom: 12px; }
			.phyto-sa-metabox .description { color: #666; font-style: italic; margin-top: 4px; display: block; }
		</style>

		<div class="phyto-sa-metabox">

			<div class="phyto-sa-year-round-row">
				<label>
					<input type="checkbox"
						id="phyto_sa_year_round"
						name="phyto_sa_year_round"
						value="1"
						<?php checked( $year_round ); ?> />
					<?php esc_html_e( 'Available year-round (disables month selection)', 'phyto-seasonal-availability' ); ?>
				</label>
			</div>

			<p><strong><?php esc_html_e( 'Available months:', 'phyto-seasonal-availability' ); ?></strong></p>
			<div class="phyto-sa-months" id="phyto_sa_months_wrapper">
				<?php foreach ( $this->months as $num => $label ) : ?>
					<label>
						<input type="checkbox"
							name="phyto_sa_months[]"
							value="<?php echo esc_attr( $num ); ?>"
							<?php checked( in_array( $num, $selected_months, true ) ); ?>
							<?php disabled( $year_round ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<span class="description">
				<?php esc_html_e( 'Customers will only be able to purchase this product during the selected months. Leave all unchecked and "year-round" unticked to allow purchase any month.', 'phyto-seasonal-availability' ); ?>
			</span>

			<p style="margin-top:16px;">
				<label for="phyto_sa_message">
					<strong><?php esc_html_e( 'Unavailable message:', 'phyto-seasonal-availability' ); ?></strong>
				</label>
			</p>
			<textarea id="phyto_sa_message" name="phyto_sa_message" rows="3"
				style="width:100%;"><?php echo esc_textarea( $message ); ?></textarea>
			<span class="description">
				<?php esc_html_e( 'Shown to customers when the product is outside its available season.', 'phyto-seasonal-availability' ); ?>
			</span>

		</div>

		<script>
		(function () {
			var yearRound = document.getElementById('phyto_sa_year_round');
			var wrapper   = document.getElementById('phyto_sa_months_wrapper');
			if ( ! yearRound || ! wrapper ) { return; }

			function syncDisabled() {
				var checkboxes = wrapper.querySelectorAll('input[type="checkbox"]');
				checkboxes.forEach(function (cb) {
					cb.disabled = yearRound.checked;
				});
			}
			yearRound.addEventListener('change', syncDisabled);
		}());
		</script>
		<?php
	}

	/**
	 * Save meta box values on product save.
	 *
	 * @param int $post_id The product post ID.
	 */
	public function save_meta_box( $post_id ) {
		// Nonce verification.
		if ( ! isset( $_POST['phyto_sa_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phyto_sa_nonce'] ) ), 'phyto_sa_save_meta' ) ) {
			return;
		}

		// Autosave guard.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save available months (array of ints 1–12).
		$months = array();
		if ( isset( $_POST['phyto_sa_months'] ) && is_array( $_POST['phyto_sa_months'] ) ) {
			foreach ( $_POST['phyto_sa_months'] as $m ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
				$m = intval( $m );
				if ( $m >= 1 && $m <= 12 ) {
					$months[] = $m;
				}
			}
		}
		update_post_meta( $post_id, '_phyto_sa_months', $months );

		// Save custom unavailable message.
		$message = isset( $_POST['phyto_sa_message'] )
			? sanitize_textarea_field( wp_unslash( $_POST['phyto_sa_message'] ) )
			: '';
		update_post_meta( $post_id, '_phyto_sa_message', $message );

		// Save year-round flag.
		$year_round = isset( $_POST['phyto_sa_year_round'] ) ? 1 : 0;
		update_post_meta( $post_id, '_phyto_sa_year_round', $year_round );

		// If the product is now in season, send subscriber notifications.
		if ( ! $year_round && ! empty( $months ) ) {
			$current_month = (int) gmdate( 'n' );
			if ( in_array( $current_month, $months, true ) ) {
				if ( class_exists( 'Phyto_Seasonal_Subscribers' ) ) {
					$subs = new Phyto_Seasonal_Subscribers();
					$subs->send_notifications( $post_id );
				}
			}
		}
	}

	/**
	 * Register the "Seasonal Subscribers" sub-menu under WooCommerce.
	 */
	public function subscribers_page() {
		add_submenu_page(
			'woocommerce',
			__( 'Seasonal Subscribers', 'phyto-seasonal-availability' ),
			__( 'Seasonal Subscribers', 'phyto-seasonal-availability' ),
			'manage_woocommerce',
			'phyto-seasonal-subscribers',
			array( $this, 'render_subscribers_page' )
		);
	}

	/**
	 * Render the subscribers admin page.
	 */
	public function render_subscribers_page() {
		global $wpdb;

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'phyto-seasonal-availability' ) );
		}

		$table = $wpdb->prefix . 'phyto_seasonal_subscribers';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY subscribed_at DESC" );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Seasonal Availability — Subscribers', 'phyto-seasonal-availability' ); ?></h1>

			<p>
				<a href="<?php echo esc_url( add_query_arg( 'phyto_sa_export', '1' ) ); ?>"
					class="button button-secondary">
					<?php esc_html_e( 'Export CSV', 'phyto-seasonal-availability' ); ?>
				</a>
			</p>

			<?php if ( empty( $rows ) ) : ?>
				<p><?php esc_html_e( 'No subscribers yet.', 'phyto-seasonal-availability' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'phyto-seasonal-availability' ); ?></th>
							<th><?php esc_html_e( 'Email', 'phyto-seasonal-availability' ); ?></th>
							<th><?php esc_html_e( 'Subscribed', 'phyto-seasonal-availability' ); ?></th>
							<th><?php esc_html_e( 'Notified', 'phyto-seasonal-availability' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<td>
									<?php
									$product_title = get_the_title( (int) $row->product_id );
									if ( $product_title ) {
										echo '<a href="' . esc_url( get_edit_post_link( (int) $row->product_id ) ) . '">'
											. esc_html( $product_title ) . '</a>';
									} else {
										echo esc_html( '#' . $row->product_id );
									}
									?>
								</td>
								<td><?php echo esc_html( $row->email ); ?></td>
								<td><?php echo esc_html( $row->subscribed_at ); ?></td>
								<td>
									<?php
									echo $row->notified
										? esc_html__( 'Yes', 'phyto-seasonal-availability' )
										: esc_html__( 'No', 'phyto-seasonal-availability' );
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle CSV export request.
	 * Triggered by ?phyto_sa_export=1 on the subscribers page.
	 */
	public function export_csv() {
		if ( ! isset( $_GET['phyto_sa_export'] ) || '1' !== $_GET['phyto_sa_export'] ) {
			return;
		}
		if ( ! isset( $_GET['page'] ) || 'phyto-seasonal-subscribers' !== $_GET['page'] ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'phyto-seasonal-availability' ) );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'phyto_seasonal_subscribers';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY subscribed_at DESC", ARRAY_A );

		$filename = 'phyto-seasonal-subscribers-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'ID', 'Product ID', 'Product Name', 'Email', 'Subscribed At', 'Notified' ) );

		foreach ( $rows as $row ) {
			$product_title = get_the_title( (int) $row['product_id'] );
			fputcsv(
				$output,
				array(
					$row['id'],
					$row['product_id'],
					$product_title ? $product_title : '',
					$row['email'],
					$row['subscribed_at'],
					$row['notified'] ? 'Yes' : 'No',
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Enqueue admin-side scripts/styles if needed.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Currently no external admin assets required — inline styles handle meta box layout.
		// This method is kept for future use.
	}
}
