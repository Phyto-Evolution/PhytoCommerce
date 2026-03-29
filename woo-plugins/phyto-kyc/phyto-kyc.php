<?php
/**
 * Plugin Name:       Phyto KYC
 * Plugin URI:        https://github.com/Phyto-Evolution/PhytoCommerce
 * Description:       Enforces KYC (Know Your Customer) identity verification before revealing prices on WooCommerce stores. Level 1 validates PAN (retail), Level 2 validates GST (B2B). Unverified visitors see blurred prices and a KYC prompt. Integrates with sandbox.co.in for live Indian identity verification.
 * Version:           1.0.0
 * Author:            PhytoCommerce
 * License:           MIT
 * Text Domain:       phyto-kyc
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * WC requires at least: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'PHYTO_KYC_VERSION', '1.0.0' );
define( 'PHYTO_KYC_DIR',     plugin_dir_path( __FILE__ ) );
define( 'PHYTO_KYC_URL',     plugin_dir_url( __FILE__ ) );

add_action( 'before_woocommerce_init', function () {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) )
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
} );

require_once PHYTO_KYC_DIR . 'includes/class-phyto-kyc-db.php';
require_once PHYTO_KYC_DIR . 'includes/class-phyto-kyc-sandbox.php';
require_once PHYTO_KYC_DIR . 'includes/class-phyto-kyc-frontend.php';
require_once PHYTO_KYC_DIR . 'includes/class-phyto-kyc-admin.php';

register_activation_hook( __FILE__, [ 'Phyto_KYC_DB', 'install' ] );
register_uninstall_hook( __FILE__, 'phyto_kyc_uninstall' );

function phyto_kyc_uninstall(): void {
    global $wpdb;
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_kyc_profile`" );
    $wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}phyto_kyc_document`" );
    delete_option( 'phyto_kyc_settings' );
}

add_action( 'plugins_loaded', function () {
    if ( ! class_exists( 'WooCommerce' ) ) return;
    Phyto_KYC_Frontend::init();
    if ( is_admin() ) Phyto_KYC_Admin::init();
} );
