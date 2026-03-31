<?php
/**
 * Plugin Name:       Phyto Grex Registry for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Attach scientific taxonomy metadata (genus, species, grex, authority, conservation status) to WooCommerce products. Displays as a "Scientific Profile" tab on the product page.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-grex-registry
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoGrexRegistry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_GREX_VERSION', '1.0.0' );
define( 'PHYTO_GREX_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_GREX_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 */
function phyto_grex_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_grex_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_grex_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Grex Registry requires %s to be installed and active.', 'phyto-grex-registry' ),
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
function phyto_grex_init() {
	if ( ! phyto_grex_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_GREX_PATH . 'includes/class-phyto-grex-admin.php';
	require_once PHYTO_GREX_PATH . 'includes/class-phyto-grex-frontend.php';

	$admin    = new Phyto_Grex_Admin();
	$frontend = new Phyto_Grex_Frontend();

	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_grex_init' );
