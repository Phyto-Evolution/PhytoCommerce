<?php
/**
 * Plugin Name:       Phyto Growth Stage for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Tag WooCommerce products with a growth stage (Deflasked → Specimen). Colour-coded badge on shop listings and product pages.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-growth-stage
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoGrowthStage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_GS_VERSION', '1.0.0' );
define( 'PHYTO_GS_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_GS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_gs_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_gs_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_gs_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Growth Stage requires %s to be installed and active.', 'phyto-growth-stage' ),
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
function phyto_gs_init() {
	if ( ! phyto_gs_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_GS_PATH . 'includes/class-phyto-growth-stage-admin.php';
	require_once PHYTO_GS_PATH . 'includes/class-phyto-growth-stage-frontend.php';

	$admin    = new Phyto_Growth_Stage_Admin();
	$frontend = new Phyto_Growth_Stage_Frontend();

	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_gs_init' );
