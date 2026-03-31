<?php
/**
 * Plugin Name:       Phyto Source Badge for WooCommerce
 * Plugin URI:        https://github.com/kshivaramakrishnan/PhytoCommerce
 * Description:       Create sourcing-origin badges (Tissue Culture, Wild Collected, etc.) and display them on WooCommerce product listings and pages.
 * Version:           1.0.0
 * Author:            K. Shivaramakrishnan / Forest Studio Labs
 * Author URI:        https://www.linkedin.com/in/kshivaramakrishnan/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       phyto-source-badge
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * WC requires at least: 8.0
 * WC tested up to:   9.0
 *
 * @package PhytoSourceBadge
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
				esc_html__( 'Phyto Source Badge requires %s to be installed and active.', 'phyto-source-badge' ),
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
function phyto_sb_init() {
	if ( ! phyto_sb_check_woocommerce() ) {
		return;
	}

	require_once PHYTO_SB_PATH . 'includes/class-phyto-source-badge-cpt.php';
	require_once PHYTO_SB_PATH . 'includes/class-phyto-source-badge-admin.php';
	require_once PHYTO_SB_PATH . 'includes/class-phyto-source-badge-frontend.php';

	$cpt      = new Phyto_Source_Badge_CPT();
	$admin    = new Phyto_Source_Badge_Admin();
	$frontend = new Phyto_Source_Badge_Frontend();

	$cpt->register_hooks();
	$admin->register_hooks();
	$frontend->register_hooks();
}
add_action( 'plugins_loaded', 'phyto_sb_init' );

/**
 * Activation hook: seed default badge definitions if none exist yet.
 *
 * Creates four starter badges so stores have useful badges immediately
 * after activation without any manual configuration.
 */
function phyto_sb_activate() {
	// Only seed if no phyto_badge posts exist yet.
	$existing = get_posts(
		array(
			'post_type'      => 'phyto_badge',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);

	if ( ! empty( $existing ) ) {
		return;
	}

	$defaults = array(
		array(
			'title'   => __( 'Tissue Culture', 'phyto-source-badge' ),
			'color'   => '#3a9a6a',
			'icon'    => '🧫',
			'tooltip' => __( 'Propagated via sterile tissue culture in a laboratory setting.', 'phyto-source-badge' ),
		),
		array(
			'title'   => __( 'Wild Collected', 'phyto-source-badge' ),
			'color'   => '#8B4513',
			'icon'    => '🌿',
			'tooltip' => __( 'Ethically and legally collected from wild habitat.', 'phyto-source-badge' ),
		),
		array(
			'title'   => __( 'Nursery Grown', 'phyto-source-badge' ),
			'color'   => '#4caf7d',
			'icon'    => '🪴',
			'tooltip' => __( 'Raised from establishment to sale entirely in nursery conditions.', 'phyto-source-badge' ),
		),
		array(
			'title'   => __( 'Conservation Propagation', 'phyto-source-badge' ),
			'color'   => '#1a3c2b',
			'icon'    => '♻️',
			'tooltip' => __( 'Propagated as part of an active species conservation programme.', 'phyto-source-badge' ),
		),
	);

	foreach ( $defaults as $badge ) {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'phyto_badge',
				'post_title'  => $badge['title'],
				'post_status' => 'publish',
			)
		);

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_phyto_badge_color',   $badge['color'] );
			update_post_meta( $post_id, '_phyto_badge_icon',    $badge['icon'] );
			update_post_meta( $post_id, '_phyto_badge_tooltip', $badge['tooltip'] );
		}
	}
}
register_activation_hook( __FILE__, 'phyto_sb_activate' );
