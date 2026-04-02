<?php
/**
 * Frontend widget for Phyto Acclimation Bundler.
 *
 * @package PhytoAcclimationBundler
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_AB_Frontend {

	public function register_hooks() {
		add_action( 'woocommerce_after_cart_table',         array( $this, 'maybe_render_widget' ) );
		add_action( 'woocommerce_before_checkout_form',     array( $this, 'maybe_render_widget' ) );
		add_action( 'woocommerce_cart_calculate_fees',      array( $this, 'apply_bundle_discount' ) );
		add_action( 'wp_enqueue_scripts',                   array( $this, 'enqueue' ) );
	}

	private function get_kit_ids() {
		$raw = get_option( 'phyto_ab_kit_ids', '' );
		return array_filter( array_map( 'absint', explode( ',', $raw ) ) );
	}

	private function get_trigger_tags() {
		$raw = get_option( 'phyto_ab_tags', 'tc-plant,deflasked,tissue-culture' );
		return array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	}

	private function get_trigger_stages() {
		$raw = get_option( 'phyto_ab_stage_ids', '' );
		return array_filter( array_map( 'trim', explode( ',', $raw ) ) );
	}

	private function cart_has_trigger() {
		if ( ! WC()->cart ) { return false; }
		$tags   = $this->get_trigger_tags();
		$stages = $this->get_trigger_stages();

		foreach ( WC()->cart->get_cart() as $item ) {
			$pid = $item['product_id'];

			// Stage-based trigger.
			if ( $stages ) {
				$stage = get_post_meta( $pid, '_phyto_growth_stage', true );
				if ( $stage && in_array( $stage, $stages, true ) ) {
					return true;
				}
			}

			// Tag-based trigger.
			foreach ( $tags as $tag ) {
				if ( has_term( $tag, 'product_tag', $pid ) ) {
					return true;
				}
			}
		}
		return false;
	}

	private function get_cart_product_ids() {
		if ( ! WC()->cart ) { return array(); }
		return array_column( WC()->cart->get_cart(), 'product_id' );
	}

	public function maybe_render_widget() {
		if ( ! $this->cart_has_trigger() ) { return; }

		$kit_ids  = $this->get_kit_ids();
		$in_cart  = $this->get_cart_product_ids();
		$show_ids = array_slice( array_diff( $kit_ids, $in_cart ), 0, (int) get_option( 'phyto_ab_max_show', 3 ) );

		if ( empty( $show_ids ) ) { return; }

		$headline = get_option( 'phyto_ab_headline', __( 'Complete your acclimation setup', 'phyto-acclimation-bundler' ) );
		$discount = (int) get_option( 'phyto_ab_discount', 0 );
		?>
		<div class="phyto-ab-widget" id="phyto-ab-widget">
			<button class="phyto-ab-dismiss" id="phyto-ab-dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'phyto-acclimation-bundler' ); ?>">✕</button>
			<h3 class="phyto-ab-headline">🌱 <?php echo esc_html( $headline ); ?></h3>
			<?php if ( $discount > 0 ) : ?>
			<p class="phyto-ab-discount-note">
				<?php printf( esc_html__( 'Add all kit items and save %d%%!', 'phyto-acclimation-bundler' ), $discount ); ?>
			</p>
			<?php endif; ?>
			<div class="phyto-ab-products">
				<?php foreach ( $show_ids as $pid ) :
					$product = wc_get_product( $pid );
					if ( ! $product ) continue;
				?>
				<div class="phyto-ab-product-card">
					<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></a>
					<div class="phyto-ab-product-info">
						<strong><?php echo esc_html( $product->get_name() ); ?></strong>
						<span class="phyto-ab-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
					</div>
					<button class="phyto-ab-add-btn button" data-product-id="<?php echo esc_attr( $pid ); ?>">
						<?php esc_html_e( 'Add', 'phyto-acclimation-bundler' ); ?>
					</button>
				</div>
				<?php endforeach; ?>
			</div>
			<?php if ( $discount > 0 ) : ?>
			<button class="phyto-ab-add-all button button-primary" data-ids="<?php echo esc_attr( implode( ',', $show_ids ) ); ?>" data-discount="<?php echo esc_attr( $discount ); ?>">
				<?php printf( esc_html__( 'Add All (%d%% off)', 'phyto-acclimation-bundler' ), $discount ); ?>
			</button>
			<?php endif; ?>
		</div>
		<?php
	}

	public function apply_bundle_discount( $cart ) {
		$discount = (int) get_option( 'phyto_ab_discount', 0 );
		if ( $discount <= 0 || ! $this->cart_has_trigger() ) { return; }

		$kit_ids  = $this->get_kit_ids();
		$in_cart  = $this->get_cart_product_ids();
		$kit_in_cart = array_intersect( $kit_ids, $in_cart );

		if ( count( $kit_in_cart ) < count( $kit_ids ) ) { return; }

		$kit_total = 0;
		foreach ( $cart->get_cart() as $item ) {
			if ( in_array( $item['product_id'], $kit_ids, true ) ) {
				$kit_total += $item['line_total'];
			}
		}

		$cart->add_fee( __( 'Acclimation Kit Discount', 'phyto-acclimation-bundler' ), -( $kit_total * $discount / 100 ) );
	}

	public function enqueue() {
		if ( ! is_cart() && ! is_checkout() ) { return; }
		wp_enqueue_style( 'phyto-ab', PHYTO_AB_URL . 'assets/css/frontend.css', array(), PHYTO_AB_VERSION );
		wp_enqueue_script( 'phyto-ab', PHYTO_AB_URL . 'assets/js/frontend.js', array( 'jquery' ), PHYTO_AB_VERSION, true );
	}
}
