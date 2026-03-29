<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_TCB_DB {

    const GENERATIONS = [ 'G0', 'G1', 'G2', 'G3+', 'Acclimated', 'Hardened' ];
    const STATUSES     = [ 'Active', 'Depleted', 'Quarantined', 'Archived' ];
    const CONTAMINATION_TYPES = [ 'Bacterial', 'Fungal', 'Viral', 'Pest', 'Unknown', 'Other' ];

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_tc_batch` (
            `id_batch`           BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `parent_id_batch`    BIGINT(20)   DEFAULT NULL,
            `batch_code`         VARCHAR(50)  NOT NULL,
            `species_name`       VARCHAR(200) NOT NULL DEFAULT '',
            `generation`         VARCHAR(20)  NOT NULL DEFAULT 'G0',
            `date_initiation`    DATE         DEFAULT NULL,
            `date_deflask`       DATE         DEFAULT NULL,
            `date_certified`     DATE         DEFAULT NULL,
            `sterility_protocol` TEXT,
            `units_produced`     INT(11)      NOT NULL DEFAULT 0,
            `units_remaining`    INT(11)      NOT NULL DEFAULT 0,
            `low_stock_alerted`  TINYINT(1)   NOT NULL DEFAULT 0,
            `batch_status`       VARCHAR(20)  NOT NULL DEFAULT 'Active',
            `notes`              TEXT,
            `date_add`           DATETIME     NOT NULL,
            `date_upd`           DATETIME     NOT NULL,
            PRIMARY KEY (`id_batch`),
            UNIQUE KEY `batch_code` (`batch_code`),
            KEY `idx_status` (`batch_status`),
            KEY `idx_parent` (`parent_id_batch`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_tc_batch_product` (
            `id_link`      BIGINT(20) NOT NULL AUTO_INCREMENT,
            `product_id`   BIGINT(20) NOT NULL,
            `variation_id` BIGINT(20) NOT NULL DEFAULT 0,
            `id_batch`     BIGINT(20) NOT NULL,
            PRIMARY KEY (`id_link`),
            UNIQUE KEY `product_variation_batch` (`product_id`, `variation_id`),
            KEY `idx_batch` (`id_batch`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_tc_contamination_log` (
            `id_log`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `id_batch`       BIGINT(20)   NOT NULL,
            `incident_date`  DATE         NOT NULL,
            `type`           VARCHAR(20)  NOT NULL DEFAULT 'Unknown',
            `affected_units` INT(11)      NOT NULL DEFAULT 0,
            `description`    TEXT,
            `resolved`       TINYINT(1)   NOT NULL DEFAULT 0,
            `date_add`       DATETIME     NOT NULL,
            `date_upd`       DATETIME     NOT NULL,
            PRIMARY KEY (`id_log`),
            KEY `idx_batch` (`id_batch`),
            KEY `idx_date` (`incident_date`),
            KEY `idx_resolved` (`resolved`)
        ) $charset;" );

        add_option( 'phyto_tcb_settings', [ 'low_stock_threshold' => 10 ] );
    }

    public static function suggest_batch_code( string $species_prefix = 'TC' ): string {
        global $wpdb;
        $prefix = strtoupper( substr( preg_replace( '/[^A-Za-z]/', '', $species_prefix ), 0, 4 ) ) ?: 'TC';
        $last   = $wpdb->get_var( $wpdb->prepare(
            "SELECT batch_code FROM `{$wpdb->prefix}phyto_tc_batch` WHERE batch_code LIKE %s ORDER BY batch_code DESC",
            $prefix . '%'
        ) );
        $next = $last ? ( (int) substr( $last, strlen( $prefix ) ) + 1 ) : 1;
        return $prefix . str_pad( $next, 4, '0', STR_PAD_LEFT );
    }

    public static function create_batch( array $data ): int|false {
        global $wpdb;
        $now  = current_time( 'mysql' );
        $wpdb->insert( $wpdb->prefix . 'phyto_tc_batch', [
            'parent_id_batch'    => absint( $data['parent_id_batch'] ?? 0 ) ?: null,
            'batch_code'         => sanitize_text_field( $data['batch_code'] ),
            'species_name'       => sanitize_text_field( $data['species_name'] ),
            'generation'         => in_array( $data['generation'], self::GENERATIONS, true ) ? $data['generation'] : 'G0',
            'date_initiation'    => sanitize_text_field( $data['date_initiation'] ?? '' ) ?: null,
            'date_deflask'       => sanitize_text_field( $data['date_deflask']    ?? '' ) ?: null,
            'sterility_protocol' => sanitize_textarea_field( $data['sterility_protocol'] ?? '' ),
            'units_produced'     => absint( $data['units_produced'] ?? 0 ),
            'units_remaining'    => absint( $data['units_remaining'] ?? $data['units_produced'] ?? 0 ),
            'batch_status'       => in_array( $data['batch_status'], self::STATUSES, true ) ? $data['batch_status'] : 'Active',
            'notes'              => sanitize_textarea_field( $data['notes'] ?? '' ),
            'date_add'           => $now,
            'date_upd'           => $now,
        ] );
        return $wpdb->insert_id ?: false;
    }

    public static function get_batch( int $id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_tc_batch` WHERE id_batch=%d", $id
        ) ) ?: null;
    }

    public static function get_batches( string $status = '', int $limit = 100 ): array {
        global $wpdb;
        $where = $status ? $wpdb->prepare( 'WHERE batch_status=%s', $status ) : '';
        return $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}phyto_tc_batch` $where ORDER BY date_add DESC LIMIT $limit" ) ?: [];
    }

    public static function link_product( int $product_id, int $variation_id, int $batch_id ): void {
        global $wpdb;
        $wpdb->replace( $wpdb->prefix . 'phyto_tc_batch_product', [
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'id_batch'     => $batch_id,
        ] );
    }

    public static function get_batch_by_product( int $product_id, int $variation_id = 0 ): ?object {
        global $wpdb;
        $link = $wpdb->get_row( $wpdb->prepare(
            "SELECT id_batch FROM `{$wpdb->prefix}phyto_tc_batch_product` WHERE product_id=%d AND variation_id=%d",
            $product_id, $variation_id
        ) );
        return $link ? self::get_batch( (int) $link->id_batch ) : null;
    }
}
