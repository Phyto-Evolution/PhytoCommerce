<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_WS_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_wholesale_application` (
            `id_app`         BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `user_id`        BIGINT(20)   NOT NULL DEFAULT 0,
            `business_name`  VARCHAR(200) DEFAULT NULL,
            `gst_number`     VARCHAR(30)  DEFAULT NULL,
            `address`        TEXT,
            `phone`          VARCHAR(30)  DEFAULT NULL,
            `website`        VARCHAR(200) DEFAULT NULL,
            `message`        TEXT,
            `status`         VARCHAR(20)  NOT NULL DEFAULT 'Pending',
            `admin_notes`    TEXT,
            `date_add`       DATETIME     DEFAULT NULL,
            `date_upd`       DATETIME     DEFAULT NULL,
            PRIMARY KEY (`id_app`),
            KEY `idx_user` (`user_id`),
            KEY `idx_status` (`status`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_wholesale_product` (
            `id_ws`           BIGINT(20) NOT NULL AUTO_INCREMENT,
            `product_id`      BIGINT(20) NOT NULL,
            `moq`             INT(11)    NOT NULL DEFAULT 0,
            `price_tiers`     LONGTEXT,
            `wholesale_only`  TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_ws`),
            UNIQUE KEY `product_id` (`product_id`)
        ) $charset;" );

        add_option( 'phyto_ws_settings', [
            'require_approval'    => 1,
            'invoice_on_delivery' => 0,
            'invoice_days'        => 30,
        ] );
    }

    public static function get_application( int $user_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_wholesale_application` WHERE user_id=%d ORDER BY id_app DESC",
            $user_id
        ) ) ?: null;
    }

    public static function submit_application( int $user_id, array $data ): int|false {
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'phyto_wholesale_application', [
            'user_id'       => $user_id,
            'business_name' => sanitize_text_field( $data['business_name'] ?? '' ),
            'gst_number'    => sanitize_text_field( $data['gst_number']    ?? '' ),
            'address'       => sanitize_textarea_field( $data['address']   ?? '' ),
            'phone'         => sanitize_text_field( $data['phone']         ?? '' ),
            'website'       => esc_url_raw( $data['website']               ?? '' ),
            'message'       => sanitize_textarea_field( $data['message']   ?? '' ),
            'status'        => 'Pending',
            'date_add'      => current_time( 'mysql' ),
            'date_upd'      => current_time( 'mysql' ),
        ] );
        return $wpdb->insert_id ?: false;
    }

    public static function update_status( int $id_app, string $status, string $notes = '' ): void {
        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'phyto_wholesale_application', [
            'status'      => $status,
            'admin_notes' => sanitize_textarea_field( $notes ),
            'date_upd'    => current_time( 'mysql' ),
        ], [ 'id_app' => $id_app ] );

        // Promote/demote WP role
        $app = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM `{$wpdb->prefix}phyto_wholesale_application` WHERE id_app=%d", $id_app ) );
        if ( $app && $app->user_id ) {
            $user = new WP_User( $app->user_id );
            if ( $status === 'Approved' ) {
                $user->set_role( 'phyto_wholesaler' );
            } elseif ( $status === 'Rejected' ) {
                $user->set_role( 'customer' );
            }
        }
    }

    public static function get_product_config( int $product_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_wholesale_product` WHERE product_id=%d",
            $product_id
        ) ) ?: null;
    }

    public static function save_product_config( int $product_id, int $moq, array $tiers, bool $wholesale_only ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'phyto_wholesale_product';
        $data  = [
            'product_id'     => $product_id,
            'moq'            => $moq,
            'price_tiers'    => wp_json_encode( $tiers ),
            'wholesale_only' => $wholesale_only ? 1 : 0,
        ];
        $existing = self::get_product_config( $product_id );
        if ( $existing ) {
            $wpdb->update( $table, $data, [ 'product_id' => $product_id ] );
        } else {
            $wpdb->insert( $table, $data );
        }
    }
}
