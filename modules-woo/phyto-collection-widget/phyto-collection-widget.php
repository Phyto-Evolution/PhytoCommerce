<?php
/**
 * Plugin Name:       Phyto Collection Widget for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Lets customers build a personal plant collection. Auto-adds purchased plants, shows a product-page badge, provides a My Account tab with inline note editing, an admin overview, and optional public collection URLs.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-collection-widget
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.4
 *
 * @package PhytoCollectionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_CW_VERSION', '1.0.0' );
define( 'PHYTO_CW_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_CW_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
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
function phyto_cw_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_cw_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_cw_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Collection Widget requires %s to be installed and active.', 'phyto-collection-widget' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Plugin activation: create the DB table and flush rewrite rules.
 */
function phyto_cw_activate() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	require_once PHYTO_CW_PATH . 'includes/class-phyto-cw-db.php';
	Phyto_CW_DB::create_table();
	phyto_cw_register_rewrite_endpoint();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'phyto_cw_activate' );

/**
 * Plugin deactivation: flush rewrite rules.
 */
function phyto_cw_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'phyto_cw_deactivate' );

/**
 * Register the My Account rewrite endpoint for collection sub-pages.
 * Registered on init so it is always present; flushed on activate/deactivate.
 */
function phyto_cw_register_rewrite_endpoint() {
	add_rewrite_endpoint( 'plant-collection', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'phyto_cw_register_rewrite_endpoint' );

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_cw_init() {
	if ( ! phyto_cw_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_CW_PATH . 'includes/class-phyto-cw-db.php';
	require_once PHYTO_CW_PATH . 'includes/class-phyto-cw-admin.php';
	require_once PHYTO_CW_PATH . 'includes/class-phyto-cw-myaccount.php';
	require_once PHYTO_CW_PATH . 'includes/class-phyto-cw-frontend.php';

	$admin     = new Phyto_CW_Admin();
	$myaccount = new Phyto_CW_MyAccount();
	$frontend  = new Phyto_CW_Frontend();

	$admin->register_hooks();
	$myaccount->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_cw_init' );
