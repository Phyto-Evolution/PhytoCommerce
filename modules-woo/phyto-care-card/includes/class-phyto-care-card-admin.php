<?php
/**
 * Admin meta box for Phyto Care Card.
 *
 * Registers the "Plant Care Card" meta box on the product edit screen and
 * handles save / sanitisation of all care-guide fields.
 *
 * @package PhytoCareCard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Care_Card_Admin
 */
class Phyto_Care_Card_Admin {

	/**
	 * Allowed values for the light_req select field.
	 *
	 * @var array
	 */
	private $light_options = array(
		'Full Sun',
		'Partial Shade',
		'Bright Indirect',
		'Low Light',
		'Grow Light',
	);

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the "Plant Care Card" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto-care-card',
			__( 'Plant Care Card', 'phyto-care-card' ),
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
		wp_nonce_field( 'phyto_cc_save_meta', 'phyto_cc_nonce' );

		$light       = (string) get_post_meta( $post->ID, '_phyto_cc_light_req', true );
		$watering    = (string) get_post_meta( $post->ID, '_phyto_cc_watering', true );
		$humidity    = (string) get_post_meta( $post->ID, '_phyto_cc_humidity', true );
		$temp_min    = get_post_meta( $post->ID, '_phyto_cc_temp_min', true );
		$temp_max    = get_post_meta( $post->ID, '_phyto_cc_temp_max', true );
		$media       = (string) get_post_meta( $post->ID, '_phyto_cc_potting_media', true );
		$fert        = (string) get_post_meta( $post->ID, '_phyto_cc_fertilisation', true );
		$dormancy    = (string) get_post_meta( $post->ID, '_phyto_cc_dormancy_notes', true );
		$tips        = (string) get_post_meta( $post->ID, '_phyto_cc_special_tips', true );
		$attach_email = (string) get_post_meta( $post->ID, '_phyto_cc_attach_email', true );
		?>
		<style>
			.phyto-cc-meta table { width:100%; border-collapse:collapse; }
			.phyto-cc-meta th { text-align:left; padding:6px 8px; width:160px; font-weight:600; vertical-align:top; }
			.phyto-cc-meta td { padding:6px 8px; }
			.phyto-cc-meta input[type="text"],
			.phyto-cc-meta input[type="number"],
			.phyto-cc-meta select,
			.phyto-cc-meta textarea { width:100%; box-sizing:border-box; }
			.phyto-cc-meta .temp-row td { display:flex; gap:12px; align-items:center; }
			.phyto-cc-meta .temp-row input { width:80px; flex-shrink:0; }
			.phyto-cc-meta .section-head { background:#f0f4f0; font-weight:700; padding:8px; margin:12px 0 4px; border-left:3px solid #1a3c2b; }
		</style>
		<div class="phyto-cc-meta">
			<table>
				<tr>
					<th><label for="phyto_cc_light_req"><?php esc_html_e( 'Light Requirements', 'phyto-care-card' ); ?></label></th>
					<td>
						<select name="phyto_cc_light_req" id="phyto_cc_light_req">
							<option value=""><?php esc_html_e( '— Select —', 'phyto-care-card' ); ?></option>
							<?php foreach ( $this->light_options as $opt ) : ?>
								<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $light, $opt ); ?>>
									<?php echo esc_html( $opt ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_cc_watering"><?php esc_html_e( 'Watering', 'phyto-care-card' ); ?></label></th>
					<td><input type="text" name="phyto_cc_watering" id="phyto_cc_watering" value="<?php echo esc_attr( $watering ); ?>" placeholder="<?php esc_attr_e( 'e.g. Keep evenly moist; water when top 2 cm dry', 'phyto-care-card' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="phyto_cc_humidity"><?php esc_html_e( 'Humidity', 'phyto-care-card' ); ?></label></th>
					<td><input type="text" name="phyto_cc_humidity" id="phyto_cc_humidity" value="<?php echo esc_attr( $humidity ); ?>" placeholder="<?php esc_attr_e( 'e.g. 60–80% RH', 'phyto-care-card' ); ?>"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Temperature (°C)', 'phyto-care-card' ); ?></th>
					<td>
						<div style="display:flex;gap:16px;align-items:center;">
							<label for="phyto_cc_temp_min"><?php esc_html_e( 'Min', 'phyto-care-card' ); ?></label>
							<input type="number" name="phyto_cc_temp_min" id="phyto_cc_temp_min" value="<?php echo esc_attr( $temp_min ); ?>" step="0.1" style="width:80px;">
							<label for="phyto_cc_temp_max"><?php esc_html_e( 'Max', 'phyto-care-card' ); ?></label>
							<input type="number" name="phyto_cc_temp_max" id="phyto_cc_temp_max" value="<?php echo esc_attr( $temp_max ); ?>" step="0.1" style="width:80px;">
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_cc_potting_media"><?php esc_html_e( 'Potting Media', 'phyto-care-card' ); ?></label></th>
					<td><input type="text" name="phyto_cc_potting_media" id="phyto_cc_potting_media" value="<?php echo esc_attr( $media ); ?>" placeholder="<?php esc_attr_e( 'e.g. Chunky bark + perlite 60/40', 'phyto-care-card' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="phyto_cc_fertilisation"><?php esc_html_e( 'Fertilisation', 'phyto-care-card' ); ?></label></th>
					<td><input type="text" name="phyto_cc_fertilisation" id="phyto_cc_fertilisation" value="<?php echo esc_attr( $fert ); ?>" placeholder="<?php esc_attr_e( 'e.g. Half-strength balanced NPK fortnightly during growing season', 'phyto-care-card' ); ?>"></td>
				</tr>
				<tr>
					<th><label for="phyto_cc_dormancy_notes"><?php esc_html_e( 'Dormancy Notes', 'phyto-care-card' ); ?></label></th>
					<td><textarea name="phyto_cc_dormancy_notes" id="phyto_cc_dormancy_notes" rows="3" placeholder="<?php esc_attr_e( 'Describe dormancy period, requirements, and what to watch for.', 'phyto-care-card' ); ?>"><?php echo esc_textarea( $dormancy ); ?></textarea></td>
				</tr>
				<tr>
					<th><label for="phyto_cc_special_tips"><?php esc_html_e( 'Special Care Tips', 'phyto-care-card' ); ?></label></th>
					<td><textarea name="phyto_cc_special_tips" id="phyto_cc_special_tips" rows="3" placeholder="<?php esc_attr_e( 'Any extra tips, common mistakes, or acclimation notes.', 'phyto-care-card' ); ?>"><?php echo esc_textarea( $tips ); ?></textarea></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Attach to Order Email', 'phyto-care-card' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="phyto_cc_attach_email" value="1" <?php checked( '1', $attach_email ); ?>>
							<?php esc_html_e( 'Attach this care card PDF to the customer completed-order email', 'phyto-care-card' ); ?>
						</label>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Save care card meta when the product is saved.
	 *
	 * Guards against autosave, REST saves without nonce, and insufficient capability.
	 *
	 * @param int     $post_id Post ID being saved.
	 * @param WP_Post $post    Post object being saved.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Skip autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verify nonce.
		if (
			! isset( $_POST['phyto_cc_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_cc_nonce'] ), 'phyto_cc_save_meta' )
		) {
			return;
		}

