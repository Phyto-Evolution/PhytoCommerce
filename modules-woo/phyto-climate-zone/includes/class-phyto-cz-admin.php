<?php
/**
 * Admin meta box for Phyto Climate Zone.
 *
 * Registers the "Climate Suitability" meta box on the product edit screen and
 * handles save / sanitisation of all climate-zone fields.
 *
 * @package PhytoClimateZone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_CZ_Admin
 */
class Phyto_CZ_Admin {

	/**
	 * Return the canonical list of India climate zone definitions.
	 *
	 * The list is passed through the `phyto_cz_zone_definitions` filter so
	 * third-party code can add, remove, or modify zones without patching this file.
	 *
	 * @return array Keyed array: zone_key => [ label, emoji, regions ].
	 */
	public function get_zone_definitions() {
		$zones = array(
			'coastal'            => array(
				'label'   => __( 'Coastal & Humid', 'phyto-climate-zone' ),
				'emoji'   => '🌊',
				'regions' => __( 'Kerala, coastal Karnataka, Goa, coastal TN/AP', 'phyto-climate-zone' ),
			),
			'tropical_highland'  => array(
				'label'   => __( 'Tropical Highland', 'phyto-climate-zone' ),
				'emoji'   => '⛰️',
				'regions' => __( 'Nilgiris, Coorg, Munnar, NE hills', 'phyto-climate-zone' ),
			),
			'tropical_plains'    => array(
				'label'   => __( 'Tropical Plains', 'phyto-climate-zone' ),
				'emoji'   => '☀️',
				'regions' => __( 'Most of peninsular India — Chennai, Bengaluru plains, Hyderabad', 'phyto-climate-zone' ),
			),
			'arid'               => array(
				'label'   => __( 'Arid & Semi-Arid', 'phyto-climate-zone' ),
				'emoji'   => '🏜️',
				'regions' => __( 'Rajasthan, parts of Maharashtra/Karnataka interior', 'phyto-climate-zone' ),
			),
			'temperate'          => array(
				'label'   => __( 'Temperate North', 'phyto-climate-zone' ),
				'emoji'   => '🌿',
				'regions' => __( 'Himachal, Uttarakhand foothills, J&K valleys', 'phyto-climate-zone' ),
			),
			'subtropical'        => array(
				'label'   => __( 'Sub-tropical', 'phyto-climate-zone' ),
				'emoji'   => '🌾',
				'regions' => __( 'Punjab, Haryana, UP, Delhi belt', 'phyto-climate-zone' ),
			),
			'northeast'          => array(
				'label'   => __( 'North-East Humid', 'phyto-climate-zone' ),
				'emoji'   => '🌧️',
				'regions' => __( 'Assam, Meghalaya, Manipur, Sikkim', 'phyto-climate-zone' ),
			),
		);

		/**
		 * Filter the complete list of India climate zone definitions.
		 *
		 * @since 1.0.0
		 * @param array $zones Keyed array of zone definitions.
		 *                     Each entry: [ 'label' => string, 'emoji' => string, 'regions' => string ].
		 */
		return apply_filters( 'phyto_cz_zone_definitions', $zones );
	}

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the "Climate Suitability" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto-climate-zone',
			__( 'Climate Suitability', 'phyto-climate-zone' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'default'
		);
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param WP_Post $post The current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_cz_save_meta', 'phyto_cz_nonce' );

		$saved_zones  = (array) get_post_meta( $post->ID, '_phyto_cz_zones', true );
		$temp_min     = get_post_meta( $post->ID, '_phyto_cz_temp_min', true );
		$temp_max     = get_post_meta( $post->ID, '_phyto_cz_temp_max', true );
		$placement    = (string) get_post_meta( $post->ID, '_phyto_cz_placement', true );
		$notes        = (string) get_post_meta( $post->ID, '_phyto_cz_notes', true );
		$zone_defs    = $this->get_zone_definitions();

