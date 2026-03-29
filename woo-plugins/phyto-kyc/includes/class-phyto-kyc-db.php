<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_KYC_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_kyc_profile` (
            `id_kyc_profile`  BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `user_id`         BIGINT(20)   NOT NULL,
            `kyc_level`       TINYINT(1)   NOT NULL DEFAULT 0,
            `level1_status`   VARCHAR(20)  NOT NULL DEFAULT 'NotStarted',
            `level2_status`   VARCHAR(20)  NOT NULL DEFAULT 'NotStarted',
            `pan_number`      VARCHAR(20)  DEFAULT NULL,
            `pan_name`        VARCHAR(200) DEFAULT NULL,
            `gst_number`      VARCHAR(20)  DEFAULT NULL,
            `business_pan`    VARCHAR(20)  DEFAULT NULL,
            `business_name`   VARCHAR(200) DEFAULT NULL,
            `api_response_l1` LONGTEXT,
            `api_response_l2` LONGTEXT,
            `admin_notes`     TEXT,
            `reviewed_by`     BIGINT(20)   DEFAULT NULL,
            `date_add`        DATETIME     DEFAULT NULL,
            `date_upd`        DATETIME     DEFAULT NULL,
            PRIMARY KEY (`id_kyc_profile`),
            UNIQUE KEY `user_id` (`user_id`)
        ) $charset;" );

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_kyc_document` (
            `id_document`    BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `id_kyc_profile` BIGINT(20)   NOT NULL,
            `user_id`        BIGINT(20)   NOT NULL,
            `kyc_level`      TINYINT(1)   NOT NULL DEFAULT 1,
            `doc_type`       VARCHAR(50)  NOT NULL,
            `file_path`      VARCHAR(500) NOT NULL,
            `file_name`      VARCHAR(255) NOT NULL,
            `mime_type`      VARCHAR(100) DEFAULT NULL,
            `date_add`       DATETIME     DEFAULT NULL,
            PRIMARY KEY (`id_document`),
            KEY `id_kyc_profile` (`id_kyc_profile`),
            KEY `user_id` (`user_id`)
        ) $charset;" );

        add_option( 'phyto_kyc_settings', [
            'enabled'    => 1,
            'mode'       => 'sandbox',
            'api_key'    => '',
            'require_l1' => 1,
            'require_l2' => 0,
        ] );
    }

    public static function get_profile( int $user_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_kyc_profile` WHERE user_id=%d",
            $user_id
        ) ) ?: null;
    }

    public static function ensure_profile( int $user_id ): object {
        $p = self::get_profile( $user_id );
        if ( ! $p ) {
            global $wpdb;
            $wpdb->insert( $wpdb->prefix . 'phyto_kyc_profile', [
                'user_id'  => $user_id,
                'date_add' => current_time( 'mysql' ),
                'date_upd' => current_time( 'mysql' ),
            ] );
            $p = self::get_profile( $user_id );
        }
        return $p;
    }

    public static function is_verified( int $user_id ): bool {
        $settings = get_option( 'phyto_kyc_settings', [] );
        if ( empty( $settings['enabled'] ) ) return true; // KYC disabled — everyone passes
        $profile  = self::get_profile( $user_id );
        if ( ! $profile ) return false;
        if ( ! empty( $settings['require_l1'] ) && $profile->level1_status !== 'Verified' ) return false;
        if ( ! empty( $settings['require_l2'] ) && $profile->level2_status !== 'Verified' ) return false;
        return true;
    }
}
