<?php
/**
 * WooCommerce Settings tab for Phyto Live Arrival Guarantee.
 *
 * Adds a "LAG" tab under WooCommerce → Settings with global defaults
 * for guarantee window, policy type, opt-in label, and policy disclaimer.
 *
 * @package PhytoLiveArrival
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_LAG_Settings
 */
class Phyto_LAG_Settings {

	/**
	 * Register WordPress/WooCommerce hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_phyto_lag', array( $this, 'render_settings_tab' ) );
		add_action( 'woocommerce_update_options_phyto_lag', array( $this, 'save_settings' ) );
	}

	/**
	 * Add the "LAG" tab to the WooCommerce Settings tab list.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array Modified tabs.
	 */
	public function add_settings_tab( $tabs ) {
		$tabs['phyto_lag'] = __( 'LAG', 'phyto-live-arrival' );
		return $tabs;
	}

	/**
	 * Output the settings fields for the LAG tab.
	 */
	public function render_settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save the settings fields when the form is submitted.
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Return the array of settings fields.
	 *
	 * @return array WooCommerce settings fields definition.
	 */
	public function get_settings() {
		return array(
			array(
				'id'    => 'phyto_lag_section_title',
				'title' => __( 'Live Arrival Guarantee Settings', 'phyto-live-arrival' ),
				'type'  => 'title',
				'desc'  => __( 'Global defaults for the Live Arrival Guarantee system. Individual products can override these values.', 'phyto-live-arrival' ),
			),

			array(
				'id'                => 'phyto_lag_default_window',
				'title'             => __( 'Default Guarantee Window (hours)', 'phyto-live-arrival' ),
				'type'              => 'number',
				'desc'              => __( 'How many hours after delivery the buyer has to report a claim.', 'phyto-live-arrival' ),
				'default'           => 24,
				'custom_attributes' => array(
					'min'  => 1,
					'step' => 1,
				),
				'css'               => 'width:80px;',
			),

			array(
				'id'      => 'phyto_lag_default_policy',
				'title'   => __( 'Default Policy Type', 'phyto-live-arrival' ),
				'type'    => 'select',
				'options' => array(
					'replacement'  => __( 'Replacement', 'phyto-live-arrival' ),
					'refund'       => __( 'Refund', 'phyto-live-arrival' ),
					'store-credit' => __( 'Store Credit', 'phyto-live-arrival' ),
				),
				'default' => 'replacement',
				'desc'    => __( 'Default resolution offered when a LAG claim is approved.', 'phyto-live-arrival' ),
			),

			array(
				'id'      => 'phyto_lag_checkout_label',
				'title'   => __( 'Checkout Opt-in Label', 'phyto-live-arrival' ),
				'type'    => 'text',
				'desc'    => __( 'Label shown next to the opt-in checkbox at checkout.', 'phyto-live-arrival' ),
				'default' => __( 'I accept the Live Arrival Guarantee terms for live plant orders.', 'phyto-live-arrival' ),
				'css'     => 'width:100%;max-width:500px;',
			),

			array(
				'id'      => 'phyto_lag_disclaimer',
				'title'   => __( 'Policy Disclaimer Text', 'phyto-live-arrival' ),
				'type'    => 'textarea',
				'desc'    => __( 'Displayed on the product page and appended to order confirmation emails for LAG-enrolled products.', 'phyto-live-arrival' ),
				'default' => __( 'This product is covered by our Live Arrival Guarantee. If your plant does not arrive in viable condition, contact us within the guarantee window stated on the product page with a photo of the plant as received.', 'phyto-live-arrival' ),
				'css'     => 'width:100%;max-width:500px;height:80px;',
			),

			array(
				'id'   => 'phyto_lag_section_end',
				'type' => 'sectionend',
			),
		);
	}
}
