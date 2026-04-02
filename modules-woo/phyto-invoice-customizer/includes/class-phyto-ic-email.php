<?php
/**
 * Email injection class for Phyto Invoice Customizer.
 *
 * Hooks into WooCommerce order confirmation emails to inject:
 *  - Branded shop header
 *  - Live Arrival Guarantee statement
 *  - TC batch numbers per line item (if phyto_tc_batch tables exist)
 *  - Phytosanitary certificate references (if table exists)
 *  - Custom footer note
 *
 * @package PhytoInvoiceCustomizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_IC_Email
 */
class Phyto_IC_Email {

	/**
	 * Register email hooks.
	 */
	public function register_hooks() {
		// Branded header before order details.
		add_action( 'woocommerce_email_before_order_table', array( $this, 'inject_header' ), 5, 3 );

		// LAG text after order table.
		add_action( 'woocommerce_email_after_order_table', array( $this, 'inject_lag' ), 10, 3 );

		// TC batch + phytosanitary per line item (hook into order item meta).
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', array( $this, 'inject_line_item_meta' ), 10, 2 );

		// Footer note.
		add_action( 'woocommerce_email_footer', array( $this, 'inject_footer_note' ) );
	}

	/**
	 * Inject branded header above the order table in emails.
	 *
	 * @param WC_Order $order   Current order.
	 * @param bool     $sent_to_admin Whether email is for admin.
	 * @param bool     $plain_text Whether plain-text email.
	 */
	public function inject_header( $order, $sent_to_admin, $plain_text ) {
		if ( $plain_text ) {
			return;
		}

		$brand = Phyto_IC_Settings::get_brand_name();

		echo '<div style="text-align:center;padding:10px 0 16px;border-bottom:2px solid #e8e8e8;margin-bottom:16px;">';
		echo '<strong style="font-size:18px;color:#1a1a1a;">' . esc_html( $brand ) . '</strong>';
		echo '</div>';
	}

	/**
	 * Inject LAG statement after the order table in emails.
	 *
	 * @param WC_Order $order      Current order.
	 * @param bool     $sent_to_admin Whether email is for admin.
	 * @param bool     $plain_text Whether plain-text email.
	 */
	public function inject_lag( $order, $sent_to_admin, $plain_text ) {
		if ( ! Phyto_IC_Settings::show_lag() ) {
			return;
		}

		$lag_text = Phyto_IC_Settings::get_lag_text();

		if ( $plain_text ) {
			echo "\n" . esc_html( $lag_text ) . "\n";
			return;
		}

		// TC batch numbers block.
		if ( Phyto_IC_Settings::show_batch() ) {
			$batch_html = $this->get_batch_html( $order );
			if ( $batch_html ) {
				echo wp_kses_post( $batch_html );
			}
		}

		// Phytosanitary reference.
		if ( Phyto_IC_Settings::show_phyto() ) {
			$phyto_html = $this->get_phyto_html( $order );
			if ( $phyto_html ) {
				echo wp_kses_post( $phyto_html );
			}
		}

		echo '<div style="background:#f0f8f0;border-left:4px solid #3a9a6a;padding:12px 16px;margin:16px 0;border-radius:4px;">';
		echo '<strong style="display:block;margin-bottom:4px;color:#2d7a54;">🌿 Live Arrival Guarantee</strong>';
		echo '<p style="margin:0;color:#333;">' . esc_html( $lag_text ) . '</p>';
		echo '</div>';
	}

	/**
	 * Inject custom footer note into email footer.
	 *
	 * @param WC_Email $email Current email object.
	 */
	public function inject_footer_note( $email ) {
		$note = Phyto_IC_Settings::get_footer_note();
		if ( '' === $note ) {
			return;
		}

		echo '<p style="text-align:center;color:#777;font-size:12px;margin-top:16px;">' . esc_html( $note ) . '</p>';
	}

	/**
	 * Build HTML block showing TC batch numbers for line items.
	 *
	 * @param WC_Order $order Current order.
	 * @return string HTML or empty string.
	 */
	private function get_batch_html( $order ) {
		global $wpdb;

		$batch_table   = $wpdb->prefix . 'phyto_tc_batch';
		$product_table = $wpdb->prefix . 'phyto_tc_batch_product';

		// Silently skip if tables don't exist.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$exists = $wpdb->get_var( "SHOW TABLES LIKE '{$batch_table}'" );
		if ( ! $exists ) {
			return '';
		}

		$rows = array();
		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$batch = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT b.batch_code FROM {$batch_table} b
					 JOIN {$product_table} bp ON bp.batch_id = b.id
					 WHERE bp.product_id = %d
					 ORDER BY b.id DESC LIMIT 1",
					$product_id
				)
			);
			if ( $batch ) {
				$rows[] = '<tr><td>' . esc_html( $item->get_name() ) . '</td><td><code>' . esc_html( $batch ) . '</code></td></tr>';
			}
		}

		if ( ! $rows ) {
			return '';
		}

		$html  = '<div style="margin:12px 0;"><strong>' . esc_html__( 'TC Batch References', 'phyto-invoice-customizer' ) . '</strong>';
		$html .= '<table style="width:100%;border-collapse:collapse;margin-top:6px;">';
		$html .= '<thead><tr><th style="text-align:left;padding:4px 8px;background:#f5f5f5;">' . esc_html__( 'Product', 'phyto-invoice-customizer' ) . '</th>';
		$html .= '<th style="text-align:left;padding:4px 8px;background:#f5f5f5;">' . esc_html__( 'Batch Code', 'phyto-invoice-customizer' ) . '</th></tr></thead>';
		$html .= '<tbody>' . implode( '', $rows ) . '</tbody></table></div>';

		return $html;
	}

	/**
	 * Build HTML block showing phytosanitary certificate reference.
	 *
	 * @param WC_Order $order Current order.
	 * @return string HTML or empty string.
	 */
	private function get_phyto_html( $order ) {
		global $wpdb;

		$table = $wpdb->prefix . 'phyto_phytosanitary_doc';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$exists = $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" );
		if ( ! $exists ) {
			return '';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ref = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT certificate_number FROM {$table} WHERE order_id = %d LIMIT 1",
				$order->get_id()
			)
		);

		if ( ! $ref ) {
			return '';
		}

		return '<p style="margin:8px 0;"><strong>' . esc_html__( 'Phytosanitary Certificate:', 'phyto-invoice-customizer' ) . '</strong> <code>' . esc_html( $ref ) . '</code></p>';
	}

	/**
	 * Placeholder — line item meta filter (currently pass-through).
	 *
	 * @param array        $formatted_meta Formatted meta array.
	 * @param WC_Order_Item $item           Order item.
	 * @return array
	 */
	public function inject_line_item_meta( $formatted_meta, $item ) {
		return $formatted_meta;
	}
}
