<?php
/**
 * Admin functionality for Phyto Live Arrival Guarantee.
 *
 * Handles:
 *  - Product meta box (enable LAG, window hours, policy type, custom note)
 *  - Orders list "LAG" column
 *  - Order detail claim meta box (log claim, notes, resolution status)
 *
 * @package PhytoLiveArrival
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_LAG_Admin
 */
class Phyto_LAG_Admin {

	/**
	 * Valid policy type values.
	 *
	 * @var array
	 */
	private $policy_types = array(
		'replacement'  => '',
		'refund'       => '',
		'store-credit' => '',
	);

	/**
	 * Valid claim resolution status values.
	 *
	 * @var array
	 */
	private $resolution_statuses = array(
		'pending'           => '',
		'replacement-sent'  => '',
		'refunded'          => '',
		'rejected'          => '',
	);

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		// Product meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_product_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_product_meta_box' ), 10, 2 );

		// Orders list column.
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'add_orders_column' ) );
		add_filter( 'manage_woocommerce_page_wc-orders_columns', array( $this, 'add_orders_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'render_orders_column' ), 10, 2 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'render_orders_column_hpos' ), 10, 2 );

		// Order detail claim meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_claim_meta_box' ) );
		add_action( 'save_post_shop_order', array( $this, 'save_claim_meta_box' ), 10, 2 );
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_claim_meta_box_hpos' ) );

		// Admin styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Enqueue admin CSS.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_styles( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		$relevant = array( 'post', 'shop_order', 'woocommerce_page_wc-orders' );
		if ( in_array( $screen->id, $relevant, true ) || 'post' === $screen->base ) {
			wp_enqueue_style(
				'phyto-lag-admin',
				PHYTO_LAG_URL . 'assets/css/admin.css',
				array(),
				PHYTO_LAG_VERSION
			);
		}
	}

	// -------------------------------------------------------------------------
	// Product meta box
	// -------------------------------------------------------------------------

	/**
	 * Register the product meta box.
	 */
	public function add_product_meta_box() {
		add_meta_box(
			'phyto-lag-product',
			__( 'Live Arrival Guarantee', 'phyto-live-arrival' ),
			array( $this, 'render_product_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render the product meta box.
	 *
	 * @param WP_Post $post Current product post.
	 */
	public function render_product_meta_box( $post ) {
		wp_nonce_field( 'phyto_lag_save_product_meta', 'phyto_lag_product_nonce' );

		$enabled      = get_post_meta( $post->ID, '_phyto_lag_enabled', true );
		$window       = get_post_meta( $post->ID, '_phyto_lag_window_hours', true );
		$policy       = get_post_meta( $post->ID, '_phyto_lag_policy_type', true );
		$note         = get_post_meta( $post->ID, '_phyto_lag_policy_note', true );

		$default_window  = (int) get_option( 'phyto_lag_default_window', 24 );
		$default_policy  = (string) get_option( 'phyto_lag_default_policy', 'replacement' );

		if ( '' === $window ) {
			$window = $default_window;
		}
		if ( '' === $policy ) {
			$policy = $default_policy;
		}
		?>
		<div class="phyto-lag-product-meta">
			<p>
				<label>
					<input type="checkbox" name="phyto_lag_enabled" value="1" <?php checked( '1', $enabled ); ?>>
					<?php esc_html_e( 'Enable Live Arrival Guarantee for this product', 'phyto-live-arrival' ); ?>
				</label>
			</p>

			<p>
				<label for="phyto_lag_window_hours">
					<strong><?php esc_html_e( 'Guarantee Window (hours)', 'phyto-live-arrival' ); ?></strong>
				</label><br>
				<input
					type="number"
					id="phyto_lag_window_hours"
					name="phyto_lag_window_hours"
					value="<?php echo esc_attr( $window ); ?>"
					min="1"
					step="1"
					style="width:80px;"
				>
				<span class="description"><?php esc_html_e( 'Hours after delivery to report a claim.', 'phyto-live-arrival' ); ?></span>
			</p>

			<p>
				<label for="phyto_lag_policy_type">
					<strong><?php esc_html_e( 'Policy Type', 'phyto-live-arrival' ); ?></strong>
				</label><br>
				<select id="phyto_lag_policy_type" name="phyto_lag_policy_type" style="width:100%;">
					<option value="replacement" <?php selected( $policy, 'replacement' ); ?>>
						<?php esc_html_e( 'Replacement', 'phyto-live-arrival' ); ?>
					</option>
					<option value="refund" <?php selected( $policy, 'refund' ); ?>>
						<?php esc_html_e( 'Refund', 'phyto-live-arrival' ); ?>
					</option>
					<option value="store-credit" <?php selected( $policy, 'store-credit' ); ?>>
						<?php esc_html_e( 'Store Credit', 'phyto-live-arrival' ); ?>
					</option>
				</select>
			</p>

			<p>
				<label for="phyto_lag_policy_note">
					<strong><?php esc_html_e( 'Custom Policy Note', 'phyto-live-arrival' ); ?></strong>
				</label><br>
				<textarea
					id="phyto_lag_policy_note"
					name="phyto_lag_policy_note"
					rows="3"
					style="width:100%;"
					placeholder="<?php esc_attr_e( 'Optional: override the global disclaimer for this product.', 'phyto-live-arrival' ); ?>"
				><?php echo esc_textarea( $note ); ?></textarea>
			</p>
		</div>
		<?php
	}

	/**
	 * Save product LAG meta.
	 *
	 * @param int     $post_id Post ID being saved.
	 * @param WP_Post $post    Post object.
	 */
	public function save_product_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if (
			! isset( $_POST['phyto_lag_product_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_lag_product_nonce'] ), 'phyto_lag_save_product_meta' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Enabled checkbox.
		$enabled = isset( $_POST['phyto_lag_enabled'] ) ? '1' : '0';
		update_post_meta( $post_id, '_phyto_lag_enabled', $enabled );

		// Window hours.
		if ( isset( $_POST['phyto_lag_window_hours'] ) ) {
			$window = absint( $_POST['phyto_lag_window_hours'] );
			update_post_meta( $post_id, '_phyto_lag_window_hours', max( 1, $window ) );
		}

		// Policy type.
		if ( isset( $_POST['phyto_lag_policy_type'] ) ) {
			$policy = sanitize_key( wp_unslash( $_POST['phyto_lag_policy_type'] ) );
			if ( array_key_exists( $policy, $this->policy_types ) ) {
				update_post_meta( $post_id, '_phyto_lag_policy_type', $policy );
			}
		}

		// Custom policy note.
		if ( isset( $_POST['phyto_lag_policy_note'] ) ) {
			update_post_meta(
				$post_id,
				'_phyto_lag_policy_note',
				sanitize_textarea_field( wp_unslash( $_POST['phyto_lag_policy_note'] ) )
			);
		}
	}

	// -------------------------------------------------------------------------
	// Orders list LAG column
	// -------------------------------------------------------------------------

	/**
	 * Add a "LAG" column to the orders list.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_orders_column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'order_status' === $key ) {
				$new['phyto_lag'] = __( 'LAG', 'phyto-live-arrival' );
			}
		}
		return $new;
	}

	/**
	 * Render the LAG column for legacy (CPT-based) orders list.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Order post ID.
	 */
	public function render_orders_column( $column, $post_id ) {
		if ( 'phyto_lag' !== $column ) {
			return;
		}
		$order = wc_get_order( $post_id );
		$this->render_lag_badge( $order );
	}

	/**
	 * Render the LAG column for HPOS orders list.
	 *
	 * @param string    $column Column slug.
	 * @param WC_Order  $order  Order object.
	 */
	public function render_orders_column_hpos( $column, $order ) {
		if ( 'phyto_lag' !== $column ) {
			return;
		}
		$this->render_lag_badge( $order );
	}

	/**
	 * Output the LAG badge if the order has LAG-enrolled products and buyer accepted.
	 *
	 * @param WC_Order|false $order Order object.
	 */
	private function render_lag_badge( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$accepted = $order->get_meta( '_phyto_lag_accepted' );
		if ( '1' !== $accepted ) {
			echo '<span class="phyto-lag-badge phyto-lag-badge--none" title="' . esc_attr__( 'No LAG', 'phyto-live-arrival' ) . '">—</span>';
			return;
		}

		$has_lag = false;
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			if ( '1' === get_post_meta( $product_id, '_phyto_lag_enabled', true ) ) {
				$has_lag = true;
				break;
			}
		}

		if ( $has_lag ) {
			$claimed = $order->get_meta( '_phyto_lag_claimed' );
			$class   = 'phyto-lag-badge--active';
			$label   = __( 'LAG', 'phyto-live-arrival' );
			if ( '1' === $claimed ) {
				$class = 'phyto-lag-badge--claimed';
				$label = __( 'Claimed', 'phyto-live-arrival' );
			}
			echo '<span class="phyto-lag-badge ' . esc_attr( $class ) . '">' . esc_html( $label ) . '</span>';
		} else {
			echo '<span class="phyto-lag-badge phyto-lag-badge--none">—</span>';
		}
	}

	// -------------------------------------------------------------------------
	// Order detail claim meta box
	// -------------------------------------------------------------------------

	/**
	 * Register the claim meta box on order detail screen.
	 */
	public function add_claim_meta_box() {
		add_meta_box(
			'phyto-lag-claim',
			__( 'Live Arrival Guarantee — Claim', 'phyto-live-arrival' ),
			array( $this, 'render_claim_meta_box' ),
			'shop_order',
			'side',
			'default'
		);
		// Also register for HPOS.
		add_meta_box(
			'phyto-lag-claim',
			__( 'Live Arrival Guarantee — Claim', 'phyto-live-arrival' ),
			array( $this, 'render_claim_meta_box' ),
			'woocommerce_page_wc-orders',
			'side',
			'default'
		);
	}

	/**
	 * Render the claim meta box.
	 *
	 * @param WP_Post|WC_Order $post_or_order Post object (legacy) or order (HPOS).
	 */
	public function render_claim_meta_box( $post_or_order ) {
		if ( $post_or_order instanceof WP_Post ) {
			$order = wc_get_order( $post_or_order->ID );
		} elseif ( $post_or_order instanceof WC_Order ) {
			$order = $post_or_order;
		} else {
			return;
		}

		if ( ! $order ) {
			return;
		}

		wp_nonce_field( 'phyto_lag_save_claim', 'phyto_lag_claim_nonce' );

		$accepted   = $order->get_meta( '_phyto_lag_accepted' );
		$claimed    = $order->get_meta( '_phyto_lag_claimed' );
		$claim_note = $order->get_meta( '_phyto_lag_claim_notes' );
		$resolution = $order->get_meta( '_phyto_lag_resolution' );

		if ( '' === $resolution ) {
			$resolution = 'pending';
		}

		$resolution_labels = array(
			'pending'          => __( 'Pending', 'phyto-live-arrival' ),
			'replacement-sent' => __( 'Replacement Sent', 'phyto-live-arrival' ),
			'refunded'         => __( 'Refunded', 'phyto-live-arrival' ),
			'rejected'         => __( 'Rejected', 'phyto-live-arrival' ),
		);
		?>
		<div class="phyto-lag-claim-meta">
			<?php if ( '1' !== $accepted ) : ?>
				<p class="description"><?php esc_html_e( 'Buyer did not opt in to the Live Arrival Guarantee.', 'phyto-live-arrival' ); ?></p>
			<?php else : ?>
				<p>
					<label>
						<input type="checkbox" name="phyto_lag_claimed" value="1" <?php checked( '1', $claimed ); ?>>
						<strong><?php esc_html_e( 'Mark as Claimed', 'phyto-live-arrival' ); ?></strong>
					</label>
				</p>

				<p>
					<label for="phyto_lag_claim_notes">
						<strong><?php esc_html_e( 'Claim Notes', 'phyto-live-arrival' ); ?></strong>
					</label><br>
					<textarea
						id="phyto_lag_claim_notes"
						name="phyto_lag_claim_notes"
						rows="4"
						style="width:100%;"
						placeholder="<?php esc_attr_e( 'Describe the issue, photo references, customer communication, etc.', 'phyto-live-arrival' ); ?>"
					><?php echo esc_textarea( $claim_note ); ?></textarea>
				</p>

				<p>
					<label for="phyto_lag_resolution">
						<strong><?php esc_html_e( 'Resolution Status', 'phyto-live-arrival' ); ?></strong>
					</label><br>
					<select id="phyto_lag_resolution" name="phyto_lag_resolution" style="width:100%;">
						<?php foreach ( $resolution_labels as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $resolution, $val ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save claim meta box for legacy (CPT) orders.
	 *
	 * @param int     $post_id Order post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_claim_meta_box( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( 'shop_order' !== get_post_type( $post_id ) ) {
			return;
		}

		if (
			! isset( $_POST['phyto_lag_claim_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_lag_claim_nonce'] ), 'phyto_lag_save_claim' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			return;
		}

		$this->persist_claim_meta( $order );
	}

	/**
	 * Save claim meta box for HPOS orders.
	 *
	 * @param int $order_id Order ID.
	 */
	public function save_claim_meta_box_hpos( $order_id ) {
		if (
			! isset( $_POST['phyto_lag_claim_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_lag_claim_nonce'] ), 'phyto_lag_save_claim' )
		) {
			return;
		}

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$this->persist_claim_meta( $order );
	}

	/**
	 * Write claim meta to the order.
	 *
	 * @param WC_Order $order Order to update.
	 */
	private function persist_claim_meta( WC_Order $order ) {
		$claimed    = isset( $_POST['phyto_lag_claimed'] ) ? '1' : '0';
		$claim_note = isset( $_POST['phyto_lag_claim_notes'] )
			? sanitize_textarea_field( wp_unslash( $_POST['phyto_lag_claim_notes'] ) )
			: '';

		$resolution = 'pending';
		if ( isset( $_POST['phyto_lag_resolution'] ) ) {
			$val = sanitize_key( wp_unslash( $_POST['phyto_lag_resolution'] ) );
			if ( array_key_exists( $val, $this->resolution_statuses ) ) {
				$resolution = $val;
			}
		}

		$order->update_meta_data( '_phyto_lag_claimed', $claimed );
		$order->update_meta_data( '_phyto_lag_claim_notes', $claim_note );
		$order->update_meta_data( '_phyto_lag_resolution', $resolution );
		$order->save();
	}
}
