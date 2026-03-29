<?php
/**
 * Plugin Name:       Phyto Quick Add
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Streamlined product creation for WooCommerce plant catalogues. Loads botanical taxonomy packs from the PhytoCommerce GitHub taxonomy directory, auto-suggests genus/species/cultivar with smart dropdowns, and creates WooCommerce products + category hierarchy in one form. Supports AI-generated descriptions via OpenAI/Anthropic.
 * Version:           3.0.0
 * Author:            PhytoCommerce
 * License:           MIT
 * Text Domain:       phyto-quickadd
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_QA_VERSION', '3.0.0' );
define( 'PHYTO_QA_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_QA_URL',     plugin_dir_url( __FILE__ ) );

require_once PHYTO_QA_DIR . 'includes/class-phyto-taxonomy.php';
require_once PHYTO_QA_DIR . 'includes/class-phyto-qa-admin.php';

register_activation_hook( __FILE__, '__return_null' ); // No DB tables needed
register_uninstall_hook( __FILE__, 'phyto_qa_uninstall' );

function phyto_qa_uninstall(): void {
    delete_option( 'phyto_qa_settings' );
    // Purge taxonomy cache transients
    global $wpdb;
    $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_phyto_tax_%'" );
    $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE option_name LIKE '_transient_timeout_phyto_tax_%'" );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    if ( is_admin() ) Phyto_QA_Admin::init();
} );
