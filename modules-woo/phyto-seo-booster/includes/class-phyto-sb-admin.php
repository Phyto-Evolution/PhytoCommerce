<?php
/**
 * Admin page for Phyto SEO Booster.
 *
 * @package PhytoSeoBooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_SB_Admin {

	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 55 );
		add_action( 'woocommerce_settings_tabs_phyto_seo', array( $this, 'render_settings' ) );
		add_action( 'woocommerce_update_options_phyto_seo', array( $this, 'save_settings' ) );

		// AJAX.
		add_action( 'wp_ajax_phyto_sb_run_audit', array( $this, 'ajax_run_audit' ) );
		add_action( 'wp_ajax_phyto_sb_generate_meta', array( $this, 'ajax_generate_meta' ) );
	}

	public function add_menu() {
		add_management_page(
			__( 'Phyto SEO Audit', 'phyto-seo-booster' ),
			__( 'Phyto SEO', 'phyto-seo-booster' ),
			'manage_woocommerce',
			'phyto-seo-audit',
			array( $this, 'render_page' )
		);
	}

	public function add_settings_tab( $tabs ) {
		$tabs['phyto_seo'] = __( 'Phyto SEO', 'phyto-seo-booster' );
		return $tabs;
	}

	public function render_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	private function get_settings() {
		return array(
			array( 'id' => 'phyto_seo_section', 'title' => __( 'SEO Booster Settings', 'phyto-seo-booster' ), 'type' => 'title' ),
			array(
				'id'    => 'phyto_sb_api_key',
				'title' => __( 'Claude API Key', 'phyto-seo-booster' ),
				'type'  => 'password',
				'desc'  => __( 'Required for AI-powered meta generation. Get a key at console.anthropic.com.', 'phyto-seo-booster' ),
			),
			array(
				'id'      => 'phyto_sb_currency',
				'title'   => __( 'Schema Currency', 'phyto-seo-booster' ),
				'type'    => 'text',
				'default' => get_woocommerce_currency(),
				'desc'    => __( 'ISO 4217 currency code for JSON-LD Product schema (e.g. INR, USD).', 'phyto-seo-booster' ),
			),
			array( 'id' => 'phyto_seo_section_end', 'type' => 'sectionend' ),
		);
	}

	public function render_page() {
		$audits = Phyto_SB_DB::get_all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Phyto SEO Audit', 'phyto-seo-booster' ); ?></h1>
			<p>
				<button id="phyto-sb-run-audit" class="button button-primary"><?php esc_html_e( 'Run Full Audit', 'phyto-seo-booster' ); ?></button>
				<?php if ( get_option( 'phyto_sb_api_key' ) ) : ?>
				<button id="phyto-sb-generate-meta" class="button" style="margin-left:8px;"><?php esc_html_e( 'Generate Missing Meta (AI)', 'phyto-seo-booster' ); ?></button>
				<?php endif; ?>
				<span id="phyto-sb-status" style="margin-left:12px;color:#666;"></span>
			</p>
			<?php if ( $audits ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'phyto-seo-booster' ); ?></th>
						<th style="width:80px;"><?php esc_html_e( 'Score', 'phyto-seo-booster' ); ?></th>
						<th><?php esc_html_e( 'Issues', 'phyto-seo-booster' ); ?></th>
						<th style="width:80px;"><?php esc_html_e( 'Action', 'phyto-seo-booster' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $audits as $a ) :
					$product = wc_get_product( $a->product_id );
					if ( ! $product ) continue;
					$score   = (int) $a->score;
					$color   = $score >= 80 ? '#3a9a6a' : ( $score >= 50 ? '#e8a135' : '#c0392b' );
					$issues  = json_decode( $a->issues_json, true ) ?: array();
					$labels  = array( 'meta_title' => 'Meta Title', 'meta_desc' => 'Meta Desc', 'description' => 'Description', 'has_image' => 'Image', 'has_sku' => 'SKU', 'has_price' => 'Price' );
				?>
				<tr>
					<td><?php echo esc_html( $product->get_name() ); ?></td>
					<td><strong style="color:<?php echo esc_attr( $color ); ?>;"><?php echo esc_html( $score ); ?>/100</strong></td>
					<td><?php
						if ( $issues ) {
							echo esc_html( implode( ', ', array_map( fn( $i ) => $labels[ $i ] ?? $i, $issues ) ) );
						} else {
							echo '<span style="color:#3a9a6a;">✓ All good</span>';
						}
					?></td>
					<td><a href="<?php echo esc_url( get_edit_post_link( $a->product_id ) ); ?>" class="button button-small"><?php esc_html_e( 'Fix', 'phyto-seo-booster' ); ?></a></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?php else : ?>
			<p><?php esc_html_e( 'No audit data yet. Click "Run Full Audit" to analyse your product catalogue.', 'phyto-seo-booster' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	public function ajax_run_audit() {
		check_ajax_referer( 'phyto_sb_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		$count = Phyto_SB_Audit::run_full_audit();
		wp_send_json_success( array( 'count' => $count ) );
	}

	public function ajax_generate_meta() {
		check_ajax_referer( 'phyto_sb_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}

		$api_key = get_option( 'phyto_sb_api_key', '' );
		if ( ! $api_key ) {
			wp_send_json_error( 'No API key configured.' );
		}

		$rows    = Phyto_SB_DB::get_products_below_score( 100 );
		$updated = 0;

		foreach ( $rows as $row ) {
			$product = wc_get_product( $row->product_id );
			if ( ! $product ) continue;

			$result = Phyto_SB_Audit::call_claude( $product->get_name(), $api_key );
			if ( ! $result ) continue;

			if ( ! empty( $result['meta_title'] ) ) {
				update_post_meta( $row->product_id, '_yoast_wpseo_title', sanitize_text_field( $result['meta_title'] ) );
				update_post_meta( $row->product_id, 'rank_math_title', sanitize_text_field( $result['meta_title'] ) );
			}
			if ( ! empty( $result['meta_description'] ) ) {
				update_post_meta( $row->product_id, '_yoast_wpseo_metadesc', sanitize_text_field( $result['meta_description'] ) );
				update_post_meta( $row->product_id, 'rank_math_description', sanitize_text_field( $result['meta_description'] ) );
			}
			$updated++;
			usleep( 300000 ); // 300ms between API calls.
		}

		wp_send_json_success( array( 'updated' => $updated ) );
	}

	public function enqueue_assets( $hook ) {
		if ( 'tools_page_phyto-seo-audit' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'phyto-sb-admin', PHYTO_SB_URL . 'assets/css/admin.css', array(), PHYTO_SB_VERSION );
		wp_enqueue_script( 'phyto-sb-admin', PHYTO_SB_URL . 'assets/js/admin.js', array( 'jquery' ), PHYTO_SB_VERSION, true );
		wp_localize_script( 'phyto-sb-admin', 'phytoSb', array(
			'nonce'    => wp_create_nonce( 'phyto_sb_nonce' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}
}