		if ( empty( $placement ) ) {
			$placement = 'both';
		}
		?>
		<style>
			.phyto-cz-meta { font-size: 13px; }
			.phyto-cz-meta table { width: 100%; border-collapse: collapse; }
			.phyto-cz-meta th { text-align: left; padding: 6px 8px; width: 160px; font-weight: 600; vertical-align: top; }
			.phyto-cz-meta td { padding: 6px 8px; }
			.phyto-cz-meta input[type="number"],
			.phyto-cz-meta textarea { width: 100%; box-sizing: border-box; }
			.phyto-cz-meta input[type="number"] { width: 100px; }
			.phyto-cz-zones-grid { display: flex; flex-wrap: wrap; gap: 6px 16px; margin: 0; padding: 0; list-style: none; }
			.phyto-cz-zones-grid li { min-width: 220px; }
			.phyto-cz-zones-grid label { display: flex; align-items: center; gap: 6px; cursor: pointer; }
			.phyto-cz-zones-grid .phyto-cz-region { color: #777; font-size: 11px; margin-left: 22px; display: block; }
			.phyto-cz-section-head { font-weight: 600; color: #1a3c2b; margin: 10px 0 4px; }
		</style>

		<div class="phyto-cz-meta">
			<table>
				<tbody>
					<tr>
						<th scope="row">
							<label><?php esc_html_e( 'Suitable Climate Zones', 'phyto-climate-zone' ); ?></label>
						</th>
						<td>
							<ul class="phyto-cz-zones-grid">
								<?php foreach ( $zone_defs as $key => $def ) : ?>
									<li>
										<label>
											<input
												type="checkbox"
												name="phyto_cz_zones[]"
												value="<?php echo esc_attr( $key ); ?>"
												<?php checked( in_array( $key, $saved_zones, true ) ); ?>
											/>
											<span><?php echo esc_html( $def['emoji'] . ' ' . $def['label'] ); ?></span>
										</label>
										<span class="phyto-cz-region"><?php echo esc_html( $def['regions'] ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="phyto_cz_temp_min"><?php esc_html_e( 'Min Temperature (°C)', 'phyto-climate-zone' ); ?></label>
						</th>
						<td>
							<input
								type="number"
								id="phyto_cz_temp_min"
								name="phyto_cz_temp_min"
								value="<?php echo esc_attr( $temp_min ); ?>"
								step="0.5"
								min="-20"
								max="60"
							/>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="phyto_cz_temp_max"><?php esc_html_e( 'Max Temperature (°C)', 'phyto-climate-zone' ); ?></label>
						</th>
						<td>
							<input
								type="number"
								id="phyto_cz_temp_max"
								name="phyto_cz_temp_max"
								value="<?php echo esc_attr( $temp_max ); ?>"
								step="0.5"
								min="-20"
								max="60"
							/>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<?php esc_html_e( 'Indoor / Outdoor', 'phyto-climate-zone' ); ?>
						</th>
						<td>
							<label style="margin-right:16px;">
								<input type="radio" name="phyto_cz_placement" value="indoor" <?php checked( $placement, 'indoor' ); ?> />
								<?php esc_html_e( 'Indoor', 'phyto-climate-zone' ); ?>
							</label>
							<label style="margin-right:16px;">
								<input type="radio" name="phyto_cz_placement" value="outdoor" <?php checked( $placement, 'outdoor' ); ?> />
								<?php esc_html_e( 'Outdoor', 'phyto-climate-zone' ); ?>
							</label>
							<label>
								<input type="radio" name="phyto_cz_placement" value="both" <?php checked( $placement, 'both' ); ?> />
								<?php esc_html_e( 'Both', 'phyto-climate-zone' ); ?>
							</label>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="phyto_cz_notes"><?php esc_html_e( 'Climate Notes', 'phyto-climate-zone' ); ?></label>
						</th>
						<td>
							<textarea
								id="phyto_cz_notes"
								name="phyto_cz_notes"
								rows="4"
								placeholder="<?php esc_attr_e( 'Any additional notes about climate requirements or special care…', 'phyto-climate-zone' ); ?>"
							><?php echo esc_textarea( $notes ); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save meta box data with nonce check and capability check.
	 *
	 * @param int     $post_id The post ID being saved.
	 * @param WP_Post $post    The post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Nonce check.
		if ( ! isset( $_POST['phyto_cz_nonce'] ) ) {
			return;
		}
		check_admin_referer( 'phyto_cz_save_meta', 'phyto_cz_nonce' );

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// --- Zones (array of allowed keys) ---
		$allowed_keys  = array_keys( $this->get_zone_definitions() );
		$submitted     = isset( $_POST['phyto_cz_zones'] ) ? (array) $_POST['phyto_cz_zones'] : array();
		$sanitized_zones = array();
		foreach ( $submitted as $key ) {
			$key = sanitize_key( $key );
			if ( in_array( $key, $allowed_keys, true ) ) {
				$sanitized_zones[] = $key;
			}
		}
		update_post_meta( $post_id, '_phyto_cz_zones', $sanitized_zones );

		// --- Temperature min/max ---
		$temp_min = isset( $_POST['phyto_cz_temp_min'] ) ? $_POST['phyto_cz_temp_min'] : '';
		$temp_max = isset( $_POST['phyto_cz_temp_max'] ) ? $_POST['phyto_cz_temp_max'] : '';

		if ( '' !== $temp_min ) {
			update_post_meta( $post_id, '_phyto_cz_temp_min', (float) $temp_min );
		} else {
			delete_post_meta( $post_id, '_phyto_cz_temp_min' );
		}

		if ( '' !== $temp_max ) {
			update_post_meta( $post_id, '_phyto_cz_temp_max', (float) $temp_max );
		} else {
			delete_post_meta( $post_id, '_phyto_cz_temp_max' );
		}

		// --- Placement (indoor / outdoor / both) ---
		$allowed_placements = array( 'indoor', 'outdoor', 'both' );
		$placement = isset( $_POST['phyto_cz_placement'] ) ? sanitize_key( $_POST['phyto_cz_placement'] ) : 'both';
		if ( ! in_array( $placement, $allowed_placements, true ) ) {
			$placement = 'both';
		}
		update_post_meta( $post_id, '_phyto_cz_placement', $placement );

		// --- Notes ---
		$notes = isset( $_POST['phyto_cz_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['phyto_cz_notes'] ) ) : '';
		update_post_meta( $post_id, '_phyto_cz_notes', $notes );
	}
}
