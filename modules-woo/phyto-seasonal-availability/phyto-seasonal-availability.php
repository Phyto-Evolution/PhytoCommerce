<?php
/**
 * Plugin Name:       Phyto Seasonal Availability for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Block product purchases during off-season months and capture "notify me when in season" email subscribers.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-seasonal-availability
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoSeasonalAvailability
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PHYTO_SA_VERSION', '1.0.0' );
define( 'PHYTO_SA_PATH', plugin_dir_path( __FILE__ ) );
define( 'PHYTO_SA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Check whether WooCommerce is active. Shows an admin notice if not.
 *
 * @return bool True when WooCommerce is available.
 */
function phyto_sa_check_woocommerce() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'phyto_sa_woocommerce_missing_notice' );
		return false;
	}
	return true;
}

/**
 * Admin notice shown when WooCommerce is not active.
 */
function phyto_sa_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			printf(
				/* translators: %s: WooCommerce plugin link */
				esc_html__( 'Phyto Seasonal Availability requires %s to be installed and active.', 'phyto-seasonal-availability' ),
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
function phyto_sa_init() {
	if ( ! phyto_sa_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_SA_PATH . 'includes/class-phyto-seasonal-admin.php';
	require_once PHYTO_SA_PATH . 'includes/class-phyto-seasonal-frontend.php';
	require_once PHYTO_SA_PATH . 'includes/class-phyto-seasonal-subscribers.php';

	$admin       = new Phyto_Seasonal_Admin();
	$frontend    = new Phyto_Seasonal_Frontend();
	$subscribers = new Phyto_Seasonal_Subscribers();

	$admin->register_hooks();
	$frontend->register_hooks();
	$subscribers->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_sa_init' );

/**
 * Activation hook: creates the phyto_seasonal_subscribers DB table.
 */
function phyto_sa_activate() {
	global $wpdb;

	$table_name      = $wpdb->prefix . 'phyto_seasonal_subscribers';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		product_id bigint(20) unsigned NOT NULL,
		email varchar(200) NOT NULL,
		subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		notified tinyint(1) NOT NULL DEFAULT 0,
		PRIMARY KEY (id),
		KEY product_id (product_id),
		KEY email (email(100))
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
register_activation_hook( __FILE__, 'phyto_sa_activate' );
