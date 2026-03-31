<?php
/**
 * Custom Post Type for Phyto Source Badge definitions.
 *
 * Registers the `phyto_badge` CPT and provides the admin meta box for
 * editing badge color, icon, and tooltip. Also exposes a static helper
 * that returns all badge definitions for use by other classes.
 *
 * @package PhytoSourceBadge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Source_Badge_CPT
 */
class Phyto_Source_Badge_CPT {

	/**
	 * Register WordPress hooks.
	 */
	public function register_hooks() {
		add_action( 'init',          array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post_phyto_badge', array( $this, 'save_meta_box' ), 10, 2 );
	}

	/**
	 * Register the `phyto_badge` custom post type.
	 *
	 * Not public (no front-end archive or single pages); appears in the
	 * WooCommerce admin menu as "Products → Source Badges".
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => _x( 'Source Badges', 'post type general name', 'phyto-source-badge' ),
			'singular_name'      => _x( 'Source Badge', 'post type singular name', 'phyto-source-badge' ),
			'add_new'            => __( 'Add New Badge', 'phyto-source-badge' ),
			'add_new_item'       => __( 'Add New Source Badge', 'phyto-source-badge' ),
			'edit_item'          => __( 'Edit Source Badge', 'phyto-source-badge' ),
			'new_item'           => __( 'New Source Badge', 'phyto-source-badge' ),
			'view_item'          => __( 'View Source Badge', 'phyto-source-badge' ),
			'search_items'       => __( 'Search Source Badges', 'phyto-source-badge' ),
			'not_found'          => __( 'No source badges found.', 'phyto-source-badge' ),
			'not_found_in_trash' => __( 'No source badges found in trash.', 'phyto-source-badge' ),
			'menu_name'          => __( 'Source Badges', 'phyto-source-badge' ),
		);

		register_post_type(
			'phyto_badge',
			array(
				'labels'              => $labels,
				'description'         => __( 'Sourcing-origin badges assigned to WooCommerce products.', 'phyto-source-badge' ),
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
	 * Add the badge-details meta box to the phyto_badge edit screen.
	 */
	public function add_meta_box() {
		add_meta_box(
			'phyto_sb_badge_details',
			__( 'Badge Details', 'phyto-source-badge' ),
			array( $this, 'render_meta_box' ),
			'phyto_badge',
			'normal',
			'high'
		);
	}

	/**
	 * Render the badge-details meta box HTML.
	 *
	 * Outputs fields for badge color, icon emoji, and tooltip text.
	 *
	 * @param WP_Post $post Current phyto_badge post object.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( 'phyto_sb_save_badge_details', 'phyto_sb_badge_nonce' );

		$color   = (string) get_post_meta( $post->ID, '_phyto_badge_color', true );
		$icon    = (string) get_post_meta( $post->ID, '_phyto_badge_icon', true );
		$tooltip = (string) get_post_meta( $post->ID, '_phyto_badge_tooltip', true );

		// Default color fallback.
		if ( empty( $color ) ) {
			$color = '#3a9a6a';
		}
		?>
		<style>
			.phyto-sb-badge-form table { width: 100%; border-collapse: collapse; }
			.phyto-sb-badge-form th { text-align: left; padding: 8px 12px 8px 0; font-weight: 600; width: 130px; vertical-align: top; color: #1d2327; }
			.phyto-sb-badge-form td { padding: 6px 0; }
			.phyto-sb-badge-form input[type="text"],
			.phyto-sb-badge-form input[type="color"] { vertical-align: middle; }
			.phyto-sb-badge-form input[type="text"] { width: 100%; max-width: 400px; }
			.phyto-sb-badge-form input[type="color"] { width: 44px; height: 30px; padding: 2px; border-radius: 4px; cursor: pointer; }
			.phyto-sb-badge-form .phyto-sb-color-row { display: flex; align-items: center; gap: 10px; }
			.phyto-sb-badge-form .phyto-sb-color-hex { width: 90px; font-family: monospace; }
			.phyto-sb-badge-form .description { color: #666; font-size: 12px; margin-top: 4px; display: block; }
			.phyto-sb-preview { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; border-width: 1px; border-style: solid; margin-top: 14px; }
		</style>

		<div class="phyto-sb-badge-form">
			<table>
				<tr>
					<th><label for="phyto_sb_color"><?php esc_html_e( 'Badge Color', 'phyto-source-badge' ); ?></label></th>
					<td>
						<div class="phyto-sb-color-row">
							<input
								type="color"
								id="phyto_sb_color_picker"
								value="<?php echo esc_attr( $color ); ?>"
							/>
							<input
								type="text"
								id="phyto_sb_color"
								name="phyto_sb_color"
								value="<?php echo esc_attr( $color ); ?>"
								class="phyto-sb-color-hex"
								maxlength="7"
								pattern="#[0-9a-fA-F]{6}"
							/>
						</div>
						<span class="description"><?php esc_html_e( 'Hex colour code (e.g. #3a9a6a). Used for the badge border, text, and tinted background.', 'phyto-source-badge' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_sb_icon"><?php esc_html_e( 'Icon Emoji', 'phyto-source-badge' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_sb_icon"
							name="phyto_sb_icon"
							value="<?php echo esc_attr( $icon ); ?>"
							maxlength="2"
							style="width:60px;font-size:20px;text-align:center;"
						/>
						<span class="description"><?php esc_html_e( 'One emoji character displayed before the badge label (max 2 chars).', 'phyto-source-badge' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><label for="phyto_sb_tooltip"><?php esc_html_e( 'Tooltip', 'phyto-source-badge' ); ?></label></th>
					<td>
						<input
							type="text"
							id="phyto_sb_tooltip"
							name="phyto_sb_tooltip"
							value="<?php echo esc_attr( $tooltip ); ?>"
						/>
						<span class="description"><?php esc_html_e( 'Short explanation shown when the customer hovers the badge.', 'phyto-source-badge' ); ?></span>
					</td>
				</tr>
			</table>

			<div id="phyto-sb-preview-wrap">
				<p style="margin-bottom:4px;font-weight:600;font-size:12px;color:#666;"><?php esc_html_e( 'Preview', 'phyto-source-badge' ); ?></p>
				<span
					id="phyto-sb-preview"
					class="phyto-sb-preview"
				>
					<span id="phyto-sb-preview-icon"><?php echo esc_html( $icon ); ?></span>
					<span id="phyto-sb-preview-title"><?php echo esc_html( $post->post_title ); ?></span>
				</span>
			</div>
		</div>

		<script>
		( function () {
			var colorPicker = document.getElementById( 'phyto_sb_color_picker' );
			var colorHex    = document.getElementById( 'phyto_sb_color' );
			var iconInput   = document.getElementById( 'phyto_sb_icon' );
			var titleEl     = document.getElementById( 'phyto-sb-preview-title' );
			var iconEl      = document.getElementById( 'phyto-sb-preview-icon' );
			var preview     = document.getElementById( 'phyto-sb-preview' );
			var postTitle   = document.getElementById( 'title' ); // WP post title field

			function hexToRgba( hex, alpha ) {
				var r = parseInt( hex.slice( 1, 3 ), 16 );
				var g = parseInt( hex.slice( 3, 5 ), 16 );
				var b = parseInt( hex.slice( 5, 7 ), 16 );
				return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
			}

			function updatePreview() {
				var color = colorHex.value || '#3a9a6a';
				var rgba  = hexToRgba( color, 0.12 );
				preview.style.backgroundColor = rgba;
				preview.style.borderColor      = color;
				preview.style.color            = color;
				iconEl.textContent  = iconInput.value || '';
				if ( postTitle ) {
					titleEl.textContent = postTitle.value || '<?php echo esc_js( $post->post_title ); ?>';
				}
			}

			// Keep hex text input and color picker in sync.
			colorPicker.addEventListener( 'input', function () {
				colorHex.value = colorPicker.value;
				updatePreview();
			} );
			colorHex.addEventListener( 'input', function () {
				if ( /^#[0-9a-fA-F]{6}$/.test( colorHex.value ) ) {
					colorPicker.value = colorHex.value;
				}
				updatePreview();
			} );

			iconInput.addEventListener( 'input', updatePreview );
			if ( postTitle ) {
				postTitle.addEventListener( 'input', updatePreview );
			}

			updatePreview();
		} )();
		</script>
		<?php
	}

	/**
	 * Save badge meta box data when the phyto_badge post is saved.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function save_meta_box( $post_id, $post ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		// Verify nonce.
		if (
			! isset( $_POST['phyto_sb_badge_nonce'] ) ||
			! wp_verify_nonce( sanitize_key( $_POST['phyto_sb_badge_nonce'] ), 'phyto_sb_save_badge_details' )
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

		// Save badge color (must be a valid 6-digit hex code).
		if ( isset( $_POST['phyto_sb_color'] ) ) {
			$raw_color = sanitize_text_field( wp_unslash( $_POST['phyto_sb_color'] ) );
			if ( preg_match( '/^#[0-9a-fA-F]{6}$/', $raw_color ) ) {
				update_post_meta( $post_id, '_phyto_badge_color', $raw_color );
			}
		}

		// Save icon emoji (max 2 characters).
		if ( isset( $_POST['phyto_sb_icon'] ) ) {
			$raw_icon = sanitize_text_field( wp_unslash( $_POST['phyto_sb_icon'] ) );
			// Limit to 2 characters.
			$icon = mb_substr( $raw_icon, 0, 2 );
			update_post_meta( $post_id, '_phyto_badge_icon', $icon );
		}

		// Save tooltip text.
		if ( isset( $_POST['phyto_sb_tooltip'] ) ) {
			update_post_meta(
				$post_id,
				'_phyto_badge_tooltip',
				sanitize_text_field( wp_unslash( $_POST['phyto_sb_tooltip'] ) )
			);
		}
	}

	/**
	 * Return all published badge definitions.
	 *
	 * Used by the admin product meta box and frontend renderer.
	 *
	 * @return array Array of badge data arrays, each containing:
	 *               - int    id
	 *               - string title
	 *               - string color
	 *               - string icon
	 *               - string tooltip
	 */
	public static function get_all_badges() {
		$posts = get_posts(
			array(
				'post_type'      => 'phyto_badge',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$badges = array();

		foreach ( $posts as $post ) {
			$badges[] = array(
				'id'      => (int) $post->ID,
				'title'   => $post->post_title,
				'color'   => (string) get_post_meta( $post->ID, '_phyto_badge_color', true ),
				'icon'    => (string) get_post_meta( $post->ID, '_phyto_badge_icon', true ),
				'tooltip' => (string) get_post_meta( $post->ID, '_phyto_badge_tooltip', true ),
			);
		}

		return $badges;
	}
}
