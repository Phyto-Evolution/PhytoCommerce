<?php
/**
 * Custom Post Type for Phyto TC Batch records.
 *
 * Registers the `phyto_tc_batch` CPT (not publicly queryable; surfaced under
 * the WooCommerce admin menu as "TC Batches") and provides the meta box for
 * editing all batch fields. Also exposes a static helper that returns batch
 * data for use by the admin and frontend classes.
 *
 * @package PhytoTCBatchTracker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_TCB_CPT
 */
class Phyto_TCB_CPT {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'init',                      array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes',            array( $this, 'add_meta_box' ) );
		add_action( 'save_post_phyto_tc_batch',  array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the `phyto_tc_batch` custom post type.
	 *
	 * Not publicly queryable; managed under WooCommerce → TC Batches.
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'TC Batches', 'post type general name', 'phyto-tc-batch-tracker' ),
			'singular_name'      => _x( 'TC Batch', 'post type singular name', 'phyto-tc-batch-tracker' ),
			'add_new'            => __( 'Add New Batch', 'phyto-tc-batch-tracker' ),
			'add_new_item'       => __( 'Add New TC Batch', 'phyto-tc-batch-tracker' ),
			'edit_item'          => __( 'Edit TC Batch', 'phyto-tc-batch-tracker' ),
			'new_item'           => __( 'New TC Batch', 'phyto-tc-batch-tracker' ),
			'view_item'          => __( 'View TC Batch', 'phyto-tc-batch-tracker' ),
			'search_items'       => __( 'Search TC Batches', 'phyto-tc-batch-tracker' ),
			'not_found'          => __( 'No TC batches found.', 'phyto-tc-batch-tracker' ),
			'not_found_in_trash' => __( 'No TC batches found in trash.', 'phyto-tc-batch-tracker' ),
			'menu_name'          => __( 'TC Batches', 'phyto-tc-batch-tracker' ),
		);

		register_post_type(
			'phyto_tc_batch',
			array(
				'labels'              => $labels,
				'description'         => __( 'Tissue-culture batch provenance records linked to WooCommerce products.', 'phyto-tc-batch-tracker' ),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_menu'        => 'woocommerce',
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'show_in_rest'        => false,
				'query_var'           => false,
				'rewrite'             => false,
				'capability_type'     => 'post',
				'has_archive'         => false,
				'hierarchical'        => false,
				'menu_position'       => null,
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
			)
		);
	}

	/**
	 * Add the batch-details meta box to the phyto_tc_batch edit screen.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_tcb_batch_details',
			__( 'Batch Details', 'phyto-tc-batch-tracker' ),
			array( $this, 'render_meta_box' ),
			'phyto_tc_batch',
			'normal',
			'high'
		);
	}

	/**
	 * Render the batch-details meta box HTML.
	 *
	 * Outputs fields for batch code, parent plant/donor clone, agar medium
	 * formula, deflask date, lab technician/operator, batch notes, and status.
	 *
	 * @param WP_Post $post Current phyto_tc_batch post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_tcb_save_batch_details', 'phyto_tcb_batch_nonce' );

		$batch_code   = (string) get_post_meta( $post->ID, '_phyto_tcb_batch_code',    true );
		$parent_plant = (string) get_post_meta( $post->ID, '_phyto_tcb_parent_plant',  true );
		$agar_medium  = (string) get_post_meta( $post->ID, '_phyto_tcb_agar_medium',   true );
		$deflask_date = (string) get_post_meta( $post->ID, '_phyto_tcb_deflask_date',  true );
		$operator     = (string) get_post_meta( $post->ID, '_phyto_tcb_operator',      true );
		$notes        = (string) get_post_meta( $post->ID, '_phyto_tcb_notes',         true );
		$status       = (string) get_post_meta( $post->ID, '_phyto_tcb_status',        true );

		if ( empty( $status ) ) {
			$status = 'active';
		}

		$status_options = array(
			'active'      => __( 'Active', 'phyto-tc-batch-tracker' ),
			'depleted'    => __( 'Depleted', 'phyto-tc-batch-tracker' ),
			'quarantined' => __( 'Quarantined', 'phyto-tc-batch-tracker' ),
		);
		?>
		<style>
			.phyto-tcb-form table { width: 100%; border-collapse: collapse; }
			.phyto-tcb-form th { text-align: left; padding: 8px 12px 8px 0; font-weight: 600; width: 160px; vertical-align: top; color: #1d2327; }
			.phyto-tcb-form td { padding: 6px 0; }
			.phyto-tcb-form input[type="text"],
			.phyto-tcb-form input[type="date"],
			.phyto-tcb-form select { width: 100%; max-width: 480px; }
			.phyto-tcb-form textarea { width: 100%; max-width: 480px; min-height: 80px; resize: vertical; }
			.phyto-tcb-form .description { color: #666; font-size: 12px; margin-top: 4px; display: block; }
		</style>

		<div class="phyto-tcb-form">
			<table>
				<tr>
					<th><label for="phyto_tcb_batch_code"><?php esc_html_e( 'Batch Code', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_tcb_batch_code"
							name="phyto_tcb_batch_code"
							value="<?php echo esc_attr( $batch_code ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. TC-2025-001', 'phyto-tc-batch-tracker' ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'Unique batch identifier used for provenance tracking and lab records.', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_parent_plant"><?php esc_html_e( 'Parent Plant / Donor Clone', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_tcb_parent_plant"
							name="phyto_tcb_parent_plant"
							value="<?php echo esc_attr( $parent_plant ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Clone A-7, Accession #1234', 'phyto-tc-batch-tracker' ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'The mother plant or donor clone from which explants were taken.', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_agar_medium"><?php esc_html_e( 'Agar Medium Formula', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_tcb_agar_medium"
							name="phyto_tcb_agar_medium"
							value="<?php echo esc_attr( $agar_medium ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. MS + 0.1 mg/L BAP', 'phyto-tc-batch-tracker' ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'Culture medium formula used for this batch (Murashige-Skoog, etc.).', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_deflask_date"><?php esc_html_e( 'Deflask Date', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<input
							type="date"
							id="phyto_tcb_deflask_date"
							name="phyto_tcb_deflask_date"
							value="<?php echo esc_attr( $deflask_date ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'Date when plantlets were transferred from sterile culture to substrate.', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_operator"><?php esc_html_e( 'Lab Technician / Operator', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_tcb_operator"
							name="phyto_tcb_operator"
							value="<?php echo esc_attr( $operator ); ?>"
							placeholder="<?php esc_attr_e( 'e.g. Dr. Ananya Sharma', 'phyto-tc-batch-tracker' ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'Name of the technician or operator responsible for this batch.', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_status"><?php esc_html_e( 'Status', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<select id="phyto_tcb_status" name="phyto_tcb_status">
							<?php foreach ( $status_options as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $status, $val ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span class="description"><?php esc_html_e( 'Current batch status: Active (plants available), Depleted (sold out), or Quarantined (suspected health issue).', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_tcb_notes"><?php esc_html_e( 'Batch Notes', 'phyto-tc-batch-tracker' ); ?></label></th>
					<td>
						<textarea
							id="phyto_tcb_notes"
							name="phyto_tcb_notes"
						><?php echo esc_textarea( $notes ); ?></textarea>
						<span class="description"><?php esc_html_e( 'Internal notes about contamination events, viability rates, observations, etc.', 'phyto-tc-batch-tracker' ); ?></span>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save batch meta box data when the phyto_tc_batch post is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Verify nonce.
		if (
			! isset( $_POST['phyto_tcb_batch_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_tcb_batch_nonce'] ), 'phyto_tcb_save_batch_details' )
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

		$text_fields = array(
			'phyto_tcb_batch_code'   => '_phyto_tcb_batch_code',
			'phyto_tcb_parent_plant' => '_phyto_tcb_parent_plant',
			'phyto_tcb_agar_medium'  => '_phyto_tcb_agar_medium',
			'phyto_tcb_operator'     => '_phyto_tcb_operator',
		);

		foreach ( $text_fields as $field => $meta_key ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_post_meta(
					$post_id,
					$meta_key,
					sanitize_text_field( wp_unslash( $_POST[ $field ] ) )
				);
			}
		}

		// Deflask date — must be a valid YYYY-MM-DD date.
		if ( isset( $_POST['phyto_tcb_deflask_date'] ) ) {
			$raw_date = sanitize_text_field( wp_unslash( $_POST['phyto_tcb_deflask_date'] ) );
			if ( '' === $raw_date || preg_match( '/^\d{4}-\d{2}-\d{2}$/', $raw_date ) ) {
				update_post_meta( $post_id, '_phyto_tcb_deflask_date', $raw_date );
			}
		}

		// Status — must be one of the allowed values.
		if ( isset( $_POST['phyto_tcb_status'] ) ) {
			$raw_status     = sanitize_text_field( wp_unslash( $_POST['phyto_tcb_status'] ) );
			$allowed_status = array( 'active', 'depleted', 'quarantined' );
			if ( in_array( $raw_status, $allowed_status, true ) ) {
				update_post_meta( $post_id, '_phyto_tcb_status', $raw_status );
			}
		}

		// Notes — sanitize as textarea (allows newlines).
		if ( isset( $_POST['phyto_tcb_notes'] ) ) {
			update_post_meta(
				$post_id,
				'_phyto_tcb_notes',
				sanitize_textarea_field( wp_unslash( $_POST['phyto_tcb_notes'] ) )
			);
		}
	}

	/**
	 * Return all published TC batch definitions.
	 *
	 * Used by the admin product meta box and frontend renderer.
	 *
	 * @return array Array of batch data arrays, each containing:
	 *               - int    id
	 *               - string title        (post title, used as display name)
	 *               - string batch_code
	 *               - string parent_plant
	 *               - string agar_medium
	 *               - string deflask_date
	 *               - string operator
	 *               - string notes
	 *               - string status       (active|depleted|quarantined)
	 */
	public static function get_all_batches() {
		$posts = get_posts(
			array(
				'post_type'      => 'phyto_tc_batch',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$batches = array();

		foreach ( $posts as $post ) {
			$batches[] = array(
				'id'           => (int) $post->ID,
				'title'        => $post->post_title,
				'batch_code'   => (string) get_post_meta( $post->ID, '_phyto_tcb_batch_code',    true ),
				'parent_plant' => (string) get_post_meta( $post->ID, '_phyto_tcb_parent_plant',  true ),
				'agar_medium'  => (string) get_post_meta( $post->ID, '_phyto_tcb_agar_medium',   true ),
				'deflask_date' => (string) get_post_meta( $post->ID, '_phyto_tcb_deflask_date',  true ),
				'operator'     => (string) get_post_meta( $post->ID, '_phyto_tcb_operator',      true ),
				'notes'        => (string) get_post_meta( $post->ID, '_phyto_tcb_notes',         true ),
				'status'       => (string) get_post_meta( $post->ID, '_phyto_tcb_status',        true ),
			);
		}

		return $batches;
	}

	/**
	 * Return a single batch record by post ID.
	 *
	 * @param int $batch_id Post ID of the phyto_tc_batch record.
	 * @return array|false Batch data array or false if not found/not published.
	 */
	public static function get_batch( $batch_id ) {
		$post = get_post( (int) $batch_id );

		if ( ! $post || 'phyto_tc_batch' !== $post->post_type || 'publish' !== $post->post_status ) {
			return false;
		}

		return array(
			'id'           => (int) $post->ID,
			'title'        => $post->post_title,
			'batch_code'   => (string) get_post_meta( $post->ID, '_phyto_tcb_batch_code',    true ),
			'parent_plant' => (string) get_post_meta( $post->ID, '_phyto_tcb_parent_plant',  true ),
			'agar_medium'  => (string) get_post_meta( $post->ID, '_phyto_tcb_agar_medium',   true ),
			'deflask_date' => (string) get_post_meta( $post->ID, '_phyto_tcb_deflask_date',  true ),
			'operator'     => (string) get_post_meta( $post->ID, '_phyto_tcb_operator',      true ),
			'notes'        => (string) get_post_meta( $post->ID, '_phyto_tcb_notes',         true ),
			'status'       => (string) get_post_meta( $post->ID, '_phyto_tcb_status',        true ),
		);
	}
}
