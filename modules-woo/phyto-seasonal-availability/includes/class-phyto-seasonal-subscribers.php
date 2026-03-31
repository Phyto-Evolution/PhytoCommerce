<?php
/**
 * Subscribers class: handles AJAX subscription and in-season email notifications.
 *
 * @package PhytoSeasonalAvailability
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Seasonal_Subscribers
 *
 * Manages the subscription table: inserts new subscriber records via AJAX,
 * and dispatches WP mail notifications when a product returns to season.
 */
class Phyto_Seasonal_Subscribers {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'wp_ajax_phyto_sa_subscribe',        array( $this, 'handle_subscribe' ) );
		add_action( 'wp_ajax_nopriv_phyto_sa_subscribe', array( $this, 'handle_subscribe' ) );
	}

	/**
	 * Handle the AJAX subscribe request.
	 *
	 * Validates nonce, sanitises and validates the email address, then inserts
	 * a new row into phyto_seasonal_subscribers (skipping duplicates).
	 * Returns a JSON response the front-end subscribe.js consumes.
	 */
	public function handle_subscribe() {
		// Nonce verification.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'phyto_sa_subscribe_nonce' ) ) {
			wp_send_json_error( array( 'code' => 'invalid_nonce' ), 403 );
		}

		// Validate email.
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'code' => 'invalid_email' ), 400 );
		}

		// Validate product ID.
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			wp_send_json_error( array( 'code' => 'invalid_product' ), 400 );
		}

		global $wpdb;
		$table = $wpdb->prefix . 'phyto_seasonal_subscribers';

		// Check for existing subscription.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND email = %s LIMIT 1",
				$product_id,
				$email
			)
		);

		if ( $existing ) {
			wp_send_json_error( array( 'code' => 'already_subscribed' ), 200 );
		}

		// Insert new subscriber.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$inserted = $wpdb->insert(
			$table,
			array(
				'product_id'    => $product_id,
				'email'         => $email,
				'subscribed_at' => current_time( 'mysql' ),
				'notified'      => 0,
			),
			array( '%d', '%s', '%s', '%d' )
		);

		if ( false === $inserted ) {
			wp_send_json_error( array( 'code' => 'db_error' ), 500 );
		}

		wp_send_json_success( array( 'code' => 'subscribed' ) );
	}

	/**
	 * Send in-season notification emails to all unnotified subscribers for a product.
	 *
	 * Should be called after a product's available months are updated and the current
	 * month falls within the new season window. Marks each subscriber as notified
	 * after successfully dispatching the email.
	 *
	 * @param int $product_id WooCommerce product post ID.
	 */
	public function send_notifications( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'phyto_seasonal_subscribers';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$subscribers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, email FROM {$table} WHERE product_id = %d AND notified = 0",
				$product_id
			)
		);

		if ( empty( $subscribers ) ) {
			return;
		}

		$product       = wc_get_product( $product_id );
		$product_name  = $product ? $product->get_name() : get_the_title( $product_id );
		$product_url   = get_permalink( $product_id );
		$blog_name     = get_bloginfo( 'name' );
		$admin_email   = get_bloginfo( 'admin_email' );

		$subject = sprintf(
			/* translators: 1: plant product name, 2: store name */
			__( '%1$s is back in season at %2$s', 'phyto-seasonal-availability' ),
			$product_name,
			$blog_name
		);

		foreach ( $subscribers as $subscriber ) {
			$message = sprintf(
				/* translators: 1: plant product name, 2: product URL, 3: store name */
				__(
					"Good news!\n\n%1\$s is now available for shipping.\n\nShop now: %2\$s\n\n— %3\$s",
					'phyto-seasonal-availability'
				),
				$product_name,
				$product_url,
				$blog_name
			);

			$sent = wp_mail(
				$subscriber->email,
				$subject,
				$message,
				array(
					'From: ' . $blog_name . ' <' . $admin_email . '>',
					'Content-Type: text/plain; charset=UTF-8',
				)
			);

			if ( $sent ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->update(
					$table,
					array( 'notified' => 1 ),
					array( 'id' => $subscriber->id ),
					array( '%d' ),
					array( '%d' )
				);
			}
		}
	}
}
