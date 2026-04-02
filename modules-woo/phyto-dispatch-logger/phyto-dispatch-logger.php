<?php
/**
 * Plugin Name:       Phyto Dispatch Logger for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Records dispatch conditions (temperature, humidity, packing method, gel/heat packs, transit days, staff, photo) against WooCommerce orders. Displays a Dispatch Conditions card on the customer Order Details page.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-dispatch-logger
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoDispatchLogger
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_DL_VERSION', '1.0.0' );
define( 'PHYTO_DL_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_DL_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'custom_order_tables',
				__FILE__,
				true
			);
		}
	}
);

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_dl_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_dl_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_dl_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Dispatch Logger requires %s to be installed and active.', 'phyto-dispatch-logger' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Create the dispatch log database table on plugin activation.
 */
function phyto_dl_activate() {
	require_once PHYTO_DL_PATH . 'includes/class-phyto-dl-db.php';
	Phyto_DL_DB::create_table();
}
register_activation_hook( __FILE__, 'phyto_dl_activate' );

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_dl_init() {
	if ( ! phyto_dl_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_DL_PATH . 'includes/class-phyto-dl-db.php';
	require_once PHYTO_DL_PATH . 'includes/class-phyto-dl-admin.php';
	require_once PHYTO_DL_PATH . 'includes/class-phyto-dl-frontend.php';

	$admin    = new Phyto_DL_Admin();
	$frontend = new Phyto_DL_Frontend();

	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_dl_init' );
