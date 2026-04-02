<?php
/**
 * Database tables for Phyto Bundle Builder.
 *
 * phyto_bundle_templates — named bundle templates
 *   id, name, description, slot_count, discount_pct, status (active/draft), created_at
 *
 * phyto_bundle_slots — allowed product categories or specific products per slot
 *   id, template_id, slot_index, slot_label, product_ids (JSON), category_ids (JSON)
 *
 * @package PhytoBundleBuilder
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_BB_DB {

	public static function install() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$t1 = $wpdb->prefix . 'phyto_bundle_templates';
		$t2 = $wpdb->prefix . 'phyto_bundle_slots';

		$sql = "CREATE TABLE {$t1} (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name         VARCHAR(255) NOT NULL DEFAULT '',
			description  TEXT,
			slot_count   TINYINT UNSIGNED NOT NULL DEFAULT 3,
			discount_pct TINYINT UNSIGNED NOT NULL DEFAULT 0,
			status       ENUM('active','draft') NOT NULL DEFAULT 'draft',
			created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset};

		CREATE TABLE {$t2} (
			id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			template_id  BIGINT UNSIGNED NOT NULL,
			slot_index   TINYINT UNSIGNED NOT NULL DEFAULT 0,
			slot_label   VARCHAR(100) NOT NULL DEFAULT '',
			product_ids  TEXT,
			category_ids TEXT,
			PRIMARY KEY (id),
			KEY template_id (template_id)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public static function templates_table() { global $wpdb; return $wpdb->prefix . 'phyto_bundle_templates'; }
	public static function slots_table()     { global $wpdb; return $wpdb->prefix . 'phyto_bundle_slots'; }

	// ── Templates ────────────────────────────────────────────────────

	public static function get_templates( $status = null ) {
		global $wpdb;
		$t = self::templates_table();
		if ( $status ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE status = %s ORDER BY id DESC", $t, $status ) );
		}
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i ORDER BY id DESC", $t ) );
	}

	public static function get_template( $id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM %i WHERE id = %d", self::templates_table(), absint( $id ) ) );
	}

	public static function save_template( $data ) {
		global $wpdb;
		$fields = array(
			'name'         => sanitize_text_field( $data['name'] ?? '' ),
			'description'  => sanitize_textarea_field( $data['description'] ?? '' ),
			'slot_count'   => max( 1, absint( $data['slot_count'] ?? 3 ) ),
			'discount_pct' => min( 100, absint( $data['discount_pct'] ?? 0 ) ),
			'status'       => in_array( $data['status'] ?? '', array( 'active', 'draft' ), true ) ? $data['status'] : 'draft',
		);

		if ( ! empty( $data['id'] ) ) {
			$wpdb->update( self::templates_table(), $fields, array( 'id' => absint( $data['id'] ) ) );
			return absint( $data['id'] );
		}
		$wpdb->insert( self::templates_table(), $fields );
		return $wpdb->insert_id;
	}

	public static function delete_template( $id ) {
		global $wpdb;
		$id = absint( $id );
		$wpdb->delete( self::slots_table(), array( 'template_id' => $id ) );
		$wpdb->delete( self::templates_table(), array( 'id' => $id ) );
	}

	// ── Slots ─────────────────────────────────────────────────────────

	public static function get_slots( $template_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM %i WHERE template_id = %d ORDER BY slot_index ASC",
			self::slots_table(), absint( $template_id )
		) );
	}

	public static function save_slots( $template_id, $slots ) {
		global $wpdb;
		$template_id = absint( $template_id );
		$wpdb->delete( self::slots_table(), array( 'template_id' => $template_id ) );
		foreach ( $slots as $idx => $slot ) {
			$wpdb->insert( self::slots_table(), array(
				'template_id'  => $template_id,
				'slot_index'   => $idx,
				'slot_label'   => sanitize_text_field( $slot['label'] ?? "Slot " . ( $idx + 1 ) ),
				'product_ids'  => wp_json_encode( array_filter( array_map( 'absint', (array) ( $slot['product_ids'] ?? array() ) ) ) ),
				'category_ids' => wp_json_encode( array_filter( array_map( 'absint', (array) ( $slot['category_ids'] ?? array() ) ) ) ),
			) );
		}
	}
}
