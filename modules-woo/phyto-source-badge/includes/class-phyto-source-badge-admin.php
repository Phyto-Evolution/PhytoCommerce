<?php
/**
 * Admin meta box for assigning Source Badges to WooCommerce products.
 *
 * Adds a "Source Badges" meta box to the product edit screen, renders a
 * checkbox list of all defined badges, and saves the selection as an array
 * of integer post IDs in `_phyto_source_badges`.
 *
 * @package PhytoSourceBadge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Source_Badge_Admin
 */
class Phyto_Source_Badge_Admin {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes',       array( $this, 'add_meta_box' ) );
		add_action( 'save_post_product',    array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the "Source Badges" meta box on the product post type.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_source_badges',
			__( 'Source Badges', 'phyto-source-badge' ),
			array( $this, 'render_meta_box' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render the Source Badges meta box on the product edit screen.
	 *
	 * Outputs a nonce and a checkbox list of all available phyto_badge definitions.
	 * If no badges are defined, a helper message directs the admin to create some.
	 *
	 * @param WP_Post $post Current product post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_sb_save_product_badges', 'phyto_sb_product_nonce' );

		$saved_ids = (array) get_post_meta( $post->ID, '_phyto_source_badges', true );
		$saved_ids = array_map( 'intval', $saved_ids );

		$badges = Phyto_Source_Badge_CPT::get_all_badges();
		?>
		<style>
			.phyto-sb-product-list { list-style: none; margin: 0; padding: 0; }
			.phyto-sb-product-list li { display: flex; align-items: center; gap: 8px; padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
			.phyto-sb-product-list li:last-child { border-bottom: none; }
			.phyto-sb-product-list label { display: flex; align-items: center; gap: 8px; cursor: pointer; flex: 1; }
			.phyto-sb-swatch {
				display: inline-block;
				width: 12px;
				height: 12px;
				border-radius: 50%;
				flex-shrink: 0;
				border-width: 1px;
				border-style: solid;
			}
			.phyto-sb-product-list .badge-icon { font-size: 14px; }
			.phyto-sb-product-list .badge-name { font-size: 12px; font-weight: 600; }
			.phyto-sb-no-badges { color: #666; font-size: 12px; }
			.phyto-sb-no-badges a { color: #2271b1; }
		</style>

		<?php if ( empty( $badges ) ) : ?>
			<p class="phyto-sb-no-badges">
				<?php
				printf(
					/* translators: %s: link to create badges */
					esc_html__( 'No badges defined yet — %s.', 'phyto-source-badge' ),
					'<a href="' . esc_url( admin_url( 'edit.php?post_type=phyto_badge' ) ) . '">' .
					esc_html__( 'create some under Products → Source Badges', 'phyto-source-badge' ) .
					'</a>'
				);
				?>
			</p>
		<?php else : ?>
			<ul class="phyto-sb-product-list">
				<?php foreach ( $badges as $badge ) : ?>
					<li>
						<label>
							<input
								type="checkbox"
								name="phyto_source_badges[]"
								value="<?php echo esc_attr( $badge['id'] ); ?>"
								<?php checked( in_array( (int) $badge['id'], $saved_ids, true ) ); ?>
							/>
							<span
								class="phyto-sb-swatch"
								style="background-color:<?php echo esc_attr( $badge['color'] ); ?>;border-color:<?php echo esc_attr( $badge['color'] ); ?>;"
							></span>
							<?php if ( ! empty( $badge['icon'] ) ) : ?>
								<span class="badge-icon"><?php echo esc_html( $badge['icon'] ); ?></span>
							<?php endif; ?>
							<span class="badge-name"><?php echo esc_html( $badge['title'] ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<?php
	}

	/**
	 * Save the selected badge IDs when the product post is saved.
	 *
	 * Validates nonce, capability, and autosave guard before writing
	 * `_phyto_source_badges` as an array of integer post IDs.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Verify nonce.
		if (
			! isset( $_POST['phyto_sb_product_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_sb_product_nonce'] ), 'phyto_sb_save_product_badges' )
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

		if ( isset( $_POST['phyto_source_badges'] ) && is_array( $_POST['phyto_source_badges'] ) ) {
			// Sanitise to an array of positive integers.
			$raw_ids = wp_unslash( $_POST['phyto_source_badges'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$badge_ids = array_values(
				array_filter(
					array_map( 'absint', $raw_ids )
				)
			);
			update_post_meta( $post_id, '_phyto_source_badges', $badge_ids );
		} else {
			// No badges checked — clear the meta.
			update_post_meta( $post_id, '_phyto_source_badges', array() );
		}
	}
}
