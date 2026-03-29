<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Loyalty_Admin {

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        // Manual point adjustment on user edit screen
        add_action( 'edit_user_profile',        [ __CLASS__, 'render_user_panel' ] );
        add_action( 'show_user_profile',        [ __CLASS__, 'render_user_panel' ] );
        add_action( 'edit_user_profile_update', [ __CLASS__, 'save_user_panel' ] );
        add_action( 'personal_options_update',  [ __CLASS__, 'save_user_panel' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            __( 'Phyto Loyalty', 'phyto-loyalty' ),
            __( 'Phyto Loyalty', 'phyto-loyalty' ),
            'manage_woocommerce',
            'phyto-loyalty',
            [ __CLASS__, 'render_overview' ],
            'dashicons-star-filled',
            56
        );
        add_submenu_page( 'phyto-loyalty', __( 'Overview', 'phyto-loyalty' ),  __( 'Overview', 'phyto-loyalty' ),  'manage_woocommerce', 'phyto-loyalty',           [ __CLASS__, 'render_overview' ] );
        add_submenu_page( 'phyto-loyalty', __( 'Customers', 'phyto-loyalty' ), __( 'Customers', 'phyto-loyalty' ), 'manage_woocommerce', 'phyto-loyalty-customers', [ __CLASS__, 'render_customers' ] );
        add_submenu_page( 'phyto-loyalty', __( 'Settings', 'phyto-loyalty' ),  __( 'Settings', 'phyto-loyalty' ),  'manage_woocommerce', 'phyto-loyalty-settings',  [ __CLASS__, 'render_settings' ] );
    }

    public static function register_settings(): void {
        register_setting( 'phyto_loyalty_settings_group', 'phyto_loyalty_settings' );
    }

    public static function render_overview(): void {
        global $wpdb;
        $stats = $wpdb->get_row( "SELECT SUM(points_balance) as total_outstanding, SUM(points_lifetime) as total_earned, SUM(points_redeemed) as total_redeemed, COUNT(*) as members FROM `{$wpdb->prefix}phyto_loyalty_account`" );
        include PHYTO_LOYALTY_DIR . 'templates/admin-overview.php';
    }

    public static function render_customers(): void {
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT a.*, u.user_email FROM `{$wpdb->prefix}phyto_loyalty_account` a JOIN `{$wpdb->users}` u ON u.ID=a.user_id ORDER BY a.points_balance DESC LIMIT 200" );
        include PHYTO_LOYALTY_DIR . 'templates/admin-customers.php';
    }

    public static function render_settings(): void {
        $settings = get_option( 'phyto_loyalty_settings', [] );
        include PHYTO_LOYALTY_DIR . 'templates/admin-settings.php';
    }

    public static function render_user_panel( WP_User $user ): void {
        $account = Phyto_Loyalty_DB::get_account( $user->ID );
        include PHYTO_LOYALTY_DIR . 'templates/admin-user-panel.php';
    }

    public static function save_user_panel( int $user_id ): void {
        if ( ! current_user_can( 'edit_user', $user_id ) ) return;
        if ( empty( $_POST['phyto_loyalty_adjust'] ) ) return;

        $points = (int) ( $_POST['phyto_loyalty_adjust_points'] ?? 0 );
        $note   = sanitize_text_field( $_POST['phyto_loyalty_adjust_note'] ?? '' );
        if ( $points === 0 ) return;

        Phyto_Loyalty_DB::add_transaction( $user_id, 'adjust', $points, 0, $note ?: __( 'Manual admin adjustment', 'phyto-loyalty' ) );
    }
}
