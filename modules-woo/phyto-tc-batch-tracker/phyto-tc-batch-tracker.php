<?php
/**
 * Plugin Name:       Phyto TC Batch Tracker for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Track tissue-culture batch provenance for WooCommerce products. Link products to TC batch records that capture batch ID, donor clone, agar medium, deflask date, lab operator, and status.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-tc-batch-tracker
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoTCBatchTracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_TCB_VERSION', '1.0.0' );
define( 'PHYTO_TCB_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_TCB_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_tcb_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_tcb_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_tcb_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto TC Batch Tracker requires %s to be installed and active.', 'phyto-tc-batch-tracker' ),
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
function phyto_tcb_init() {
	if ( ! phyto_tcb_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_TCB_PATH . 'includes/class-phyto-tcb-cpt.php';
	require_once PHYTO_TCB_PATH . 'includes/class-phyto-tcb-admin.php';
	require_once PHYTO_TCB_PATH . 'includes/class-phyto-tcb-frontend.php';

	$cpt      = new Phyto_TCB_CPT();
	$admin    = new Phyto_TCB_Admin();
	$frontend = new Phyto_TCB_Frontend();

	$cpt->register_hooks();
	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_tcb_init' );
