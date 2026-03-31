<?php
/**
 * Frontend display for Phyto Growth Stage.
 *
 * Renders colour-coded growth stage badges on WooCommerce shop/archive listings
 * and on the single product page. Optionally adds a "Care & Stage Info" product
 * tab when stage notes are present.
 *
 * @package PhytoGrowthStage
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Growth_Stage_Frontend
 */
class Phyto_Growth_Stage_Frontend {

	/**
	 * Track whether CSS has been enqueued this request.
	 *
	 * @var bool
	 */
	private $css_enqueued = false;

	/**
	 * Cached copy of stage definitions to avoid repeated filter calls.
	 *
	 * @var array|null
	 */
	private $stages = null;

	/**
	 * Return stage definitions, using a local cache after first load.
	 *
	 * @return array
	 */
	private function get_stages() {
		if ( null === $this->stages ) {
			// Re-use the admin class definitions so both classes share one source of truth.
			// The filter `phyto_growth_stage_definitions` is applied inside get_stages().
			$admin        = new Phyto_Growth_Stage_Admin();
			$this->stages = $admin->get_stages();
		}
		return $this->stages;
	}

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		// Shop / archive loop badge — before product title.
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_badge_loop' ), 10 );

		// Single product badge — in the summary before the price (priority 6).
		add_action( 'woocommerce_single_product_summary', array( $this, 'display_badge_single' ), 6 );

		// Optional "Care & Stage Info" product tab.
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_tab' ), 25 );
	}

	/**
	 * Output the growth stage badge on shop/archive loop cards.
	 *
	 * Hooked to `woocommerce_before_shop_loop_item_title`.
	 */
	public function display_badge_loop() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$this->render_badge( $product->get_id(), true );
	}

	/**
	 * Output the growth stage badge on the single product page.
	 *
	 * Hooked to `woocommerce_single_product_summary` at priority 6 so it
	 * appears above the price line.
	 */
	public function display_badge_single() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$this->render_badge( $product->get_id(), false );
	}

	/**
	 * Shared badge renderer.
	 *
	 * Looks up the stage key saved on the product, retrieves label/colour from
	 * the definitions, then outputs the badge HTML and enqueues the stylesheet.
	 *
	 * @param int  $post_id    WooCommerce product ID.
	 * @param bool $is_archive True when rendering inside the shop loop.
	 */
	public function render_badge( $post_id, $is_archive = false ) {
		$stage_key = (string) get_post_meta( $post_id, '_phyto_growth_stage', true );

		if ( empty( $stage_key ) ) {
			return;
		}

		$stages = $this->get_stages();

		if ( ! isset( $stages[ $stage_key ] ) ) {
			return;
		}

		$info = $stages[ $stage_key ];

		// Enqueue stylesheet on first render.
		$this->enqueue_css();

		$wrapper_class = $is_archive ? 'phyto-gs-badge-wrap phyto-gs-badge-wrap--archive' : 'phyto-gs-badge-wrap phyto-gs-badge-wrap--single';

		$badge_html = sprintf(
			'<div class="%1$s"><span class="phyto-gs-badge" style="--gs-color:%2$s">%3$s</span><span class="phyto-gs-meta">%4$s · %5$s</span></div>',
			esc_attr( $wrapper_class ),
			esc_attr( $info['color'] ),
			esc_html( $info['label'] ),
			esc_html( $info['difficulty'] ),
			esc_html( $info['time'] )
		);

		/**
		 * Filter the complete badge HTML before it is echoed.
		 *
		 * @since 1.0.0
		 * @param string $badge_html  Complete badge markup.
		 * @param string $stage_key   Stage slug (e.g. 'deflasked').
		 * @param array  $info        Stage definition array (label, difficulty, time, color).
		 * @param int    $post_id     WooCommerce product ID.
		 * @param bool   $is_archive  True when rendering in the shop loop.
		 */
		echo apply_filters( 'phyto_growth_stage_badge_html', $badge_html, $stage_key, $info, $post_id, $is_archive ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Enqueue the frontend CSS file (once per request).
	 */
	private function enqueue_css() {
		if ( $this->css_enqueued ) {
			return;
		}
		wp_enqueue_style(
			'phyto-gs-frontend',
			PHYTO_GS_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_GS_VERSION
		);
		$this->css_enqueued = true;
	}

	/**
	 * Add a "Care & Stage Info" product tab when stage notes are present.
	 *
	 * @param array $tabs Existing product tabs.
	 * @return array Modified product tabs.
	 */
	public function add_product_tab( $tabs ) {
		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$notes = (string) get_post_meta( $product->get_id(), '_phyto_growth_stage_notes', true );

		if ( empty( $notes ) ) {
			return $tabs;
		}

		$tabs['phyto_growth_stage_care'] = array(
			'title'    => __( 'Care & Stage Info', 'phyto-growth-stage' ),
			'priority' => 30,
			'callback' => array( $this, 'render_care_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the "Care & Stage Info" tab content.
	 */
	public function render_care_tab() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$post_id   = $product->get_id();
		$stage_key = (string) get_post_meta( $post_id, '_phyto_growth_stage', true );
		$notes     = (string) get_post_meta( $post_id, '_phyto_growth_stage_notes', true );
		$stages    = $this->get_stages();

		$this->enqueue_css();
		?>
		<div class="phyto-gs-care-tab">
			<?php if ( ! empty( $stage_key ) && isset( $stages[ $stage_key ] ) ) : ?>
				<?php $info = $stages[ $stage_key ]; ?>
				<div class="phyto-gs-care-tab__stage">
					<span class="phyto-gs-badge" style="--gs-color:<?php echo esc_attr( $info['color'] ); ?>">
						<?php echo esc_html( $info['label'] ); ?>
					</span>
					<ul class="phyto-gs-care-tab__meta">
						<li>
							<strong><?php esc_html_e( 'Care Difficulty:', 'phyto-growth-stage' ); ?></strong>
							<?php echo esc_html( $info['difficulty'] ); ?>
						</li>
						<li>
							<strong><?php esc_html_e( 'Time to Maturity:', 'phyto-growth-stage' ); ?></strong>
							<?php echo esc_html( $info['time'] ); ?>
						</li>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $notes ) ) : ?>
				<div class="phyto-gs-care-tab__notes">
					<h3><?php esc_html_e( 'Stage Notes', 'phyto-growth-stage' ); ?></h3>
					<p><?php echo nl2br( esc_html( $notes ) ); ?></p>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}
}
