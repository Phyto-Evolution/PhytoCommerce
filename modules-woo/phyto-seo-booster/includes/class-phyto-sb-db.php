<?php
/**
 * Database layer for Phyto SEO Booster.
 *
 * @package PhytoSeoBooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_SB_DB {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_seo_audit';
	}

	public static function create_table() {
		global $wpdb;
		$table = self::table();
		$cc    = $wpdb->get_charset_collate();
		$sql   = "CREATE TABLE {$table} (
			id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			product_id  BIGINT(20) UNSIGNED NOT NULL,
			score       TINYINT UNSIGNED    NOT NULL DEFAULT 0,
			issues_json LONGTEXT,
			audited_at  DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id),
			UNIQUE KEY product_id (product_id)
		) {$cc};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function upsert( $product_id, $score, $issues ) {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (product_id, score, issues_json, audited_at)
				 VALUES (%d, %d, %s, %s)
				 ON DUPLICATE KEY UPDATE score=%d, issues_json=%s, audited_at=%s",
				$product_id, $score, wp_json_encode( $issues ), current_time( 'mysql' ),
				$score, wp_json_encode( $issues ), current_time( 'mysql' )
			)
		);
	}

	public static function get_all() {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY score ASC" );
	}

	public static function get_products_below_score( $threshold = 100 ) {
		global $wpdb;
		$table = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE score < %d ORDER BY score ASC",
				(int) $threshold
			)
		);
	}
}
