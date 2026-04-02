<?php
/**
 * Plugin Name:       Phyto Quick Add for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Rapid product creation with AI-generated descriptions, multi-provider AI settings, and taxonomy pack importer from the PhytoCommerce taxonomy library.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * Text Domain:       phyto-quickadd
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoQuickAdd
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'PHYTO_QA_VERSION', '1.0.0' );
define( 'PHYTO_QA_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_QA_URL', plugin_dir_url( __FILE__ ) );

function phyto_qa_init() {
	if ( ! class_exists( 'WooCommerce' ) ) { return; }

	require_once PHYTO_QA_PATH . 'includes/class-phyto-qa-ai.php';
	require_once PHYTO_QA_PATH . 'includes/class-phyto-qa-taxonomy.php';
	require_once PHYTO_QA_PATH . 'includes/class-phyto-qa-admin.php';

	( new Phyto_QA_Admin() )->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_qa_init' );

function phyto_qa_declare_hpos() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'phyto_qa_declare_hpos' );
