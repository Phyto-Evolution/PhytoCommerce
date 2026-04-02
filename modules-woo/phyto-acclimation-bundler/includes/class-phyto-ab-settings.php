<?php
/**
 * Settings for Phyto Acclimation Bundler.
 *
 * @package PhytoAcclimationBundler
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Phyto_AB_Settings {

	public function register_hooks() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab' ), 56 );
		add_action( 'woocommerce_settings_tabs_phyto_acclim', array( $this, 'render' ) );
		add_action( 'woocommerce_update_options_phyto_acclim', array( $this, 'save' ) );
	}

	public function add_tab( $tabs ) {
		$tabs['phyto_acclim'] = __( 'Phyto Acclimation', 'phyto-acclimation-bundler' );
		return $tabs;
	}

	public function render() { woocommerce_admin_fields( $this->fields() ); }
	public function save()   { woocommerce_update_options( $this->fields() ); }

	public function fields() {
		return array(
			array( 'id' => 'phyto_ab_section', 'title' => __( 'Acclimation Bundler', 'phyto-acclimation-bundler' ), 'type' => 'title' ),
			array( 'id' => 'phyto_ab_kit_ids',   'title' => __( 'Kit Product IDs (CSV)', 'phyto-acclimation-bundler' ), 'type' => 'text', 'desc' => __( 'Comma-separated WooCommerce product IDs to suggest.', 'phyto-acclimation-bundler' ) ),
			array( 'id' => 'phyto_ab_tags',       'title' => __( 'Trigger Tags (CSV)', 'phyto-acclimation-bundler' ),   'type' => 'text', 'default' => 'tc-plant,deflasked,tissue-culture', 'desc' => __( 'Product tags that trigger the widget.', 'phyto-acclimation-bundler' ) ),
			array( 'id' => 'phyto_ab_stage_ids',  'title' => __( 'Trigger Stage IDs (CSV)', 'phyto-acclimation-bundler' ), 'type' => 'text', 'desc' => __( 'Growth stage IDs (from Phyto Growth Stage plugin) that trigger the widget.', 'phyto-acclimation-bundler' ) ),
			array( 'id' => 'phyto_ab_discount',   'title' => __( 'Bundle Discount %', 'phyto-acclimation-bundler' ),   'type' => 'number', 'default' => '0', 'custom_attributes' => array( 'min' => '0', 'max' => '100' ) ),
			array( 'id' => 'phyto_ab_headline',   'title' => __( 'Widget Headline', 'phyto-acclimation-bundler' ),     'type' => 'text', 'default' => __( 'Complete your acclimation setup', 'phyto-acclimation-bundler' ) ),
			array( 'id' => 'phyto_ab_max_show',   'title' => __( 'Max items to show', 'phyto-acclimation-bundler' ),   'type' => 'number', 'default' => '3', 'custom_attributes' => array( 'min' => '1' ) ),
			array( 'id' => 'phyto_ab_section_end', 'type' => 'sectionend' ),
		);
	}
}
