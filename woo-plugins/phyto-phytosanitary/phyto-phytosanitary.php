<?php
/**
 * Plugin Name: Phyto Phytosanitary
 * Plugin URI:  https://github.com/Phyto-Evolution/PhytoCommerce
 * Description: Attach phytosanitary certificates, CITES permits, and compliance documents to products. Customers download docs on the product page; admin tracks expiry and compliance status.
 * Version:     1.0.0
 * Author:      Phyto Evolution
 * Text Domain: phyto-phytosanitary
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_PS_VERSION', '1.0.0' );
define( 'PHYTO_PS_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_PS_URL',     plugin_dir_url( __FILE__ ) );

// HPOS compatibility declaration
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

register_activation_hook( __FILE__, function () {
    require_once PHYTO_PS_DIR . 'includes/class-phyto-ps-db.php';
    Phyto_PS_DB::install();
} );

register_uninstall_hook( __FILE__, 'phyto_ps_uninstall' );
function phyto_ps_uninstall(): void {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_ps_document`" );
    delete_option( 'phyto_ps_doc_types' );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;

    $dir = PHYTO_PS_DIR . 'includes/';
    require_once $dir . 'class-phyto-ps-db.php';
    require_once $dir . 'class-phyto-ps-frontend.php';
    require_once $dir . 'class-phyto-ps-admin.php';

    Phyto_PS_Frontend::init();
    Phyto_PS_Admin::init();
} );
