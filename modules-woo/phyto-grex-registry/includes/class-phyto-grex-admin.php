<?php
/**
 * Admin meta box for Phyto Grex Registry.
 *
 * Adds a "Scientific Profile" meta box to the WooCommerce product edit screen
 * and handles saving all taxonomy fields as post meta.
 *
 * @package PhytoGrexRegistry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Grex_Admin
 */
class Phyto_Grex_Admin {

	/**
	 * Meta field definitions: [ slug => label ].
	 * The meta key stored in the DB is _phyto_grex_{slug}.
	 *
	 * @var array
	 */
	private $text_fields = array(
		'genus'         => 'Genus',
		'species'       => 'Species',
		'grex_name'     => 'Hybrid / Grex Name',
		'authority'     => 'Registration Authority',
		'common_name'   => 'Common Name',
	);

	/**
	 * Conservation status options.
	 *
	 * @var array
	 */
	private $conservation_options = array(
		''                    => '— Select —',
		'not_evaluated'       => 'Not Evaluated',
		'least_concern'       => 'Least Concern',
		'vulnerable'          => 'Vulnerable',
		'endangered'          => 'Endangered',
		'critically_endangered' => 'Critically Endangered',
		'cites_appendix_i'    => 'CITES Appendix I',
		'cites_appendix_ii'   => 'CITES Appendix II',
	);

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the "Scientific Profile" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_grex_scientific_profile',
			__( 'Scientific Profile', 'phyto-grex-registry' ),
			array( $this, 'render_meta_box' ),
			'product',
			'normal',
			'high'
		);
	}

	/**
	 * Render the meta box HTML.
	 *
	 * @param WP_Post $post Current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_grex_save_fields', 'phyto_grex_nonce' );

		$values = $this->get_field_values( $post->ID );
		?>
		<style>
			.phyto-grex-metabox table { width: 100%; border-collapse: collapse; }
			.phyto-grex-metabox th { width: 200px; text-align: left; padding: 8px 12px 8px 0; vertical-align: top; font-weight: 600; color: #444; }
			.phyto-grex-metabox td { padding: 6px 0; }
			.phyto-grex-metabox input[type="text"],
			.phyto-grex-metabox select,
			.phyto-grex-metabox textarea { width: 100%; max-width: 500px; }
			.phyto-grex-metabox .field-hint { color: #666; font-size: 12px; margin-top: 2px; }
		</style>
		<div class="phyto-grex-metabox">
			<table>
				<tbody>
					<?php foreach ( $this->text_fields as $slug => $label ) : ?>
					<tr>
						<th scope="row">
							<label for="phyto_grex_<?php echo esc_attr( $slug ); ?>">
								<?php echo esc_html( $label ); ?>
							</label>
						</th>
						<td>
							<input
								type="text"
								id="phyto_grex_<?php echo esc_attr( $slug ); ?>"
								name="phyto_grex_<?php echo esc_attr( $slug ); ?>"
								value="<?php echo esc_attr( $values[ $slug ] ); ?>"
							/>
							<?php if ( 'authority' === $slug ) : ?>
								<p class="field-hint"><?php esc_html_e( 'e.g. RHS, ICPS, IUCN', 'phyto-grex-registry' ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>

					<tr>
						<th scope="row">
							<label for="phyto_grex_conservation_status">
								<?php esc_html_e( 'Conservation Status', 'phyto-grex-registry' ); ?>
							</label>
						</th>
						<td>
							<select id="phyto_grex_conservation_status" name="phyto_grex_conservation_status">
								<?php foreach ( $this->conservation_options as $option_value => $option_label ) : ?>
									<option
										value="<?php echo esc_attr( $option_value ); ?>"
										<?php selected( $values['conservation_status'], $option_value ); ?>
									>
										<?php echo esc_html( $option_label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="phyto_grex_notes">
								<?php esc_html_e( 'Notes', 'phyto-grex-registry' ); ?>
							</label>
						</th>
						<td>
							<textarea
								id="phyto_grex_notes"
								name="phyto_grex_notes"
								rows="4"
								style="max-width:500px;width:100%;"
							><?php echo esc_textarea( $values['notes'] ); ?></textarea>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Save meta box data when the product is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) {
		// Verify nonce.
		if (
			! isset( $_POST['phyto_grex_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_grex_nonce'] ), 'phyto_grex_save_fields' )
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

		// Save text fields.
		foreach ( array_keys( $this->text_fields ) as $slug ) {
			$meta_key = '_phyto_grex_' . $slug;
			if ( isset( $_POST[ 'phyto_grex_' . $slug ] ) ) {
				update_post_meta(
					$post_id,
					$meta_key,
					sanitize_text_field( wp_unslash( $_POST[ 'phyto_grex_' . $slug ] ) )
				);
			}
		}

		// Save conservation status (validated against allowed values).
		if ( isset( $_POST['phyto_grex_conservation_status'] ) ) {
			$raw    = sanitize_key( wp_unslash( $_POST['phyto_grex_conservation_status'] ) );
			$allowed = array_keys( $this->conservation_options );
			$value  = in_array( $raw, $allowed, true ) ? $raw : '';
			update_post_meta( $post_id, '_phyto_grex_conservation_status', $value );
		}

		// Save notes (textarea — use sanitize_textarea_field).
		if ( isset( $_POST['phyto_grex_notes'] ) ) {
			update_post_meta(
				$post_id,
				'_phyto_grex_notes',
				sanitize_textarea_field( wp_unslash( $_POST['phyto_grex_notes'] ) )
			);
		}
	}

	/**
	 * Retrieve all field values for a given product post.
	 *
	 * @param int $post_id Product post ID.
	 * @return array Associative array of field slug => value.
	 */
	private function get_field_values( $post_id ) {
		$values = array();

		foreach ( array_keys( $this->text_fields ) as $slug ) {
			$values[ $slug ] = (string) get_post_meta( $post_id, '_phyto_grex_' . $slug, true );
		}

		$values['conservation_status'] = (string) get_post_meta( $post_id, '_phyto_grex_conservation_status', true );
		$values['notes']               = (string) get_post_meta( $post_id, '_phyto_grex_notes', true );

		return $values;
	}
}
