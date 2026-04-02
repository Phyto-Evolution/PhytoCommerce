<?php
/**
 * Admin panel — bundle template CRUD.
 *
 * @package PhytoBundleBuilder
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_BB_Admin {

	public function register_hooks() {
		add_action( 'admin_menu',            array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_post_phyto_bb_save_template',   array( $this, 'handle_save' ) );
		add_action( 'admin_post_phyto_bb_delete_template', array( $this, 'handle_delete' ) );
		add_action( 'wp_ajax_phyto_bb_search_products',    array( $this, 'ajax_search_products' ) );
	}

	public function add_menu() {
		add_menu_page(
			__( 'Bundle Builder', 'phyto-bundle-builder' ),
			__( 'Bundle Builder', 'phyto-bundle-builder' ),
			'manage_woocommerce',
			'phyto-bundle-builder',
			array( $this, 'render_list' ),
			'dashicons-archive',
			57
		);
		add_submenu_page(
			'phyto-bundle-builder',
			__( 'Edit Template', 'phyto-bundle-builder' ),
			__( 'New Template', 'phyto-bundle-builder' ),
			'manage_woocommerce',
			'phyto-bundle-edit',
			array( $this, 'render_edit' )
		);
	}

	public function enqueue( $hook ) {
		if ( strpos( $hook, 'phyto-bundle' ) === false ) { return; }
		wp_enqueue_style( 'phyto-bb-admin', PHYTO_BB_URL . 'assets/css/admin.css', array(), PHYTO_BB_VERSION );
		wp_enqueue_script( 'phyto-bb-admin', PHYTO_BB_URL . 'assets/js/admin.js', array( 'jquery', 'wp-util' ), PHYTO_BB_VERSION, true );
		wp_localize_script( 'phyto-bb-admin', 'phytoBBAdmin', array(
			'nonce'   => wp_create_nonce( 'phyto_bb_admin' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function render_list() {
		$templates = Phyto_BB_DB::get_templates();
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Bundle Templates', 'phyto-bundle-builder' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-bundle-edit' ) ); ?>" class="page-title-action">
				<?php esc_html_e( 'Add New', 'phyto-bundle-builder' ); ?>
			</a>
			<hr class="wp-header-end">

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Name', 'phyto-bundle-builder' ); ?></th>
						<th><?php esc_html_e( 'Slots', 'phyto-bundle-builder' ); ?></th>
						<th><?php esc_html_e( 'Discount', 'phyto-bundle-builder' ); ?></th>
						<th><?php esc_html_e( 'Status', 'phyto-bundle-builder' ); ?></th>
						<th><?php esc_html_e( 'Shortcode', 'phyto-bundle-builder' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'phyto-bundle-builder' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $templates ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'No templates yet.', 'phyto-bundle-builder' ); ?></td></tr>
				<?php else : ?>
					<?php foreach ( $templates as $t ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $t->name ); ?></strong>
							<?php if ( $t->description ) : ?><br><em><?php echo esc_html( $t->description ); ?></em><?php endif; ?>
						</td>
						<td><?php echo esc_html( $t->slot_count ); ?></td>
						<td><?php echo $t->discount_pct > 0 ? esc_html( $t->discount_pct . '%' ) : '—'; ?></td>
						<td><?php echo $t->status === 'active' ? '<span style="color:#2d7a54">Active</span>' : '<span style="color:#999">Draft</span>'; ?></td>
						<td><code>[phyto_bundle id="<?php echo esc_attr( $t->id ); ?>"]</code></td>
						<td>
							<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'phyto-bundle-edit', 'id' => $t->id ), admin_url( 'admin.php' ) ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'phyto-bundle-builder' ); ?>
							</a>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
								<?php wp_nonce_field( 'phyto_bb_delete', '_wpnonce' ); ?>
								<input type="hidden" name="action" value="phyto_bb_delete_template" />
								<input type="hidden" name="template_id" value="<?php echo esc_attr( $t->id ); ?>" />
								<button type="submit" class="button button-small" onclick="return confirm('Delete this template?')">
									<?php esc_html_e( 'Delete', 'phyto-bundle-builder' ); ?>
								</button>
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_edit() {
		$id       = absint( $_GET['id'] ?? 0 );
		$template = $id ? Phyto_BB_DB::get_template( $id ) : null;
		$slots    = $id ? Phyto_BB_DB::get_slots( $id ) : array();

		$name         = $template ? $template->name : '';
		$desc         = $template ? $template->description : '';
		$slot_count   = $template ? (int) $template->slot_count : 3;
		$discount_pct = $template ? (int) $template->discount_pct : 0;
		$status       = $template ? $template->status : 'draft';
		?>
		<div class="wrap">
			<h1><?php echo $id ? esc_html__( 'Edit Template', 'phyto-bundle-builder' ) : esc_html__( 'New Template', 'phyto-bundle-builder' ); ?></h1>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'phyto_bb_save', '_wpnonce' ); ?>
				<input type="hidden" name="action" value="phyto_bb_save_template" />
				<input type="hidden" name="template_id" value="<?php echo esc_attr( $id ); ?>" />

				<table class="form-table">
					<tr>
						<th><label for="bb-name"><?php esc_html_e( 'Template Name *', 'phyto-bundle-builder' ); ?></label></th>
						<td><input type="text" id="bb-name" name="name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" required /></td>
					</tr>
					<tr>
						<th><label for="bb-desc"><?php esc_html_e( 'Description', 'phyto-bundle-builder' ); ?></label></th>
						<td><textarea id="bb-desc" name="description" rows="3" class="large-text"><?php echo esc_textarea( $desc ); ?></textarea></td>
					</tr>
					<tr>
						<th><label for="bb-slots"><?php esc_html_e( 'Number of Slots', 'phyto-bundle-builder' ); ?></label></th>
						<td><input type="number" id="bb-slots" name="slot_count" value="<?php echo esc_attr( $slot_count ); ?>" min="1" max="20" class="small-text" /></td>
					</tr>
					<tr>
						<th><label for="bb-discount"><?php esc_html_e( 'Bundle Discount %', 'phyto-bundle-builder' ); ?></label></th>
						<td><input type="number" id="bb-discount" name="discount_pct" value="<?php echo esc_attr( $discount_pct ); ?>" min="0" max="100" class="small-text" /></td>
					</tr>
					<tr>
						<th><label for="bb-status"><?php esc_html_e( 'Status', 'phyto-bundle-builder' ); ?></label></th>
						<td>
							<select id="bb-status" name="status">
								<option value="draft"  <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Draft', 'phyto-bundle-builder' ); ?></option>
								<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'phyto-bundle-builder' ); ?></option>
							</select>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Slot Configuration', 'phyto-bundle-builder' ); ?></h2>
				<p class="description"><?php esc_html_e( 'For each slot, optionally restrict which products or categories customers can choose from. Leave both blank to allow any product.', 'phyto-bundle-builder' ); ?></p>

				<div id="phyto-bb-slot-editor">
				<?php
				$max_slots = max( $slot_count, count( $slots ) );
				for ( $i = 0; $i < $max_slots; $i++ ) :
					$slot       = $slots[ $i ] ?? null;
					$label      = $slot ? $slot->slot_label : "Slot " . ( $i + 1 );
					$prod_ids   = $slot ? (array) json_decode( $slot->product_ids, true ) : array();
					$cat_ids    = $slot ? (array) json_decode( $slot->category_ids, true ) : array();
					?>
				<div class="phyto-bb-slot-row" data-index="<?php echo esc_attr( $i ); ?>">
					<h4><?php echo sprintf( esc_html__( 'Slot %d', 'phyto-bundle-builder' ), $i + 1 ); ?></h4>
					<p>
						<label><?php esc_html_e( 'Label', 'phyto-bundle-builder' ); ?></label>
						<input type="text" name="slots[<?php echo $i; ?>][label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
					</p>
					<p>
						<label><?php esc_html_e( 'Allowed Products (IDs, comma-sep)', 'phyto-bundle-builder' ); ?></label>
						<input type="text" name="slots[<?php echo $i; ?>][product_ids]" value="<?php echo esc_attr( implode( ',', $prod_ids ) ); ?>" class="regular-text" />
					</p>
					<p>
						<label><?php esc_html_e( 'Allowed Categories (IDs, comma-sep)', 'phyto-bundle-builder' ); ?></label>
						<input type="text" name="slots[<?php echo $i; ?>][category_ids]" value="<?php echo esc_attr( implode( ',', $cat_ids ) ); ?>" class="regular-text" />
					</p>
				</div>
				<?php endfor; ?>
				</div>

				<p class="submit">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Template', 'phyto-bundle-builder' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=phyto-bundle-builder' ) ); ?>" class="button">
						<?php esc_html_e( 'Cancel', 'phyto-bundle-builder' ); ?>
					</a>
				</p>
			</form>
		</div>
		<?php
	}

	public function handle_save() {
		check_admin_referer( 'phyto_bb_save' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Permission denied.' ); }

		$slots_raw = $_POST['slots'] ?? array();
		$slots = array();
		foreach ( $slots_raw as $idx => $slot ) {
			$prod_ids = array_filter( array_map( 'absint', explode( ',', $slot['product_ids'] ?? '' ) ) );
			$cat_ids  = array_filter( array_map( 'absint', explode( ',', $slot['category_ids'] ?? '' ) ) );
			$slots[ $idx ] = array(
				'label'        => sanitize_text_field( $slot['label'] ?? '' ),
				'product_ids'  => array_values( $prod_ids ),
				'category_ids' => array_values( $cat_ids ),
			);
		}

		$id = Phyto_BB_DB::save_template( array_merge( $_POST, array( 'id' => absint( $_POST['template_id'] ?? 0 ) ) ) );
		Phyto_BB_DB::save_slots( $id, $slots );

		wp_redirect( add_query_arg( array( 'page' => 'phyto-bundle-edit', 'id' => $id, 'saved' => '1' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public function handle_delete() {
		check_admin_referer( 'phyto_bb_delete' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( 'Permission denied.' ); }
		Phyto_BB_DB::delete_template( absint( $_POST['template_id'] ?? 0 ) );
		wp_redirect( admin_url( 'admin.php?page=phyto-bundle-builder' ) );
		exit;
	}

	public function ajax_search_products() {
		check_ajax_referer( 'phyto_bb_admin', 'nonce' );
		$term     = sanitize_text_field( wp_unslash( $_GET['q'] ?? '' ) );
		$products = wc_get_products( array(
			'status'   => 'publish',
			'limit'    => 20,
			's'        => $term,
			'return'   => 'objects',
		) );
		$results = array();
		foreach ( $products as $p ) {
			$results[] = array( 'id' => $p->get_id(), 'text' => $p->get_name() );
		}
		wp_send_json( $results );
	}
}
