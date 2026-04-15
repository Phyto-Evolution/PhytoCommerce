<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Restock_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_restock_alert` (
            `id_alert`             BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `product_id`           BIGINT(20)   NOT NULL,
            `variation_id`         BIGINT(20)   NOT NULL DEFAULT 0,
            `user_id`              BIGINT(20)   NOT NULL DEFAULT 0,
            `email`                VARCHAR(255) NOT NULL,
            `firstname`            VARCHAR(100) DEFAULT NULL,
            `date_add`             DATETIME     DEFAULT NULL,
            `notified`             TINYINT(1)   NOT NULL DEFAULT 0,
            `date_notified`        DATETIME     DEFAULT NULL,
            PRIMARY KEY (`id_alert`),
            UNIQUE KEY `uniq_product_email` (`product_id`, `variation_id`, `email`),
            KEY `idx_product_notified` (`product_id`, `variation_id`, `notified`)
        ) $charset;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        add_option( 'phyto_restock_settings', [
            'from_name'    => '',
            'max_per_run'  => 50,
            'show_form'    => 1,
        ] );
    }

    public static function subscribe( int $product_id, int $variation_id, string $email, string $firstname = '', int $user_id = 0 ): bool {
        global $wpdb;
        $table = $wpdb->prefix . 'phyto_restock_alert';

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id_alert FROM `$table` WHERE product_id=%d AND variation_id=%d AND email=%s",
            $product_id, $variation_id, $email
        ) );
        if ( $exists ) return false;

        return (bool) $wpdb->insert( $table, [
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'user_id'      => $user_id,
            'email'        => sanitize_email( $email ),
            'firstname'    => sanitize_text_field( $firstname ),
            'date_add'     => current_time( 'mysql' ),
            'notified'     => 0,
        ] );
    }

    public static function get_subscribers( int $product_id, int $variation_id = 0, bool $unnotified_only = true ): array {
        global $wpdb;
        $table = $wpdb->prefix . 'phyto_restock_alert';
        $where = $unnotified_only ? 'AND notified = 0' : '';
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM `$table` WHERE product_id=%d AND variation_id=%d $where",
            $product_id, $variation_id
        ) );
    }

    public static function mark_notified( array $ids ): void {
        if ( empty( $ids ) ) return;
        global $wpdb;
        $table       = $wpdb->prefix . 'phyto_restock_alert';
        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        $wpdb->query( $wpdb->prepare(
            "UPDATE `$table` SET notified=1, date_notified=%s WHERE id_alert IN ($placeholders)",
            array_merge( [ current_time( 'mysql' ) ], $ids )
        ) );
    }
}
