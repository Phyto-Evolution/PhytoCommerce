<?php
/**
 * Customer-facing bundle builder page.
 *
 * Shortcode: [phyto_bundle id="1"]
 *
 * @package PhytoBundleBuilder
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_BB_Frontend {

	public function register_hooks() {
		add_shortcode( 'phyto_bundle', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_phyto_bb_get_products',        array( $this, 'ajax_get_products' ) );
		add_action( 'wp_ajax_nopriv_phyto_bb_get_products', array( $this, 'ajax_get_products' ) );
		add_action( 'wp_ajax_phyto_bb_add_bundle',          array( $this, 'ajax_add_bundle' ) );
		add_action( 'wp_ajax_nopriv_phyto_bb_add_bundle',   array( $this, 'ajax_add_bundle' ) );
	}

	public function enqueue() {
		wp_enqueue_style(  'phyto-bb-frontend', PHYTO_BB_URL . 'assets/css/frontend.css', array(), PHYTO_BB_VERSION );
		wp_enqueue_script( 'phyto-bb-frontend', PHYTO_BB_URL . 'assets/js/frontend.js', array( 'jquery' ), PHYTO_BB_VERSION, true );
		wp_localize_script( 'phyto-bb-frontend', 'phytoBB', array(
			'nonce'   => wp_create_nonce( 'phyto_bb_frontend' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		) );
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'phyto_bundle' );
		$id   = absint( $atts['id'] );
		if ( ! $id ) { return '<p>' . esc_html__( 'No bundle template specified.', 'phyto-bundle-builder' ) . '</p>'; }

		$template = Phyto_BB_DB::get_template( $id );
		if ( ! $template || $template->status !== 'active' ) {
			return '<p>' . esc_html__( 'Bundle not available.', 'phyto-bundle-builder' ) . '</p>';
		}

		$slots = Phyto_BB_DB::get_slots( $id );

		ob_start();
		?>
		<div class="phyto-bb-builder" data-template="<?php echo esc_attr( $id ); ?>" data-discount="<?php echo esc_attr( $template->discount_pct ); ?>">
			<h3 class="phyto-bb-title"><?php echo esc_html( $template->name ); ?></h3>
			<?php if ( $template->description ) : ?>
			<p class="phyto-bb-desc"><?php echo esc_html( $template->description ); ?></p>
			<?php endif; ?>
			<?php if ( $template->discount_pct > 0 ) : ?>
			<p class="phyto-bb-discount-note">
				<?php printf( esc_html__( 'Complete all %d slots and save %d%%!', 'phyto-bundle-builder' ), count( $slots ), $template->discount_pct ); ?>
			</p>
			<?php endif; ?>

			<div class="phyto-bb-slots">
			<?php foreach ( $slots as $slot ) :
				$prod_ids  = (array) json_decode( $slot->product_ids,  true );
				$cat_ids   = (array) json_decode( $slot->category_ids, true );
			?>
			<div class="phyto-bb-slot" data-slot="<?php echo esc_attr( $slot->slot_index ); ?>"
				data-products="<?php echo esc_attr( wp_json_encode( $prod_ids ) ); ?>"
				data-categories="<?php echo esc_attr( wp_json_encode( $cat_ids ) ); ?>">
				<h4 class="phyto-bb-slot-label"><?php echo esc_html( $slot->slot_label ); ?></h4>
				<div class="phyto-bb-slot-search">
					<input type="text" class="phyto-bb-search-input" placeholder="<?php esc_attr_e( 'Search products…', 'phyto-bundle-builder' ); ?>" />
					<div class="phyto-bb-results"></div>
				</div>
				<div class="phyto-bb-slot-selected phyto-bb-empty">
					<?php esc_html_e( 'No product selected', 'phyto-bundle-builder' ); ?>
				</div>
			</div>
			<?php endforeach; ?>
			</div>

			<div class="phyto-bb-summary">
				<div class="phyto-bb-total">
					<?php esc_html_e( 'Bundle Total:', 'phyto-bundle-builder' ); ?>
					<span class="phyto-bb-total-price">—</span>
				</div>
				<button type="button" class="phyto-bb-add-to-cart button button-primary" disabled>
					<?php esc_html_e( 'Add Bundle to Cart', 'phyto-bundle-builder' ); ?>
				</button>
				<span class="phyto-bb-cart-status"></span>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function ajax_get_products() {
		check_ajax_referer( 'phyto_bb_frontend', 'nonce' );

		$search      = sanitize_text_field( wp_unslash( $_POST['search'] ?? '' ) );
		$product_ids = array_filter( array_map( 'absint', (array) json_decode( wp_unslash( $_POST['product_ids'] ?? '[]' ), true ) ) );
		$cat_ids     = array_filter( array_map( 'absint', (array) json_decode( wp_unslash( $_POST['category_ids'] ?? '[]' ), true ) ) );

		$args = array(
			'status'  => 'publish',
			'limit'   => 12,
			's'       => $search,
			'return'  => 'objects',
		);

		if ( $product_ids ) {
			$args['include'] = $product_ids;
		} elseif ( $cat_ids ) {
			$args['category'] = array_map( function( $id ) {
				$term = get_term( $id, 'product_cat' );
				return $term ? $term->slug : null;
			}, $cat_ids );
			$args['category'] = array_filter( $args['category'] );
		}

		$products = wc_get_products( $args );
		$results  = array();
		foreach ( $products as $p ) {
			$img_id = $p->get_image_id();
			$results[] = array(
				'id'    => $p->get_id(),
				'name'  => $p->get_name(),
				'price' => (float) $p->get_price(),
				'price_html' => $p->get_price_html(),
				'img'   => $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : wc_placeholder_img_src( 'thumbnail' ),
			);
		}
		wp_send_json_success( $results );
	}

	public function ajax_add_bundle() {
		check_ajax_referer( 'phyto_bb_frontend', 'nonce' );

		$template_id = absint( $_POST['template_id'] ?? 0 );
		$selections  = (array) json_decode( wp_unslash( $_POST['selections'] ?? '[]' ), true );

		if ( ! $template_id || empty( $selections ) ) {
			wp_send_json_error( __( 'Invalid bundle data.', 'phyto-bundle-builder' ) );
		}

		$template = Phyto_BB_DB::get_template( $template_id );
		if ( ! $template || $template->status !== 'active' ) {
			wp_send_json_error( __( 'Bundle not available.', 'phyto-bundle-builder' ) );
		}

		$added  = 0;
		$failed = array();

		foreach ( $selections as $sel ) {
			$pid = absint( $sel['product_id'] ?? 0 );
			if ( ! $pid ) { continue; }
			$result = WC()->cart->add_to_cart( $pid, 1, 0, array(), array(
				'phyto_bundle_id'    => $template_id,
				'phyto_bundle_disc'  => (int) $template->discount_pct,
			) );
			if ( $result ) { $added++; } else { $failed[] = $pid; }
		}

		if ( $added === 0 ) {
			wp_send_json_error( __( 'Could not add products to cart.', 'phyto-bundle-builder' ) );
		}

		wp_send_json_success( array(
			'added'    => $added,
			'cart_url' => wc_get_cart_url(),
		) );
	}
}
