<?php
/**
 * Plugin Name:       Phyto Care Card for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Generate downloadable PDF care guides per product. Optionally attach to order emails.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-care-card
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoCareCard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_CC_VERSION', '1.0.0' );
define( 'PHYTO_CC_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_CC_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_cc_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_cc_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_cc_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Care Card requires %s to be installed and active.', 'phyto-care-card' ),
				'<strong>WooCommerce</strong>'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Register custom rewrite rule for PDF download endpoint.
 *
 * Pattern: /care-card/{product_id}/
 */
function phyto_cc_add_rewrite_rule() {
	add_rewrite_rule(
		'^care-card/([0-9]+)/?$',
		'index.php?phyto_care_card_id=$matches[1]',
		'top'
	);
}
add_action( 'init', 'phyto_cc_add_rewrite_rule' );

/**
 * Register the custom query variable.
 *
 * @param array $vars Existing query vars.
 * @return array Modified query vars.
 */
function phyto_cc_query_vars( $vars ) {
	$vars[] = 'phyto_care_card_id';
	return $vars;
}
add_filter( 'query_vars', 'phyto_cc_query_vars' );

/**
 * Flush rewrite rules on plugin activation.
 */
function phyto_cc_activate() {
	phyto_cc_add_rewrite_rule();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'phyto_cc_activate' );

/**
 * Flush rewrite rules on plugin deactivation.
 */
function phyto_cc_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'phyto_cc_deactivate' );

/**
 * Boot all plugin components once WordPress and WooCommerce are ready.
 */
function phyto_cc_init() {
	if ( ! phyto_cc_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_CC_PATH . 'includes/class-phyto-care-card-admin.php';
	require_once PHYTO_CC_PATH . 'includes/class-phyto-care-card-generator.php';
	require_once PHYTO_CC_PATH . 'includes/class-phyto-care-card-frontend.php';
	require_once PHYTO_CC_PATH . 'includes/class-phyto-care-card-email.php';

	$admin    = new Phyto_Care_Card_Admin();
	$frontend = new Phyto_Care_Card_Frontend();
	$email    = new Phyto_Care_Card_Email();

	$admin->register_hooks();
	$frontend->register_hooks();
	$email->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_cc_init' );
