<?php
/**
 * Frontend display for Phyto Grex Registry.
 *
 * Injects a "Scientific Profile" tab on the WooCommerce single product page
 * and renders the taxonomy card inside it when at least Genus or Species is set.
 *
 * @package PhytoGrexRegistry
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Grex_Frontend
 */
class Phyto_Grex_Frontend {

	/**
	 * Human-readable labels for every field.
	 *
	 * @var array
	 */
	private $field_labels = array(
		'genus'               => 'Genus',
		'species'             => 'Species',
		'grex_name'           => 'Hybrid / Grex Name',
		'authority'           => 'Registration Authority',
		'conservation_status' => 'Conservation Status',
		'common_name'         => 'Common Name',
		'notes'               => 'Notes',
	);

	/**
	 * Conservation status badge colours.
	 * Maps meta value => CSS class suffix used in frontend.css.
	 *
	 * @var array
	 */
	private $status_classes = array(
		'not_evaluated'         => 'ne',
		'least_concern'         => 'lc',
		'vulnerable'            => 'vu',
		'endangered'            => 'en',
		'critically_endangered' => 'cr',
		'cites_appendix_i'      => 'cites-i',
		'cites_appendix_ii'     => 'cites-ii',
	);

	/**
	 * Human-readable status labels.
	 *
	 * @var array
	 */
	private $status_labels = array(
		'not_evaluated'         => 'Not Evaluated',
		'least_concern'         => 'Least Concern',
		'vulnerable'            => 'Vulnerable',
		'endangered'            => 'Endangered',
		'critically_endangered' => 'Critically Endangered',
		'cites_appendix_i'      => 'CITES Appendix I',
		'cites_appendix_ii'     => 'CITES Appendix II',
	);

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_product_tabs', array( $this, 'add_product_tab' ), 25 );
	}

	/**
	 * Add the "Scientific Profile" tab to WooCommerce product tabs.
	 * Only added when at least Genus or Species is filled in.
	 *
	 * @param array $tabs Existing tabs array.
	 * @return array Modified tabs array.
	 */
	public function add_product_tab( $tabs ) {
		global $product;

		if ( ! $product ) {
			return $tabs;
		}

		$product_id = $product->get_id();
		$genus      = get_post_meta( $product_id, '_phyto_grex_genus', true );
		$species    = get_post_meta( $product_id, '_phyto_grex_species', true );

		if ( empty( $genus ) && empty( $species ) ) {
			return $tabs;
		}

		/**
		 * Filter the tab title.
		 *
		 * @since 1.0.0
		 * @param string $title Default tab title.
		 */
		$title = apply_filters( 'phyto_grex_tab_title', __( 'Scientific Profile', 'phyto-grex-registry' ) );

		$tabs['phyto_grex_profile'] = array(
			'title'    => $title,
			'priority' => 25,
			'callback' => array( $this, 'render_tab' ),
		);

		return $tabs;
	}

	/**
	 * Render the Scientific Profile tab content.
	 * Enqueues the stylesheet and outputs the taxonomy card.
	 */
	public function render_tab() {
		global $product;

		if ( ! $product ) {
			return;
		}

		wp_enqueue_style(
			'phyto-grex-frontend',
			PHYTO_GREX_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_GREX_VERSION
		);

		$product_id = $product->get_id();

		// Build field data array.
		$raw_fields = array(
			'genus'               => (string) get_post_meta( $product_id, '_phyto_grex_genus', true ),
			'species'             => (string) get_post_meta( $product_id, '_phyto_grex_species', true ),
			'grex_name'           => (string) get_post_meta( $product_id, '_phyto_grex_grex_name', true ),
			'authority'           => (string) get_post_meta( $product_id, '_phyto_grex_authority', true ),
			'conservation_status' => (string) get_post_meta( $product_id, '_phyto_grex_conservation_status', true ),
			'common_name'         => (string) get_post_meta( $product_id, '_phyto_grex_common_name', true ),
			'notes'               => (string) get_post_meta( $product_id, '_phyto_grex_notes', true ),
		);

		/**
		 * Filter the fields array before output.
		 * Allows third-party plugins to add, remove, or reorder fields.
		 *
		 * @since 1.0.0
		 * @param array $raw_fields  Associative array of field_slug => value.
		 * @param int   $product_id  WooCommerce product ID.
		 */
		$raw_fields = apply_filters( 'phyto_grex_fields', $raw_fields, $product_id );

		// Remove empty fields before display.
		$fields = array_filter( $raw_fields, function( $v ) {
			return '' !== $v;
		} );

		if ( empty( $fields ) ) {
			return;
		}

		?>
		<div class="phyto-grex-profile">
			<h2 class="phyto-grex-profile__heading">
				<?php esc_html_e( 'Scientific Profile', 'phyto-grex-registry' ); ?>
			</h2>
			<dl class="phyto-grex-profile__dl">
				<?php foreach ( $fields as $slug => $value ) : ?>
					<?php
					$label = isset( $this->field_labels[ $slug ] )
						? $this->field_labels[ $slug ]
						: ucwords( str_replace( '_', ' ', $slug ) );
					?>
					<div class="phyto-grex-profile__row">
						<dt class="phyto-grex-profile__term">
							<?php echo esc_html( __( $label, 'phyto-grex-registry' ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?>
						</dt>
						<dd class="phyto-grex-profile__desc">
							<?php if ( 'conservation_status' === $slug ) : ?>
								<?php $this->render_conservation_badge( $value ); ?>
							<?php elseif ( 'notes' === $slug ) : ?>
								<span class="phyto-grex-profile__notes"><?php echo nl2br( esc_html( $value ) ); ?></span>
							<?php else : ?>
								<?php echo esc_html( $value ); ?>
							<?php endif; ?>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
		<?php
	}

	/**
	 * Output a colour-coded conservation status badge.
	 *
	 * @param string $status_value Raw meta value for conservation status.
	 */
	private function render_conservation_badge( $status_value ) {
		$css_class = isset( $this->status_classes[ $status_value ] )
			? 'phyto-grex-badge phyto-grex-badge--' . $this->status_classes[ $status_value ]
			: 'phyto-grex-badge phyto-grex-badge--ne';

		$label = isset( $this->status_labels[ $status_value ] )
			? $this->status_labels[ $status_value ]
			: esc_html__( 'Not Evaluated', 'phyto-grex-registry' );

		echo '<span class="' . esc_attr( $css_class ) . '">' . esc_html( $label ) . '</span>';
	}
}
