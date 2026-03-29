<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Bundle_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_bundle` (
            `id_bundle`      BIGINT(20)     NOT NULL AUTO_INCREMENT,
            `name`           VARCHAR(255)   NOT NULL DEFAULT '',
            `description`    TEXT,
            `discount_type`  VARCHAR(10)    NOT NULL DEFAULT 'percent',
            `discount_value` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
            `active`         TINYINT(1)     NOT NULL DEFAULT 1,
            `date_add`       DATETIME       DEFAULT NULL,
            `date_upd`       DATETIME       DEFAULT NULL,
            PRIMARY KEY (`id_bundle`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_bundle_slot` (
            `id_slot`      BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `id_bundle`    BIGINT(20)   NOT NULL,
            `slot_name`    VARCHAR(100) NOT NULL DEFAULT '',
            `slot_type`    VARCHAR(50)  NOT NULL DEFAULT 'category',
            `category_id`  BIGINT(20)   NOT NULL DEFAULT 0,
            `required`     TINYINT(1)   NOT NULL DEFAULT 1,
            `position`     INT(11)      NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_slot`),
            KEY `id_bundle` (`id_bundle`)
        ) $charset;" );
    }

    public static function get_bundles( bool $active_only = true ): array {
        global $wpdb;
        $where = $active_only ? 'WHERE active=1' : '';
        return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}phyto_bundle` $where ORDER BY name" ) ?: [];
    }

    public static function get_bundle( int $id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_bundle` WHERE id_bundle=%d", $id
        ) ) ?: null;
    }

    public static function get_slots( int $id_bundle ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_bundle_slot` WHERE id_bundle=%d ORDER BY position",
            $id_bundle
        ) ) ?: [];
    }

    public static function save_bundle( array $data, int $id = 0 ): int|false {
        global $wpdb;
        $row = [
            'name'           => sanitize_text_field( $data['name'] ),
            'description'    => wp_kses_post( $data['description'] ?? '' ),
            'discount_type'  => in_array( $data['discount_type'] ?? '', [ 'percent', 'amount' ], true ) ? $data['discount_type'] : 'percent',
            'discount_value' => (float) ( $data['discount_value'] ?? 0 ),
            'active'         => isset( $data['active'] ) ? 1 : 0,
            'date_upd'       => current_time( 'mysql' ),
        ];
        if ( $id ) {
            $wpdb->update( $wpdb->prefix . 'phyto_bundle', $row, [ 'id_bundle' => $id ] );
            return $id;
        }
        $row['date_add'] = current_time( 'mysql' );
        $wpdb->insert( $wpdb->prefix . 'phyto_bundle', $row );
        return $wpdb->insert_id ?: false;
    }

    public static function save_slots( int $id_bundle, array $slots ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'phyto_bundle_slot';
        $wpdb->delete( $table, [ 'id_bundle' => $id_bundle ] );
        foreach ( $slots as $pos => $s ) {
            $wpdb->insert( $table, [
                'id_bundle'   => $id_bundle,
                'slot_name'   => sanitize_text_field( $s['slot_name'] ?? '' ),
                'slot_type'   => 'category',
                'category_id' => absint( $s['category_id'] ?? 0 ),
                'required'    => isset( $s['required'] ) ? 1 : 0,
                'position'    => (int) $pos,
            ] );
        }
    }

    /** Calculate discount amount for a total. */
    public static function calculate_discount( object $bundle, float $subtotal ): float {
        if ( $bundle->discount_type === 'percent' ) {
            return round( $subtotal * ( (float) $bundle->discount_value / 100 ), 2 );
        }
        return min( (float) $bundle->discount_value, $subtotal );
    }
}
