<?php
/**
 * Frontend display for Phyto TC Batch Tracker.
 *
 * Adds a "Batch Provenance" tab to the WooCommerce single product page
 * listing all TC batches linked to the product. The tab is hidden when no
 * batches are linked. Enqueues the frontend CSS on render.
 *
 * @package PhytoTCBatchTracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_TCB_Frontend
 */
class Phyto_TCB_Frontend {

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_provenance_tab' ) );
	}

	/**
	 * Add the "Batch Provenance" tab to the single product tabs array.
	 *
	 * The tab is only added when the product has at least one linked batch.
	 * Relies on WooCommerce's `woocommerce_product_tabs` filter.
	 *
	 * @param array $tabs Existing product tabs.
	 * @return array Modified product tabs.
	 */
	public function add_provenance_tab( $tabs ) {
		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$batch_ids = (array) get_post_meta( $product->get_id(), '_phyto_tc_batches', true );
		$batch_ids = array_filter( array_map( 'intval', $batch_ids ) );

		if ( empty( $batch_ids ) ) {
			return $tabs;
		}

		/**
		 * Filter the front-end Batch Provenance tab title.
		 *
		 * @since 1.0.0
		 * @param string $title Default tab title.
		 */
		$tab_title = apply_filters( 'phyto_tcb_tab_title', __( 'Batch Provenance', 'phyto-tc-batch-tracker' ) );

		$tabs['phyto_tc_batch_provenance'] = array(
			'title'    => $tab_title,
			'priority' => 50,
			'callback' => array( $this, 'render_provenance_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the Batch Provenance tab content on the single product page.
	 *
	 * Outputs a table of linked TC batches. Fields displayed can be
	 * customised via the `phyto_tcb_batch_fields` filter.
	 */
	public function render_provenance_tab() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$batch_ids = (array) get_post_meta( $product->get_id(), '_phyto_tc_batches', true );
		$batch_ids = array_filter( array_map( 'intval', $batch_ids ) );

		if ( empty( $batch_ids ) ) {
			return;
		}

		// Collect batch data for each linked ID.
		$batches = array();
		foreach ( $batch_ids as $batch_id ) {
			$batch = Phyto_TCB_CPT::get_batch( $batch_id );
			if ( $batch ) {
				$batches[] = $batch;
			}
		}

		if ( empty( $batches ) ) {
			return;
		}

		/**
		 * Filter the list of fields shown in the Batch Provenance tab.
		 *
		 * Each entry is an array with keys:
		 *   - 'key'   (string) — the key in the batch data array
		 *   - 'label' (string) — the column/row label displayed to the customer
		 *
		 * Return an empty array to suppress all fields (tab will still show but
		 * the table body will be empty).
		 *
		 * @since 1.0.0
		 * @param array $fields Array of field definition arrays.
		 */
		$fields = apply_filters(
			'phyto_tcb_batch_fields',
			array(
				array(
					'key'   => 'batch_code',
					'label' => __( 'Batch ID', 'phyto-tc-batch-tracker' ),
				),
				array(
					'key'   => 'deflask_date',
					'label' => __( 'Deflask Date', 'phyto-tc-batch-tracker' ),
				),
				array(
					'key'   => 'status',
					'label' => __( 'Status', 'phyto-tc-batch-tracker' ),
				),
			)
		);

		/**
		 * Filter the status badge labels shown in the Batch Provenance tab.
		 *
		 * @since 1.0.0
		 * @param array $labels Associative array of status => display label.
		 */
		$status_labels = apply_filters(
			'phyto_tcb_status_labels',
			array(
				'active'      => __( 'Active', 'phyto-tc-batch-tracker' ),
				'depleted'    => __( 'Depleted', 'phyto-tc-batch-tracker' ),
				'quarantined' => __( 'Quarantined', 'phyto-tc-batch-tracker' ),
			)
		);

		wp_enqueue_style(
			'phyto-tcb-frontend',
			PHYTO_TCB_URL . 'assets/css/admin.css',
			array(),
			PHYTO_TCB_VERSION
		);
		?>
		<div class="phyto-tcb-provenance">
			<h2><?php esc_html_e( 'Batch Provenance', 'phyto-tc-batch-tracker' ); ?></h2>
			<p class="phyto-tcb-provenance__intro">
				<?php esc_html_e( 'The plant material in this product originates from the following tissue-culture batch(es):', 'phyto-tc-batch-tracker' ); ?>
			</p>
			<table class="phyto-tcb-provenance__table">
				<thead>
					<tr>
						<?php foreach ( $fields as $field ) : ?>
							<th><?php echo esc_html( $field['label'] ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $batches as $batch ) : ?>
						<tr>
							<?php foreach ( $fields as $field ) :
								$value = isset( $batch[ $field['key'] ] ) ? $batch[ $field['key'] ] : '';
							?>
								<td>
									<?php if ( 'status' === $field['key'] ) :
										$status_label = isset( $status_labels[ $value ] ) ? $status_labels[ $value ] : esc_html( $value );
									?>
										<span class="phyto-tcb-status phyto-tcb-status--<?php echo esc_attr( $value ); ?>">
											<?php echo esc_html( $status_label ); ?>
										</span>
									<?php elseif ( 'deflask_date' === $field['key'] && ! empty( $value ) ) :
										// Format date for display.
										$timestamp = strtotime( $value );
										echo esc_html( $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : $value );
									else : ?>
										<?php echo esc_html( $value ); ?>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}
