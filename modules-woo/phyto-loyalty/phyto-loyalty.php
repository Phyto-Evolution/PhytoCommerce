<?php
/**
 * Plugin Name:       Phyto Loyalty (WooCommerce)
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Points-based loyalty programme. Customers earn Green Points on purchases and redeem them as cart discounts.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-loyalty
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoLoyalty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_LOYALTY_VERSION', '1.0.0' );
define( 'PHYTO_LOYALTY_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_LOYALTY_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_loyalty_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_loyalty_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_loyalty_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Loyalty requires %s to be installed and active.', 'phyto-loyalty' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Create the loyalty ledger table on activation.
 */
function phyto_loyalty_activate() {
	require_once PHYTO_LOYALTY_PATH . 'includes/class-phyto-loyalty-db.php';
	Phyto_Loyalty_DB::create_table();
}
register_activation_hook( __FILE__, 'phyto_loyalty_activate' );

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_loyalty_init() {
	if ( ! phyto_loyalty_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_LOYALTY_PATH . 'includes/class-phyto-loyalty-db.php';
	require_once PHYTO_LOYALTY_PATH . 'includes/class-phyto-loyalty-settings.php';
	require_once PHYTO_LOYALTY_PATH . 'includes/class-phyto-loyalty-admin.php';
	require_once PHYTO_LOYALTY_PATH . 'includes/class-phyto-loyalty-frontend.php';

	$settings = new Phyto_Loyalty_Settings();
	$admin    = new Phyto_Loyalty_Admin();
	$frontend = new Phyto_Loyalty_Frontend();

	$settings->register_hooks();
	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_loyalty_init' );
