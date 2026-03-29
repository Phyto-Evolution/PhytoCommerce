<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_WS_Admin {

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_post_phyto_ws_update_status', [ __CLASS__, 'handle_status_update' ] );
        // Product wholesale settings tab
        add_filter( 'woocommerce_product_data_tabs',     [ __CLASS__, 'add_product_tab' ] );
        add_action( 'woocommerce_product_data_panels',   [ __CLASS__, 'render_product_panel' ] );
        add_action( 'woocommerce_process_product_meta',  [ __CLASS__, 'save_product_panel' ] );
    }

    public static function register_menu(): void {
        add_submenu_page(
            'woocommerce',
            __( 'Wholesale Applications', 'phyto-wholesale' ),
            __( 'Wholesale', 'phyto-wholesale' ),
            'manage_woocommerce',
            'phyto-wholesale',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function render_page(): void {
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT a.*, u.user_email FROM `{$wpdb->prefix}phyto_wholesale_application` a
             LEFT JOIN `{$wpdb->users}` u ON u.ID = a.user_id
             ORDER BY a.date_add DESC LIMIT 200"
        );
        include PHYTO_WS_DIR . 'templates/admin-applications.php';
    }

    public static function handle_status_update(): void {
        check_admin_referer( 'phyto_ws_status' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Unauthorized' );

        $id_app = absint( $_POST['id_app'] ?? 0 );
        $status = in_array( $_POST['status'] ?? '', [ 'Approved', 'Rejected', 'Pending' ], true )
                  ? $_POST['status'] : 'Pending';
        $notes  = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );

        Phyto_WS_DB::update_status( $id_app, $status, $notes );
        wp_redirect( admin_url( 'admin.php?page=phyto-wholesale&updated=1' ) );
        exit;
    }

    public static function add_product_tab( array $tabs ): array {
        $tabs['phyto_wholesale'] = [
            'label'  => __( 'Wholesale', 'phyto-wholesale' ),
            'target' => 'phyto_wholesale_product_data',
            'class'  => [],
        ];
        return $tabs;
    }

    public static function render_product_panel(): void {
        global $post;
        $config = Phyto_WS_DB::get_product_config( $post->ID );
        $tiers  = $config ? json_decode( $config->price_tiers ?? '[]', true ) : [];
        include PHYTO_WS_DIR . 'templates/admin-product-panel.php';
    }

    public static function save_product_panel( int $post_id ): void {
        $moq           = absint( $_POST['phyto_ws_moq'] ?? 0 );
        $wholesale_only = isset( $_POST['phyto_ws_wholesale_only'] ) ? 1 : 0;
        $tiers_raw      = $_POST['phyto_ws_tiers'] ?? [];
        $tiers          = [];
        foreach ( (array) $tiers_raw as $t ) {
            if ( isset( $t['min_qty'], $t['price'] ) ) {
                $tiers[] = [ 'min_qty' => absint( $t['min_qty'] ), 'price' => floatval( $t['price'] ) ];
            }
        }
        Phyto_WS_DB::save_product_config( $post_id, $moq, $tiers, (bool) $wholesale_only );
    }
}
