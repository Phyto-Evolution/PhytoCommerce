<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_TCB_Admin {

    public static function init(): void {
        add_action( 'admin_menu', [ __CLASS__, 'register_menu' ] );
        add_action( 'wp_ajax_phyto_tcb_create',   [ __CLASS__, 'ajax_create' ] );
        add_action( 'wp_ajax_phyto_tcb_suggest',  [ __CLASS__, 'ajax_suggest' ] );
        add_action( 'admin_enqueue_scripts',       [ __CLASS__, 'enqueue' ] );
    }

    public static function register_menu(): void {
        add_menu_page(
            __( 'TC Batch Tracker', 'phyto-tc-batch' ),
            __( 'TC Batches', 'phyto-tc-batch' ),
            'manage_woocommerce',
            'phyto-tc-batch',
            [ __CLASS__, 'render_page' ],
            'dashicons-analytics',
            57
        );
    }

    public static function enqueue( string $hook ): void {
        if ( strpos( $hook, 'phyto-tc-batch' ) === false ) return;
        wp_enqueue_script( 'phyto-tcb-admin', PHYTO_TCB_URL . 'assets/js/admin.js', [ 'jquery' ], PHYTO_TCB_VERSION, true );
        wp_localize_script( 'phyto-tcb-admin', 'phytoTCB', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_tcb' ),
        ] );
    }

    public static function render_page(): void {
        $tab     = sanitize_key( $_GET['tab'] ?? 'list' );
        $batches = Phyto_TCB_DB::get_batches();
        include PHYTO_TCB_DIR . 'templates/admin-batches.php';
    }

    public static function ajax_suggest(): void {
        check_ajax_referer( 'phyto_tcb', 'nonce' );
        $prefix = sanitize_text_field( $_POST['species'] ?? '' );
        wp_send_json_success( [ 'code' => Phyto_TCB_DB::suggest_batch_code( $prefix ) ] );
    }

    public static function ajax_create(): void {
        check_ajax_referer( 'phyto_tcb', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error( [ 'message' => 'Unauthorized' ] );
        $id = Phyto_TCB_DB::create_batch( $_POST );
        $id ? wp_send_json_success( [ 'id_batch' => $id, 'message' => __( 'Batch created.', 'phyto-tc-batch' ) ] )
            : wp_send_json_error( [ 'message' => __( 'Could not create batch.', 'phyto-tc-batch' ) ] );
    }
}
