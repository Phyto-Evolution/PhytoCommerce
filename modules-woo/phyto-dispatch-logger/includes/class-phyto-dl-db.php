<?php
/**
 * Database layer for Phyto Dispatch Logger.
 *
 * Manages the {prefix}phyto_dispatch_log table.
 *
 * Columns:
 *  id             — auto-increment primary key
 *  order_id       — WooCommerce order ID (UNIQUE)
 *  dispatch_date  — DATE
 *  temp_celsius   — DECIMAL(5,2)
 *  humidity_pct   — DECIMAL(5,2)
 *  packing_method — VARCHAR(100)
 *  gel_pack       — TINYINT(1)
 *  heat_pack      — TINYINT(1)
 *  transit_days   — SMALLINT UNSIGNED
 *  staff_name     — VARCHAR(150)
 *  notes          — TEXT
 *  photo_filename — VARCHAR(255)
 *  date_add       — DATETIME
 *  date_upd       — DATETIME
 *
 * @package PhytoDispatchLogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_DL_DB
 */
class Phyto_DL_DB {

	/**
	 * Return the full table name including the WP prefix.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_dispatch_log';
	}

	/**
	 * Create (or upgrade) the dispatch log table using dbDelta.
	 *
	 * Safe to call on every activation — dbDelta only alters when needed.
	 */
	public static function create_table() {
		global $wpdb;

		$table      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id       BIGINT(20) UNSIGNED NOT NULL,
			dispatch_date  DATE            NOT NULL,
			temp_celsius   DECIMAL(5,2)    NOT NULL DEFAULT '0.00',
			humidity_pct   DECIMAL(5,2)    NOT NULL DEFAULT '0.00',
			packing_method VARCHAR(100)    NOT NULL DEFAULT '',
			gel_pack       TINYINT(1)      NOT NULL DEFAULT 0,
			heat_pack      TINYINT(1)      NOT NULL DEFAULT 0,
			transit_days   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
			staff_name     VARCHAR(150)    NOT NULL DEFAULT '',
			notes          TEXT,
			photo_filename VARCHAR(255)    NOT NULL DEFAULT '',
			date_add       DATETIME        NOT NULL DEFAULT '0000-00-00 00:00:00',
			date_upd       DATETIME        NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY order_id (order_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Retrieve a single dispatch log row by WooCommerce order ID.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return object|null Row as stdClass, or null if not found.
	 */
	public static function get_by_order( $order_id ) {
		global $wpdb;

		$table = self::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table}` WHERE order_id = %d LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(int) $order_id
			)
		);
	}

	/**
	 * Insert or update a dispatch log record for a given order.
	 *
	 * Uses INSERT … ON DUPLICATE KEY UPDATE because order_id has a UNIQUE key.
	 *
	 * @param array $data Associative array of column => value pairs (excluding id, date_add, date_upd).
	 * @return int|false Number of affected rows, or false on failure.
	 */
	public static function upsert( array $data ) {
		global $wpdb;

		$table = self::table_name();
		$now   = current_time( 'mysql' );

		$dispatch_date  = isset( $data['dispatch_date'] ) ? sanitize_text_field( $data['dispatch_date'] ) : '';
		$temp_celsius   = isset( $data['temp_celsius'] ) ? (float) $data['temp_celsius'] : 0.0;
		$humidity_pct   = isset( $data['humidity_pct'] ) ? (float) $data['humidity_pct'] : 0.0;
		$packing_method = isset( $data['packing_method'] ) ? sanitize_text_field( $data['packing_method'] ) : '';
		$gel_pack       = isset( $data['gel_pack'] ) ? (int) (bool) $data['gel_pack'] : 0;
		$heat_pack      = isset( $data['heat_pack'] ) ? (int) (bool) $data['heat_pack'] : 0;
		$transit_days   = isset( $data['transit_days'] ) ? absint( $data['transit_days'] ) : 0;
		$staff_name     = isset( $data['staff_name'] ) ? sanitize_text_field( $data['staff_name'] ) : '';
		$notes          = isset( $data['notes'] ) ? sanitize_textarea_field( $data['notes'] ) : '';
		$photo_filename = isset( $data['photo_filename'] ) ? sanitize_file_name( $data['photo_filename'] ) : '';
		$order_id       = isset( $data['order_id'] ) ? absint( $data['order_id'] ) : 0;

		if ( 0 === $order_id ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO `{$table}`
					(order_id, dispatch_date, temp_celsius, humidity_pct, packing_method,
					 gel_pack, heat_pack, transit_days, staff_name, notes,
					 photo_filename, date_add, date_upd)
				VALUES
					(%d, %s, %f, %f, %s, %d, %d, %d, %s, %s, %s, %s, %s)
				ON DUPLICATE KEY UPDATE
					dispatch_date  = VALUES(dispatch_date),
					temp_celsius   = VALUES(temp_celsius),
					humidity_pct   = VALUES(humidity_pct),
					packing_method = VALUES(packing_method),
					gel_pack       = VALUES(gel_pack),
					heat_pack      = VALUES(heat_pack),
					transit_days   = VALUES(transit_days),
					staff_name     = VALUES(staff_name),
					notes          = VALUES(notes),
					photo_filename = IF(VALUES(photo_filename) != '', VALUES(photo_filename), photo_filename),
					date_upd       = VALUES(date_upd)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$order_id,
				$dispatch_date,
				$temp_celsius,
				$humidity_pct,
				$packing_method,
				$gel_pack,
				$heat_pack,
				$transit_days,
				$staff_name,
				$notes,
				$photo_filename,
				$now,
				$now
			)
		);
	}

	/**
	 * Retrieve all dispatch log rows, newest first.
	 *
	 * @param int $limit  Maximum rows to return. 0 = no limit.
	 * @param int $offset Row offset for pagination.
	 * @return array Array of stdClass row objects.
	 */
	public static function get_all( $limit = 0, $offset = 0 ) {
		global $wpdb;

		$table = self::table_name();

		if ( $limit > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$table}` ORDER BY dispatch_date DESC, id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					(int) $limit,
					(int) $offset
				)
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY dispatch_date DESC, id DESC" );
	}

	/**
	 * Delete a dispatch log row by its primary key.
	 *
	 * @param int $id Row ID.
	 * @return int|false Number of rows deleted, or false on failure.
	 */
	public static function delete( $id ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->delete(
			self::table_name(),
			array( 'id' => (int) $id ),
			array( '%d' )
		);
	}
}
