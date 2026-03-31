<?php
/**
 * Plugin Name:       Phyto Restock Alert for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       "Notify me when available" subscriber system for out-of-stock plant products. Auto-notifies subscribers when stock is restored.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-restock-alert
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoRestockAlert
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_RS_VERSION', '1.0.0' );
define( 'PHYTO_RS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_RS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_rs_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_rs_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_rs_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Restock Alert requires %s to be installed and active.', 'phyto-restock-alert' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Create the subscribers DB table on plugin activation.
 */
function phyto_rs_activate() {
	require_once PHYTO_RS_PATH . 'includes/class-phyto-rs-db.php';
	Phyto_RS_DB::create_table();
}
register_activation_hook( __FILE__, 'phyto_rs_activate' );

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_rs_init() {
	if ( ! phyto_rs_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_RS_PATH . 'includes/class-phyto-rs-db.php';
	require_once PHYTO_RS_PATH . 'includes/class-phyto-rs-admin.php';
	require_once PHYTO_RS_PATH . 'includes/class-phyto-rs-frontend.php';

	$admin    = new Phyto_RS_Admin();
	$frontend = new Phyto_RS_Frontend();

	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_rs_init' );
