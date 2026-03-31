<?php
/**
 * Admin meta box for Phyto Growth Stage.
 *
 * Adds a "Growth Stage" meta box to the WooCommerce product edit screen,
 * saves stage and notes as post meta, and adds a Growth Stage column to the
 * products admin list table.
 *
 * @package PhytoGrowthStage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Growth_Stage_Admin
 */
class Phyto_Growth_Stage_Admin {

	/**
	 * Return the canonical stage definitions array.
	 *
	 * Filtered via `phyto_growth_stage_definitions` so third-party plugins can
	 * add, remove, or modify stages without patching core code.
	 *
	 * @return array {
	 *     Keyed by stage slug.
	 *
	 *     @type string $label      Human-readable stage name.
	 *     @type string $difficulty Care-difficulty label.
	 *     @type string $time       Estimated time-to-maturity range.
	 *     @type string $color      Hex colour for the badge.
	 * }
	 */
	public function get_stages() {
		$stages = array(
			'deflasked'   => array(
				'label'      => __( 'Deflasked', 'phyto-growth-stage' ),
				'difficulty' => __( 'Beginner', 'phyto-growth-stage' ),
				'time'       => __( '3–6 months', 'phyto-growth-stage' ),
				'color'      => '#5b9bd5',
			),
			'juvenile'    => array(
				'label'      => __( 'Juvenile', 'phyto-growth-stage' ),
				'difficulty' => __( 'Beginner', 'phyto-growth-stage' ),
				'time'       => __( '6–12 months', 'phyto-growth-stage' ),
				'color'      => '#4caf7d',
			),
			'semi_mature' => array(
				'label'      => __( 'Semi-Mature', 'phyto-growth-stage' ),
				'difficulty' => __( 'Intermediate', 'phyto-growth-stage' ),
				'time'       => __( '12–18 months', 'phyto-growth-stage' ),
				'color'      => '#26a69a',
			),
			'mature'      => array(
				'label'      => __( 'Mature', 'phyto-growth-stage' ),
				'difficulty' => __( 'Intermediate', 'phyto-growth-stage' ),
				'time'       => __( '18–36 months', 'phyto-growth-stage' ),
				'color'      => '#e8a135',
			),
			'specimen'    => array(
				'label'      => __( 'Specimen', 'phyto-growth-stage' ),
				'difficulty' => __( 'Advanced', 'phyto-growth-stage' ),
				'time'       => __( '36+ months', 'phyto-growth-stage' ),
				'color'      => '#8e44ad',
			),
		);

		/**
		 * Filter the growth stage definitions.
		 *
		 * @since 1.0.0
		 * @param array $stages Associative array of stage_slug => definition.
		 */
		return apply_filters( 'phyto_growth_stage_definitions', $stages );
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );
		add_filter( 'manage_product_posts_columns', array( $this, 'add_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	/**
	 * Register the "Growth Stage" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_growth_stage',
			__( 'Growth Stage', 'phyto-growth-stage' ),
			array( $this, 'render_meta_box' ),
			'product',
			'side',
			'high'
		);
	}

