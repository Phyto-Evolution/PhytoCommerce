<?php
/**
 * Plugin Name:       Phyto TC Cost Calculator for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Admin-only tissue-culture batch cost calculator. Tracks substrate, overhead and labour costs, derives cost-per-plant and suggests retail pricing at configurable margin targets.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-tc-cost-calculator
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoTcCostCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_TC_CALC_VERSION', '1.0.0' );
define( 'PHYTO_TC_CALC_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_TC_CALC_URL', plugin_dir_url( __FILE__ ) );

function phyto_tc_calc_check_wc() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Phyto TC Cost Calculator requires WooCommerce.', 'phyto-tc-cost-calculator' ) . '</p></div>';
		} );
		return false;
	}
	return true;
}

function phyto_tc_calc_activate() {
	require_once PHYTO_TC_CALC_PATH . 'includes/class-phyto-tc-calc-db.php';
	Phyto_TC_Calc_DB::create_table();
}
register_activation_hook( __FILE__, 'phyto_tc_calc_activate' );

function phyto_tc_calc_init() {
	if ( ! phyto_tc_calc_check_wc() || ! is_admin() ) {
		return;
	}
	require_once PHYTO_TC_CALC_PATH . 'includes/class-phyto-tc-calc-db.php';
	require_once PHYTO_TC_CALC_PATH . 'includes/class-phyto-tc-calc-admin.php';
	( new Phyto_TC_Calc_Admin() )->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_tc_calc_init' );
