<?php
/**
 * Frontend display for Phyto Source Badge.
 *
 * Renders sourcing-origin badge strips on WooCommerce shop/archive listings
 * and on the single product summary. Enqueues the stylesheet on first render.
 *
 * @package PhytoSourceBadge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Source_Badge_Frontend
 */
class Phyto_Source_Badge_Frontend {

	/**
	 * Track whether CSS has been enqueued this request.
	 *
	 * @var bool
	 */
	private $css_enqueued = false;

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		// Shop / archive loop badge strip — before product title.
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_badges_loop' ), 10 );

		// Single product badge strip — in the summary block (priority 7, just above the price at 10).
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_badges_single' ), 7 );
	}

	/**
	 * Output the source badge strip on shop/archive loop cards.
	 *
	 * Hooked to `woocommerce_before_shop_loop_item_title`.
	 */
	public function display_badges_loop() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$this->render_badges( $product->get_id(), true );
	}

	/**
	 * Output the source badge strip on the single product page.
	 *
	 * Hooked to `woocommerce_single_product_summary` at priority 7.
	 */
	public function display_badges_single() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$this->render_badges( $product->get_id(), false );
	}

	/**
	 * Shared badge strip renderer.
	 *
	 * Reads `_phyto_source_badges` from the product, fetches badge meta for
	 * each ID, and outputs the HTML strip. Enqueues the CSS on first call.
	 * Silently returns when no badges are assigned.
	 *
	 * The output HTML can be suppressed entirely via the
	 * `phyto_source_badge_output` filter, and extra CSS classes can be added
	 * to the wrapper via the `phyto_source_badge_classes` filter.
	 *
	 * @param int  $post_id    WooCommerce product ID.
	 * @param bool $is_archive True when rendering inside the shop loop.
	 */
	public function render_badges( $post_id, $is_archive = false ) {
		$badge_ids = (array) get_post_meta( $post_id, '_phyto_source_badges', true );
		$badge_ids = array_filter( array_map( 'intval', $badge_ids ) );

		if ( empty( $badge_ids ) ) {
			return;
		}

		// Collect badge data for each assigned ID.
		$badges_data = array();
		foreach ( $badge_ids as $badge_id ) {
			$badge_post = get_post( $badge_id );

			if ( ! $badge_post || 'publish' !== $badge_post->post_status ) {
				continue;
			}

			$badges_data[] = array(
				'id'      => $badge_id,
				'title'   => $badge_post->post_title,
				'color'   => (string) get_post_meta( $badge_id, '_phyto_badge_color', true ),
				'icon'    => (string) get_post_meta( $badge_id, '_phyto_badge_icon', true ),
				'tooltip' => (string) get_post_meta( $badge_id, '_phyto_badge_tooltip', true ),
			);
		}

		if ( empty( $badges_data ) ) {
			return;
		}

		$this->enqueue_css();

		// Build wrapper classes, filterable by third parties.
		$wrap_classes = array( 'phyto-sb-wrap' );
		if ( $is_archive ) {
			$wrap_classes[] = 'phyto-sb-wrap--archive';
		} else {
			$wrap_classes[] = 'phyto-sb-wrap--single';
		}

		/**
		 * Filter the CSS classes applied to the badge strip wrapper element.
		 *
		 * @since 1.0.0
		 * @param array $wrap_classes Array of CSS class strings.
		 * @param int   $post_id      WooCommerce product ID.
		 * @param bool  $is_archive   True when rendering in the shop loop.
		 */
		$wrap_classes = (array) apply_filters( 'phyto_source_badge_classes', $wrap_classes, $post_id, $is_archive );
		$wrap_class   = implode( ' ', array_map( 'sanitize_html_class', $wrap_classes ) );

		// Build individual badge spans.
		$badges_html = '';
		foreach ( $badges_data as $badge ) {
			$color   = ! empty( $badge['color'] ) ? $badge['color'] : '#3a9a6a';
			$icon    = ! empty( $badge['icon'] ) ? $badge['icon'] . ' ' : '';
			$tooltip = ! empty( $badge['tooltip'] ) ? $badge['tooltip'] : $badge['title'];

			$badges_html .= sprintf(
				'<span class="phyto-sb-badge" style="--sb-color:%1$s" title="%2$s">%3$s%4$s</span>',
				esc_attr( $color ),
				esc_attr( $tooltip ),
				esc_html( $icon ),
				esc_html( $badge['title'] )
			);
		}

		$output = sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $wrap_class ),
			$badges_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above
		);

		/**
		 * Filter the complete badge strip HTML before it is echoed.
		 *
		 * Return an empty string to suppress output entirely.
		 *
		 * @since 1.0.0
		 * @param string $output      Complete badge strip markup.
		 * @param int    $post_id     WooCommerce product ID.
		 * @param bool   $is_archive  True when rendering in the shop loop.
		 * @param array  $badges_data Array of badge data arrays rendered in this strip.
		 */
		$output = apply_filters( 'phyto_source_badge_output', $output, $post_id, $is_archive, $badges_data );

		if ( ! empty( $output ) ) {
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — filtered output, documented above
		}
	}

	/**
	 * Enqueue the frontend CSS file (once per request).
	 */
	private function enqueue_css() {
		if ( $this->css_enqueued ) {
			return;
		}
		wp_enqueue_style(
			'phyto-sb-frontend',
			PHYTO_SB_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_SB_VERSION
		);
		$this->css_enqueued = true;
	}
}
