<?php
/**
 * Admin UI for Phyto Quick Add.
 *
 * @package PhytoQuickAdd
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_QA_Admin {

	public function register_hooks() {
		add_action( 'admin_menu',             array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_phyto_qa_add_product',       array( $this, 'ajax_add_product' ) );
		add_action( 'wp_ajax_phyto_qa_generate_desc',     array( $this, 'ajax_generate_desc' ) );
		add_action( 'wp_ajax_phyto_qa_test_ai',           array( $this, 'ajax_test_ai' ) );
		add_action( 'wp_ajax_phyto_qa_save_ai_settings',  array( $this, 'ajax_save_ai_settings' ) );
		add_action( 'wp_ajax_phyto_qa_fetch_taxonomy',    array( $this, 'ajax_fetch_taxonomy' ) );
		add_action( 'wp_ajax_phyto_qa_import_pack',       array( $this, 'ajax_import_pack' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'edit.php?post_type=product',
			__( 'Quick Add Product', 'phyto-quickadd' ),
			__( 'Quick Add', 'phyto-quickadd' ),
			'manage_woocommerce',
			'phyto-quickadd',
			array( $this, 'render_page' )
		);
	}

	public function enqueue( $hook ) {
		if ( strpos( $hook, 'phyto-quickadd' ) === false ) { return; }
		wp_enqueue_media();
		wp_enqueue_style( 'select2',        includes_url( 'css/jquery-ui-fresh.css' ) );
		wp_enqueue_style( 'phyto-qa-admin', PHYTO_QA_URL . 'assets/css/admin.css', array(), PHYTO_QA_VERSION );
		wp_enqueue_script( 'phyto-qa-admin', PHYTO_QA_URL . 'assets/js/admin.js', array( 'jquery', 'wp-util' ), PHYTO_QA_VERSION, true );
		wp_localize_script( 'phyto-qa-admin', 'phytoQA', array(
			'nonce'    => wp_create_nonce( 'phyto_qa_nonce' ),
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'provider' => get_option( 'phyto_qa_ai_provider', 'claude' ),
		) );
	}

	public function render_page() {
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'add';
		$tabs = array(
			'add'      => __( 'Add Product', 'phyto-quickadd' ),
			'ai'       => __( 'AI Settings', 'phyto-quickadd' ),
			'taxonomy' => __( 'Taxonomy Importer', 'phyto-quickadd' ),
		);
		?>
		<div class="wrap phyto-qa-wrap">
			<h1><?php esc_html_e( 'Phyto Quick Add', 'phyto-quickadd' ); ?></h1>
			<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'post_type' => 'product', 'page' => 'phyto-quickadd', 'tab' => $key ), admin_url( 'edit.php' ) ) ); ?>"
				   class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
			</nav>

			<div class="phyto-qa-tab-content">
			<?php
			switch ( $active_tab ) {
				case 'ai':      $this->render_ai_tab();       break;
				case 'taxonomy': $this->render_taxonomy_tab(); break;
				default:        $this->render_add_tab();
			}
			?>
			</div>
		</div>
		<?php
	}

	private function render_add_tab() {
		$categories = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'number' => 200 ) );
		?>
		<div class="phyto-qa-add-form">
			<div class="phyto-qa-form-col">
				<table class="form-table phyto-qa-table">
					<tr>
						<th><label for="qa-name"><?php esc_html_e( 'Product Name *', 'phyto-quickadd' ); ?></label></th>
						<td><input type="text" id="qa-name" class="regular-text" placeholder="Echeveria elegans 'Pearl of Nuremberg'" /></td>
					</tr>
					<tr>
						<th><label for="qa-price"><?php esc_html_e( 'Regular Price *', 'phyto-quickadd' ); ?></label></th>
						<td><input type="number" id="qa-price" class="small-text" min="0" step="0.01" /></td>
					</tr>
					<tr>
						<th><label for="qa-sale-price"><?php esc_html_e( 'Sale Price', 'phyto-quickadd' ); ?></label></th>
						<td><input type="number" id="qa-sale-price" class="small-text" min="0" step="0.01" /></td>
					</tr>
					<tr>
						<th><label for="qa-stock"><?php esc_html_e( 'Stock Qty', 'phyto-quickadd' ); ?></label></th>
						<td><input type="number" id="qa-stock" class="small-text" min="0" step="1" value="1" /></td>
					</tr>
					<tr>
						<th><label for="qa-sku"><?php esc_html_e( 'SKU', 'phyto-quickadd' ); ?></label></th>
						<td><input type="text" id="qa-sku" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label for="qa-category"><?php esc_html_e( 'Category', 'phyto-quickadd' ); ?></label></th>
						<td>
							<select id="qa-category">
								<option value=""><?php esc_html_e( '— Select —', 'phyto-quickadd' ); ?></option>
								<?php foreach ( $categories as $cat ) : ?>
								<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><label for="qa-tags"><?php esc_html_e( 'Tags (comma-sep)', 'phyto-quickadd' ); ?></label></th>
						<td><input type="text" id="qa-tags" class="regular-text" placeholder="succulent, rosette, easy-care" /></td>
					</tr>
					<tr>
						<th><label for="qa-short-desc"><?php esc_html_e( 'Short Description', 'phyto-quickadd' ); ?></label></th>
						<td><textarea id="qa-short-desc" rows="3" class="large-text"></textarea></td>
					</tr>
					<tr>
						<th>
							<label for="qa-desc"><?php esc_html_e( 'Description', 'phyto-quickadd' ); ?></label>
							<br><button type="button" id="qa-gen-desc" class="button button-secondary" style="margin-top:6px;">
								<?php esc_html_e( 'Generate (AI)', 'phyto-quickadd' ); ?>
							</button>
						</th>
						<td>
							<textarea id="qa-desc" rows="8" class="large-text"></textarea>
							<p id="qa-gen-status" class="description"></p>
						</td>
					</tr>
					<tr>
						<th><label><?php esc_html_e( 'Images', 'phyto-quickadd' ); ?></label></th>
						<td>
							<button type="button" id="qa-pick-images" class="button"><?php esc_html_e( 'Select / Upload Images', 'phyto-quickadd' ); ?></button>
							<div id="qa-image-preview" class="phyto-qa-image-preview"></div>
							<input type="hidden" id="qa-image-ids" value="" />
						</td>
					</tr>
				</table>

				<p class="phyto-qa-actions">
					<button type="button" id="qa-submit" class="button button-primary button-large">
						<?php esc_html_e( 'Add Product', 'phyto-quickadd' ); ?>
					</button>
					<span id="qa-submit-status" class="description"></span>
				</p>
			</div>
		</div>
		<?php
	}

	private function render_ai_tab() {
		$provider = get_option( 'phyto_qa_ai_provider', 'claude' );
		$providers = array(
			'claude'  => 'Claude (Anthropic)',
			'openai'  => 'OpenAI (GPT-4o mini)',
			'gemini'  => 'Google Gemini',
			'mistral' => 'Mistral AI',
			'cohere'  => 'Cohere',
		);
		?>
		<div class="phyto-qa-ai-settings">
			<table class="form-table">
				<tr>
					<th><label for="qa-ai-provider"><?php esc_html_e( 'AI Provider', 'phyto-quickadd' ); ?></label></th>
					<td>
						<select id="qa-ai-provider">
							<?php foreach ( $providers as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $provider, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<?php foreach ( $providers as $key => $label ) : ?>
				<tr class="qa-key-row" data-provider="<?php echo esc_attr( $key ); ?>" <?php echo $provider !== $key ? 'style="display:none"' : ''; ?>>
					<th><label for="qa-key-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?> API Key</label></th>
					<td>
						<input type="password" id="qa-key-<?php echo esc_attr( $key ); ?>" class="regular-text qa-api-key"
							data-provider="<?php echo esc_attr( $key ); ?>"
							value="<?php echo esc_attr( get_option( 'phyto_qa_ai_key_' . $key, '' ) ); ?>" />
					</td>
				</tr>
				<?php endforeach; ?>
			</table>

			<p class="phyto-qa-actions">
				<button type="button" id="qa-save-ai" class="button button-primary"><?php esc_html_e( 'Save Settings', 'phyto-quickadd' ); ?></button>
				<button type="button" id="qa-test-ai" class="button button-secondary"><?php esc_html_e( 'Test Connection', 'phyto-quickadd' ); ?></button>
				<span id="qa-ai-status" class="description"></span>
			</p>
		</div>
		<?php
	}

	private function render_taxonomy_tab() {
		?>
		<div class="phyto-qa-taxonomy">
			<p class="description">
				<?php esc_html_e( 'Import PhytoCommerce taxonomy packs as WooCommerce product categories (family → genus hierarchy).', 'phyto-quickadd' ); ?>
			</p>
			<p>
				<button type="button" id="qa-fetch-taxonomy" class="button button-primary">
					<?php esc_html_e( 'Fetch Taxonomy Index', 'phyto-quickadd' ); ?>
				</button>
				<span id="qa-taxonomy-status" class="description"></span>
			</p>
			<div id="qa-taxonomy-packs" class="phyto-qa-pack-grid"></div>
		</div>
		<?php
	}

	// ── AJAX ──────────────────────────────────────────────────────────────

	public function ajax_add_product() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$name       = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$price      = wc_format_decimal( $_POST['price'] ?? 0 );
		$sale_price = wc_format_decimal( $_POST['sale_price'] ?? '' );
		$stock      = absint( $_POST['stock'] ?? 1 );
		$sku        = sanitize_text_field( wp_unslash( $_POST['sku'] ?? '' ) );
		$cat_id     = absint( $_POST['category'] ?? 0 );
		$tags_raw   = sanitize_text_field( wp_unslash( $_POST['tags'] ?? '' ) );
		$short_desc = wp_kses_post( wp_unslash( $_POST['short_desc'] ?? '' ) );
		$desc       = wp_kses_post( wp_unslash( $_POST['description'] ?? '' ) );
		$image_ids  = array_filter( array_map( 'absint', explode( ',', $_POST['image_ids'] ?? '' ) ) );

		if ( ! $name || ! $price ) {
			wp_send_json_error( __( 'Name and price are required.', 'phyto-quickadd' ) );
		}

		$product = new WC_Product_Simple();
		$product->set_name( $name );
		$product->set_regular_price( $price );
		if ( $sale_price !== '' ) { $product->set_sale_price( $sale_price ); }
		$product->set_manage_stock( true );
		$product->set_stock_quantity( $stock );
		if ( $sku ) { $product->set_sku( $sku ); }
		$product->set_short_description( $short_desc );
		$product->set_description( $desc );
		if ( $cat_id ) { $product->set_category_ids( array( $cat_id ) ); }

		if ( $image_ids ) {
			$product->set_image_id( $image_ids[0] );
			if ( count( $image_ids ) > 1 ) {
				$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
			}
		}

		if ( $tags_raw ) {
			$tags = array_map( 'trim', explode( ',', $tags_raw ) );
			$product->set_tag_ids( array_map( function( $t ) {
				$term = get_term_by( 'name', $t, 'product_tag' ) ?: wp_insert_term( $t, 'product_tag' );
				return is_wp_error( $term ) ? null : ( is_array( $term ) ? $term['term_id'] : $term->term_id );
			}, $tags ) );
		}

		$id = $product->save();
		if ( ! $id || is_wp_error( $id ) ) {
			wp_send_json_error( __( 'Failed to save product.', 'phyto-quickadd' ) );
		}

		wp_send_json_success( array(
			'id'   => $id,
			'edit' => get_edit_post_link( $id, 'raw' ),
			'view' => get_permalink( $id ),
		) );
	}

	public function ajax_generate_desc() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$context = sanitize_text_field( wp_unslash( $_POST['context'] ?? '' ) );

		if ( ! $name ) {
			wp_send_json_error( __( 'Enter a product name first.', 'phyto-quickadd' ) );
		}

		$result = Phyto_QA_AI::generate( $name, $context );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}
		wp_send_json_success( array( 'text' => $result ) );
	}

	public function ajax_test_ai() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$provider = sanitize_key( $_POST['provider'] ?? 'claude' );
		$key      = sanitize_text_field( wp_unslash( $_POST['key'] ?? '' ) );

		$result = Phyto_QA_AI::test( $provider, $key );
		if ( $result === true ) {
			wp_send_json_success( __( 'Connection OK!', 'phyto-quickadd' ) );
		}
		wp_send_json_error( $result );
	}

	public function ajax_save_ai_settings() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$provider = sanitize_key( $_POST['provider'] ?? 'claude' );
		update_option( 'phyto_qa_ai_provider', $provider );

		$providers = array( 'claude', 'openai', 'gemini', 'mistral', 'cohere' );
		foreach ( $providers as $p ) {
			$key = sanitize_text_field( wp_unslash( $_POST[ 'key_' . $p ] ?? '' ) );
			if ( $key ) {
				update_option( 'phyto_qa_ai_key_' . $p, $key );
			}
		}

		wp_send_json_success( __( 'Settings saved.', 'phyto-quickadd' ) );
	}

	public function ajax_fetch_taxonomy() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$index = Phyto_QA_Taxonomy::fetch_index();
		if ( is_wp_error( $index ) ) {
			wp_send_json_error( $index->get_error_message() );
		}
		wp_send_json_success( $index );
	}

	public function ajax_import_pack() {
		check_ajax_referer( 'phyto_qa_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Permission denied.' );
		}

		$path = sanitize_text_field( wp_unslash( $_POST['path'] ?? '' ) );
		if ( ! $path ) {
			wp_send_json_error( 'No pack path provided.' );
		}

		$pack_data = Phyto_QA_Taxonomy::fetch_pack( $path );
		if ( is_wp_error( $pack_data ) ) {
			wp_send_json_error( $pack_data->get_error_message() );
		}

		$result = Phyto_QA_Taxonomy::import_pack( $pack_data );
		wp_send_json_success( $result );
	}
}
