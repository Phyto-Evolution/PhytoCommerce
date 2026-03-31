<?php
/**
 * Front-end features for Phyto Loyalty.
 *
 * - My Account "My Points" tab.
 * - Cart redeem block (AJAX apply / remove).
 * - Checkout earn preview.
 * - Order completion → credit points.
 * - Order refund → deduct points.
 *
 * @package PhytoLoyalty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Loyalty_Frontend
 */
class Phyto_Loyalty_Frontend {

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		// Assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// My Account tab.
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_tab' ) );
		add_action( 'woocommerce_account_my-points_endpoint', array( $this, 'render_my_points_page' ) );
		add_action( 'init', array( $this, 'register_endpoint' ) );

		// Cart redeem block.
		add_action( 'woocommerce_cart_totals_before_order_total', array( $this, 'render_cart_redeem_block' ) );

		// Cart fee for applied points.
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_points_fee' ) );

		// Checkout earn preview.
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'render_checkout_earn_preview' ) );

		// Order lifecycle.
		add_action( 'woocommerce_order_status_completed', array( $this, 'credit_points_on_complete' ), 10, 1 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'deduct_points_on_refund' ), 10, 1 );

		// AJAX handlers.
		add_action( 'wp_ajax_phyto_loyalty_apply_points', array( $this, 'ajax_apply_points' ) );
		add_action( 'wp_ajax_phyto_loyalty_remove_points', array( $this, 'ajax_remove_points' ) );
	}

	/**
	 * Enqueue front-end assets.
	 */
	public function enqueue_assets() {
		if ( ! is_woocommerce() && ! is_account_page() && ! is_cart() && ! is_checkout() ) {
			return;
		}

		wp_enqueue_style(
			'phyto-loyalty-frontend',
			PHYTO_LOYALTY_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_LOYALTY_VERSION
		);

		wp_enqueue_script(
			'phyto-loyalty-frontend',
			PHYTO_LOYALTY_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			PHYTO_LOYALTY_VERSION,
			true
		);

		wp_localize_script(
			'phyto-loyalty-frontend',
			'phytoLoyalty',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'applyNonce'    => wp_create_nonce( 'phyto_loyalty_apply' ),
				'removeNonce'   => wp_create_nonce( 'phyto_loyalty_remove' ),
				'label'         => Phyto_Loyalty_Settings::get_label(),
				'applying'      => __( 'Applying…', 'phyto-loyalty' ),
				'removing'      => __( 'Removing…', 'phyto-loyalty' ),
			)
		);
	}

	// -------------------------------------------------------------------------
	// My Account tab
	// -------------------------------------------------------------------------

	/**
	 * Register the My Points query var / endpoint.
	 */
	public function register_endpoint() {
		add_rewrite_endpoint( 'my-points', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add "My Points" to the My Account menu.
	 *
	 * @param array $items Existing menu items.
	 * @return array Modified menu items.
	 */
	public function add_account_tab( $items ) {
		$label = Phyto_Loyalty_Settings::get_label();
		$new   = array();

		foreach ( $items as $key => $value ) {
			$new[ $key ] = $value;
			if ( 'orders' === $key ) {
				$new['my-points'] = sprintf(
					/* translators: %s: points label */
					__( 'My %s', 'phyto-loyalty' ),
					$label
				);
			}
		}

		return $new;
	}

	/**
	 * Render the My Points page content.
	 */
	public function render_my_points_page() {
		$user_id  = get_current_user_id();
		$balance  = Phyto_Loyalty_DB::get_balance( $user_id );
		$ledger   = Phyto_Loyalty_DB::get_ledger( $user_id, 20 );
		$label    = Phyto_Loyalty_Settings::get_label();
		$earn_rate   = Phyto_Loyalty_Settings::get_earn_rate();
		$redeem_rate = Phyto_Loyalty_Settings::get_redeem_rate();
		$min_redeem  = Phyto_Loyalty_Settings::get_min_redeem();
		?>
		<div class="phyto-loyalty-my-points">

			<div class="phyto-loyalty-balance-card">
				<span class="phyto-loyalty-balance-number"><?php echo esc_html( number_format( $balance ) ); ?></span>
				<span class="phyto-loyalty-balance-label"><?php echo esc_html( $label ); ?></span>
			</div>

			<div class="phyto-loyalty-how-to-earn">
				<h3><?php esc_html_e( 'How to Earn Points', 'phyto-loyalty' ); ?></h3>
				<ul>
					<li>
						<?php
						printf(
							/* translators: 1: points per rupee, 2: points label */
							esc_html__( 'Earn %1$s %2$s for every ₹10 spent on your order.', 'phyto-loyalty' ),
							esc_html( number_format( $earn_rate * 10, 0 ) ),
							esc_html( $label )
						);
						?>
					</li>
					<li>
						<?php
						printf(
							/* translators: 1: minimum points, 2: points label, 3: rupee value */
							esc_html__( 'Redeem at least %1$s %2$s — each point is worth ₹%3$s.', 'phyto-loyalty' ),
							esc_html( number_format( $min_redeem ) ),
							esc_html( $label ),
							esc_html( number_format( $redeem_rate, 2 ) )
						);
						?>
					</li>
					<li><?php esc_html_e( 'Points are credited when your order is marked Complete.', 'phyto-loyalty' ); ?></li>
					<li><?php esc_html_e( 'Points are deducted if an order is fully refunded.', 'phyto-loyalty' ); ?></li>
				</ul>
			</div>

			<?php if ( ! empty( $ledger ) ) : ?>
			<h3><?php esc_html_e( 'Recent Transactions', 'phyto-loyalty' ); ?></h3>
			<table class="woocommerce-orders-table woocommerce-MyAccount-orders phyto-loyalty-ledger-table shop_table shop_table_responsive">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Activity', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Points', 'phyto-loyalty' ); ?></th>
						<th><?php esc_html_e( 'Note', 'phyto-loyalty' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $ledger as $row ) : ?>
					<tr>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $row->created_at ) ) ); ?></td>
						<td><?php echo esc_html( ucfirst( $row->action ) ); ?></td>
						<td class="<?php echo $row->points >= 0 ? 'phyto-loyalty-positive' : 'phyto-loyalty-negative'; ?>">
							<?php echo esc_html( ( $row->points >= 0 ? '+' : '' ) . number_format( $row->points ) ); ?>
						</td>
						<td><?php echo esc_html( $row->note ); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
			<p><?php esc_html_e( 'No transactions yet. Complete an order to earn your first points!', 'phyto-loyalty' ); ?></p>
			<?php endif; ?>

		</div>
		<?php
	}

	// -------------------------------------------------------------------------
	// Cart redeem block
	// -------------------------------------------------------------------------

	/**
	 * Render the redeem-points block below cart totals.
	 */
	public function render_cart_redeem_block() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();

		/**
		 * Filter whether a user can redeem points at checkout.
		 *
		 * @param bool $can_redeem Whether the user is allowed to redeem.
		 * @param int  $user_id    WordPress user ID.
		 */
		$can_redeem = (bool) apply_filters( 'phyto_loyalty_can_redeem', true, $user_id );

		if ( ! $can_redeem ) {
			return;
		}

		$balance     = Phyto_Loyalty_DB::get_balance( $user_id );
		$min_redeem  = Phyto_Loyalty_Settings::get_min_redeem();
		$label       = Phyto_Loyalty_Settings::get_label();
		$redeem_rate = Phyto_Loyalty_Settings::get_redeem_rate();
		$applied     = (int) WC()->session->get( 'phyto_loyalty_applied_points', 0 );

		?>
		<tr class="phyto-loyalty-redeem-row">
			<th colspan="2">
				<div class="phyto-loyalty-redeem-block">
					<strong><?php esc_html_e( 'Redeem Points', 'phyto-loyalty' ); ?></strong>
					<p class="phyto-loyalty-balance-line">
						<?php
						printf(
							/* translators: 1: balance, 2: points label */
							esc_html__( 'Your balance: %1$s %2$s', 'phyto-loyalty' ),
							'<span id="phyto-loyalty-balance">' . esc_html( number_format( $balance ) ) . '</span>',
							esc_html( $label )
						);
						?>
					</p>

					<?php if ( $applied > 0 ) : ?>
						<p class="phyto-loyalty-applied-msg">
							<?php
							printf(
								/* translators: 1: applied points, 2: points label, 3: discount amount */
								esc_html__( '%1$s %2$s applied (₹%3$s discount).', 'phyto-loyalty' ),
								esc_html( number_format( $applied ) ),
								esc_html( $label ),
								esc_html( number_format( $applied * $redeem_rate, 2 ) )
							);
							?>
						</p>
						<button type="button" id="phyto-loyalty-remove-btn" class="button">
							<?php esc_html_e( 'Remove Points', 'phyto-loyalty' ); ?>
						</button>
					<?php elseif ( $balance >= $min_redeem ) : ?>
						<div class="phyto-loyalty-apply-form">
							<input
								type="number"
								id="phyto-loyalty-points-input"
								min="<?php echo esc_attr( $min_redeem ); ?>"
								max="<?php echo esc_attr( $balance ); ?>"
								step="1"
								placeholder="<?php echo esc_attr( $min_redeem ); ?>"
							/>
							<button type="button" id="phyto-loyalty-apply-btn" class="button">
								<?php esc_html_e( 'Apply Points', 'phyto-loyalty' ); ?>
							</button>
						</div>
						<p class="phyto-loyalty-hint">
							<?php
							printf(
								/* translators: 1: min points, 2: points label */
								esc_html__( 'Minimum %1$s %2$s required to redeem.', 'phyto-loyalty' ),
								esc_html( number_format( $min_redeem ) ),
								esc_html( $label )
							);
							?>
						</p>
					<?php else : ?>
						<p class="phyto-loyalty-hint">
							<?php
							printf(
								/* translators: 1: min points, 2: points label */
								esc_html__( 'Earn %1$s %2$s to start redeeming.', 'phyto-loyalty' ),
								esc_html( number_format( $min_redeem ) ),
								esc_html( $label )
							);
							?>
						</p>
					<?php endif; ?>

					<p id="phyto-loyalty-message" class="phyto-loyalty-msg" style="display:none;"></p>
				</div>
			</th>
		</tr>
		<?php
	}

	/**
	 * Add the points discount as a negative cart fee.
	 *
	 * @param \WC_Cart $cart The current cart.
	 */
	public function apply_points_fee( $cart ) {
		$applied = (int) WC()->session->get( 'phyto_loyalty_applied_points', 0 );

		if ( $applied <= 0 ) {
			return;
		}

		$redeem_rate  = Phyto_Loyalty_Settings::get_redeem_rate();
		$max_pct      = Phyto_Loyalty_Settings::get_max_redeem_pct();
		$cart_total   = $cart->get_subtotal();
		$max_discount = $cart_total * ( $max_pct / 100 );
		$discount     = min( $applied * $redeem_rate, $max_discount );

		if ( $discount > 0 ) {
			$cart->add_fee(
				sprintf(
					/* translators: %s: points label */
					__( '%s Discount', 'phyto-loyalty' ),
					Phyto_Loyalty_Settings::get_label()
				),
				-$discount,
				false,
				'',
				'phyto_loyalty_discount'
			);
		}
	}

	// -------------------------------------------------------------------------
	// Checkout earn preview
	// -------------------------------------------------------------------------

	/**
	 * Display how many points will be earned on this order at checkout.
	 */
	public function render_checkout_earn_preview() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$cart  = WC()->cart;
		$total = $cart ? $cart->get_subtotal() : 0;

		if ( $total <= 0 ) {
			return;
		}

		// Fake WC_Order just to pass into calculate_earn; use subtotal for estimate.
		$points = (int) floor( $total * Phyto_Loyalty_Settings::get_earn_rate() );
		$label  = Phyto_Loyalty_Settings::get_label();

		if ( $points > 0 ) {
			?>
			<div class="phyto-loyalty-checkout-earn">
				<?php
				printf(
					/* translators: 1: points, 2: points label */
					esc_html__( 'You will earn %1$s %2$s for this order.', 'phyto-loyalty' ),
					'<strong>' . esc_html( number_format( $points ) ) . '</strong>',
					esc_html( $label )
				);
				?>
			</div>
			<?php
		}
	}

	// -------------------------------------------------------------------------
	// Order lifecycle
	// -------------------------------------------------------------------------

	/**
	 * Credit points when an order is marked Complete.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function credit_points_on_complete( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Avoid double-crediting.
		if ( $order->get_meta( '_phyto_loyalty_points_credited' ) ) {
			return;
		}

		$user_id = $order->get_customer_id();

		if ( ! $user_id ) {
			return;
		}

		$total  = (float) $order->get_total();
		$points = Phyto_Loyalty_Settings::calculate_earn( $total, $order );

		if ( $points > 0 ) {
			Phyto_Loyalty_DB::add_entry(
				$user_id,
				$points,
				'earn',
				$order_id,
				sprintf(
					/* translators: %s: order number */
					__( 'Points earned for Order #%s', 'phyto-loyalty' ),
					$order->get_order_number()
				)
			);
			$order->update_meta_data( '_phyto_loyalty_points_credited', $points );
			$order->save();
		}

		// Clear any redeemed session points (they were already applied as a fee).
		WC()->session->set( 'phyto_loyalty_applied_points', 0 );
	}

	/**
	 * Deduct points when an order is refunded.
	 *
	 * @param int $order_id WooCommerce order ID.
	 */
	public function deduct_points_on_refund( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Only deduct if points were previously credited.
		$credited = (int) $order->get_meta( '_phyto_loyalty_points_credited' );

		if ( $credited <= 0 ) {
			return;
		}

		// Avoid double-deducting.
		if ( $order->get_meta( '_phyto_loyalty_points_deducted' ) ) {
			return;
		}

		$user_id = $order->get_customer_id();

		if ( ! $user_id ) {
			return;
		}

		Phyto_Loyalty_DB::add_entry(
			$user_id,
			-$credited,
			'redeem',
			$order_id,
			sprintf(
				/* translators: %s: order number */
				__( 'Points deducted for refunded Order #%s', 'phyto-loyalty' ),
				$order->get_order_number()
			)
		);

		$order->update_meta_data( '_phyto_loyalty_points_deducted', $credited );
		$order->save();
	}

	// -------------------------------------------------------------------------
	// AJAX handlers
	// -------------------------------------------------------------------------

	/**
	 * AJAX: apply a points redemption to the current cart session.
	 */
	public function ajax_apply_points() {
		check_ajax_referer( 'phyto_loyalty_apply', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in to redeem points.', 'phyto-loyalty' ) ) );
		}

		$user_id = get_current_user_id();

		/** @var bool $can_redeem */
		$can_redeem = (bool) apply_filters( 'phyto_loyalty_can_redeem', true, $user_id );
		if ( ! $can_redeem ) {
			wp_send_json_error( array( 'message' => __( 'Points redemption is not available for your account.', 'phyto-loyalty' ) ) );
		}

		$points = isset( $_POST['points'] ) ? absint( $_POST['points'] ) : 0;

		if ( $points <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid number of points.', 'phyto-loyalty' ) ) );
		}

		$balance    = Phyto_Loyalty_DB::get_balance( $user_id );
		$min_redeem = Phyto_Loyalty_Settings::get_min_redeem();

		if ( $balance < $min_redeem ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: 1: min points, 2: points label */
						__( 'You need at least %1$s %2$s to redeem.', 'phyto-loyalty' ),
						number_format( $min_redeem ),
						Phyto_Loyalty_Settings::get_label()
					),
				)
			);
		}

		if ( $points > $balance ) {
			$points = $balance;
		}

		WC()->session->set( 'phyto_loyalty_applied_points', $points );
		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => sprintf(
					/* translators: 1: points, 2: points label */
					__( '%1$s %2$s applied successfully.', 'phyto-loyalty' ),
					number_format( $points ),
					Phyto_Loyalty_Settings::get_label()
				),
				'points'  => $points,
			)
		);
	}

	/**
	 * AJAX: remove the currently applied points redemption.
	 */
	public function ajax_remove_points() {
		check_ajax_referer( 'phyto_loyalty_remove', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'phyto-loyalty' ) ) );
		}

		WC()->session->set( 'phyto_loyalty_applied_points', 0 );
		WC()->cart->calculate_totals();

		wp_send_json_success(
			array(
				'message' => __( 'Points removed from cart.', 'phyto-loyalty' ),
			)
		);
	}
}
