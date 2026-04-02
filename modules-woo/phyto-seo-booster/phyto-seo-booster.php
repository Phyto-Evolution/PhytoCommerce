<?php
/**
 * Plugin Name:       Phyto SEO Booster for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       AI-powered SEO automation for WooCommerce product pages. Injects JSON-LD structured data, auto-generates meta titles/descriptions via Claude AI, and provides a full SEO audit dashboard.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-seo-booster
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoSeoBooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_SB_VERSION', '1.0.0' );
define( 'PHYTO_SB_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_SB_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_sb_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_sb_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_sb_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto SEO Booster requires %s to be installed and active.', 'phyto-seo-booster' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Activation hook — create the audit DB table.
 */
function phyto_sb_activate() {
	if ( ! phyto_sb_check_woocommerce() ) {
		return;
	}
	require_once PHYTO_SB_PATH . 'includes/class-phyto-sb-db.php';
	Phyto_SB_DB::create_table();
}
register_activation_hook( __FILE__, 'phyto_sb_activate' );

/**
 * Bootstrap the plugin after all plugins are loaded.
 */
function phyto_sb_init() {
	if ( ! phyto_sb_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_SB_PATH . 'includes/class-phyto-sb-db.php';
	require_once PHYTO_SB_PATH . 'includes/class-phyto-sb-schema.php';
	require_once PHYTO_SB_PATH . 'includes/class-phyto-sb-audit.php';
	require_once PHYTO_SB_PATH . 'includes/class-phyto-sb-admin.php';

	// JSON-LD schema injection on product pages.
	$schema = new Phyto_SB_Schema();
	$schema->register_hooks();

	// Auto meta generation on product save.
	$audit = new Phyto_SB_Audit();
	$audit->register_hooks();

	// Admin page + WooCommerce settings tab.
	if ( is_admin() ) {
		$admin = new Phyto_SB_Admin();
		$admin->register_hooks();
	}
}
add_action( 'plugins_loaded', 'phyto_sb_init' );

/**
 * Declare HPOS compatibility.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);
