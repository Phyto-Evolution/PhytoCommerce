<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Phyto_QA_Admin {

    public static function init(): void {
        add_action( 'admin_menu',             [ __CLASS__, 'register_menu' ] );
        add_action( 'admin_enqueue_scripts',  [ __CLASS__, 'enqueue' ] );
        // AJAX: product creation
        add_action( 'wp_ajax_phyto_qa_create_product', [ __CLASS__, 'handle_create' ] );
        // AJAX: taxonomy import
        add_action( 'wp_ajax_phyto_qa_import_pack',    [ __CLASS__, 'handle_import' ] );
        // AJAX: AI description generation
        add_action( 'wp_ajax_phyto_qa_ai_description', [ __CLASS__, 'handle_ai_description' ] );
        // AJAX: fetch pack data for dropdowns
        add_action( 'wp_ajax_phyto_qa_fetch_pack',     [ __CLASS__, 'handle_fetch_pack' ] );
    }

    public static function register_menu(): void {
        add_submenu_page(
            'woocommerce',
            __( 'Phyto Quick Add', 'phyto-quickadd' ),
            __( 'Quick Add', 'phyto-quickadd' ),
            'manage_woocommerce',
            'phyto-quickadd',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function enqueue( string $hook ): void {
        if ( strpos( $hook, 'phyto-quickadd' ) === false ) return;
        wp_enqueue_style( 'phyto-qa-admin', PHYTO_QA_URL . 'assets/css/admin.css', [], PHYTO_QA_VERSION );
        wp_enqueue_script( 'phyto-qa-admin', PHYTO_QA_URL . 'assets/js/admin.js', [ 'jquery', 'select2' ], PHYTO_QA_VERSION, true );
        wp_localize_script( 'phyto-qa-admin', 'phytoQA', [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'phyto_qa' ),
        ] );
    }

    public static function render_page(): void {
        $tab      = sanitize_key( $_GET['tab'] ?? 'quickadd' );
        $index    = Phyto_Taxonomy::fetch_index();
        $settings = get_option( 'phyto_qa_settings', [] );
        // Get all WC product categories for the form
        $categories = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name' ] );
        include PHYTO_QA_DIR . 'templates/admin-quickadd.php';
    }

    public static function handle_create(): void {
        check_ajax_referer( 'phyto_qa', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error( [ 'message' => 'Unauthorized' ] );

        $name        = sanitize_text_field( $_POST['name']        ?? '' );
        $sku         = sanitize_text_field( $_POST['sku']         ?? '' );
        $price       = floatval( $_POST['price']                  ?? 0 );
        $stock       = intval( $_POST['stock']                    ?? 0 );
        $description = wp_kses_post( $_POST['description']        ?? '' );
        $cat_ids     = array_map( 'absint', (array) ( $_POST['categories'] ?? [] ) );

        if ( ! $name ) wp_send_json_error( [ 'message' => __( 'Product name is required.', 'phyto-quickadd' ) ] );

        $product = new WC_Product_Simple();
        $product->set_name( $name );
        $product->set_sku( $sku ?: '' );
        $product->set_regular_price( (string) $price );
        $product->set_stock_quantity( $stock );
        $product->set_manage_stock( true );
        $product->set_stock_status( $stock > 0 ? 'instock' : 'outofstock' );
        $product->set_description( $description );
        $product->set_category_ids( $cat_ids );
        $product->set_status( 'publish' );

        $product_id = $product->save();
        if ( ! $product_id ) wp_send_json_error( [ 'message' => __( 'Product could not be created.', 'phyto-quickadd' ) ] );

        wp_send_json_success( [
            'product_id'  => $product_id,
            'edit_url'    => get_edit_post_link( $product_id, 'url' ),
            'message'     => sprintf( __( 'Product "%s" created (ID: %d)', 'phyto-quickadd' ), esc_html( $name ), $product_id ),
        ] );
    }

    public static function handle_import(): void {
        check_ajax_referer( 'phyto_qa', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error( [ 'message' => 'Unauthorized' ] );

        $pack_file = sanitize_text_field( $_POST['pack_file'] ?? '' );
        if ( ! $pack_file ) wp_send_json_error( [ 'message' => 'No pack specified.' ] );

        $result = Phyto_Taxonomy::import_pack( $pack_file );
        if ( $result['success'] ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result );
        }
    }

    public static function handle_ai_description(): void {
        check_ajax_referer( 'phyto_qa', 'nonce' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) wp_send_json_error( [ 'message' => 'Unauthorized' ] );

        $settings = get_option( 'phyto_qa_settings', [] );
        $provider = $settings['ai_provider'] ?? 'openai';
        $api_key  = $settings['ai_api_key']  ?? '';
        $name     = sanitize_text_field( $_POST['name'] ?? '' );
        $notes    = sanitize_text_field( $_POST['notes'] ?? '' );

        if ( ! $api_key || ! $name ) wp_send_json_error( [ 'message' => 'Missing API key or product name.' ] );

        $prompt = "Write a short, engaging WooCommerce product description (2-3 sentences) for a plant: \"$name\". Notes: $notes. Focus on care requirements, unique features, and appeal to plant enthusiasts.";

        if ( $provider === 'anthropic' ) {
            $response = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
                'headers' => [ 'x-api-key' => $api_key, 'anthropic-version' => '2023-06-01', 'Content-Type' => 'application/json' ],
                'body'    => wp_json_encode( [ 'model' => 'claude-haiku-4-5-20251001', 'max_tokens' => 300, 'messages' => [ [ 'role' => 'user', 'content' => $prompt ] ] ] ),
                'timeout' => 30,
            ] );
            $body        = json_decode( wp_remote_retrieve_body( $response ), true );
            $description = $body['content'][0]['text'] ?? '';
        } else {
            $response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
                'headers' => [ 'Authorization' => 'Bearer ' . $api_key, 'Content-Type' => 'application/json' ],
                'body'    => wp_json_encode( [ 'model' => 'gpt-4o-mini', 'max_tokens' => 300, 'messages' => [ [ 'role' => 'user', 'content' => $prompt ] ] ] ),
                'timeout' => 30,
            ] );
            $body        = json_decode( wp_remote_retrieve_body( $response ), true );
            $description = $body['choices'][0]['message']['content'] ?? '';
        }

        if ( $description ) {
            wp_send_json_success( [ 'description' => wp_kses_post( $description ) ] );
        } else {
            wp_send_json_error( [ 'message' => 'AI description generation failed.' ] );
        }
    }

    public static function handle_fetch_pack(): void {
        check_ajax_referer( 'phyto_qa', 'nonce' );
        $pack_file = sanitize_text_field( $_POST['pack_file'] ?? '' );
        if ( ! $pack_file ) wp_send_json_error();
        $pack = Phyto_Taxonomy::fetch_pack( $pack_file );
        $pack ? wp_send_json_success( $pack ) : wp_send_json_error( [ 'message' => 'Could not fetch pack.' ] );
    }
}
