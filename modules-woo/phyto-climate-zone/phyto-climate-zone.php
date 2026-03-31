<?php
/**
 * Plugin Name:       Phyto Climate Zone for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Tag WooCommerce products with India climate-zone suitability. Shows suitability badges on shop listings and a dedicated tab on single product pages.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-climate-zone
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoClimateZone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_CZ_VERSION', '1.0.0' );
define( 'PHYTO_CZ_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_CZ_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_cz_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_cz_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_cz_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Climate Zone requires %s to be installed and active.', 'phyto-climate-zone' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Bootstrap: load includes and register hooks.
 * Runs on plugins_loaded to ensure WooCommerce is available.
 */
function phyto_cz_init() {
	if ( ! phyto_cz_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_CZ_PATH . 'includes/class-phyto-cz-admin.php';
	require_once PHYTO_CZ_PATH . 'includes/class-phyto-cz-frontend.php';

	$admin    = new Phyto_CZ_Admin();
	$frontend = new Phyto_CZ_Frontend();

	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_cz_init' );
