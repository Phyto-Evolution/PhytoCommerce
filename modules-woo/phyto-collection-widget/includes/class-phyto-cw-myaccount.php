<?php
/**
 * My Account tab for Phyto Collection Widget.
 *
 * @package PhytoCollectionWidget
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_CW_MyAccount {

	public function register_hooks() {
		// Register My Account endpoint.
		add_action( 'init', array( $this, 'add_endpoint' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );
		add_action( 'woocommerce_account_plant-collection_endpoint', array( $this, 'render_tab' ) );

		// AJAX.
		add_action( 'wp_ajax_phyto_cw_update_note', array( $this, 'ajax_update_note' ) );
		add_action( 'wp_ajax_phyto_cw_remove_item', array( $this, 'ajax_remove_item' ) );

		// JS.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_endpoint() {
		add_rewrite_endpoint( 'plant-collection', EP_ROOT | EP_PAGES );
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'plant-collection';
		return $vars;
	}

	public function add_menu_item( $items ) {
		$new = array();
		foreach ( $items as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'orders' === $key ) {
				$new['plant-collection'] = __( 'My Collection', 'phyto-collection-widget' );
			}
		}
		return $new;
	}

	public function render_tab() {
		$customer_id = get_current_user_id();
		$items       = Phyto_CW_DB::get_by_customer( $customer_id );
		$allow_public = get_option( 'phyto_cw_allow_public', 'no' ) === 'yes';
		?>
		<div class="phyto-cw-collection">
			<h2><?php esc_html_e( '🌿 My Plant Collection', 'phyto-collection-widget' ); ?></h2>
			<?php if ( empty( $items ) ) : ?>
				<p><?php esc_html_e( 'You haven\'t collected any plants yet. Plants you purchase will appear here automatically.', 'phyto-collection-widget' ); ?></p>
			<?php else : ?>
			<table class="phyto-cw-table woocommerce-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Plant', 'phyto-collection-widget' ); ?></th>
						<th><?php esc_html_e( 'Acquired', 'phyto-collection-widget' ); ?></th>
						<th><?php esc_html_e( 'My Notes', 'phyto-collection-widget' ); ?></th>
						<?php if ( $allow_public ) : ?><th><?php esc_html_e( 'Public', 'phyto-collection-widget' ); ?></th><?php endif; ?>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $items as $item ) :
					$product = wc_get_product( $item->product_id );
					if ( ! $product ) continue;
					$img  = $product->get_image( 'thumbnail' );
					$link = get_permalink( $item->product_id );
				?>
				<tr data-id="<?php echo esc_attr( $item->id ); ?>">
					<td>
						<a href="<?php echo esc_url( $link ); ?>"><?php echo wp_kses_post( $img ); ?></a>
						<strong><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></strong>
					</td>
					<td><?php echo $item->date_acquired ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->date_acquired ) ) ) : '—'; ?></td>
					<td>
						<textarea class="phyto-cw-note" rows="2" placeholder="<?php esc_attr_e( 'Add a care note…', 'phyto-collection-widget' ); ?>"><?php echo esc_textarea( $item->personal_note ); ?></textarea>
						<button class="phyto-cw-save-note button button-small"><?php esc_html_e( 'Save', 'phyto-collection-widget' ); ?></button>
					</td>
					<?php if ( $allow_public ) : ?>
					<td><input type="checkbox" class="phyto-cw-public" <?php checked( $item->is_public, 1 ); ?>></td>
					<?php endif; ?>
					<td><button class="phyto-cw-remove button button-small" style="color:#c0392b;"><?php esc_html_e( 'Remove', 'phyto-collection-widget' ); ?></button></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php endif; ?>
		</div>
		<?php
	}

	public function ajax_update_note() {
		check_ajax_referer( 'phyto_cw_nonce', 'nonce' );
		$item_id     = absint( $_POST['item_id'] ?? 0 );
		$customer_id = get_current_user_id();
		$note        = sanitize_textarea_field( $_POST['note'] ?? '' );

		if ( ! $customer_id || ! $item_id ) {
			wp_send_json_error();
		}

		$ok = Phyto_CW_DB::update_note( $item_id, $customer_id, $note );
		$ok ? wp_send_json_success() : wp_send_json_error();
	}

	public function ajax_remove_item() {
		check_ajax_referer( 'phyto_cw_nonce', 'nonce' );
		$item_id     = absint( $_POST['item_id'] ?? 0 );
		$customer_id = get_current_user_id();

		if ( ! $customer_id || ! $item_id ) {
			wp_send_json_error();
		}

		$ok = Phyto_CW_DB::remove_item( $item_id, $customer_id );
		$ok ? wp_send_json_success() : wp_send_json_error();
	}

	public function enqueue_scripts() {
		if ( ! is_account_page() ) {
			return;
		}
		wp_enqueue_script( 'phyto-cw-myaccount', PHYTO_CW_URL . 'assets/js/myaccount.js', array( 'jquery' ), PHYTO_CW_VERSION, true );
		wp_localize_script( 'phyto-cw-myaccount', 'phytoCw', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'phyto_cw_nonce' ),
		) );
	}
}