	/**
	 * Render the Growth Stage meta box HTML.
	 *
	 * Outputs a nonce, a stage <select> with live JS preview of difficulty and
	 * time-to-maturity, and a Stage Notes textarea.
	 *
	 * @param WP_Post $post Current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_gs_save_fields', 'phyto_gs_nonce' );

		$current_stage = (string) get_post_meta( $post->ID, '_phyto_growth_stage', true );
		$current_notes = (string) get_post_meta( $post->ID, '_phyto_growth_stage_notes', true );
		$stages        = $this->get_stages();
		?>
		<style>
			.phyto-gs-metabox label { display: block; font-weight: 600; margin-bottom: 4px; color: #1d2327; }
			.phyto-gs-metabox select { width: 100%; margin-bottom: 10px; }
			.phyto-gs-metabox textarea { width: 100%; }
			#phyto-gs-preview { margin: 6px 0 12px; padding: 6px 10px; border-radius: 4px; font-size: 12px; background: #f0f6fc; border-left: 3px solid #2271b1; display: none; }
			#phyto-gs-preview strong { display: block; margin-bottom: 2px; }
		</style>

		<div class="phyto-gs-metabox">
			<label for="phyto_gs_stage">
				<?php esc_html_e( 'Current Stage', 'phyto-growth-stage' ); ?>
			</label>
			<select id="phyto_gs_stage" name="phyto_growth_stage">
				<option value=""><?php esc_html_e( '— Select Stage —', 'phyto-growth-stage' ); ?></option>
				<?php foreach ( $stages as $slug => $info ) : ?>
					<option
						value="<?php echo esc_attr( $slug ); ?>"
						data-difficulty="<?php echo esc_attr( $info['difficulty'] ); ?>"
						data-time="<?php echo esc_attr( $info['time'] ); ?>"
						data-color="<?php echo esc_attr( $info['color'] ); ?>"
						<?php selected( $current_stage, $slug ); ?>
					>
						<?php echo esc_html( $info['label'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<div id="phyto-gs-preview"></div>

			<label for="phyto_gs_notes">
				<?php esc_html_e( 'Stage Notes', 'phyto-growth-stage' ); ?>
			</label>
			<textarea
				id="phyto_gs_notes"
				name="phyto_growth_stage_notes"
				rows="4"
				placeholder="<?php esc_attr_e( 'Optional notes about this plant\'s current stage…', 'phyto-growth-stage' ); ?>"
			><?php echo esc_textarea( $current_notes ); ?></textarea>
		</div>

		<script>
		( function() {
			var stagesData = <?php echo wp_json_encode( $this->build_js_stages_data( $stages ) ); ?>;
			var select  = document.getElementById( 'phyto_gs_stage' );
			var preview = document.getElementById( 'phyto-gs-preview' );

			function updatePreview() {
				var val = select.value;
				if ( ! val || ! stagesData[ val ] ) {
					preview.style.display = 'none';
					return;
				}
				var s = stagesData[ val ];
				preview.style.display   = 'block';
				preview.style.borderColor = s.color;
				preview.innerHTML =
					'<strong>' + s.label + '</strong>' +
					'<span><?php echo esc_js( __( 'Difficulty:', 'phyto-growth-stage' ) ); ?> ' + s.difficulty + '</span><br>' +
					'<span><?php echo esc_js( __( 'Time to maturity:', 'phyto-growth-stage' ) ); ?> ' + s.time + '</span>';
			}

			select.addEventListener( 'change', updatePreview );
			updatePreview();
		} )();
		</script>
		<?php
	}

	/**
	 * Build a JS-safe associative array of stage data for the inline script.
	 *
	 * @param array $stages Stage definitions from get_stages().
	 * @return array
	 */
	private function build_js_stages_data( $stages ) {
		$data = array();
		foreach ( $stages as $slug => $info ) {
			$data[ $slug ] = array(
				'label'      => $info['label'],
				'difficulty' => $info['difficulty'],
				'time'       => $info['time'],
				'color'      => $info['color'],
			);
		}
		return $data;
	}

	/**
	 * Save meta box data when the product is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Verify nonce.
		if (
			! isset( $_POST['phyto_gs_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_gs_nonce'] ), 'phyto_gs_save_fields' )
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

		// Save stage key (validated against allowed slugs).
		if ( isset( $_POST['phyto_growth_stage'] ) ) {
			$raw     = sanitize_key( wp_unslash( $_POST['phyto_growth_stage'] ) );
			$allowed = array_keys( $this->get_stages() );
			$value   = in_array( $raw, $allowed, true ) ? $raw : '';
			update_post_meta( $post_id, '_phyto_growth_stage', $value );
		}

		// Save notes textarea.
		if ( isset( $_POST['phyto_growth_stage_notes'] ) ) {
			update_post_meta(
				$post_id,
				'_phyto_growth_stage_notes',
				sanitize_textarea_field( wp_unslash( $_POST['phyto_growth_stage_notes'] ) )
			);
		}
	}

	/**
	 * Add a "Growth Stage" column to the products admin list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_column( $columns ) {
		// Insert the Growth Stage column after the product name column.
		$new_columns = array();
		foreach ( $columns as $key => $label ) {
			$new_columns[ $key ] = $label;
			if ( 'name' === $key ) {
				$new_columns['phyto_growth_stage'] = __( 'Growth Stage', 'phyto-growth-stage' );
			}
		}
		return $new_columns;
	}

	/**
	 * Render the Growth Stage column cell for each product row.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Product post ID.
	 */
	public function render_column( $column, $post_id ) {
		if ( 'phyto_growth_stage' !== $column ) {
			return;
		}

		$stage_key = (string) get_post_meta( $post_id, '_phyto_growth_stage', true );

		if ( empty( $stage_key ) ) {
			echo '<span style="color:#999;">—</span>';
			return;
		}

		$stages = $this->get_stages();

		if ( ! isset( $stages[ $stage_key ] ) ) {
			echo '<span style="color:#999;">—</span>';
			return;
		}

		$info = $stages[ $stage_key ];
		printf(
			'<span style="display:inline-block;padding:2px 10px;border-radius:20px;background:%1$s;color:#fff;font-size:11px;font-weight:700;letter-spacing:.3px;">%2$s</span><br><span style="font-size:11px;color:#666;">%3$s</span>',
			esc_attr( $info['color'] ),
			esc_html( $info['label'] ),
			esc_html( $info['difficulty'] )
		);
	}
}
