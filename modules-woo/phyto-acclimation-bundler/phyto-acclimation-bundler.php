<?php
/**
 * Plugin Name:       Phyto Acclimation Bundler for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Auto-suggests acclimation care accessories in a dismissable cart widget when a tissue-culture or deflasked plant is added. Configurable trigger tags, kit products, and optional bundle discount.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * Text Domain:       phyto-acclimation-bundler
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoAcclimationBundler
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PHYTO_AB_VERSION', '1.0.0' );
define( 'PHYTO_AB_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_AB_URL', plugin_dir_url( __FILE__ ) );

function phyto_ab_init() {
	if ( ! class_exists( 'WooCommerce' ) ) { return; }
	require_once PHYTO_AB_PATH . 'includes/class-phyto-ab-settings.php';
	require_once PHYTO_AB_PATH . 'includes/class-phyto-ab-frontend.php';
	( new Phyto_AB_Settings() )->register_hooks();
	( new Phyto_AB_Frontend() )->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_ab_init' );
