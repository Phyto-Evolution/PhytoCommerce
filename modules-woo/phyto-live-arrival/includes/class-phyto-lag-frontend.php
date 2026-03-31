<?php
/**
 * Front-end functionality for Phyto Live Arrival Guarantee.
 *
 * Handles:
 *  - Product page "Live Arrival Guaranteed" badge with expandable policy details
 *  - Checkout opt-in checkbox (required when cart contains LAG products)
 *  - Storing opt-in acceptance in order meta
 *  - Order confirmation email LAG policy reminder paragraph
 *
 * Developer hooks:
 *  - phyto_lag_is_eligible   (filter) — override LAG eligibility per product
 *  - phyto_lag_window_hours  (filter) — override guarantee window per product
 *  - phyto_lag_checkout_label (filter) — override checkout opt-in label text
 *
 * @package PhytoLiveArrival
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_LAG_Frontend
 */
class Phyto_LAG_Frontend {

	/**
	 * Register WordPress/WooCommerce hooks.
	 */
	public function register_hooks() {
		// Product page badge.
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_product_badge' ), 15 );

		// Checkout opt-in checkbox.
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'render_checkout_optin' ) );

		// Validate the opt-in checkbox before order is placed.
		add_action( 'woocommerce_checkout_process', array( $this, 'validate_checkout_optin' ) );

		// Save the opt-in value to order meta.
		add_action( 'woocommerce_checkout_order_created', array( $this, 'save_checkout_optin' ) );

		// Order confirmation email footer.
		add_action( 'woocommerce_email_order_details', array( $this, 'append_email_lag_reminder' ), 20, 4 );

		// Frontend styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	}

	// -------------------------------------------------------------------------
	// Helper
	// -------------------------------------------------------------------------

	/**
	 * Determine whether a product is LAG-eligible.
	 *
	 * Respects the `phyto_lag_is_eligible` filter so developers can override.
	 *
	 * @param int $product_id Product ID to check.
	 * @return bool
	 */
	public function is_eligible( $product_id ) {
		$enabled = '1' === get_post_meta( $product_id, '_phyto_lag_enabled', true );

		/**
		 * Filter: phyto_lag_is_eligible
		 *
		 * @param bool $enabled    Whether LAG is enabled for this product.
		 * @param int  $product_id The product ID.
		 */
		return (bool) apply_filters( 'phyto_lag_is_eligible', $enabled, $product_id );
	}

	/**
	 * Get the effective guarantee window in hours for a product.
	 *
	 * @param int $product_id Product ID.
	 * @return int Hours.
	 */
	public function get_window_hours( $product_id ) {
		$stored  = get_post_meta( $product_id, '_phyto_lag_window_hours', true );
		$default = (int) get_option( 'phyto_lag_default_window', 24 );
		$hours   = ( '' !== $stored ) ? (int) $stored : $default;

		/**
		 * Filter: phyto_lag_window_hours
		 *
		 * @param int $hours      Guarantee window in hours.
		 * @param int $product_id The product ID.
		 */
		return (int) apply_filters( 'phyto_lag_window_hours', $hours, $product_id );
	}

	/**
	 * Get the human-readable policy type label.
	 *
	 * @param string $policy Policy slug (replacement|refund|store-credit).
	 * @return string Translated label.
	 */
	private function policy_label( $policy ) {
		$labels = array(
			'replacement'  => __( 'Replacement', 'phyto-live-arrival' ),
			'refund'       => __( 'Full Refund', 'phyto-live-arrival' ),
			'store-credit' => __( 'Store Credit', 'phyto-live-arrival' ),
		);
		return isset( $labels[ $policy ] ) ? $labels[ $policy ] : ucfirst( $policy );
	}

	/**
	 * Check whether any item in the cart has LAG enabled.
	 *
	 * @return bool
	 */
	private function cart_has_lag_product() {
		if ( ! WC()->cart ) {
			return false;
		}
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( $this->is_eligible( $item['product_id'] ) ) {
				return true;
			}
		}
		return false;
	}

	// -------------------------------------------------------------------------
	// Frontend styles
	// -------------------------------------------------------------------------

	/**
	 * Enqueue the frontend CSS on product and checkout pages.
	 */
	public function enqueue_frontend_styles() {
		if ( is_product() || is_checkout() ) {
			wp_enqueue_style(
				'phyto-lag-frontend',
				PHYTO_LAG_URL . 'assets/css/frontend.css',
				array(),
				PHYTO_LAG_VERSION
			);
		}
	}

	// -------------------------------------------------------------------------
	// Product page badge
	// -------------------------------------------------------------------------

	/**
	 * Render the "Live Arrival Guaranteed" badge on the single product page.
	 *
	 * Hooked at priority 15 on woocommerce_single_product_summary.
	 */
	public function render_product_badge() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$product_id = $product->get_id();

		if ( ! $this->is_eligible( $product_id ) ) {
			return;
		}

		$hours      = $this->get_window_hours( $product_id );
		$policy     = get_post_meta( $product_id, '_phyto_lag_policy_type', true );
		$custom_note = get_post_meta( $product_id, '_phyto_lag_policy_note', true );
		$disclaimer = get_option( 'phyto_lag_disclaimer', '' );

		if ( '' === $policy ) {
			$policy = get_option( 'phyto_lag_default_policy', 'replacement' );
		}

		$unique_id = 'phyto-lag-details-' . $product_id;
		?>
		<div class="phyto-lag-badge-wrap">
			<button
				type="button"
				class="phyto-lag-badge-btn"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $unique_id ); ?>"
			>
				<span class="phyto-lag-icon" aria-hidden="true">&#9679;</span>
				<?php esc_html_e( 'Live Arrival Guaranteed', 'phyto-live-arrival' ); ?>
				<span class="phyto-lag-chevron" aria-hidden="true">&#9660;</span>
			</button>

			<div id="<?php echo esc_attr( $unique_id ); ?>" class="phyto-lag-details" hidden>
				<ul>
					<li>
						<?php
						printf(
							/* translators: %d: number of hours */
							esc_html( _n(
								'Report within %d hour of delivery.',
								'Report within %d hours of delivery.',
								$hours,
								'phyto-live-arrival'
							) ),
							(int) $hours
						);
						?>
					</li>
					<li>
						<?php
						printf(
							/* translators: %s: policy type label */
							esc_html__( 'Resolution: %s', 'phyto-live-arrival' ),
							esc_html( $this->policy_label( $policy ) )
						);
						?>
					</li>
				</ul>
				<?php if ( $custom_note ) : ?>
					<p class="phyto-lag-custom-note"><?php echo esc_html( $custom_note ); ?></p>
				<?php elseif ( $disclaimer ) : ?>
					<p class="phyto-lag-disclaimer"><?php echo esc_html( $disclaimer ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<script>
		(function() {
			var btn = document.querySelector('.phyto-lag-badge-btn[aria-controls="<?php echo esc_js( $unique_id ); ?>"]');
			if (!btn) return;
			btn.addEventListener('click', function() {
				var expanded = this.getAttribute('aria-expanded') === 'true';
				this.setAttribute('aria-expanded', String(!expanded));
				var panel = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
				if (panel) {
					if (expanded) {
						panel.setAttribute('hidden', '');
					} else {
						panel.removeAttribute('hidden');
					}
				}
				var chevron = this.querySelector('.phyto-lag-chevron');
				if (chevron) {
					chevron.textContent = expanded ? '\u25BC' : '\u25B2';
				}
			});
		}());
		</script>
		<?php
	}

	// -------------------------------------------------------------------------
	// Checkout opt-in
	// -------------------------------------------------------------------------

	/**
	 * Render the LAG opt-in checkbox before the Place Order button.
	 *
	 * Only rendered when the cart contains at least one LAG-enabled product.
	 */
	public function render_checkout_optin() {
		if ( ! $this->cart_has_lag_product() ) {
			return;
		}

		$label = (string) get_option(
			'phyto_lag_checkout_label',
			__( 'I accept the Live Arrival Guarantee terms for live plant orders.', 'phyto-live-arrival' )
		);

		/**
		 * Filter: phyto_lag_checkout_label
		 *
		 * @param string $label The checkbox label text.
		 */
		$label = apply_filters( 'phyto_lag_checkout_label', $label );
		?>
		<div class="phyto-lag-checkout-optin">
			<label for="phyto_lag_accept">
				<input
					type="checkbox"
					id="phyto_lag_accept"
					name="phyto_lag_accept"
					value="1"
				>
				<?php echo esc_html( $label ); ?>
				<abbr class="required" title="<?php esc_attr_e( 'required', 'phyto-live-arrival' ); ?>">*</abbr>
			</label>
		</div>
		<?php
	}

	/**
	 * Validate that the buyer has checked the LAG opt-in.
	 *
	 * Called on woocommerce_checkout_process — adds a WC error if missing.
	 */
	public function validate_checkout_optin() {
		if ( ! $this->cart_has_lag_product() ) {
			return;
		}

		if ( ! isset( $_POST['phyto_lag_accept'] ) || '1' !== $_POST['phyto_lag_accept'] ) {
			wc_add_notice(
				__( 'Please accept the Live Arrival Guarantee terms to complete your order.', 'phyto-live-arrival' ),
				'error'
			);
		}
	}

	/**
	 * Save the LAG opt-in value to the order after it is created.
	 *
	 * @param WC_Order $order Newly created order.
	 */
	public function save_checkout_optin( WC_Order $order ) {
		$accepted = ( isset( $_POST['phyto_lag_accept'] ) && '1' === $_POST['phyto_lag_accept'] ) ? '1' : '0';
		$order->update_meta_data( '_phyto_lag_accepted', $accepted );
		$order->save();
	}

	// -------------------------------------------------------------------------
	// Order confirmation email
	// -------------------------------------------------------------------------

	/**
	 * Append a LAG policy reminder paragraph to the order confirmation email.
	 *
	 * @param WC_Order $order          Order object.
	 * @param bool     $sent_to_admin  Whether the email is going to the admin.
	 * @param bool     $plain_text     Whether the email is plain text.
	 * @param WC_Email $email          Email object.
	 */
	public function append_email_lag_reminder( $order, $sent_to_admin, $plain_text, $email ) {
		if ( $sent_to_admin ) {
			return;
		}

		// Only on customer-facing order confirmation / processing emails.
		if ( ! in_array( $email->id, array( 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order' ), true ) ) {
			return;
		}

		$accepted = $order->get_meta( '_phyto_lag_accepted' );
		if ( '1' !== $accepted ) {
			return;
		}

		// Check whether order contains LAG products.
		$lag_products = array();
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			if ( $this->is_eligible( $product_id ) ) {
				$lag_products[] = $item->get_name();
			}
		}

		if ( empty( $lag_products ) ) {
			return;
		}

		$disclaimer = (string) get_option( 'phyto_lag_disclaimer', '' );
		if ( '' === $disclaimer ) {
			return;
		}

		if ( $plain_text ) {
			echo "\n\n--- " . esc_html__( 'Live Arrival Guarantee', 'phyto-live-arrival' ) . " ---\n";
			echo esc_html( $disclaimer ) . "\n";
		} else {
			?>
			<div style="margin:24px 0;padding:16px;border:1px solid #c3e6cb;background:#f4fff7;border-radius:4px;">
				<strong style="color:#1a3c2b;"><?php esc_html_e( 'Live Arrival Guarantee', 'phyto-live-arrival' ); ?></strong>
				<p style="margin:8px 0 0;color:#333;"><?php echo esc_html( $disclaimer ); ?></p>
			</div>
			<?php
		}
	}
}
