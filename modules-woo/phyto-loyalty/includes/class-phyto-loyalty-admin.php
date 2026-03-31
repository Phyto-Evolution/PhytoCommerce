<?php
/**
 * Admin features for Phyto Loyalty.
 *
 * - User profile meta box: balance display, manual adjust, transaction ledger.
 * - Orders list: "Points Earned" column.
 *
 * @package PhytoLoyalty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Loyalty_Admin
 */
class Phyto_Loyalty_Admin {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		// Enqueue admin CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// User edit screen meta box.
		add_action( 'show_user_profile', array( $this, 'render_user_section' ) );
		add_action( 'edit_user_profile', array( $this, 'render_user_section' ) );
		add_action( 'personal_options_update', array( $this, 'save_manual_adjust' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_manual_adjust' ) );

		// Orders list column.
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_order_column' ) );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_order_column' ), 10, 2 );

		// Legacy orders list (WC < 7.9).
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_order_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_order_column_legacy' ), 10, 2 );
	}

	/**
	 * Enqueue admin stylesheet.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'user-edit.php', 'profile.php' ), true ) ) {
			return;
		}
		wp_enqueue_style(
			'phyto-loyalty-admin',
			PHYTO_LOYALTY_URL . 'assets/css/admin.css',
			array(),
			PHYTO_LOYALTY_VERSION
		);
	}

	// -------------------------------------------------------------------------
	// User edit screen
	// -------------------------------------------------------------------------

	/**
	 * Render the loyalty meta box section on the user edit screen.
	 *
	 * @param \WP_User $user The user being edited.
	 */
	public function render_user_section( $user ) {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$balance  = Phyto_Loyalty_DB::get_balance( $user->ID );
		$ledger   = Phyto_Loyalty_DB::get_ledger( $user->ID, 50 );
		$label    = Phyto_Loyalty_Settings::get_label();
		$nonce    = wp_create_nonce( 'phyto_loyalty_manual_adjust_' . $user->ID );
		?>
		<div class="phyto-loyalty-admin-section">
			<h2><?php esc_html_e( 'Loyalty Points', 'phyto-loyalty' ); ?></h2>

			<table class="form-table phyto-loyalty-balance-table">
				<tr>
					<th><?php esc_html_e( 'Current Balance', 'phyto-loyalty' ); ?></th>
					<td>
						<strong><?php echo esc_html( number_format( $balance ) ); ?></strong>
						<?php echo esc_html( $label ); ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Manual Adjustment', 'phyto-loyalty' ); ?></th>
					<td>
						<input
							type="hidden"
							name="phyto_loyalty_manual_nonce"
							value="<?php echo esc_attr( $nonce ); ?>"
						/>
						<label for="phyto_loyalty_adjust_points">
							<?php esc_html_e( 'Points (use negative to deduct):', 'phyto-loyalty' ); ?>
						</label>
						<input
							type="number"
							id="phyto_loyalty_adjust_points"
							name="phyto_loyalty_adjust_points"
							value=""
							step="1"
							class="small-text"
						/>
						<br /><br />
						<label for="phyto_loyalty_adjust_note">
							<?php esc_html_e( 'Reason / Note:', 'phyto-loyalty' ); ?>
						</label>
						<input
							type="text"
							id="phyto_loyalty_adjust_note"
							name="phyto_loyalty_adjust_note"
							value=""
							class="regular-text"
						/>
						<p class="description">
							<?php esc_html_e( 'Leave Points blank to skip adjustment.', 'phyto-loyalty' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php if ( ! empty( $ledger ) ) : ?>
			<h3><?php esc_html_e( 'Transaction Ledger', 'phyto-loyalty' ); ?></h3>
			<table class="widefat phyto-loyalty-ledger-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Action', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Points', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Order', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Note', 'phyto-loyalty' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $ledger as $row ) : ?>
					<tr>
						<td><?php echo esc_html( $row->created_at ); ?></td>
						<td><?php echo esc_html( ucfirst( $row->action ) ); ?></td>
						<td class="<?php echo $row->points >= 0 ? 'phyto-loyalty-positive' : 'phyto-loyalty-negative'; ?>">
							<?php echo esc_html( ( $row->points >= 0 ? '+' : '' ) . number_format( $row->points ) ); ?>
						</td>
						<td>
							<?php if ( $row->order_id ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $row->order_id ) ); ?>">
									#<?php echo esc_html( $row->order_id ); ?>
								</a>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( $row->note ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save a manual point adjustment submitted from the user edit screen.
	 *
	 * @param int $user_id The user being updated.
	 */
	public function save_manual_adjust( $user_id ) {
		if ( ! isset( $_POST['phyto_loyalty_manual_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phyto_loyalty_manual_nonce'] ) ), 'phyto_loyalty_manual_adjust_' . $user_id ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$points = isset( $_POST['phyto_loyalty_adjust_points'] ) ? intval( $_POST['phyto_loyalty_adjust_points'] ) : 0;

		if ( 0 === $points ) {
			return;
		}

		$note = isset( $_POST['phyto_loyalty_adjust_note'] )
			? sanitize_text_field( wp_unslash( $_POST['phyto_loyalty_adjust_note'] ) )
			: '';

		if ( empty( $note ) ) {
			$note = __( 'Manual admin adjustment', 'phyto-loyalty' );
		}

		Phyto_Loyalty_DB::add_entry( $user_id, $points, 'manual', null, $note );
	}

	// -------------------------------------------------------------------------
	// Orders list column
	// -------------------------------------------------------------------------

	/**
	 * Add the "Points Earned" column to the orders list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_order_column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'order_total' === $key ) {
				$new['phyto_loyalty_points'] = __( 'Points Earned', 'phyto-loyalty' );
			}
		}
		return $new;
	}

	/**
	 * Render the "Points Earned" column value (HPOS orders).
	 *
	 * @param string    $column  Column key.
	 * @param \WC_Order $order   WooCommerce order object.
	 */
	public function render_order_column( $column, $order ) {
		if ( 'phyto_loyalty_points' !== $column ) {
			return;
		}

		$this->output_order_points( $order->get_id() );
	}

	/**
	 * Render the "Points Earned" column value (legacy CPT orders).
	 *
	 * @param string $column   Column key.
	 * @param int    $post_id  Post/order ID.
	 */
	public function render_order_column_legacy( $column, $post_id ) {
		if ( 'phyto_loyalty_points' !== $column ) {
			return;
		}

		$this->output_order_points( $post_id );
	}

	/**
	 * Echo the points earned for a given order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	private function output_order_points( $order_id ) {
		$rows = Phyto_Loyalty_DB::get_by_order( $order_id );

		$earned = 0;
		foreach ( $rows as $row ) {
			if ( 'earn' === $row->action ) {
				$earned += (int) $row->points;
			}
		}

		if ( $earned > 0 ) {
			echo '<span class="phyto-loyalty-order-pts">+' . esc_html( number_format( $earned ) ) . '</span>';
		} else {
			echo '&mdash;';
		}
	}
}
