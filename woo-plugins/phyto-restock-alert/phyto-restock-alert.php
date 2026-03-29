<?php
/**
 * Plugin Name:       Phyto Restock Alert
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Adds a "Notify me when available" form to out-of-stock WooCommerce products. Customers subscribe without an account; when stock is replenished the module auto-dispatches notification emails and clears the list.
 * Version:           1.0.0
 * Author:            PhytoCommerce
 * Author URI:        https://phytocommerce.in
 * License:           MIT
 * Text Domain:       phyto-restock-alert
 * Domain Path:       /languages
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 * WC tested up to:   9.x
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_RESTOCK_VERSION', '1.0.0' );
define( 'PHYTO_RESTOCK_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_RESTOCK_URL',     plugin_dir_url( __FILE__ ) );

// Declare HPOS compatibility
add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

// ─── Autoload ────────────────────────────────────────────────────────────────
require_once PHYTO_RESTOCK_DIR . 'includes/class-phyto-restock-db.php';
require_once PHYTO_RESTOCK_DIR . 'includes/class-phyto-restock-frontend.php';
require_once PHYTO_RESTOCK_DIR . 'includes/class-phyto-restock-notifier.php';
require_once PHYTO_RESTOCK_DIR . 'includes/class-phyto-restock-admin.php';

// ─── Activation / Deactivation ───────────────────────────────────────────────
register_activation_hook( __FILE__, [ 'Phyto_Restock_DB', 'install' ] );
register_deactivation_hook( __FILE__, '__return_null' );

register_uninstall_hook( __FILE__, 'phyto_restock_uninstall' );
function phyto_restock_uninstall() {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_restock_alert`" );
    delete_option( 'phyto_restock_settings' );
}

// ─── Boot ────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_Restock_Frontend::init();
    Phyto_Restock_Notifier::init();
    if ( is_admin() ) {
        Phyto_Restock_Admin::init();
    }
} );
