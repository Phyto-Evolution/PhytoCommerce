<?php
/**
 * Plugin Name:       Phyto Bundle Builder
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Build-your-own plant bundle for WooCommerce. Define bundles with named slots (e.g. "Carnivore", "Succulent", "Bromeliad"), each tied to a product category. Customers pick one product per slot from a guided front-end UI. Bundles support % or flat discount. Compatible with WooCommerce HPOS.
 * Version:           1.0.0
 * Author:            PhytoCommerce
 * License:           MIT
 * Text Domain:       phyto-bundle
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_BUNDLE_VERSION', '1.0.0' );
define( 'PHYTO_BUNDLE_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_BUNDLE_URL',     plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) )
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
} );

require_once PHYTO_BUNDLE_DIR . 'includes/class-phyto-bundle-db.php';
require_once PHYTO_BUNDLE_DIR . 'includes/class-phyto-bundle-frontend.php';
require_once PHYTO_BUNDLE_DIR . 'includes/class-phyto-bundle-cart.php';
require_once PHYTO_BUNDLE_DIR . 'includes/class-phyto-bundle-admin.php';

register_activation_hook( __FILE__, [ 'Phyto_Bundle_DB', 'install' ] );
register_uninstall_hook( __FILE__, 'phyto_bundle_uninstall' );

function phyto_bundle_uninstall(): void {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_bundle`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_bundle_slot`" );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_Bundle_Frontend::init();
    Phyto_Bundle_Cart::init();
    if ( is_admin() ) Phyto_Bundle_Admin::init();
} );
