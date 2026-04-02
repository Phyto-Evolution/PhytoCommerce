<?php
/**
 * Plugin Name:       Phyto Wholesale Portal for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       B2B wholesale application flow, admin approval, per-product MOQ and tiered pricing, dedicated My Account tab, and wholesale role management.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * Text Domain:       phyto-wholesale-portal
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PHYTO_WP_VERSION', '1.0.0' );
define( 'PHYTO_WP_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_WP_URL', plugin_dir_url( __FILE__ ) );

function phyto_wp_init() {
	if ( ! class_exists( 'WooCommerce' ) ) { return; }

	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-db.php';
	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-roles.php';
	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-pricing.php';
	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-frontend.php';
	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-myaccount.php';
	require_once PHYTO_WP_PATH . 'includes/class-phyto-wp-admin.php';

	Phyto_WP_Roles::ensure_role();

	( new Phyto_WP_Pricing() )->register_hooks();
	( new Phyto_WP_Frontend() )->register_hooks();
	( new Phyto_WP_MyAccount() )->register_hooks();
	( new Phyto_WP_Admin() )->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_wp_init' );

register_activation_hook( __FILE__, 'phyto_wp_activate' );
function phyto_wp_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-phyto-wp-db.php';
	Phyto_WP_DB::install();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-phyto-wp-roles.php';
	Phyto_WP_Roles::ensure_role();
}

function phyto_wp_declare_hpos() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'phyto_wp_declare_hpos' );
