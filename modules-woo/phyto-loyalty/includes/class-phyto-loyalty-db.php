<?php
/**
 * Database layer for Phyto Loyalty.
 *
 * Handles table creation and all CRUD operations on the
 * {prefix}phyto_loyalty_ledger table.
 *
 * @package PhytoLoyalty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Loyalty_DB
 */
class Phyto_Loyalty_DB {

	/**
	 * Table name (without prefix).
	 */
	const TABLE = 'phyto_loyalty_ledger';

	/**
	 * Create (or upgrade) the ledger table via dbDelta.
	 */
	public static function create_table() {
		global $wpdb;

		$table      = $wpdb->prefix . self::TABLE;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id     BIGINT(20) UNSIGNED NOT NULL,
			order_id    BIGINT(20) UNSIGNED DEFAULT NULL,
			points      INT(11) NOT NULL DEFAULT 0,
			action      ENUM('earn','redeem','manual','expire') NOT NULL DEFAULT 'earn',
			note        TEXT DEFAULT NULL,
			created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY idx_user_id (user_id),
			KEY idx_order_id (order_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a ledger entry.
	 *
	 * @param int    $user_id  WordPress user ID.
	 * @param int    $points   Points delta (positive or negative).
	 * @param string $action   One of: earn, redeem, manual, expire.
	 * @param int    $order_id Optional order ID.
	 * @param string $note     Optional human-readable note.
	 * @return int|false Inserted row ID or false on failure.
	 */
	public static function add_entry( $user_id, $points, $action, $order_id = null, $note = '' ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		$result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$table,
			array(
				'user_id'    => absint( $user_id ),
				'order_id'   => $order_id ? absint( $order_id ) : null,
				'points'     => intval( $points ),
				'action'     => sanitize_key( $action ),
				'note'       => sanitize_text_field( $note ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		return $result ? $wpdb->insert_id : false;
	}

	/**
	 * Get current point balance for a user.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return int Current balance (never negative).
	 */
	public static function get_balance( $user_id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		$balance = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT SUM(points) FROM {$table} WHERE user_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $user_id )
			)
		);

		return max( 0, (int) $balance );
	}

	/**
	 * Get ledger rows for a user, most recent first.
	 *
	 * @param int $user_id WordPress user ID.
	 * @param int $limit   Number of rows to return (0 = all).
	 * @return array Array of row objects.
	 */
	public static function get_ledger( $user_id, $limit = 20 ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		if ( $limit > 0 ) {
			$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $user_id ),
					absint( $limit )
				)
			);
		} else {
			$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $user_id )
				)
			);
		}

		return $rows ? $rows : array();
	}

	/**
	 * Get all ledger rows linked to a specific order.
	 *
	 * @param int $order_id WooCommerce order ID.
	 * @return array Array of row objects.
	 */
	public static function get_by_order( $order_id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE order_id = %d ORDER BY created_at ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $order_id )
			)
		);

		return $rows ? $rows : array();
	}

	/**
	 * Expire points older than the configured expiry window for a user.
	 *
	 * Calculates how many points are "expired" and inserts a negative expire
	 * entry so the balance is correct. Points already used (redeem/expire rows)
	 * are not double-counted.
	 *
	 * @param int $user_id     WordPress user ID.
	 * @param int $expiry_days Number of days after which earned points expire.
	 */
	public static function expire_old_points( $user_id, $expiry_days ) {
		global $wpdb;

		if ( $expiry_days <= 0 ) {
			return;
		}

		$table    = $wpdb->prefix . self::TABLE;
		$cutoff   = gmdate( 'Y-m-d H:i:s', strtotime( "-{$expiry_days} days" ) );

		// Sum of points earned before cutoff.
		$earned = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT COALESCE(SUM(points),0) FROM {$table} WHERE user_id = %d AND action = 'earn' AND created_at < %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $user_id ),
				$cutoff
			)
		);

		// Sum already expired or redeemed (negative entries).
		$used = abs(
			(int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT COALESCE(SUM(points),0) FROM {$table} WHERE user_id = %d AND action IN ('redeem','expire')", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $user_id )
				)
			)
		);

		$to_expire = $earned - $used;

		if ( $to_expire > 0 ) {
			self::add_entry( $user_id, -$to_expire, 'expire', null, __( 'Points expired', 'phyto-loyalty' ) );
		}
	}
}
