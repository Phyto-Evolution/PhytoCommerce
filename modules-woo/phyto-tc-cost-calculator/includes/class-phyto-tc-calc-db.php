<?php
/**
 * DB layer for Phyto TC Cost Calculator.
 *
 * @package PhytoTcCostCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_TC_Calc_DB {

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_tc_cost_estimate';
	}

	public static function create_table() {
		global $wpdb;
		$t  = self::table();
		$cc = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$t} (
			id             BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			batch_id       VARCHAR(100)        NOT NULL DEFAULT '',
			estimate_label VARCHAR(200)        NOT NULL DEFAULT '',
			inputs_json    LONGTEXT,
			results_json   LONGTEXT,
			created_at     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at     DATETIME            NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY (id)
		) {$cc};";
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function save( $data ) {
		global $wpdb;
		$t   = self::table();
		$now = current_time( 'mysql' );
		$row = array(
			'batch_id'       => sanitize_text_field( $data['batch_id'] ?? '' ),
			'estimate_label' => sanitize_text_field( $data['estimate_label'] ?? '' ),
			'inputs_json'    => wp_json_encode( $data['inputs'] ?? array() ),
			'results_json'   => wp_json_encode( $data['results'] ?? array() ),
			'updated_at'     => $now,
		);
		$id = absint( $data['id'] ?? 0 );
		if ( $id ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( $t, $row, array( 'id' => $id ), null, array( '%d' ) );
			return $id;
		}
		$row['created_at'] = $now;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->insert( $t, $row );
		return $wpdb->insert_id;
	}

	public static function get_all() {
		global $wpdb;
		$t = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $wpdb->get_results( "SELECT * FROM {$t} ORDER BY created_at DESC" );
	}

	public static function get( $id ) {
		global $wpdb;
		$t = self::table();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$t} WHERE id = %d", absint( $id ) ) );
	}

	public static function delete( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->delete( self::table(), array( 'id' => absint( $id ) ), array( '%d' ) );
	}
}
