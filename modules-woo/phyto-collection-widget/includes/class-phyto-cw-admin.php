<?php
/**
 * Admin class for Phyto Collection Widget.
 *
 * @package PhytoCollectionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_CW_Admin {

	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 55 );
		add_action( 'woocommerce_settings_tabs_phyto_collection', array( $this, 'render_settings' ) );
		add_action( 'woocommerce_update_options_phyto_collection', array( $this, 'save_settings' ) );
	}

	public function add_menu() {
		add_submenu_page( 'woocommerce', __( 'Plant Collections', 'phyto-collection-widget' ), __( 'Plant Collections', 'phyto-collection-widget' ), 'manage_woocommerce', 'phyto-collections', array( $this, 'render_page' ) );
	}

	public function add_settings_tab( $tabs ) {
		$tabs['phyto_collection'] = __( 'Phyto Collection', 'phyto-collection-widget' );
		return $tabs;
	}

	public function render_settings() {
		woocommerce_admin_fields( array(
			array( 'id' => 'phyto_cw_section', 'title' => __( 'Collection Settings', 'phyto-collection-widget' ), 'type' => 'title' ),
			array(
				'id'      => 'phyto_cw_allow_public',
				'title'   => __( 'Allow Public Collections', 'phyto-collection-widget' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Let customers share individual collection items publicly.', 'phyto-collection-widget' ),
				'default' => 'no',
			),
			array( 'id' => 'phyto_cw_section_end', 'type' => 'sectionend' ),
		) );
	}

	public function save_settings() {
		woocommerce_update_options( array(
			array( 'id' => 'phyto_cw_allow_public', 'type' => 'checkbox' ),
		) );
	}

	public function render_page() {
		$search = isset( $_GET['search'] ) ? sanitize_text_field( $_GET['search'] ) : '';
		$items  = Phyto_CW_DB::get_all_admin( array( 'search' => $search, 'limit' => 100 ) );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Plant Collections', 'phyto-collection-widget' ); ?></h1>
			<form method="get">
				<input type="hidden" name="page" value="phyto-collections">
				<input type="search" name="search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by email or name…', 'phyto-collection-widget' ); ?>">
				<button class="button"><?php esc_html_e( 'Search', 'phyto-collection-widget' ); ?></button>
			</form>
			<table class="wp-list-table widefat fixed striped" style="margin-top:12px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Customer', 'phyto-collection-widget' ); ?></th>
						<th><?php esc_html_e( 'Product', 'phyto-collection-widget' ); ?></th>
						<th><?php esc_html_e( 'Date Acquired', 'phyto-collection-widget' ); ?></th>
						<th><?php esc_html_e( 'Public', 'phyto-collection-widget' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $items as $item ) :
					$product = wc_get_product( $item->product_id );
				?>
				<tr>
					<td><?php echo esc_html( $item->customer_name ); ?> <small>&lt;<?php echo esc_html( $item->customer_email ); ?>&gt;</small></td>
					<td><?php echo $product ? esc_html( $product->get_name() ) : '#' . esc_html( $item->product_id ); ?></td>
					<td><?php echo esc_html( $item->date_acquired ); ?></td>
					<td><?php echo $item->is_public ? '✓' : '—'; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
