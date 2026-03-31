<?php
/**
 * WooCommerce order email attachment for Phyto Care Card.
 *
 * Generates care-guide PDFs for eligible order items and attaches them to the
 * customer_completed_order email when the product-level opt-in is enabled.
 *
 * @package PhytoCareCard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Care_Card_Email
 */
class Phyto_Care_Card_Email {

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_email_attachments', array( $this, 'attach_to_email' ), 10, 3 );
	}

	/**
	 * Attach care card PDFs to the customer completed-order email.
	 *
	 * Iterates over every order item. For items whose product has care data and
	 * has `_phyto_cc_attach_email` set to "1", generates the PDF, saves it to
	 * the uploads directory, and appends the file path to $attachments.
	 *
	 * @param array           $attachments Existing email attachment file paths.
	 * @param string          $email_id    WooCommerce email class ID.
	 * @param WC_Order|mixed  $order       The order object (may be other types for other emails).
	 * @return array Modified attachment file paths.
	 */
	public function attach_to_email( $attachments, $email_id, $order ) {
		// Only fire on customer completed-order email.
		if ( 'customer_completed_order' !== $email_id ) {
			return $attachments;
		}

		if ( ! $order instanceof WC_Order ) {
			return $attachments;
		}

		$upload_dir   = wp_upload_dir();
		$care_dir     = trailingslashit( $upload_dir['basedir'] ) . 'phyto-care-cards';

		// Create the directory if it does not exist.
		if ( ! is_dir( $care_dir ) ) {
			wp_mkdir_p( $care_dir );
		}

		$generator      = new Phyto_Care_Card_Generator();
		$attached_ids   = array(); // Track processed product IDs to avoid duplicates.

		foreach ( $order->get_items() as $item ) {
			/** @var WC_Order_Item_Product $item */
			$product_id = (int) $item->get_product_id();

			if ( ! $product_id || isset( $attached_ids[ $product_id ] ) ) {
				continue;
			}

			// Check opt-in flag.
			$attach = get_post_meta( $product_id, '_phyto_cc_attach_email', true );
			if ( '1' !== (string) $attach ) {
				continue;
			}

			// Check that at least one care field is populated.
			if ( ! $this->has_care_data( $product_id ) ) {
				continue;
			}

			$file_path = $care_dir . '/product-' . $product_id . '.pdf';

			// Generate PDF and write to file.
			$pdf = $generator->generate( $product_id );

			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			$bytes_written = file_put_contents( $file_path, $pdf );

			if ( false !== $bytes_written && file_exists( $file_path ) ) {
				$attachments[]              = $file_path;
				$attached_ids[ $product_id ] = true;
			}
		}

		return $attachments;
	}

	/**
	 * Check whether the product has at least one care meta value populated.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return bool True when at least one care field is non-empty.
	 */
	private function has_care_data( $product_id ) {
		$care_keys = array(
			'_phyto_cc_light_req',
			'_phyto_cc_watering',
			'_phyto_cc_humidity',
			'_phyto_cc_temp_min',
			'_phyto_cc_temp_max',
			'_phyto_cc_potting_media',
			'_phyto_cc_fertilisation',
			'_phyto_cc_dormancy_notes',
			'_phyto_cc_special_tips',
		);
		foreach ( $care_keys as $key ) {
			$val = get_post_meta( $product_id, $key, true );
			if ( '' !== (string) $val ) {
				return true;
			}
		}
		return false;
	}
}
