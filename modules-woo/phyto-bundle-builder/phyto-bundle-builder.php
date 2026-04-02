<?php
/**
 * Plugin Name:       Phyto Bundle Builder for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Create named bundle templates with product slots; customers build custom bundles on the front end with real-time pricing and an automatic discount applied at checkout.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * Text Domain:       phyto-bundle-builder
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoBundleBuilder
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PHYTO_BB_VERSION', '1.0.0' );
define( 'PHYTO_BB_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_BB_URL', plugin_dir_url( __FILE__ ) );

function phyto_bb_init() {
	if ( ! class_exists( 'WooCommerce' ) ) { return; }

	require_once PHYTO_BB_PATH . 'includes/class-phyto-bb-db.php';
	require_once PHYTO_BB_PATH . 'includes/class-phyto-bb-admin.php';
	require_once PHYTO_BB_PATH . 'includes/class-phyto-bb-frontend.php';
	require_once PHYTO_BB_PATH . 'includes/class-phyto-bb-cart.php';

	( new Phyto_BB_Admin() )->register_hooks();
	( new Phyto_BB_Frontend() )->register_hooks();
	( new Phyto_BB_Cart() )->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_bb_init' );

register_activation_hook( __FILE__, 'phyto_bb_activate' );
function phyto_bb_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-phyto-bb-db.php';
	Phyto_BB_DB::install();
}

function phyto_bb_declare_hpos() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'phyto_bb_declare_hpos' );
