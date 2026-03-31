<?php
/**
 * Admin: product meta box and products list-table column for TC Batch Tracker.
 *
 * Adds a "TC Batch Links" meta box to the product edit screen with a
 * Select2-powered searchable multi-select, saves linked batch IDs to
 * `_phyto_tc_batches`, and adds a "TC Batches" count column to the
 * WooCommerce products list table.
 *
 * @package PhytoTCBatchTracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_TCB_Admin
 */
class Phyto_TCB_Admin {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes',                  array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product',               array( $this, 'save_meta_box' ), 10, 2 );
		add_action( 'admin_enqueue_scripts',           array( $this, 'enqueue_assets' ) );

		// Products list-table column.
		add_filter( 'manage_product_posts_columns',       array( $this, 'add_column_header' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_column_cell' ), 10, 2 );
	}

	/**
	 * Enqueue Select2 and admin JS/CSS on the product edit screen.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_assets( $hook_suffix ) {
		// Only on product add/edit screens.
		if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}

		// WooCommerce already bundles Select2; enqueue the WC version.
		wp_enqueue_style( 'select2' );
		wp_enqueue_script( 'select2' );

		wp_enqueue_style(
			'phyto-tcb-admin',
			PHYTO_TCB_URL . 'assets/css/admin.css',
			array( 'select2' ),
			PHYTO_TCB_VERSION
		);

		wp_enqueue_script(
			'phyto-tcb-admin',
			PHYTO_TCB_URL . 'assets/js/admin.js',
			array( 'jquery', 'select2' ),
			PHYTO_TCB_VERSION,
			true
		);
	}

	/**
	 * Register the "TC Batch Links" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_tcb_product_links',
			__( 'TC Batch Links', 'phyto-tc-batch-tracker' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the TC Batch Links meta box on the product edit screen.
	 *
	 * Outputs a nonce and a searchable Select2 multi-select of all published
	 * phyto_tc_batch records. If no batches exist, a helper link is shown.
	 *
	 * @param WP_Post $post Current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_tcb_save_product_links', 'phyto_tcb_product_nonce' );

		$saved_ids = (array) get_post_meta( $post->ID, '_phyto_tc_batches', true );
		$saved_ids = array_map( 'intval', $saved_ids );

		$batches = Phyto_TCB_CPT::get_all_batches();

		$status_labels = apply_filters(
			'phyto_tcb_status_labels',
			array(
				'active'      => __( 'Active', 'phyto-tc-batch-tracker' ),
				'depleted'    => __( 'Depleted', 'phyto-tc-batch-tracker' ),
				'quarantined' => __( 'Quarantined', 'phyto-tc-batch-tracker' ),
			)
		);
		?>
		<div class="phyto-tcb-product-wrap">
			<?php if ( empty( $batches ) ) : ?>
				<p class="phyto-tcb-no-batches">
					<?php
					printf(
						/* translators: %s: link to create TC batches */
						esc_html__( 'No TC batches defined yet — %s.', 'phyto-tc-batch-tracker' ),
						'<a href="' . esc_url( admin_url( 'edit.php?post_type=phyto_tc_batch' ) ) . '">' .
						esc_html__( 'create some under WooCommerce → TC Batches', 'phyto-tc-batch-tracker' ) .
						'</a>'
					);
					?>
				</p>
			<?php else : ?>
				<p class="phyto-tcb-help">
					<?php esc_html_e( 'Select one or more TC batches whose plant material is contained in this product. Use the search box to filter by batch code or name.', 'phyto-tc-batch-tracker' ); ?>
				</p>
				<select
					id="phyto_tc_batches"
					name="phyto_tc_batches[]"
					multiple="multiple"
					class="phyto-tcb-select2"
					style="width:100%;"
					data-placeholder="<?php esc_attr_e( 'Search and select TC batches…', 'phyto-tc-batch-tracker' ); ?>"
				>
					<?php foreach ( $batches as $batch ) :
						$display_code   = ! empty( $batch['batch_code'] ) ? $batch['batch_code'] : $batch['title'];
						$status_key     = ! empty( $batch['status'] ) ? $batch['status'] : 'active';
						$status_label   = isset( $status_labels[ $status_key ] ) ? $status_labels[ $status_key ] : $status_key;
						$option_label   = esc_html( $display_code . ' — ' . $status_label );
						$selected       = in_array( (int) $batch['id'], $saved_ids, true ) ? ' selected="selected"' : '';
					?>
						<option value="<?php echo esc_attr( $batch['id'] ); ?>"<?php echo $selected; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above ?>>
							<?php echo $option_label; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="phyto-tcb-manage-link">
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=phyto_tc_batch' ) ); ?>">
						<?php esc_html_e( 'Manage TC Batches', 'phyto-tc-batch-tracker' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Save linked TC batch IDs when the product post is saved.
	 *
	 * Validates nonce, capability, and autosave guard before writing
	 * `_phyto_tc_batches` as an array of integer post IDs.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Verify nonce.
		if (
			! isset( $_POST['phyto_tcb_product_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_tcb_product_nonce'] ), 'phyto_tcb_save_product_links' )
		) {
			return;
		}

		// Skip autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['phyto_tc_batches'] ) && is_array( $_POST['phyto_tc_batches'] ) ) {
			// Sanitise to an array of positive integers.
			$raw_ids    = wp_unslash( $_POST['phyto_tc_batches'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$batch_ids  = array_values(
				array_filter(
					array_map( 'absint', $raw_ids )
				)
			);
			update_post_meta( $post_id, '_phyto_tc_batches', $batch_ids );
		} else {
			// No batches selected — clear the meta.
			update_post_meta( $post_id, '_phyto_tc_batches', array() );
		}
	}

	/**
	 * Add a "TC Batches" column header to the products list table.
	 *
	 * @param array $columns Existing column headers.
	 * @return array Modified column headers.
	 */
	public function add_column_header( $columns ) {
		// Insert before the last column (typically 'date').
		$new_columns = array();

		foreach ( $columns as $key => $label ) {
			if ( 'date' === $key ) {
				$new_columns['phyto_tc_batches'] = __( 'TC Batches', 'phyto-tc-batch-tracker' );
			}
			$new_columns[ $key ] = $label;
		}

		// Fallback: append if 'date' column not present.
		if ( ! isset( $new_columns['phyto_tc_batches'] ) ) {
			$new_columns['phyto_tc_batches'] = __( 'TC Batches', 'phyto-tc-batch-tracker' );
		}

		return $new_columns;
	}

	/**
	 * Render the "TC Batches" column cell for each product row.
	 *
	 * Shows the linked batch count, or a dash when none are linked.
	 *
	 * @param string $column  Column slug being rendered.
	 * @param int    $post_id Current product post ID.
	 */
	public function render_column_cell( $column, $post_id ) {
		if ( 'phyto_tc_batches' !== $column ) {
			return;
		}

		$batch_ids = (array) get_post_meta( $post_id, '_phyto_tc_batches', true );
		$batch_ids = array_filter( array_map( 'intval', $batch_ids ) );
		$count     = count( $batch_ids );

		if ( 0 === $count ) {
			echo '<span class="phyto-tcb-col-none">&mdash;</span>';
			return;
		}

		printf(
			'<a href="%1$s" class="phyto-tcb-col-count" title="%2$s">%3$d</a>',
			esc_url( get_edit_post_link( $post_id ) . '#phyto_tcb_product_links' ),
			/* translators: %d: number of linked TC batches */
			esc_attr( sprintf( _n( '%d TC batch linked', '%d TC batches linked', $count, 'phyto-tc-batch-tracker' ), $count ) ),
			absint( $count )
		);
	}
}
