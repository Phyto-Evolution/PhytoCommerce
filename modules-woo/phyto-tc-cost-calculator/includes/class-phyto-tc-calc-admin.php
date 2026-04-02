<?php
/**
 * Admin page for Phyto TC Cost Calculator.
 *
 * @package PhytoTcCostCalculator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_TC_Calc_Admin {

	public function register_hooks() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_phyto_tc_save', array( $this, 'ajax_save' ) );
		add_action( 'wp_ajax_phyto_tc_load', array( $this, 'ajax_load' ) );
		add_action( 'wp_ajax_phyto_tc_delete', array( $this, 'ajax_delete' ) );
		add_action( 'admin_post_phyto_tc_export', array( $this, 'export_csv' ) );
	}

	public function add_menu() {
		add_submenu_page( 'woocommerce', __( 'TC Cost Calculator', 'phyto-tc-cost-calculator' ), __( 'TC Cost Calc', 'phyto-tc-cost-calculator' ), 'manage_woocommerce', 'phyto-tc-calc', array( $this, 'render' ) );
	}

	public function render() {
		$estimates = Phyto_TC_Calc_DB::get_all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'TC Cost Calculator', 'phyto-tc-cost-calculator' ); ?></h1>
			<div class="phyto-tc-wrap">
				<!-- Calculator Panel -->
				<div class="phyto-tc-calc-panel">
					<h2><?php esc_html_e( 'New / Edit Estimate', 'phyto-tc-cost-calculator' ); ?></h2>
					<input type="hidden" id="phyto-tc-id" value="">
					<div class="phyto-tc-field">
						<label><?php esc_html_e( 'Batch ID', 'phyto-tc-cost-calculator' ); ?></label>
						<input type="text" id="phyto-tc-batch-id" placeholder="e.g. BATCH-2026-Q1">
					</div>
					<div class="phyto-tc-field">
						<label><?php esc_html_e( 'Estimate Label', 'phyto-tc-cost-calculator' ); ?></label>
						<input type="text" id="phyto-tc-label" placeholder="e.g. Q1 2026 Nepenthes Run">
					</div>
					<hr>
					<h3><?php esc_html_e( 'Cost Inputs', 'phyto-tc-cost-calculator' ); ?></h3>
					<?php
					$fields = array(
						array( 'substrate_cost',    __( 'Substrate cost per litre (₹)', 'phyto-tc-cost-calculator' ), 100 ),
						array( 'substrate_litres',  __( 'Litres of substrate used', 'phyto-tc-cost-calculator' ), 10 ),
						array( 'overhead_monthly',  __( 'Overhead cost per month (₹)', 'phyto-tc-cost-calculator' ), 5000 ),
						array( 'production_months', __( 'Production months', 'phyto-tc-cost-calculator' ), 3 ),
						array( 'labour_hourly',     __( 'Labour cost per hour (₹)', 'phyto-tc-cost-calculator' ), 150 ),
						array( 'labour_hours',      __( 'Labour hours', 'phyto-tc-cost-calculator' ), 20 ),
						array( 'plants_produced',   __( 'Plants produced', 'phyto-tc-cost-calculator' ), 500 ),
					);
					foreach ( $fields as $f ) : ?>
					<div class="phyto-tc-field">
						<label><?php echo esc_html( $f[1] ); ?></label>
						<input type="number" id="phyto-tc-<?php echo esc_attr( $f[0] ); ?>" class="phyto-tc-input" data-key="<?php echo esc_attr( $f[0] ); ?>" value="<?php echo esc_attr( $f[2] ); ?>" min="0" step="0.01">
					</div>
					<?php endforeach; ?>
					<div class="phyto-tc-field">
						<label><?php esc_html_e( 'Target Gross Margin %', 'phyto-tc-cost-calculator' ); ?> <span id="phyto-tc-margin-val">40</span>%</label>
						<input type="range" id="phyto-tc-margin" min="20" max="80" step="5" value="40">
					</div>
					<hr>
					<h3><?php esc_html_e( 'Results', 'phyto-tc-cost-calculator' ); ?></h3>
					<table class="phyto-tc-results">
						<tr><th><?php esc_html_e( 'Total Substrate Cost', 'phyto-tc-cost-calculator' ); ?></th><td id="res-substrate">—</td></tr>
						<tr><th><?php esc_html_e( 'Total Overhead', 'phyto-tc-cost-calculator' ); ?></th><td id="res-overhead">—</td></tr>
						<tr><th><?php esc_html_e( 'Total Labour', 'phyto-tc-cost-calculator' ); ?></th><td id="res-labour">—</td></tr>
						<tr class="phyto-tc-total"><th><?php esc_html_e( 'Total Cost', 'phyto-tc-cost-calculator' ); ?></th><td id="res-total">—</td></tr>
						<tr class="phyto-tc-total"><th><?php esc_html_e( 'Cost per Plant', 'phyto-tc-cost-calculator' ); ?></th><td id="res-per-plant">—</td></tr>
						<tr><th><?php esc_html_e( 'Suggested Retail (40% margin)', 'phyto-tc-cost-calculator' ); ?></th><td id="res-price-40">—</td></tr>
						<tr><th><?php esc_html_e( 'Suggested Retail (50% margin)', 'phyto-tc-cost-calculator' ); ?></th><td id="res-price-50">—</td></tr>
						<tr><th><?php esc_html_e( 'Suggested Retail (60% margin)', 'phyto-tc-cost-calculator' ); ?></th><td id="res-price-60">—</td></tr>
						<tr class="phyto-tc-target"><th><?php esc_html_e( 'Suggested Retail (target margin)', 'phyto-tc-cost-calculator' ); ?></th><td id="res-price-target">—</td></tr>
					</table>
					<div style="margin-top:16px;">
						<button id="phyto-tc-save" class="button button-primary"><?php esc_html_e( 'Save Estimate', 'phyto-tc-cost-calculator' ); ?></button>
						<button id="phyto-tc-new" class="button" style="margin-left:8px;"><?php esc_html_e( 'Clear', 'phyto-tc-cost-calculator' ); ?></button>
					</div>
				</div>

				<!-- Saved Estimates -->
				<div class="phyto-tc-saved-panel">
					<h2><?php esc_html_e( 'Saved Estimates', 'phyto-tc-cost-calculator' ); ?>
						<a href="<?php echo esc_url( admin_url( 'admin-post.php?action=phyto_tc_export&_wpnonce=' . wp_create_nonce( 'phyto_tc_export' ) ) ); ?>" class="button button-small" style="float:right;"><?php esc_html_e( '⬇ CSV', 'phyto-tc-cost-calculator' ); ?></a>
					</h2>
					<table class="wp-list-table widefat striped" id="phyto-tc-estimates-table">
						<thead><tr>
							<th><?php esc_html_e( 'Batch', 'phyto-tc-cost-calculator' ); ?></th>
							<th><?php esc_html_e( 'Label', 'phyto-tc-cost-calculator' ); ?></th>
							<th><?php esc_html_e( '₹/plant', 'phyto-tc-cost-calculator' ); ?></th>
							<th><?php esc_html_e( 'Retail (target)', 'phyto-tc-cost-calculator' ); ?></th>
							<th><?php esc_html_e( 'Date', 'phyto-tc-cost-calculator' ); ?></th>
							<th></th>
						</tr></thead>
						<tbody>
						<?php foreach ( $estimates as $est ) :
							$results = json_decode( $est->results_json, true ) ?: array();
							$cpp     = isset( $results['cost_per_plant'] ) ? '₹' . number_format( $results['cost_per_plant'], 2 ) : '—';
							$retail  = isset( $results['price_target'] ) ? '₹' . number_format( $results['price_target'], 2 ) : '—';
						?>
						<tr data-id="<?php echo esc_attr( $est->id ); ?>">
							<td><?php echo esc_html( $est->batch_id ); ?></td>
							<td><?php echo esc_html( $est->estimate_label ); ?></td>
							<td><?php echo esc_html( $cpp ); ?></td>
							<td><?php echo esc_html( $retail ); ?></td>
							<td><?php echo esc_html( substr( $est->created_at, 0, 10 ) ); ?></td>
							<td>
								<button class="button button-small phyto-tc-load-btn"><?php esc_html_e( 'Load', 'phyto-tc-cost-calculator' ); ?></button>
								<button class="button button-small phyto-tc-del-btn" style="color:#c0392b;margin-left:4px;"><?php esc_html_e( 'Del', 'phyto-tc-cost-calculator' ); ?></button>
							</td>
						</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php
	}

	public function ajax_save() {
		check_ajax_referer( 'phyto_tc_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_send_json_error(); }

		$data = array(
			'id'             => absint( $_POST['id'] ?? 0 ),
			'batch_id'       => sanitize_text_field( $_POST['batch_id'] ?? '' ),
			'estimate_label' => sanitize_text_field( $_POST['label'] ?? '' ),
			'inputs'         => json_decode( stripslashes( $_POST['inputs'] ?? '{}' ), true ),
			'results'        => json_decode( stripslashes( $_POST['results'] ?? '{}' ), true ),
		);

		$id = Phyto_TC_Calc_DB::save( $data );
		wp_send_json_success( array( 'id' => $id ) );
	}

	public function ajax_load() {
		check_ajax_referer( 'phyto_tc_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_send_json_error(); }
		$row = Phyto_TC_Calc_DB::get( absint( $_POST['id'] ?? 0 ) );
		if ( ! $row ) { wp_send_json_error(); }
		wp_send_json_success( array(
			'id'      => $row->id,
			'batch'   => $row->batch_id,
			'label'   => $row->estimate_label,
			'inputs'  => json_decode( $row->inputs_json, true ),
			'results' => json_decode( $row->results_json, true ),
		) );
	}

	public function ajax_delete() {
		check_ajax_referer( 'phyto_tc_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_send_json_error(); }
		Phyto_TC_Calc_DB::delete( absint( $_POST['id'] ?? 0 ) );
		wp_send_json_success();
	}

	public function export_csv() {
		check_admin_referer( 'phyto_tc_export' );
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die(); }
		$rows = Phyto_TC_Calc_DB::get_all();
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="tc-cost-estimates-' . gmdate( 'Y-m-d' ) . '.csv"' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'ID', 'Batch ID', 'Label', 'Cost/Plant (₹)', 'Retail 40%', 'Retail 50%', 'Retail 60%', 'Date' ) );
		foreach ( $rows as $r ) {
			$res = json_decode( $r->results_json, true ) ?: array();
			fputcsv( $out, array(
				$r->id, $r->batch_id, $r->estimate_label,
				$res['cost_per_plant'] ?? '', $res['price_40'] ?? '', $res['price_50'] ?? '', $res['price_60'] ?? '',
				$r->created_at,
			) );
		}
		fclose( $out );
		exit;
	}

	public function enqueue( $hook ) {
		if ( 'woocommerce_page_phyto-tc-calc' !== $hook ) { return; }
		wp_enqueue_style( 'phyto-tc-calc', PHYTO_TC_CALC_URL . 'assets/css/admin.css', array(), PHYTO_TC_CALC_VERSION );
		wp_enqueue_script( 'phyto-tc-calc', PHYTO_TC_CALC_URL . 'assets/js/calculator.js', array( 'jquery' ), PHYTO_TC_CALC_VERSION, true );
		wp_localize_script( 'phyto-tc-calc', 'phytoTc', array( 'nonce' => wp_create_nonce( 'phyto_tc_nonce' ), 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}
}
