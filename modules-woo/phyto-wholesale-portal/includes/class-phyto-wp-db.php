<?php
/**
 * Database management for Phyto Wholesale Portal.
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_DB {

	public static function install() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();
		$table   = $wpdb->prefix . 'phyto_wholesale_apps';

		$sql = "CREATE TABLE {$table} (
			id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id       BIGINT UNSIGNED NOT NULL DEFAULT 0,
			business_name VARCHAR(255) NOT NULL DEFAULT '',
			contact_name  VARCHAR(255) NOT NULL DEFAULT '',
			email         VARCHAR(255) NOT NULL DEFAULT '',
			phone         VARCHAR(50)  NOT NULL DEFAULT '',
			tax_id        VARCHAR(100) NOT NULL DEFAULT '',
			website       VARCHAR(255) NOT NULL DEFAULT '',
			notes         TEXT,
			status        ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
			created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id (user_id),
			KEY status  (status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'phyto_wholesale_apps';
	}

	public static function create_application( $data ) {
		global $wpdb;
		$wpdb->insert( self::table(), array(
			'user_id'       => absint( $data['user_id'] ?? 0 ),
			'business_name' => sanitize_text_field( $data['business_name'] ?? '' ),
			'contact_name'  => sanitize_text_field( $data['contact_name'] ?? '' ),
			'email'         => sanitize_email( $data['email'] ?? '' ),
			'phone'         => sanitize_text_field( $data['phone'] ?? '' ),
			'tax_id'        => sanitize_text_field( $data['tax_id'] ?? '' ),
			'website'       => esc_url_raw( $data['website'] ?? '' ),
			'notes'         => sanitize_textarea_field( $data['notes'] ?? '' ),
		) );
		return $wpdb->insert_id;
	}

	public static function get_by_user( $user_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM %i WHERE user_id = %d ORDER BY id DESC LIMIT 1",
			self::table(), absint( $user_id )
		) );
	}

	public static function get_all( $status = null ) {
		global $wpdb;
		if ( $status ) {
			return $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM %i WHERE status = %s ORDER BY created_at DESC",
				self::table(), $status
			) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i ORDER BY created_at DESC", self::table() ) );
	}

	public static function update_status( $id, $status ) {
		global $wpdb;
		$wpdb->update( self::table(), array( 'status' => $status ), array( 'id' => absint( $id ) ) );
	}
}
