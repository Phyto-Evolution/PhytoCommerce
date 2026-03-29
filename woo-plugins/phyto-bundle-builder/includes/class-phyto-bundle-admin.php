<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_Bundle_Admin {

    public static function init(): void {
        add_action( 'admin_menu',  [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_post_phyto_bundle_save', [ __CLASS__, 'handle_save' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            __( 'Bundle Builder', 'phyto-bundle' ),
            __( 'Bundles', 'phyto-bundle' ),
            'manage_woocommerce',
            'phyto-bundles',
            [ __CLASS__, 'render_list' ],
            'dashicons-products',
            58
        );
        add_submenu_page( 'phyto-bundles', __( 'All Bundles', 'phyto-bundle' ), __( 'All Bundles', 'phyto-bundle' ), 'manage_woocommerce', 'phyto-bundles',      [ __CLASS__, 'render_list' ] );
        add_submenu_page( 'phyto-bundles', __( 'New Bundle',  'phyto-bundle' ), __( 'New Bundle',  'phyto-bundle' ), 'manage_woocommerce', 'phyto-bundles-new', [ __CLASS__, 'render_edit' ] );
    }

    public static function enqueue( string $hook ): void {
        if ( strpos( $hook, 'phyto-bundle' ) === false ) return;
        wp_enqueue_script( 'phyto-bundle-admin', PHYTO_BUNDLE_URL . 'assets/js/admin.js', [ 'jquery' ], PHYTO_BUNDLE_VERSION, true );
    }

    public static function render_list(): void {
        $bundles = Phyto_Bundle_DB::get_bundles( false );
        include PHYTO_BUNDLE_DIR . 'templates/admin-list.php';
    }

    public static function render_edit(): void {
        $id     = absint( $_GET['id'] ?? 0 );
        $bundle = $id ? Phyto_Bundle_DB::get_bundle( $id ) : null;
        $slots  = $id ? Phyto_Bundle_DB::get_slots( $id ) : [];
        $cats   = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ] );
        include PHYTO_BUNDLE_DIR . 'templates/admin-edit.php';
    }

    public static function handle_save(): void {
        check_admin_referer( 'phyto_bundle_save' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Unauthorized' );

        $id    = absint( $_POST['id_bundle'] ?? 0 );
        $id    = Phyto_Bundle_DB::save_bundle( $_POST, $id );
        $slots = (array) ( $_POST['slots'] ?? [] );
        if ( $id ) Phyto_Bundle_DB::save_slots( $id, $slots );

        wp_redirect( admin_url( 'admin.php?page=phyto-bundles&saved=1' ) );
        exit;
    }
}
