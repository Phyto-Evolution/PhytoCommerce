<?php
/**
 * Plugin Name:       Phyto Live Arrival for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Live Arrival Guarantee (LAG) system for live plant orders — per-product policy, checkout opt-in, claim logging, and email reminders.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-live-arrival
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoLiveArrival
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_LAG_VERSION', '1.0.0' );
define( 'PHYTO_LAG_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_LAG_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_lag_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_lag_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_lag_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Live Arrival requires %s to be installed and active.', 'phyto-live-arrival' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_lag_init() {
	if ( ! phyto_lag_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_LAG_PATH . 'includes/class-phyto-lag-admin.php';
	require_once PHYTO_LAG_PATH . 'includes/class-phyto-lag-settings.php';
	require_once PHYTO_LAG_PATH . 'includes/class-phyto-lag-frontend.php';

	$admin    = new Phyto_LAG_Admin();
	$settings = new Phyto_LAG_Settings();
	$frontend = new Phyto_LAG_Frontend();

	$admin->register_hooks();
	$settings->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_lag_init' );
