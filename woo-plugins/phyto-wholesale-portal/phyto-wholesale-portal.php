<?php
/**
 * Plugin Name:       Phyto Wholesale Portal
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       B2B wholesale tier for WooCommerce. Businesses apply for wholesale access, admin approves/rejects, and approved customers are assigned a WP role with wholesale pricing. Supports per-product MOQ and tiered quantity pricing. Optional invoice-on-delivery payment terms for approved wholesalers.
 * Version:           1.0.0
 * Author:            PhytoCommerce
 * License:           MIT
 * Text Domain:       phyto-wholesale
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_WS_VERSION', '1.0.0' );
define( 'PHYTO_WS_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_WS_URL',     plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) )
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
} );

require_once PHYTO_WS_DIR . 'includes/class-phyto-ws-db.php';
require_once PHYTO_WS_DIR . 'includes/class-phyto-ws-pricing.php';
require_once PHYTO_WS_DIR . 'includes/class-phyto-ws-frontend.php';
require_once PHYTO_WS_DIR . 'includes/class-phyto-ws-admin.php';

register_activation_hook( __FILE__, 'phyto_ws_activate' );
register_uninstall_hook( __FILE__, 'phyto_ws_uninstall' );

function phyto_ws_activate(): void {
    Phyto_WS_DB::install();
    // Create 'phyto_wholesaler' WP role
    if ( ! get_role( 'phyto_wholesaler' ) ) {
        add_role( 'phyto_wholesaler', __( 'PhytoCommerce Wholesaler', 'phyto-wholesale' ), [
            'read'         => true,
            'edit_posts'   => false,
        ] );
    }
}

function phyto_ws_uninstall(): void {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_wholesale_application`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_wholesale_product`" );
    remove_role( 'phyto_wholesaler' );
    delete_option( 'phyto_ws_settings' );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_WS_Pricing::init();
    Phyto_WS_Frontend::init();
    if ( is_admin() ) Phyto_WS_Admin::init();
} );
