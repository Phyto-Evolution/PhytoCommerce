<?php
/**
 * Admin panel for Phyto Wholesale Portal.
 *
 * @package PhytoWholesalePortal
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_WP_Admin {

	public function register_hooks() {
		add_action( 'admin_menu',            array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_phyto_wp_approve', array( $this, 'ajax_approve' ) );
		add_action( 'wp_ajax_phyto_wp_reject',  array( $this, 'ajax_reject' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 57 );
		add_action( 'woocommerce_settings_tabs_phyto_wholesale', array( $this, 'render_settings' ) );
		add_action( 'woocommerce_update_options_phyto_wholesale', array( $this, 'save_settings' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Wholesale Applications', 'phyto-wholesale-portal' ),
			__( 'Wholesale Apps', 'phyto-wholesale-portal' ),
			'manage_woocommerce',
			'phyto-wholesale-apps',
			array( $this, 'render_list' )
		);
	}

	public function enqueue( $hook ) {
		if ( strpos( $hook, 'phyto-wholesale-apps' ) === false ) { return; }
		wp_enqueue_style( 'phyto-wp-admin', PHYTO_WP_URL . 'assets/css/admin.css', array(), PHYTO_WP_VERSION );
		wp_enqueue_script( 'phyto-wp-admin', PHYTO_WP_URL . 'assets/js/admin.js', array( 'jquery' ), PHYTO_WP_VERSION, true );
		wp_localize_script( 'phyto-wp-admin', 'phytoWPAdmin', array(
			'nonce'   => wp_create_nonce( 'phyto_wp_admin' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function render_list() {
		$status_filter = sanitize_key( $_GET['status'] ?? '' );
		$apps = Phyto_WP_DB::get_all( $status_filter ?: null );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Wholesale Applications', 'phyto-wholesale-portal' ); ?></h1>

			<ul class="subsubsub">
				<?php foreach ( array( '' => __( 'All', 'phyto-wholesale-portal' ), 'pending' => __( 'Pending', 'phyto-wholesale-portal' ), 'approved' => __( 'Approved', 'phyto-wholesale-portal' ), 'rejected' => __( 'Rejected', 'phyto-wholesale-portal' ) ) as $key => $label ) : ?>
				<li><a href="<?php echo esc_url( add_query_arg( 'status', $key, admin_url( 'admin.php?page=phyto-wholesale-apps' ) ) ); ?>"
					   <?php echo $status_filter === $key ? 'class="current"' : ''; ?>>
					<?php echo esc_html( $label ); ?>
				</a> | </li>
				<?php endforeach; ?>
			</ul>

			<table class="wp-list-table widefat fixed striped phyto-ws-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Business', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Contact', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Email', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Tax ID', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Status', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Date', 'phyto-wholesale-portal' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'phyto-wholesale-portal' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $apps ) ) : ?>
					<tr><td colspan="7"><?php esc_html_e( 'No applications found.', 'phyto-wholesale-portal' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $apps as $app ) :
						$user     = $app->user_id ? get_userdata( $app->user_id ) : null;
						$edit_url = $user ? get_edit_user_link( $user->ID ) : '';
					?>
					<tr data-id="<?php echo esc_attr( $app->id ); ?>" data-user="<?php echo esc_attr( $app->user_id ); ?>">
						<td><strong><?php echo esc_html( $app->business_name ); ?></strong>
							<?php if ( $app->website ) : ?><br><a href="<?php echo esc_url( $app->website ); ?>" target="_blank"><?php echo esc_html( $app->website ); ?></a><?php endif; ?>
						</td>
						<td><?php echo esc_html( $app->contact_name ); ?>
							<?php if ( $user && $edit_url ) : ?><br><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit User', 'phyto-wholesale-portal' ); ?></a><?php endif; ?>
						</td>
						<td><?php echo esc_html( $app->email ); ?></td>
						<td><?php echo esc_html( $app->tax_id ); ?></td>
						<td class="phyto-ws-status phyto-ws-status-<?php echo esc_attr( $app->status ); ?>">
							<?php echo esc_html( ucfirst( $app->status ) ); ?>
						</td>
						<td><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $app->created_at ) ) ); ?></td>
						<td>
							<?php if ( $app->status !== 'approved' ) : ?>
							<button class="button button-primary phyto-ws-approve" data-id="<?php echo esc_attr( $app->id ); ?>" data-user="<?php echo esc_attr( $app->user_id ); ?>">
								<?php esc_html_e( 'Approve', 'phyto-wholesale-portal' ); ?>
							</button>
							<?php endif; ?>
							<?php if ( $app->status !== 'rejected' ) : ?>
							<button class="button phyto-ws-reject" data-id="<?php echo esc_attr( $app->id ); ?>" data-user="<?php echo esc_attr( $app->user_id ); ?>">
								<?php esc_html_e( 'Reject', 'phyto-wholesale-portal' ); ?>
							</button>
							<?php endif; ?>
						</td>
					</tr>
					<?php if ( $app->notes ) : ?>
					<tr class="phyto-ws-notes-row">
						<td colspan="7"><em><?php echo esc_html( $app->notes ); ?></em></td>
					</tr>
					<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function ajax_approve() {
		check_ajax_referer( 'phyto_wp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_send_json_error(); }
		$id      = absint( $_POST['id'] ?? 0 );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		Phyto_WP_DB::update_status( $id, 'approved' );
		if ( $user_id ) {
			Phyto_WP_Roles::grant( $user_id );
			// Notify user
			$user = get_userdata( $user_id );
			if ( $user ) {
				wp_mail( $user->user_email, __( 'Your wholesale application has been approved!', 'phyto-wholesale-portal' ),
					__( 'Congratulations! Your wholesale account is now active. Log in to shop at wholesale prices.', 'phyto-wholesale-portal' ) );
			}
		}
		wp_send_json_success();
	}

	public function ajax_reject() {
		check_ajax_referer( 'phyto_wp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_send_json_error(); }
		$id      = absint( $_POST['id'] ?? 0 );
		$user_id = absint( $_POST['user_id'] ?? 0 );
		Phyto_WP_DB::update_status( $id, 'rejected' );
		if ( $user_id ) {
			Phyto_WP_Roles::revoke( $user_id );
		}
		wp_send_json_success();
	}

	public function add_settings_tab( $tabs ) {
		$tabs['phyto_wholesale'] = __( 'Phyto Wholesale', 'phyto-wholesale-portal' );
		return $tabs;
	}

	public function render_settings() { woocommerce_admin_fields( $this->fields() ); }
	public function save_settings()   { woocommerce_update_options( $this->fields() ); }

	private function fields() {
		return array(
			array( 'id' => 'phyto_ws_section', 'title' => __( 'Wholesale Portal', 'phyto-wholesale-portal' ), 'type' => 'title' ),
			array(
				'id'    => 'phyto_ws_apply_page',
				'title' => __( 'Application Page', 'phyto-wholesale-portal' ),
				'type'  => 'single_select_page',
				'desc'  => __( 'Page with the [phyto_wholesale_apply] shortcode.', 'phyto-wholesale-portal' ),
			),
			array(
				'id'      => 'phyto_ws_show_prices',
				'title'   => __( 'Show Wholesale Prices', 'phyto-wholesale-portal' ),
				'type'    => 'select',
				'options' => array(
					'wholesale_only' => __( 'To wholesale customers only', 'phyto-wholesale-portal' ),
					'all'            => __( 'To all logged-in users', 'phyto-wholesale-portal' ),
				),
				'default' => 'wholesale_only',
			),
			array( 'id' => 'phyto_ws_section_end', 'type' => 'sectionend' ),
		);
	}
}
