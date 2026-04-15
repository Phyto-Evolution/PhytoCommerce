<?php
/**
 * Plugin Name:       Phyto TC Batch Tracker
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Tissue culture batch lifecycle management for WooCommerce. Track TC batches from initiation through deflasking to sale. Features: batch codes, generation tracking (G0–Acclimated), contamination logs, lineage (mother batch), unit counts, low-stock alerts, and product linking.
 * Version:           1.1.0
 * Author:            PhytoCommerce
 * License:           MIT
 * Text Domain:       phyto-tc-batch
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_TCB_VERSION', '1.1.0' );
define( 'PHYTO_TCB_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_TCB_URL',     plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) )
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
} );

require_once PHYTO_TCB_DIR . 'includes/class-phyto-tcb-db.php';
require_once PHYTO_TCB_DIR . 'includes/class-phyto-tcb-admin.php';
require_once PHYTO_TCB_DIR . 'includes/class-phyto-tcb-product.php';

register_activation_hook( __FILE__, [ 'Phyto_TCB_DB', 'install' ] );
register_uninstall_hook( __FILE__, 'phyto_tcb_uninstall' );

function phyto_tcb_uninstall(): void {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_tc_batch`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_tc_batch_product`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_tc_contamination_log`" );
    delete_option( 'phyto_tcb_settings' );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_TCB_Product::init();
    if ( is_admin() ) Phyto_TCB_Admin::init();
} );
