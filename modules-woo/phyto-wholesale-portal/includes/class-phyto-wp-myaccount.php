<?php
/**
 * My Account wholesale tab for Phyto Wholesale Portal.
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_MyAccount {

	public function register_hooks() {
		add_filter( 'woocommerce_account_menu_items',    array( $this, 'add_menu_item' ) );
		add_action( 'woocommerce_account_wholesale_endpoint', array( $this, 'render_tab' ) );
		add_action( 'init',                              array( $this, 'add_endpoint' ) );
	}

	public function add_endpoint() {
		add_rewrite_endpoint( 'wholesale', EP_ROOT | EP_PAGES );
	}

	public function add_menu_item( $items ) {
		$logout = $items['customer-logout'] ?? null;
		unset( $items['customer-logout'] );
		$items['wholesale'] = __( 'Wholesale', 'phyto-wholesale-portal' );
		if ( $logout !== null ) { $items['customer-logout'] = $logout; }
		return $items;
	}

	public function render_tab() {
		$user_id = get_current_user_id();
		$is_ws   = Phyto_WP_Roles::is_wholesale( $user_id );
		$app     = Phyto_WP_DB::get_by_user( $user_id );
		?>
		<div class="phyto-ws-account">
			<?php if ( $is_ws ) : ?>
			<p class="phyto-ws-notice phyto-ws-approved">
				<?php esc_html_e( 'You are an approved wholesale customer. Wholesale prices apply to your orders automatically.', 'phyto-wholesale-portal' ); ?>
			</p>
			<?php elseif ( $app && $app->status === 'pending' ) : ?>
			<p class="phyto-ws-notice phyto-ws-pending">
				<?php esc_html_e( 'Your wholesale application is pending review.', 'phyto-wholesale-portal' ); ?>
			</p>
			<?php elseif ( $app && $app->status === 'rejected' ) : ?>
			<p class="phyto-ws-notice phyto-ws-rejected">
				<?php esc_html_e( 'Your application was not approved. Please contact us for details.', 'phyto-wholesale-portal' ); ?>
			</p>
			<?php else : ?>
			<p><?php esc_html_e( 'Apply for a wholesale account to access trade pricing.', 'phyto-wholesale-portal' ); ?></p>
			<?php
			$page_id = get_option( 'phyto_ws_apply_page', 0 );
			if ( $page_id ) {
				echo '<p><a href="' . esc_url( get_permalink( $page_id ) ) . '" class="button">' . esc_html__( 'Apply Now', 'phyto-wholesale-portal' ) . '</a></p>';
			}
			?>
			<?php endif; ?>
		</div>
		<?php
	}
}
