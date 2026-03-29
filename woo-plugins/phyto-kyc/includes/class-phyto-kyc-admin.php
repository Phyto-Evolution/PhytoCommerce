<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_KYC_Admin {

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_phyto_kyc_review', [ __CLASS__, 'handle_review' ] );
    }

    public static function register_menu(): void {
        add_submenu_page(
            'woocommerce',
            __( 'KYC Verification', 'phyto-kyc' ),
            __( 'KYC', 'phyto-kyc' ),
            'manage_woocommerce',
            'phyto-kyc',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting( 'phyto_kyc_settings_group', 'phyto_kyc_settings' );
    }

    public static function render_page(): void {
        $tab = sanitize_key( $_GET['tab'] ?? 'list' );
        $settings = get_option( 'phyto_kyc_settings', [] );
        global $wpdb;
        $profiles = $wpdb->get_results(
            "SELECT p.*, u.user_email FROM `{$wpdb->prefix}phyto_kyc_profile` p
             JOIN `{$wpdb->users}` u ON u.ID = p.user_id
             ORDER BY p.date_upd DESC LIMIT 200"
        );
        include PHYTO_KYC_DIR . 'templates/admin-kyc.php';
    }

    public static function handle_review(): void {
        check_admin_referer( 'phyto_kyc_review' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Unauthorized' );
        global $wpdb;

        $id      = absint( $_POST['id_kyc_profile'] );
        $level   = absint( $_POST['kyc_level_target'] ?? 1 );
        $status  = in_array( $_POST['review_status'] ?? '', [ 'Verified', 'Rejected' ], true ) ? $_POST['review_status'] : 'Pending';
        $notes   = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );

        $update  = [ 'admin_notes' => $notes, 'reviewed_by' => get_current_user_id(), 'date_upd' => current_time( 'mysql' ) ];
        if ( $level === 1 ) $update['level1_status'] = $status;
        else                $update['level2_status'] = $status;
        if ( $status === 'Verified' ) $update['kyc_level'] = $level;

        $wpdb->update( $wpdb->prefix . 'phyto_kyc_profile', $update, [ 'id_kyc_profile' => $id ] );
        wp_redirect( admin_url( 'admin.php?page=phyto-kyc&updated=1' ) );
        exit;
    }
}
