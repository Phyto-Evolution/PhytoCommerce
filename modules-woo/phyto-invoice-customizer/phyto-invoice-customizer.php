<?php
/**
 * Plugin Name:       Phyto Invoice Customizer for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Customises WooCommerce order confirmation emails and PDF invoices with a branded header, Live Arrival Guarantee text, TC batch numbers, phytosanitary certificate references, and a custom footer note.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-invoice-customizer
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoInvoiceCustomizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_IC_VERSION', '1.0.0' );
define( 'PHYTO_IC_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_IC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_ic_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_ic_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_ic_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Invoice Customizer requires %s to be installed and active.', 'phyto-invoice-customizer' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 *
 * Loaded on plugins_loaded so WooCommerce classes are guaranteed available.
 */
function phyto_ic_init() {
	if ( ! phyto_ic_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_IC_PATH . 'includes/class-phyto-ic-settings.php';
	require_once PHYTO_IC_PATH . 'includes/class-phyto-ic-email.php';
	require_once PHYTO_IC_PATH . 'includes/class-phyto-ic-frontend.php';

	$settings = new Phyto_IC_Settings();
	$email    = new Phyto_IC_Email();
	$frontend = new Phyto_IC_Frontend();

	$settings->register_hooks();
	$email->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_ic_init' );

/**
 * Declare HPOS (High-Performance Order Storage) compatibility.
 *
 * WooCommerce 8.2+ shows a warning if plugins do not declare this explicitly.
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
