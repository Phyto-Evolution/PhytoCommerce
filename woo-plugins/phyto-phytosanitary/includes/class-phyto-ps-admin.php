<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_PS_Admin {

    public static function init(): void {
        add_action( 'admin_menu',               [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts',    [ __CLASS__, 'enqueue' ] );
        add_action( 'admin_post_phyto_ps_save', [ __CLASS__, 'handle_save' ] );
        add_action( 'admin_post_phyto_ps_delete', [ __CLASS__, 'handle_delete' ] );

        // Product meta box
        add_action( 'add_meta_boxes',           [ __CLASS__, 'add_meta_box' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            __( 'Phytosanitary', 'phyto-phytosanitary' ),
            __( 'Phytosanitary', 'phyto-phytosanitary' ),
            'manage_woocommerce',
            'phyto-phytosanitary',
            [ __CLASS__, 'render_list' ],
            'dashicons-media-document',
            59
        );
        add_submenu_page(
            'phyto-phytosanitary',
            __( 'All Documents', 'phyto-phytosanitary' ),
            __( 'All Documents', 'phyto-phytosanitary' ),
            'manage_woocommerce',
            'phyto-phytosanitary',
            [ __CLASS__, 'render_list' ]
        );
        add_submenu_page(
            'phyto-phytosanitary',
            __( 'Expiring Soon', 'phyto-phytosanitary' ),
            __( 'Expiring Soon', 'phyto-phytosanitary' ),
            'manage_woocommerce',
            'phyto-phytosanitary-expiring',
            [ __CLASS__, 'render_expiring' ]
        );
        add_submenu_page(
            'phyto-phytosanitary',
            __( 'Add Document', 'phyto-phytosanitary' ),
            __( 'Add Document', 'phyto-phytosanitary' ),
            'manage_woocommerce',
            'phyto-phytosanitary-new',
            [ __CLASS__, 'render_edit' ]
        );
    }

    public static function enqueue( string $hook ): void {
        if ( strpos( $hook, 'phyto-phytosanitary' ) === false && $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
        wp_enqueue_style(  'phyto-ps-admin', PHYTO_PS_URL . 'assets/css/admin.css', [], PHYTO_PS_VERSION );
        wp_enqueue_media();
        wp_enqueue_script( 'phyto-ps-admin', PHYTO_PS_URL . 'assets/js/admin.js', [ 'jquery' ], PHYTO_PS_VERSION, true );
    }

    public static function render_list(): void {
        $type = sanitize_text_field( $_GET['doc_type'] ?? '' );
        $docs = Phyto_PS_DB::get_all( $type );
        include PHYTO_PS_DIR . 'templates/admin-list.php';
    }

    public static function render_expiring(): void {
        $days = absint( $_GET['days'] ?? 30 );
        $docs = Phyto_PS_DB::get_expiring( $days );
        include PHYTO_PS_DIR . 'templates/admin-expiring.php';
    }

    public static function render_edit(): void {
        $id  = absint( $_GET['id'] ?? 0 );
        $doc = $id ? Phyto_PS_DB::get( $id ) : null;

        // For "edit from product page context"
        $product_id = absint( $_GET['product_id'] ?? ( $doc->product_id ?? 0 ) );

        include PHYTO_PS_DIR . 'templates/admin-edit.php';
    }

    public static function handle_save(): void {
        check_admin_referer( 'phyto_ps_save' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Unauthorized' );

        $id = absint( $_POST['id_doc'] ?? 0 );
        Phyto_PS_DB::save( $_POST, $id );
        wp_redirect( admin_url( 'admin.php?page=phyto-phytosanitary&saved=1' ) );
        exit;
    }

    public static function handle_delete(): void {
        check_admin_referer( 'phyto_ps_delete' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_die( 'Unauthorized' );

        $id = absint( $_GET['id'] ?? 0 );
        if ( $id ) Phyto_PS_DB::delete( $id );
        wp_redirect( admin_url( 'admin.php?page=phyto-phytosanitary&deleted=1' ) );
        exit;
    }

    // ── Product meta box ─────────────────────────────────────────────────────

    public static function add_meta_box(): void {
        add_meta_box(
            'phyto-ps-docs',
            __( 'Compliance Documents', 'phyto-phytosanitary' ),
            [ __CLASS__, 'render_meta_box' ],
            'product',
            'normal',
            'default'
        );
    }

    public static function render_meta_box( WP_Post $post ): void {
        $product_id = $post->ID;
        $docs       = Phyto_PS_DB::get_by_product( $product_id );
        include PHYTO_PS_DIR . 'templates/meta-box-docs.php';
    }

    /** Helper: allowed doc types. */
    public static function doc_types(): array {
        return [
            'phytosanitary' => __( 'Phytosanitary Certificate', 'phyto-phytosanitary' ),
            'cites'         => __( 'CITES Permit', 'phyto-phytosanitary' ),
            'import_permit' => __( 'Import Permit', 'phyto-phytosanitary' ),
            'quality_cert'  => __( 'Quality Certificate', 'phyto-phytosanitary' ),
            'origin_cert'   => __( 'Certificate of Origin', 'phyto-phytosanitary' ),
            'nursery_cert'  => __( 'Nursery Registration', 'phyto-phytosanitary' ),
            'other'         => __( 'Other', 'phyto-phytosanitary' ),
        ];
    }
}
