<?php
/**
 * Database layer for Phyto Restock Alert.
 *
 * Handles table creation via dbDelta and all CRUD helpers for the
 * {prefix}phyto_restock_subscribers table.
 *
 * @package PhytoRestockAlert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_RS_DB
 */
class Phyto_RS_DB {

	/**
	 * Return the full subscribers table name including the WP prefix.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_restock_subscribers';
	}

	/**
	 * Create (or upgrade) the subscribers table using dbDelta.
	 */
	public static function create_table() {
		global $wpdb;

		$table      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			product_id bigint(20) unsigned NOT NULL,
			email varchar(200) NOT NULL,
			subscribed_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			notified_at datetime DEFAULT NULL,
			PRIMARY KEY (id),
			KEY product_id (product_id),
			UNIQUE KEY product_email (product_id, email)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add a subscriber for a product.
	 *
	 * @param int    $product_id WooCommerce product ID.
	 * @param string $email      Subscriber email address.
	 * @return string 'success' | 'already_subscribed' | 'error'
	 */
	public static function add_subscriber( $product_id, $email ) {
		global $wpdb;

		$table    = self::table_name();
		$product_id = absint( $product_id );
		$email    = sanitize_email( $email );

		if ( ! is_email( $email ) || ! $product_id ) {
			return 'error';
		}

		// Check for existing subscription.
		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM `{$table}` WHERE product_id = %d AND email = %s LIMIT 1",
				$product_id,
				$email
			)
		);

		if ( $existing ) {
			return 'already_subscribed';
		}

		$result = $wpdb->insert(
			$table,
			array(
				'product_id'    => $product_id,
				'email'         => $email,
				'subscribed_at' => current_time( 'mysql' ),
				'notified_at'   => null,
			),
			array( '%d', '%s', '%s', null )
		);

		return $result ? 'success' : 'error';
	}

	/**
	 * Get all subscribers for a given product.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return array Array of row objects.
	 */
	public static function get_subscribers( $product_id ) {
		global $wpdb;

		$table = self::table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE product_id = %d ORDER BY subscribed_at DESC",
				absint( $product_id )
			)
		);
	}

	/**
	 * Get subscribers that have not yet been notified for a product.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return array Array of row objects.
	 */
	public static function get_unnotified_subscribers( $product_id ) {
		global $wpdb;

		$table = self::table_name();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE product_id = %d AND notified_at IS NULL ORDER BY subscribed_at ASC",
				absint( $product_id )
			)
		);
	}

	/**
	 * Mark all subscribers for a product as notified (sets notified_at to now).
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return int Number of rows updated.
	 */
	public static function mark_notified( $product_id ) {
		global $wpdb;

		$table = self::table_name();

		return $wpdb->update(
			$table,
			array( 'notified_at' => current_time( 'mysql' ) ),
			array( 'product_id' => absint( $product_id ) ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Delete a single subscriber row by its primary key.
	 *
	 * @param int $id Row ID to delete.
	 * @return bool True on success.
	 */
	public static function delete_subscriber( $id ) {
		global $wpdb;

		$table = self::table_name();

		return (bool) $wpdb->delete(
			$table,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}

	/**
	 * Return the subscriber count for a product.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return int
	 */
	public static function get_subscriber_count( $product_id ) {
		global $wpdb;

		$table = self::table_name();

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM `{$table}` WHERE product_id = %d",
				absint( $product_id )
			)
		);
	}
}
