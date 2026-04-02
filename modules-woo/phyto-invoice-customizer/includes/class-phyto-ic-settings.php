<?php
/**
 * WooCommerce Settings tab for Phyto Invoice Customizer.
 *
 * Registers a "Phyto Invoices" tab under WooCommerce → Settings with all
 * configurable options for the invoice customiser: brand name, Live Arrival
 * Guarantee toggle and text, TC batch number display, phytosanitary reference
 * display, and a custom footer note.
 *
 * @package PhytoInvoiceCustomizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_IC_Settings
 *
 * Implements the WooCommerce settings tab pattern using woocommerce_admin_fields /
 * woocommerce_update_options so all field sanitisation follows WC conventions.
 */
class Phyto_IC_Settings {

	/**
	 * Slug used for the WooCommerce settings tab and option keys prefix.
	 *
	 * @var string
	 */
	const TAB_ID = 'phyto_invoices';

	/**
	 * Register WordPress / WooCommerce hooks.
	 */
	public function register_hooks() {
		// Add tab to the WooCommerce Settings tab bar.
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );

		// Render settings fields for our tab.
		add_action( 'woocommerce_settings_tabs_' . self::TAB_ID, array( $this, 'render_settings_tab' ) );

		// Save settings fields when the form is submitted.
		add_action( 'woocommerce_update_options_' . self::TAB_ID, array( $this, 'save_settings' ) );
	}

	// -------------------------------------------------------------------------
	// Tab registration
	// -------------------------------------------------------------------------

	/**
	 * Add the "Phyto Invoices" tab to the WooCommerce Settings tab list.
	 *
	 * @param array $tabs Existing tabs keyed by slug.
	 * @return array Modified tabs.
	 */
	public function add_settings_tab( array $tabs ) {
		$tabs[ self::TAB_ID ] = __( 'Phyto Invoices', 'phyto-invoice-customizer' );
		return $tabs;
	}

	// -------------------------------------------------------------------------
	// Render & save
	// -------------------------------------------------------------------------

	/**
	 * Output the settings fields for the Phyto Invoices tab.
	 */
	public function render_settings_tab() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save the settings fields when the form is submitted.
	 *
	 * WooCommerce handles nonce verification and sanitisation internally for
	 * each registered field type (text, textarea, checkbox, etc.).
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );

		// If brand name was saved as empty, fall back to the blog name.
		$brand = get_option( 'phyto_ic_brand_name', '' );
		if ( '' === trim( $brand ) ) {
			update_option( 'phyto_ic_brand_name', get_bloginfo( 'name' ) );
		}
	}

	// -------------------------------------------------------------------------
	// Settings definition
	// -------------------------------------------------------------------------

	/**
	 * Return the array of WooCommerce settings field definitions.
	 *
	 * All option ids follow the naming convention phyto_ic_*.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function get_settings() {
		return array(

			// -----------------------------------------------------------------
			// Section: Branding
			// -----------------------------------------------------------------
			array(
				'id'    => 'phyto_ic_section_branding',
				'title' => __( 'Branding', 'phyto-invoice-customizer' ),
				'type'  => 'title',
				'desc'  => __( 'Controls the shop identity shown in email headers and PDF invoices.', 'phyto-invoice-customizer' ),
			),

			array(
				'id'      => 'phyto_ic_brand_name',
				'title'   => __( 'Brand Name', 'phyto-invoice-customizer' ),
				'type'    => 'text',
				'desc'    => __( 'Displayed in the email header and PDF invoice header. Leave blank to use the site title.', 'phyto-invoice-customizer' ),
				'default' => get_bloginfo( 'name' ),
				'css'     => 'width:100%;max-width:400px;',
			),

			array(
				'id'   => 'phyto_ic_section_branding_end',
				'type' => 'sectionend',
			),

			// -----------------------------------------------------------------
			// Section: Live Arrival Guarantee
			// -----------------------------------------------------------------
			array(
				'id'    => 'phyto_ic_section_lag',
				'title' => __( 'Live Arrival Guarantee', 'phyto-invoice-customizer' ),
				'type'  => 'title',
				'desc'  => __( 'Inject a Live Arrival Guarantee statement into order confirmation emails and order detail pages.', 'phyto-invoice-customizer' ),
			),

			array(
				'id'      => 'phyto_ic_show_lag',
				'title'   => __( 'Show LAG Text', 'phyto-invoice-customizer' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Include the Live Arrival Guarantee statement in order emails and the customer order detail page.', 'phyto-invoice-customizer' ),
				'default' => 'yes',
			),

			array(
				'id'      => 'phyto_ic_lag_text',
				'title'   => __( 'LAG Text', 'phyto-invoice-customizer' ),
				'type'    => 'textarea',
				'desc'    => __( 'The Live Arrival Guarantee statement printed in emails and on the order detail page.', 'phyto-invoice-customizer' ),
				'default' => __( 'All plants are covered by our Live Arrival Guarantee. If your plant arrives damaged, contact us within 24 hours with a photo.', 'phyto-invoice-customizer' ),
				'css'     => 'width:100%;max-width:600px;height:80px;',
			),

			array(
				'id'   => 'phyto_ic_section_lag_end',
				'type' => 'sectionend',
			),

			// -----------------------------------------------------------------
			// Section: TC Batch Numbers
			// -----------------------------------------------------------------
			array(
				'id'    => 'phyto_ic_section_batch',
				'title' => __( 'TC Batch Numbers', 'phyto-invoice-customizer' ),
				'type'  => 'title',
				'desc'  => __( 'Display tissue-culture batch codes per line item. Reads from the phyto_tc_batch and phyto_tc_batch_product tables (requires Phyto TC Batch Tracker for WooCommerce).', 'phyto-invoice-customizer' ),
			),

			array(
				'id'      => 'phyto_ic_show_batch',
				'title'   => __( 'Show TC Batch Numbers', 'phyto-invoice-customizer' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Print TC batch codes alongside each order line item in confirmation emails. Silently skipped if the batch tracker tables do not exist.', 'phyto-invoice-customizer' ),
				'default' => 'yes',
			),

			array(
				'id'   => 'phyto_ic_section_batch_end',
				'type' => 'sectionend',
			),

			// -----------------------------------------------------------------
			// Section: Phytosanitary Reference
			// -----------------------------------------------------------------
			array(
				'id'    => 'phyto_ic_section_phyto',
				'title' => __( 'Phytosanitary Certificate Reference', 'phyto-invoice-customizer' ),
				'type'  => 'title',
				'desc'  => __( 'Display phytosanitary certificate reference numbers in emails. Reads from the phyto_phytosanitary_doc table (requires Phyto Phytosanitary for WooCommerce).', 'phyto-invoice-customizer' ),
			),

			array(
				'id'      => 'phyto_ic_show_phyto',
				'title'   => __( 'Show Phytosanitary Reference', 'phyto-invoice-customizer' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Print phytosanitary certificate references in confirmation emails. Silently skipped if the phytosanitary tables do not exist.', 'phyto-invoice-customizer' ),
				'default' => 'yes',
			),

			array(
				'id'   => 'phyto_ic_section_phyto_end',
				'type' => 'sectionend',
			),

			// -----------------------------------------------------------------
			// Section: Footer Note
			// -----------------------------------------------------------------
			array(
				'id'    => 'phyto_ic_section_footer',
				'title' => __( 'Email Footer Note', 'phyto-invoice-customizer' ),
				'type'  => 'title',
				'desc'  => __( 'Optional text appended to the WooCommerce email footer after the standard footer content.', 'phyto-invoice-customizer' ),
			),

			array(
				'id'      => 'phyto_ic_footer_note',
				'title'   => __( 'Custom Footer Note', 'phyto-invoice-customizer' ),
				'type'    => 'textarea',
				'desc'    => __( 'Leave blank to suppress the custom footer note entirely.', 'phyto-invoice-customizer' ),
				'default' => '',
				'css'     => 'width:100%;max-width:600px;height:80px;',
			),

			array(
				'id'   => 'phyto_ic_section_footer_end',
				'type' => 'sectionend',
			),
		);
	}

	// -------------------------------------------------------------------------
	// Public option accessors (convenience wrappers used by sibling classes)
	// -------------------------------------------------------------------------

	/**
	 * Get the configured brand name, falling back to the site title.
	 *
	 * @return string
	 */
	public static function get_brand_name() {
		$brand = (string) get_option( 'phyto_ic_brand_name', '' );
		return '' !== trim( $brand ) ? $brand : get_bloginfo( 'name' );
	}

	/**
	 * Whether the LAG text block is enabled.
	 *
	 * @return bool
	 */
	public static function show_lag() {
		return 'yes' === get_option( 'phyto_ic_show_lag', 'yes' );
	}

	/**
	 * Get the LAG text string.
	 *
	 * @return string
	 */
	public static function get_lag_text() {
		return (string) get_option(
			'phyto_ic_lag_text',
			__( 'All plants are covered by our Live Arrival Guarantee. If your plant arrives damaged, contact us within 24 hours with a photo.', 'phyto-invoice-customizer' )
		);
	}

	/**
	 * Whether TC batch numbers should be displayed per line item.
	 *
	 * @return bool
	 */
	public static function show_batch() {
		return 'yes' === get_option( 'phyto_ic_show_batch', 'yes' );
	}

	/**
	 * Whether phytosanitary references should be displayed.
	 *
	 * @return bool
	 */
	public static function show_phyto() {
		return 'yes' === get_option( 'phyto_ic_show_phyto', 'yes' );
	}

	/**
	 * Get the custom footer note string (may be empty).
	 *
	 * @return string
	 */
	public static function get_footer_note() {
		return (string) get_option( 'phyto_ic_footer_note', '' );
	}
}
