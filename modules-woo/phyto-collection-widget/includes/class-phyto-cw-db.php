<?php
/**
 * Database layer for Phyto Collection Widget.
 *
 * Handles table creation and all CRUD operations against
 * {prefix}phyto_collection_item.
 *
 * @package PhytoCollectionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_CW_DB
 */
class Phyto_CW_DB {

	/**
	 * Return the fully-qualified table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_collection_item';
	}

	/**
	 * Create (or upgrade) the collection table using dbDelta.
	 *
	 * Called on plugin activation.
	 */
	public static function create_table() {
		global $wpdb;

		$table      = self::table();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			customer_id BIGINT(20) UNSIGNED NOT NULL,
			product_id  BIGINT(20) UNSIGNED NOT NULL,
			order_id    BIGINT(20) UNSIGNED DEFAULT NULL,
			personal_note TEXT,
			is_public   TINYINT(1) NOT NULL DEFAULT 0,
			date_acquired DATE DEFAULT NULL,
			date_add    DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_upd    DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			UNIQUE KEY customer_product (customer_id, product_id),
			KEY idx_customer (customer_id),
			KEY idx_product  (product_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a new collection item or update the existing one if the
	 * (customer_id, product_id) pair already exists.
	 *
	 * When updating, order_id and date_acquired are refreshed; personal_note
	 * and is_public are left unchanged so the customer's customisations survive.
	 *
	 * @param int    $customer_id  WP user ID of the purchasing customer.
	 * @param int    $product_id   WooCommerce product ID.
	 * @param int    $order_id     WooCommerce order ID.
	 * @param string $date_acquired Date string (Y-m-d) of the order.
	 * @return int|false Inserted/updated row ID, or false on failure.
	 */
	public static function add_or_update( $customer_id, $product_id, $order_id, $date_acquired ) {
		global $wpdb;

		$table = self::table();
		$now   = current_time( 'mysql' );

		// Check whether row already exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE customer_id = %d AND product_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $customer_id,
				(int) $product_id
			)
		);

		if ( $existing ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update(
				$table,
				array(
					'order_id'      => (int) $order_id,
					'date_acquired' => sanitize_text_field( $date_acquired ),
					'date_upd'      => $now,
				),
				array(
					'customer_id' => (int) $customer_id,
					'product_id'  => (int) $product_id,
				),
				array( '%d', '%s', '%s' ),
				array( '%d', '%d' )
			);
			return (int) $existing->id;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$inserted = $wpdb->insert(
			$table,
			array(
				'customer_id'   => (int) $customer_id,
				'product_id'    => (int) $product_id,
				'order_id'      => (int) $order_id,
				'personal_note' => '',
				'is_public'     => 0,
				'date_acquired' => sanitize_text_field( $date_acquired ),
				'date_add'      => $now,
				'date_upd'      => $now,
			),
			array( '%d', '%d', '%d', '%s', '%d', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Retrieve all collection items for a given customer, newest first.
	 *
	 * @param int $customer_id WP user ID.
	 * @return array Array of stdClass row objects.
	 */
	public static function get_by_customer( $customer_id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE customer_id = %d ORDER BY date_acquired DESC, date_add DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $customer_id
			)
		);
	}

	/**
	 * Retrieve a single collection item by customer and product.
	 *
	 * @param int $customer_id WP user ID.
	 * @param int $product_id  WooCommerce product ID.
	 * @return stdClass|null Row object or null if not found.
	 */
	public static function get_by_product_customer( $customer_id, $product_id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE customer_id = %d AND product_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $customer_id,
				(int) $product_id
			)
		);
	}

	/**
	 * Update the personal note for a collection item owned by a specific customer.
	 *
	 * @param int    $item_id     Row ID.
	 * @param int    $customer_id Owner's WP user ID (used as ownership guard).
	 * @param string $note        New note text.
	 * @return bool True on success.
	 */
	public static function update_note( $item_id, $customer_id, $note ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->update(
			$table,
			array(
				'personal_note' => sanitize_textarea_field( $note ),
				'date_upd'      => current_time( 'mysql' ),
			),
			array(
				'id'          => (int) $item_id,
				'customer_id' => (int) $customer_id,
			),
			array( '%s', '%s' ),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Toggle the is_public flag for a collection item owned by a specific customer.
	 *
	 * @param int $item_id     Row ID.
	 * @param int $customer_id Owner's WP user ID (ownership guard).
	 * @param int $is_public   1 to make public, 0 to make private.
	 * @return bool True on success.
	 */
	public static function set_public( $item_id, $customer_id, $is_public ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->update(
			$table,
			array(
				'is_public' => (int) (bool) $is_public,
				'date_upd'  => current_time( 'mysql' ),
			),
			array(
				'id'          => (int) $item_id,
				'customer_id' => (int) $customer_id,
			),
			array( '%d', '%s' ),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Remove a collection item owned by a specific customer.
	 *
	 * @param int $item_id     Row ID.
	 * @param int $customer_id Owner's WP user ID (ownership guard).
	 * @return bool True on success.
	 */
	public static function remove_item( $item_id, $customer_id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->delete(
			$table,
			array(
				'id'          => (int) $item_id,
				'customer_id' => (int) $customer_id,
			),
			array( '%d', '%d' )
		);

		return false !== $result;
	}

	/**
	 * Retrieve all collection items for admin display, with optional filtering.
	 *
	 * @param array $args {
	 *     Optional filter arguments.
	 *     @type string $search  Customer email or display-name fragment.
	 *     @type int    $limit   Number of rows to return (default 100).
	 *     @type int    $offset  Pagination offset (default 0).
	 * }
	 * @return array Array of stdClass row objects (with customer_email appended).
	 */
	public static function get_all_admin( $args = array() ) {
		global $wpdb;
		$table      = self::table();
		$users_table = $wpdb->users;

		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 100;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;
		$search = isset( $args['search'] ) ? sanitize_text_field( $args['search'] ) : '';

		$where = '';
		$params = array();

		if ( '' !== $search ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where   = "AND ( u.user_email LIKE %s OR u.display_name LIKE %s )";
			$params[] = $like;
			$params[] = $like;
		}

		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ci.*, u.user_email AS customer_email, u.display_name AS customer_name
				 FROM {$table} ci
				 LEFT JOIN {$users_table} u ON u.ID = ci.customer_id
				 WHERE 1=1 {$where}
				 ORDER BY ci.date_add DESC
				 LIMIT %d OFFSET %d",
				...$params
			)
		);
	}

	/**
	 * Return total count of collection items (used for admin pagination).
	 *
	 * @param string $search Optional email/name search fragment.
	 * @return int
	 */
	public static function count_all_admin( $search = '' ) {
		global $wpdb;
		$table       = self::table();
		$users_table = $wpdb->users;

		$where  = '';
		$params = array();

		if ( '' !== $search ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where   = 'AND ( u.user_email LIKE %s OR u.display_name LIKE %s )';
			$params[] = $like;
			$params[] = $like;
		}

		if ( $params ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} ci LEFT JOIN {$users_table} u ON u.ID = ci.customer_id WHERE 1=1 {$where}",
					...$params
				)
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Retrieve all public collection items for a given customer (used for public URL).
	 *
	 * @param int $customer_id WP user ID.
	 * @return array Array of stdClass row objects.
	 */
	public static function get_public_by_customer( $customer_id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE customer_id = %d AND is_public = 1 ORDER BY date_acquired DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $customer_id
			)
		);
	}
}
