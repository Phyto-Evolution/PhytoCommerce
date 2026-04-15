<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Restock_Admin {

    public static function init(): void {
        add_action( 'admin_menu',    [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_init',    [ __CLASS__, 'register_settings' ] );
        // Show subscriber count in product list
        add_filter( 'manage_product_posts_columns',       [ __CLASS__, 'add_column' ] );
        add_action( 'manage_product_posts_custom_column', [ __CLASS__, 'render_column' ], 10, 2 );
    }

    public static function register_menu(): void {
        add_submenu_page(
            'woocommerce',
            __( 'Restock Alerts', 'phyto-restock-alert' ),
            __( 'Restock Alerts', 'phyto-restock-alert' ),
            'manage_woocommerce',
            'phyto-restock-alert',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting( 'phyto_restock_settings', 'phyto_restock_settings', [
            'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
        ] );
    }

    public static function sanitize_settings( array $input ): array {
        return [
            'from_name'   => sanitize_text_field( $input['from_name']   ?? '' ),
            'max_per_run' => absint( $input['max_per_run'] ?? 50 ),
            'show_form'   => isset( $input['show_form'] ) ? 1 : 0,
        ];
    }

    public static function render_page(): void {
        global $wpdb;
        $table      = $wpdb->prefix . 'phyto_restock_alert';
        $product_id = absint( $_GET['product_id'] ?? 0 );
        $settings   = get_option( 'phyto_restock_settings', [] );

        $rows = $product_id
            ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `$table` WHERE product_id=%d ORDER BY date_add DESC", $product_id ) )
            : $wpdb->get_results( "SELECT * FROM `$table` ORDER BY date_add DESC LIMIT 500" );

        include PHYTO_RESTOCK_DIR . 'templates/admin-page.php';
    }

    public static function add_column( array $columns ): array {
        $columns['phyto_restock'] = __( 'Alerts', 'phyto-restock-alert' );
        return $columns;
    }

    public static function render_column( string $column, int $post_id ): void {
        if ( $column !== 'phyto_restock' ) return;
        global $wpdb;
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM `{$wpdb->prefix}phyto_restock_alert` WHERE product_id=%d AND notified=0",
            $post_id
        ) );
        if ( $count > 0 ) {
            printf( '<a href="%s">%d</a>',
                esc_url( admin_url( 'admin.php?page=phyto-restock-alert&product_id=' . $post_id ) ),
                (int) $count
            );
        } else {
            echo '—';
        }
    }
}
