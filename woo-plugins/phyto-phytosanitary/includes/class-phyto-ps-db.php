<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_PS_DB {

    public static function install(): void {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}phyto_ps_document` (
            `id_doc`      BIGINT(20)   NOT NULL AUTO_INCREMENT,
            `product_id`  BIGINT(20)   NOT NULL,
            `doc_type`    VARCHAR(50)  NOT NULL DEFAULT 'phytosanitary',
            `doc_title`   VARCHAR(255) NOT NULL DEFAULT '',
            `attachment_id` BIGINT(20) NOT NULL DEFAULT 0,
            `issue_date`  DATE         DEFAULT NULL,
            `expiry_date` DATE         DEFAULT NULL,
            `issuing_authority` VARCHAR(255) NOT NULL DEFAULT '',
            `reference_number`  VARCHAR(100) NOT NULL DEFAULT '',
            `public`      TINYINT(1)   NOT NULL DEFAULT 1,
            `date_add`    DATETIME     DEFAULT NULL,
            `date_upd`    DATETIME     DEFAULT NULL,
            PRIMARY KEY (`id_doc`),
            KEY `product_id` (`product_id`),
            KEY `doc_type`   (`doc_type`),
            KEY `expiry_date` (`expiry_date`)
        ) $charset;" );
    }

    /** All documents for a product, optionally filtered by public status. */
    public static function get_by_product( int $product_id, bool $public_only = false ): array {
        global $wpdb;
        $where = $public_only
            ? $wpdb->prepare( 'WHERE product_id = %d AND public = 1', $product_id )
            : $wpdb->prepare( 'WHERE product_id = %d', $product_id );
        return $wpdb->get_results(
            "SELECT * FROM `{$wpdb->prefix}phyto_ps_document` $where ORDER BY expiry_date ASC"
        ) ?: [];
    }

    public static function get( int $id_doc ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM `{$wpdb->prefix}phyto_ps_document` WHERE id_doc = %d", $id_doc
        ) ) ?: null;
    }

    public static function save( array $data, int $id_doc = 0 ): int|false {
        global $wpdb;
        $row = [
            'product_id'         => absint( $data['product_id'] ?? 0 ),
            'doc_type'           => sanitize_text_field( $data['doc_type'] ?? 'phytosanitary' ),
            'doc_title'          => sanitize_text_field( $data['doc_title'] ?? '' ),
            'attachment_id'      => absint( $data['attachment_id'] ?? 0 ),
            'issue_date'         => sanitize_text_field( $data['issue_date'] ?? '' ) ?: null,
            'expiry_date'        => sanitize_text_field( $data['expiry_date'] ?? '' ) ?: null,
            'issuing_authority'  => sanitize_text_field( $data['issuing_authority'] ?? '' ),
            'reference_number'   => sanitize_text_field( $data['reference_number'] ?? '' ),
            'public'             => isset( $data['public'] ) ? 1 : 0,
            'date_upd'           => current_time( 'mysql' ),
        ];
        if ( $id_doc ) {
            $wpdb->update( $wpdb->prefix . 'phyto_ps_document', $row, [ 'id_doc' => $id_doc ] );
            return $id_doc;
        }
        $row['date_add'] = current_time( 'mysql' );
        $wpdb->insert( $wpdb->prefix . 'phyto_ps_document', $row );
        return $wpdb->insert_id ?: false;
    }

    public static function delete( int $id_doc ): void {
        global $wpdb;
        $wpdb->delete( $wpdb->prefix . 'phyto_ps_document', [ 'id_doc' => $id_doc ] );
    }

    /** Documents expiring within $days from now (for admin dashboard warnings). */
    public static function get_expiring( int $days = 30 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT d.*, p.post_title AS product_name
               FROM `{$wpdb->prefix}phyto_ps_document` d
               LEFT JOIN `{$wpdb->posts}` p ON p.ID = d.product_id
              WHERE d.expiry_date IS NOT NULL
                AND d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL %d DAY)
              ORDER BY d.expiry_date ASC",
            $days
        ) ) ?: [];
    }

    /** All documents with their product name (for admin list). */
    public static function get_all( string $doc_type = '' ): array {
        global $wpdb;
        $where = $doc_type
            ? $wpdb->prepare( 'WHERE d.doc_type = %s', $doc_type )
            : '';
        return $wpdb->get_results(
            "SELECT d.*, p.post_title AS product_name
               FROM `{$wpdb->prefix}phyto_ps_document` d
               LEFT JOIN `{$wpdb->posts}` p ON p.ID = d.product_id
             $where
             ORDER BY d.expiry_date ASC"
        ) ?: [];
    }
}
