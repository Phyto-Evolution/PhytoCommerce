<?php
/**
 * Plugin Name:       Phyto Loyalty
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Points-based loyalty programme for WooCommerce. Customers earn points on completed orders and redeem them as cart discounts. Includes tier system (Seed → Sprout → Bloom → Rare), configurable earn/redeem rates, point expiry, and an admin dashboard. Built for Indian plant stores transacting in INR.
 * Version:           1.0.0
 * Author:            PhytoCommerce
 * Author URI:        https://phytocommerce.in
 * License:           MIT
 * Text Domain:       phyto-loyalty
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 * WC tested up to:   9.x
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_LOYALTY_VERSION', '1.0.0' );
define( 'PHYTO_LOYALTY_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_LOYALTY_URL',     plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

require_once PHYTO_LOYALTY_DIR . 'includes/class-phyto-loyalty-db.php';
require_once PHYTO_LOYALTY_DIR . 'includes/class-phyto-loyalty-engine.php';
require_once PHYTO_LOYALTY_DIR . 'includes/class-phyto-loyalty-frontend.php';
require_once PHYTO_LOYALTY_DIR . 'includes/class-phyto-loyalty-admin.php';

register_activation_hook( __FILE__, [ 'Phyto_Loyalty_DB', 'install' ] );
register_uninstall_hook( __FILE__, 'phyto_loyalty_uninstall' );

function phyto_loyalty_uninstall() {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_loyalty_account`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_loyalty_transaction`" );
    delete_option( 'phyto_loyalty_settings' );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_Loyalty_Engine::init();
    Phyto_Loyalty_Frontend::init();
    if ( is_admin() ) Phyto_Loyalty_Admin::init();
} );
