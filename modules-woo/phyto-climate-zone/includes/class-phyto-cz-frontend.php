<?php
/**
 * Frontend display for Phyto Climate Zone.
 *
 * Renders climate-suitability pill badges on WooCommerce shop/archive listings
 * and adds a "Climate Suitability" tab on the single product page.
 * The tab is hidden when no zones are selected for the product.
 *
 * @package PhytoClimateZone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_CZ_Frontend
 */
class Phyto_CZ_Frontend {

	/**
	 * Track whether CSS has been enqueued this request.
	 *
	 * @var bool
	 */
	private $css_enqueued = false;

	/**
	 * Cached zone definitions (populated on first access).
	 *
	 * @var array|null
	 */
	private $zone_defs = null;

	/**
	 * Return zone definitions, using the admin class when available and
	 * falling back to a lightweight inline copy for front-end-only contexts.
	 *
	 * @return array
	 */
	private function get_zone_definitions() {
		if ( null !== $this->zone_defs ) {
			return $this->zone_defs;
		}

		$zones = array(
			'coastal'           => array(
				'label'   => __( 'Coastal & Humid', 'phyto-climate-zone' ),
				'emoji'   => '🌊',
				'regions' => __( 'Kerala, coastal Karnataka, Goa, coastal TN/AP', 'phyto-climate-zone' ),
			),
			'tropical_highland' => array(
				'label'   => __( 'Tropical Highland', 'phyto-climate-zone' ),
				'emoji'   => '⛰️',
				'regions' => __( 'Nilgiris, Coorg, Munnar, NE hills', 'phyto-climate-zone' ),
			),
			'tropical_plains'   => array(
				'label'   => __( 'Tropical Plains', 'phyto-climate-zone' ),
				'emoji'   => '☀️',
				'regions' => __( 'Most of peninsular India — Chennai, Bengaluru plains, Hyderabad', 'phyto-climate-zone' ),
			),
			'arid'              => array(
				'label'   => __( 'Arid & Semi-Arid', 'phyto-climate-zone' ),
				'emoji'   => '🏜️',
				'regions' => __( 'Rajasthan, parts of Maharashtra/Karnataka interior', 'phyto-climate-zone' ),
			),
			'temperate'         => array(
				'label'   => __( 'Temperate North', 'phyto-climate-zone' ),
				'emoji'   => '🌿',
				'regions' => __( 'Himachal, Uttarakhand foothills, J&K valleys', 'phyto-climate-zone' ),
			),
			'subtropical'       => array(
				'label'   => __( 'Sub-tropical', 'phyto-climate-zone' ),
				'emoji'   => '🌾',
				'regions' => __( 'Punjab, Haryana, UP, Delhi belt', 'phyto-climate-zone' ),
			),
			'northeast'         => array(
				'label'   => __( 'North-East Humid', 'phyto-climate-zone' ),
				'emoji'   => '🌧️',
				'regions' => __( 'Assam, Meghalaya, Manipur, Sikkim', 'phyto-climate-zone' ),
			),
		);

		/**
		 * Filter the complete list of India climate zone definitions.
		 *
		 * @since 1.0.0
		 * @param array $zones Keyed array: zone_key => [ label, emoji, regions ].
		 */
		$this->zone_defs = apply_filters( 'phyto_cz_zone_definitions', $zones );
		return $this->zone_defs;
	}

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		// Archive / shop loop badge — before product title, priority 8 as specified.
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'display_badges_loop' ), 8 );

		// Single product "Climate Suitability" tab.
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_tab' ), 25 );
	}

	/**
	 * Output climate-zone pill badges on shop/archive loop cards.
	 *
	 * Hooked to `woocommerce_before_shop_loop_item_title` at priority 8.
	 */
	public function display_badges_loop() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$product_id = $product->get_id();
		$zones      = (array) get_post_meta( $product_id, '_phyto_cz_zones', true );
		$zones      = array_filter( $zones );

		if ( empty( $zones ) ) {
			return;
		}

		$this->enqueue_css();

		$zone_defs = $this->get_zone_definitions();
		$pills_html = '';

		foreach ( $zones as $key ) {
			if ( ! isset( $zone_defs[ $key ] ) ) {
				continue;
			}
			$def         = $zone_defs[ $key ];
			$pills_html .= sprintf(
				'<span class="phyto-cz-pill phyto-cz-pill--%1$s" title="%2$s">%3$s %4$s</span>',
				esc_attr( $key ),
				esc_attr( $def['regions'] ),
				esc_html( $def['emoji'] ),
				esc_html( $def['label'] )
			);
		}

		if ( $pills_html ) {
			echo '<div class="phyto-cz-badge-strip phyto-cz-badge-strip--archive">' . $pills_html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — already escaped above
		}
	}

	/**
	 * Add the "Climate Suitability" tab to WooCommerce product tabs.
	 * Tab is omitted entirely when no zones are assigned.
	 *
	 * @param array $tabs Existing tabs array.
	 * @return array Modified tabs array.
	 */
	public function add_product_tab( $tabs ) {
		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$zones = (array) get_post_meta( $product->get_id(), '_phyto_cz_zones', true );
		$zones = array_filter( $zones );

		if ( empty( $zones ) ) {
			return $tabs;
		}

		/**
		 * Filter the "Climate Suitability" product tab title.
		 *
		 * @since 1.0.0
		 * @param string $title Default tab label.
		 */
		$title = apply_filters( 'phyto_cz_tab_title', __( 'Climate Suitability', 'phyto-climate-zone' ) );

		$tabs['phyto_cz_suitability'] = array(
			'title'    => $title,
			'priority' => 50,
			'callback' => array( $this, 'render_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the "Climate Suitability" tab content on the single product page.
	 * Enqueues the stylesheet and outputs the zone compatibility card.
	 */
	public function render_tab() {
		global $product;

		if ( ! $product ) {
			return;
		}

		$this->enqueue_css();

		$product_id = $product->get_id();
		$zones      = (array) get_post_meta( $product_id, '_phyto_cz_zones', true );
		$zones      = array_filter( $zones );
		$temp_min   = get_post_meta( $product_id, '_phyto_cz_temp_min', true );
		$temp_max   = get_post_meta( $product_id, '_phyto_cz_temp_max', true );
		$placement  = (string) get_post_meta( $product_id, '_phyto_cz_placement', true );
		$notes      = (string) get_post_meta( $product_id, '_phyto_cz_notes', true );
		$zone_defs  = $this->get_zone_definitions();

		// Placement label.
		$placement_labels = array(
			'indoor'  => __( 'Indoor', 'phyto-climate-zone' ),
			'outdoor' => __( 'Outdoor', 'phyto-climate-zone' ),
			'both'    => __( 'Indoor & Outdoor', 'phyto-climate-zone' ),
		);
		$placement_label = isset( $placement_labels[ $placement ] )
			? $placement_labels[ $placement ]
			: $placement_labels['both'];

		?>
		<div class="phyto-cz-tab">
			<h2 class="phyto-cz-tab__heading">
				<?php esc_html_e( 'Climate Suitability', 'phyto-climate-zone' ); ?>
			</h2>

			<?php if ( ! empty( $zones ) ) : ?>
				<div class="phyto-cz-tab__zones">
					<p class="phyto-cz-tab__label"><?php esc_html_e( 'Suitable for these Indian climate zones:', 'phyto-climate-zone' ); ?></p>
					<ul class="phyto-cz-tab__zone-list">
						<?php foreach ( $zones as $key ) : ?>
							<?php if ( ! isset( $zone_defs[ $key ] ) ) : ?>
								<?php continue; ?>
							<?php endif; ?>
							<?php $def = $zone_defs[ $key ]; ?>
							<li class="phyto-cz-tab__zone-item">
								<span class="phyto-cz-tab__zone-pill phyto-cz-pill--<?php echo esc_attr( $key ); ?>">
									<?php echo esc_html( $def['emoji'] . ' ' . $def['label'] ); ?>
								</span>
								<span class="phyto-cz-tab__zone-regions">
									<?php echo esc_html( $def['regions'] ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<dl class="phyto-cz-tab__meta">

				<?php if ( '' !== $temp_min || '' !== $temp_max ) : ?>
					<div class="phyto-cz-tab__meta-row">
						<dt><?php esc_html_e( 'Temperature Range', 'phyto-climate-zone' ); ?></dt>
						<dd>
							<?php
							if ( '' !== $temp_min && '' !== $temp_max ) {
								printf(
									/* translators: 1: minimum temperature, 2: maximum temperature */
									esc_html__( '%1$s°C — %2$s°C', 'phyto-climate-zone' ),
									esc_html( $temp_min ),
									esc_html( $temp_max )
								);
							} elseif ( '' !== $temp_min ) {
								printf(
									/* translators: %s: minimum temperature */
									esc_html__( 'Min %s°C', 'phyto-climate-zone' ),
									esc_html( $temp_min )
								);
							} else {
								printf(
									/* translators: %s: maximum temperature */
									esc_html__( 'Max %s°C', 'phyto-climate-zone' ),
									esc_html( $temp_max )
								);
							}
							?>
						</dd>
					</div>
				<?php endif; ?>

				<div class="phyto-cz-tab__meta-row">
					<dt><?php esc_html_e( 'Placement', 'phyto-climate-zone' ); ?></dt>
					<dd><?php echo esc_html( $placement_label ); ?></dd>
				</div>

				<?php if ( ! empty( $notes ) ) : ?>
					<div class="phyto-cz-tab__meta-row phyto-cz-tab__meta-row--notes">
						<dt><?php esc_html_e( 'Notes', 'phyto-climate-zone' ); ?></dt>
						<dd><?php echo nl2br( esc_html( $notes ) ); ?></dd>
					</div>
				<?php endif; ?>

			</dl>
		</div>
		<?php
	}

	/**
	 * Enqueue the frontend CSS file (once per request).
	 */
	private function enqueue_css() {
		if ( $this->css_enqueued ) {
			return;
		}
		wp_enqueue_style(
			'phyto-cz-frontend',
			PHYTO_CZ_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_CZ_VERSION
		);
		$this->css_enqueued = true;
	}
}