		// Check capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// --- Light requirements (select, whitelist) ---
		if ( isset( $_POST['phyto_cc_light_req'] ) ) {
			$light = sanitize_text_field( wp_unslash( $_POST['phyto_cc_light_req'] ) );
			if ( in_array( $light, $this->light_options, true ) ) {
				update_post_meta( $post_id, '_phyto_cc_light_req', $light );
			} else {
				delete_post_meta( $post_id, '_phyto_cc_light_req' );
			}
		}

		// --- Text fields ---
		$text_fields = array( 'watering', 'humidity', 'potting_media', 'fertilisation' );
		foreach ( $text_fields as $field ) {
			$key = 'phyto_cc_' . $field;
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, '_' . $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}

		// --- Numeric fields ---
		foreach ( array( 'temp_min', 'temp_max' ) as $field ) {
			$key = 'phyto_cc_' . $field;
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
				update_post_meta( $post_id, '_' . $key, (float) $_POST[ $key ] );
			} else {
				delete_post_meta( $post_id, '_' . $key );
			}
		}

		// --- Textarea fields ---
		foreach ( array( 'dormancy_notes', 'special_tips' ) as $field ) {
			$key = 'phyto_cc_' . $field;
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, '_' . $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
			}
		}

		// --- Checkbox ---
		$attach = isset( $_POST['phyto_cc_attach_email'] ) ? '1' : '0';
		update_post_meta( $post_id, '_phyto_cc_attach_email', $attach );
	}
}
