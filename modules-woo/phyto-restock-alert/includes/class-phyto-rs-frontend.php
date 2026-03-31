<?php
/**
 * Front-end features for Phyto Restock Alert.
 *
 * - Subscribe form on out-of-stock product pages.
 * - AJAX handler for subscription submissions.
 * - Auto-notify on stock restore (woocommerce_product_set_stock).
 * - Auto-notify on post status transition to 'publish'.
 * - Email sending via wp_mail().
 *
 * @package PhytoRestockAlert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_RS_Frontend
 */
class Phyto_RS_Frontend {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		// Front-end form on single product page.
		add_action( 'woocommerce_single_product_summary', array( $this, 'render_subscribe_form' ), 31 );

		// Enqueue front-end assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// AJAX: subscribe (logged-in + guests).
		add_action( 'wp_ajax_phyto_restock_subscribe', array( $this, 'ajax_subscribe' ) );
		add_action( 'wp_ajax_nopriv_phyto_restock_subscribe', array( $this, 'ajax_subscribe' ) );

		// Auto-notify on stock restore.
		add_action( 'woocommerce_product_set_stock', array( $this, 'maybe_notify_on_stock_update' ) );

		// Auto-notify on product publish.
		add_action( 'transition_post_status', array( $this, 'maybe_notify_on_publish' ), 10, 3 );
	}

	/**
	 * Enqueue front-end CSS and JS on single product pages.
	 */
	public function enqueue_scripts() {
		if ( ! is_product() ) {
			return;
		}

		wp_enqueue_style(
			'phyto-rs-frontend',
			PHYTO_RS_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_RS_VERSION
		);

		wp_enqueue_script(
			'phyto-rs-frontend',
			PHYTO_RS_URL . 'assets/js/frontend.js',
			array( 'jquery' ),
			PHYTO_RS_VERSION,
			true
		);

		wp_localize_script(
			'phyto-rs-frontend',
			'phytoRs',
			array(
				'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => wp_create_nonce( 'phyto_rs_subscribe' ),
				'msgSubscribing'    => __( 'Subscribing…', 'phyto-restock-alert' ),
				'msgAlready'        => __( 'You are already subscribed for this product.', 'phyto-restock-alert' ),
				'msgError'          => __( 'Something went wrong. Please try again.', 'phyto-restock-alert' ),
				'msgInvalidEmail'   => __( 'Please enter a valid email address.', 'phyto-restock-alert' ),
			)
		);
	}

	/**
	 * Render the subscribe form on single product pages for out-of-stock products.
	 */
	public function render_subscribe_form() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		// Only show for out-of-stock products.
		if ( $product->is_in_stock() ) {
			return;
		}

		$product_id = absint( $product->get_id() );

		/**
		 * Filter the label text for the email input field.
		 *
		 * @param string $label      Default label text.
		 * @param int    $product_id Current product ID.
		 */
		$label = apply_filters(
			'phyto_rs_form_label',
			__( 'Notify me when this product is back in stock:', 'phyto-restock-alert' ),
			$product_id
		);

		?>
		<div id="phyto-rs-form-wrap" class="phyto-rs-form-wrap">
			<p class="phyto-rs-label"><?php echo esc_html( $label ); ?></p>
			<form id="phyto-rs-form" class="phyto-rs-form">
				<?php wp_nonce_field( 'phyto_rs_subscribe', 'phyto_rs_nonce' ); ?>
				<input type="hidden" name="product_id" value="<?php echo absint( $product_id ); ?>" />
				<div class="phyto-rs-input-row">
					<input
						type="email"
						name="email"
						id="phyto-rs-email"
						class="phyto-rs-email"
						placeholder="<?php esc_attr_e( 'your@email.com', 'phyto-restock-alert' ); ?>"
						required
					/>
					<button type="submit" class="phyto-rs-submit button alt">
						<?php esc_html_e( 'Notify Me', 'phyto-restock-alert' ); ?>
					</button>
				</div>
				<div id="phyto-rs-message" class="phyto-rs-message" aria-live="polite"></div>
			</form>
		</div>
		<?php
	}

	/**
	 * AJAX handler: subscribe an email to a product's restock notifications.
	 */
	public function ajax_subscribe() {
		// Verify nonce.
		if ( ! isset( $_POST['phyto_rs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['phyto_rs_nonce'] ) ), 'phyto_rs_subscribe' ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'phyto-restock-alert' ) ) );
		}

		$product_id = absint( $_POST['product_id'] ?? 0 );
		$email      = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'phyto-restock-alert' ) ) );
		}

		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'phyto-restock-alert' ) ) );
		}

		$result = Phyto_RS_DB::add_subscriber( $product_id, $email );

		switch ( $result ) {
			case 'success':
				/**
				 * Filter the success message shown after a successful subscription.
				 *
				 * @param string $message    Default success message.
				 * @param int    $product_id The product subscribed to.
				 * @param string $email      The subscriber's email.
				 */
				$message = apply_filters(
					'phyto_rs_success_message',
					__( "You're on the list! We'll email you the moment this item is back in stock.", 'phyto-restock-alert' ),
					$product_id,
					$email
				);
				wp_send_json_success( array( 'message' => $message ) );
				break;

			case 'already_subscribed':
				wp_send_json_error( array(
					'code'    => 'already_subscribed',
					'message' => __( 'You are already subscribed for this product.', 'phyto-restock-alert' ),
				) );
				break;

			default:
				wp_send_json_error( array( 'message' => __( 'Something went wrong. Please try again.', 'phyto-restock-alert' ) ) );
				break;
		}
	}

	/**
	 * Fire auto-notifications when a product's stock transitions from 0 to >0.
	 *
	 * Hooked to `woocommerce_product_set_stock`.
	 *
	 * @param WC_Product $product The product whose stock was just updated.
	 */
	public function maybe_notify_on_stock_update( $product ) {
		if ( ! $product instanceof WC_Product ) {
			return;
		}

		$quantity = $product->get_stock_quantity();

		// Only fire when stock is now positive.
		if ( null === $quantity || $quantity <= 0 ) {
			return;
		}

		$this->notify_subscribers( $product->get_id() );
	}

	/**
	 * Fire auto-notifications when a product transitions to 'publish'.
	 *
	 * Hooked to `transition_post_status`.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function maybe_notify_on_publish( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		if ( 'product' !== get_post_type( $post ) ) {
			return;
		}

		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return;
		}

		// Only notify if the product is in stock when published.
		if ( ! $product->is_in_stock() ) {
			return;
		}

		$this->notify_subscribers( $post->ID );
	}

	/**
	 * Send notification emails to all unnotified subscribers for a product.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return int Number of emails successfully sent.
	 */
	public function notify_subscribers( $product_id ) {
		$product_id  = absint( $product_id );
		$subscribers = Phyto_RS_DB::get_unnotified_subscribers( $product_id );

		if ( empty( $subscribers ) ) {
			return 0;
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			return 0;
		}

		$sent = 0;

		foreach ( $subscribers as $subscriber ) {
			if ( $this->send_notification_email( $subscriber->email, $product ) ) {
				$sent++;
			}
		}

		// Mark all as notified (batch update) regardless of individual send result.
		Phyto_RS_DB::mark_notified( $product_id );

		return $sent;
	}

	/**
	 * Send a single restock notification email.
	 *
	 * @param string     $to      Recipient email address.
	 * @param WC_Product $product The restocked product.
	 * @return bool True if wp_mail() succeeded.
	 */
	private function send_notification_email( $to, WC_Product $product ) {
		$site_name  = get_bloginfo( 'name' );
		$from_name  = $site_name;
		$from_email = get_option( 'admin_email' );

		/**
		 * Filter the restock notification email subject.
		 *
		 * @param string     $subject    Default subject line.
		 * @param WC_Product $product    The restocked product.
		 * @param string     $to         Recipient email.
		 */
		$subject = apply_filters(
			'phyto_rs_email_subject',
			sprintf(
				/* translators: 1: product name, 2: site name */
				__( '%1$s is back in stock at %2$s', 'phyto-restock-alert' ),
				$product->get_name(),
				$site_name
			),
			$product,
			$to
		);

		$product_url  = get_permalink( $product->get_id() );
		$product_name = $product->get_name();

		$default_body = sprintf(
			/* translators: 1: product name, 2: product URL, 3: site name */
			__(
				"Hello,\n\nGreat news! %1\$s is now back in stock.\n\nShop now: %2\$s\n\nThank you for your patience.\n\n— %3\$s",
				'phyto-restock-alert'
			),
			$product_name,
			$product_url,
			$site_name
		);

		/**
		 * Filter the restock notification email body.
		 *
		 * @param string     $body       Default email body (plain text).
		 * @param WC_Product $product    The restocked product.
		 * @param string     $to         Recipient email.
		 */
		$body = apply_filters( 'phyto_rs_email_body', $default_body, $product, $to );

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			sprintf( 'From: %s <%s>', $from_name, $from_email ),
		);

		return wp_mail( $to, $subject, $body, $headers );
	}
}
