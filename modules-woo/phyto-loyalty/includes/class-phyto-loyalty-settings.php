<?php
/**
 * WooCommerce Settings tab for Phyto Loyalty.
 *
 * Registers a "Phyto Loyalty" tab under WooCommerce → Settings and provides
 * helper getters for all configuration values.
 *
 * @package PhytoLoyalty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Loyalty_Settings
 */
class Phyto_Loyalty_Settings {

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_phyto_loyalty', array( $this, 'output_settings' ) );
		add_action( 'woocommerce_update_options_phyto_loyalty', array( $this, 'save_settings' ) );
	}

	/**
	 * Add our tab to the WooCommerce settings tabs array.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['phyto_loyalty'] = __( 'Phyto Loyalty', 'phyto-loyalty' );
		return $tabs;
	}

	/**
	 * Output the settings fields for our tab.
	 */
	public function output_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save the settings fields for our tab.
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Return the settings array used by WooCommerce Settings API.
	 *
	 * @return array
	 */
	public function get_settings() {
		return array(
			array(
				'title' => __( 'Phyto Loyalty Programme', 'phyto-loyalty' ),
				'type'  => 'title',
				'desc'  => __( 'Configure how customers earn and redeem points.', 'phyto-loyalty' ),
				'id'    => 'phyto_loyalty_section_start',
			),
			array(
				'title'             => __( 'Points Label', 'phyto-loyalty' ),
				'desc'              => __( 'Display name for loyalty points shown to customers.', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_points_label',
				'type'              => 'text',
				'default'           => 'Green Points',
				'desc_tip'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			),
			array(
				'title'             => __( 'Points per ₹ Spent', 'phyto-loyalty' ),
				'desc'              => __( 'How many points a customer earns per rupee spent (e.g. 0.1 = 1 pt per ₹10).', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_earn_rate',
				'type'              => 'number',
				'default'           => '0.1',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
				'desc_tip'          => true,
			),
			array(
				'title'             => __( '₹ Value per Point Redeemed', 'phyto-loyalty' ),
				'desc'              => __( 'Monetary value of one point when redeemed as a discount (e.g. 0.10 = ₹0.10 per point).', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_redeem_rate',
				'type'              => 'number',
				'default'           => '0.10',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '0.01',
				),
				'desc_tip'          => true,
			),
			array(
				'title'             => __( 'Minimum Points to Redeem', 'phyto-loyalty' ),
				'desc'              => __( 'Customer must have at least this many points before they can redeem any.', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_min_redeem',
				'type'              => 'number',
				'default'           => '100',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '1',
				),
				'desc_tip'          => true,
			),
			array(
				'title'             => __( 'Max % of Order Redeemable', 'phyto-loyalty' ),
				'desc'              => __( 'Maximum percentage of the order total that can be covered by a points discount (e.g. 20 = 20%).', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_max_redeem_pct',
				'type'              => 'number',
				'default'           => '20',
				'custom_attributes' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'desc_tip'          => true,
			),
			array(
				'title'             => __( 'Points Expiry (days)', 'phyto-loyalty' ),
				'desc'              => __( 'Points expire after this many days. Set to 0 to disable expiry.', 'phyto-loyalty' ),
				'id'                => 'phyto_loyalty_expiry_days',
				'type'              => 'number',
				'default'           => '365',
				'custom_attributes' => array(
					'min'  => '0',
					'step' => '1',
				),
				'desc_tip'          => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'phyto_loyalty_section_end',
			),
		);
	}

	// -------------------------------------------------------------------------
	// Static getters
	// -------------------------------------------------------------------------

	/**
	 * Get the points label.
	 *
	 * @return string
	 */
	public static function get_label() {
		$label = get_option( 'phyto_loyalty_points_label', 'Green Points' );
		/**
		 * Filter the display label for loyalty points.
		 *
		 * @param string $label Current label.
		 */
		return (string) apply_filters( 'phyto_loyalty_points_label', $label );
	}

	/**
	 * Get the earn rate (points per ₹).
	 *
	 * @return float
	 */
	public static function get_earn_rate() {
		return (float) get_option( 'phyto_loyalty_earn_rate', 0.1 );
	}

	/**
	 * Get the redeem rate (₹ per point).
	 *
	 * @return float
	 */
	public static function get_redeem_rate() {
		return (float) get_option( 'phyto_loyalty_redeem_rate', 0.10 );
	}

	/**
	 * Get minimum points required to redeem.
	 *
	 * @return int
	 */
	public static function get_min_redeem() {
		return (int) get_option( 'phyto_loyalty_min_redeem', 100 );
	}

	/**
	 * Get maximum redeemable percentage of order total.
	 *
	 * @return int
	 */
	public static function get_max_redeem_pct() {
		return (int) get_option( 'phyto_loyalty_max_redeem_pct', 20 );
	}

	/**
	 * Get expiry days (0 = never expire).
	 *
	 * @return int
	 */
	public static function get_expiry_days() {
		return (int) get_option( 'phyto_loyalty_expiry_days', 365 );
	}

	/**
	 * Calculate points earned for a given order total.
	 *
	 * @param float     $order_total Order total in ₹.
	 * @param \WC_Order $order       The WooCommerce order object.
	 * @return int Points to credit.
	 */
	public static function calculate_earn( $order_total, $order ) {
		$rate   = self::get_earn_rate();
		$points = (int) floor( $order_total * $rate );

		/**
		 * Filter the number of points earned for an order.
		 *
		 * @param int       $points      Calculated points.
		 * @param \WC_Order $order       The WooCommerce order.
		 */
		return (int) apply_filters( 'phyto_loyalty_points_earned', $points, $order );
	}
}
