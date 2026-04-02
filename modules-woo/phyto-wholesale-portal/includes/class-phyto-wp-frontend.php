<?php
/**
 * Frontend wholesale application form (shortcode).
 *
 * Usage: [phyto_wholesale_apply]
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_Frontend {

	public function register_hooks() {
		add_shortcode( 'phyto_wholesale_apply', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts',       array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_phyto_wp_apply',        array( $this, 'ajax_apply' ) );
		add_action( 'wp_ajax_nopriv_phyto_wp_apply', array( $this, 'ajax_apply' ) );
	}

	public function enqueue() {
		if ( ! is_page() ) { return; }
		wp_enqueue_style(  'phyto-wp-frontend', PHYTO_WP_URL . 'assets/css/frontend.css', array(), PHYTO_WP_VERSION );
		wp_enqueue_script( 'phyto-wp-frontend', PHYTO_WP_URL . 'assets/js/frontend.js', array( 'jquery' ), PHYTO_WP_VERSION, true );
		wp_localize_script( 'phyto-wp-frontend', 'phytoWP', array(
			'nonce'   => wp_create_nonce( 'phyto_wp_apply' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function render_shortcode() {
		$user_id = get_current_user_id();

		// Already approved wholesale customer
		if ( Phyto_WP_Roles::is_wholesale( $user_id ) ) {
			return '<p class="phyto-ws-notice phyto-ws-approved">' .
				esc_html__( 'You are an approved wholesale customer. Wholesale pricing applies automatically.', 'phyto-wholesale-portal' ) .
				'</p>';
		}

		// Logged-out notice
		if ( ! $user_id ) {
			return '<p class="phyto-ws-notice">' .
				sprintf(
					/* translators: %s: login URL */
					wp_kses( __( 'Please <a href="%s">log in</a> or <a href="%s">register</a> to apply for a wholesale account.', 'phyto-wholesale-portal' ), array( 'a' => array( 'href' => array() ) ) ),
					esc_url( wc_get_page_permalink( 'myaccount' ) ),
					esc_url( wc_get_page_permalink( 'myaccount' ) )
				) . '</p>';
		}

		// Already applied
		$existing = Phyto_WP_DB::get_by_user( $user_id );
		if ( $existing ) {
			if ( $existing->status === 'pending' ) {
				return '<p class="phyto-ws-notice phyto-ws-pending">' . esc_html__( 'Your wholesale application is under review. We\'ll notify you by email.', 'phyto-wholesale-portal' ) . '</p>';
			}
			if ( $existing->status === 'rejected' ) {
				return '<p class="phyto-ws-notice phyto-ws-rejected">' . esc_html__( 'Your application was not approved at this time. Contact us for more information.', 'phyto-wholesale-portal' ) . '</p>';
			}
		}

		ob_start();
		$user = wp_get_current_user();
		?>
		<div class="phyto-ws-apply-form">
			<h3><?php esc_html_e( 'Wholesale Account Application', 'phyto-wholesale-portal' ); ?></h3>
			<form id="phyto-ws-form">
				<p>
					<label><?php esc_html_e( 'Business Name *', 'phyto-wholesale-portal' ); ?></label>
					<input type="text" name="business_name" required />
				</p>
				<p>
					<label><?php esc_html_e( 'Contact Name *', 'phyto-wholesale-portal' ); ?></label>
					<input type="text" name="contact_name" value="<?php echo esc_attr( $user->display_name ); ?>" required />
				</p>
				<p>
					<label><?php esc_html_e( 'Business Email *', 'phyto-wholesale-portal' ); ?></label>
					<input type="email" name="email" value="<?php echo esc_attr( $user->user_email ); ?>" required />
				</p>
				<p>
					<label><?php esc_html_e( 'Phone', 'phyto-wholesale-portal' ); ?></label>
					<input type="text" name="phone" />
				</p>
				<p>
					<label><?php esc_html_e( 'Tax ID / VAT Number', 'phyto-wholesale-portal' ); ?></label>
					<input type="text" name="tax_id" />
				</p>
				<p>
					<label><?php esc_html_e( 'Business Website', 'phyto-wholesale-portal' ); ?></label>
					<input type="url" name="website" />
				</p>
				<p>
					<label><?php esc_html_e( 'Tell us about your business', 'phyto-wholesale-portal' ); ?></label>
					<textarea name="notes" rows="4"></textarea>
				</p>
				<p>
					<button type="submit" class="button"><?php esc_html_e( 'Submit Application', 'phyto-wholesale-portal' ); ?></button>
					<span id="phyto-ws-status"></span>
				</p>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function ajax_apply() {
		check_ajax_referer( 'phyto_wp_apply', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'You must be logged in to apply.', 'phyto-wholesale-portal' ) );
		}

		$existing = Phyto_WP_DB::get_by_user( $user_id );
		if ( $existing && in_array( $existing->status, array( 'pending', 'approved' ), true ) ) {
			wp_send_json_error( __( 'An application for your account already exists.', 'phyto-wholesale-portal' ) );
		}

		$id = Phyto_WP_DB::create_application( array(
			'user_id'       => $user_id,
			'business_name' => wp_unslash( $_POST['business_name'] ?? '' ),
			'contact_name'  => wp_unslash( $_POST['contact_name'] ?? '' ),
			'email'         => wp_unslash( $_POST['email'] ?? '' ),
			'phone'         => wp_unslash( $_POST['phone'] ?? '' ),
			'tax_id'        => wp_unslash( $_POST['tax_id'] ?? '' ),
			'website'       => wp_unslash( $_POST['website'] ?? '' ),
			'notes'         => wp_unslash( $_POST['notes'] ?? '' ),
		) );

		if ( ! $id ) {
			wp_send_json_error( __( 'Failed to save application.', 'phyto-wholesale-portal' ) );
		}

		// Notify admin
		$admin_email = get_option( 'admin_email' );
		$subject     = __( 'New Wholesale Application', 'phyto-wholesale-portal' );
		$body        = sprintf( __( 'A new wholesale application has been submitted. Review it in WooCommerce > Wholesale Applications.', 'phyto-wholesale-portal' ) );
		wp_mail( $admin_email, $subject, $body );

		wp_send_json_success( __( 'Application submitted! We\'ll be in touch.', 'phyto-wholesale-portal' ) );
	}
}
