<?php
/**
 * PDF generator for Phyto Care Card.
 *
 * Builds a raw PDF 1.4 byte string using only PHP string concatenation
 * and standard PDF fonts (Helvetica, Helvetica-Bold). No external library.
 *
 * @package PhytoCareCard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Care_Card_Generator
 */
class Phyto_Care_Card_Generator {

	/**
	 * Page dimensions (A4 portrait in points, 1 pt = 1/72 inch).
	 */
	const PAGE_W = 595;
	const PAGE_H = 842;

	/**
	 * Generate a PDF care guide for the given product and return it as a string.
	 *
	 * @param int $product_id WooCommerce product ID.
	 * @return string Raw PDF byte string.
	 */
	public function generate( $product_id ) {
		$product = wc_get_product( $product_id );
		$name    = $product ? $product->get_name() : __( 'Unknown Product', 'phyto-care-card' );

		// Retrieve care meta.
		$light    = (string) get_post_meta( $product_id, '_phyto_cc_light_req', true );
		$watering = (string) get_post_meta( $product_id, '_phyto_cc_watering', true );
		$humidity = (string) get_post_meta( $product_id, '_phyto_cc_humidity', true );
		$temp_min = get_post_meta( $product_id, '_phyto_cc_temp_min', true );
		$temp_max = get_post_meta( $product_id, '_phyto_cc_temp_max', true );
		$media    = (string) get_post_meta( $product_id, '_phyto_cc_potting_media', true );
		$fert     = (string) get_post_meta( $product_id, '_phyto_cc_fertilisation', true );
		$dormancy = (string) get_post_meta( $product_id, '_phyto_cc_dormancy_notes', true );
		$tips     = (string) get_post_meta( $product_id, '_phyto_cc_special_tips', true );

		// Build temperature string.
		$temp = '';
		if ( '' !== (string) $temp_min && '' !== (string) $temp_max ) {
			$temp = $temp_min . ' – ' . $temp_max . ' °C';
		} elseif ( '' !== (string) $temp_min ) {
			$temp = 'Min ' . $temp_min . ' °C';
		} elseif ( '' !== (string) $temp_max ) {
			$temp = 'Max ' . $temp_max . ' °C';
		}

		// ---------- Build page content stream ----------
		$stream = $this->build_content_stream( $name, $light, $watering, $humidity, $temp, $media, $fert, $dormancy, $tips );
		$stream_len = strlen( $stream );

		// ---------- PDF object assembly ----------
		// We'll collect objects and their byte offsets for the xref table.
		$objects = array();
		$offsets = array();
		$pdf     = '';

		$pdf .= "%PDF-1.4\n";
		$pdf .= "%\xe2\xe3\xcf\xd3\n"; // Binary hint comment.

		// Object 1 — Catalog
		$offsets[1] = strlen( $pdf );
		$pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

		// Object 2 — Pages
		$offsets[2] = strlen( $pdf );
		$pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

		// Object 3 — Page
		$offsets[3] = strlen( $pdf );
		$pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R\n"
			. "   /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "]\n"
			. "   /Contents 4 0 R\n"
			. "   /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >>\n"
			. ">>\nendobj\n";

		// Object 4 — Content stream
		$offsets[4] = strlen( $pdf );
		$pdf .= "4 0 obj\n<< /Length " . $stream_len . " >>\nstream\n";
		$pdf .= $stream;
		$pdf .= "\nendstream\nendobj\n";

		// Object 5 — Font Helvetica (body)
		$offsets[5] = strlen( $pdf );
		$pdf .= "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica "
			. "/Encoding /WinAnsiEncoding >>\nendobj\n";

		// Object 6 — Font Helvetica-Bold (headings)
		$offsets[6] = strlen( $pdf );
		$pdf .= "6 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold "
			. "/Encoding /WinAnsiEncoding >>\nendobj\n";

		// ---------- Cross-reference table ----------
		$xref_offset = strlen( $pdf );
		$num_objects = 6;
		$pdf .= "xref\n";
		$pdf .= "0 " . ( $num_objects + 1 ) . "\n";
		$pdf .= "0000000000 65535 f \n"; // Free object 0.
		for ( $i = 1; $i <= $num_objects; $i++ ) {
			$pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
		}

		// ---------- Trailer ----------
		$pdf .= "trailer\n<< /Size " . ( $num_objects + 1 ) . " /Root 1 0 R >>\n";
		$pdf .= "startxref\n" . $xref_offset . "\n%%EOF\n";

		return $pdf;
	}

	/**
	 * Build the PDF content stream with all drawing instructions.
	 *
	 * Uses PDF operators: BT/ET for text, re/f for rectangles, w/RG/rg for colours.
	 *
	 * @param string $name     Product name.
	 * @param string $light    Light requirements value.
	 * @param string $watering Watering value.
	 * @param string $humidity Humidity value.
	 * @param string $temp     Temperature string (already formatted).
	 * @param string $media    Potting media value.
	 * @param string $fert     Fertilisation value.
	 * @param string $dormancy Dormancy notes.
	 * @param string $tips     Special care tips.
	 * @return string PDF content stream text.
	 */
	private function build_content_stream(
		$name, $light, $watering, $humidity, $temp, $media, $fert, $dormancy, $tips
	) {
		$w     = self::PAGE_W;
		$h     = self::PAGE_H;
		$mg    = 45; // Left/right margin.
		$col_w = ( $w - $mg * 2 ) / 2; // Two-column width.

		$s = '';

		// ---- Header bar (forest green #1a3c2b = 0.102 0.235 0.169) ----
		$s .= "q\n"; // Save graphics state.
		$s .= "0.102 0.235 0.169 rg\n"; // Fill colour: forest green.
		$s .= "0 " . ( $h - 90 ) . " " . $w . " 90 re\n"; // Rectangle: x y width height.
		$s .= "f\n"; // Fill.
		$s .= "Q\n"; // Restore graphics state.

		// ---- "Plant Care Guide" title in white ----
		$s .= "BT\n";
		$s .= "/F2 18 Tf\n"; // Helvetica-Bold 18pt.
		$s .= "1 1 1 rg\n"; // White fill.
		$s .= $mg . " " . ( $h - 40 ) . " Td\n";
		$s .= "(" . $this->pdf_str( "Plant Care Guide" ) . ") Tj\n";
		$s .= "ET\n";

		// ---- Product name in white below title ----
		$s .= "BT\n";
		$s .= "/F1 13 Tf\n"; // Helvetica 13pt.
		$s .= "1 1 1 rg\n";
		$s .= $mg . " " . ( $h - 62 ) . " Td\n";
		$s .= "(" . $this->pdf_str( $name ) . ") Tj\n";
		$s .= "ET\n";

		// ---- "PhytoCommerce" branding top-right in sage (#7dab8a = 0.490 0.671 0.541) ----
		$branding     = "PhytoCommerce";
		$brand_font_sz = 8;
		// Approximate width: each char ~5pt at 8pt. Rough right-align.
		$brand_x = $w - $mg - ( strlen( $branding ) * 4.5 );
		$s .= "BT\n";
		$s .= "/F1 " . $brand_font_sz . " Tf\n";
		$s .= "0.490 0.671 0.541 rg\n"; // Sage.
		$s .= $brand_x . " " . ( $h - 20 ) . " Td\n";
		$s .= "(" . $this->pdf_str( $branding ) . ") Tj\n";
		$s .= "ET\n";

		// ---- Horizontal rule below header ----
		$rule_y = $h - 96;
		$s .= "q\n";
		$s .= "0.102 0.235 0.169 RG\n"; // Stroke colour: forest green.
		$s .= "0.5 w\n"; // Line width.
		$s .= $mg . " " . $rule_y . " m\n";
		$s .= ( $w - $mg ) . " " . $rule_y . " l\n";
		$s .= "S\n";
		$s .= "Q\n";

		// ---- Two-column care fields ----
		$label_sz = 9;
		$value_sz = 9;
		$row_h    = 24; // Vertical spacing per row.

		$fields_left = array(
			array( 'Light',        $light ),
			array( 'Water',        $watering ),
			array( 'Humidity',     $humidity ),
		);
		$fields_right = array(
			array( 'Temperature',  $temp ),
			array( 'Potting Media', $media ),
			array( 'Fertilisation', $fert ),
		);

		$col_start_y = $h - 120; // Top of the two-column area.

		foreach ( $fields_left as $idx => $pair ) {
			$cy = $col_start_y - ( $idx * $row_h );
			$s .= $this->render_label_value( $pair[0], $pair[1], $mg, $cy, $label_sz, $value_sz );
		}

		foreach ( $fields_right as $idx => $pair ) {
			$cy = $col_start_y - ( $idx * $row_h );
			$s .= $this->render_label_value( $pair[0], $pair[1], $mg + $col_w, $cy, $label_sz, $value_sz );
		}

		// ---- Full-width divider before extended sections ----
		$section_y   = $col_start_y - ( count( $fields_left ) * $row_h ) - 16;
		$s .= "q\n";
		$s .= "0.8 0.8 0.8 RG\n"; // Light grey stroke.
		$s .= "0.3 w\n";
		$s .= $mg . " " . $section_y . " m\n";
		$s .= ( $w - $mg ) . " " . $section_y . " l\n";
		$s .= "S\n";
		$s .= "Q\n";

		// ---- Dormancy Notes (full-width) ----
		$dn_y = $section_y - 18;
		if ( ! empty( $dormancy ) ) {
			$s .= $this->render_section_heading( "Dormancy Notes", $mg, $dn_y, 10 );
			$lines = $this->wrap_text( $dormancy, 110 );
			$line_y = $dn_y - 16;
			foreach ( $lines as $line ) {
				$s .= "BT\n/F1 9 Tf\n0 0 0 rg\n" . $mg . " " . $line_y . " Td\n(" . $this->pdf_str( $line ) . ") Tj\nET\n";
				$line_y -= 13;
			}
			$dn_end_y = $line_y - 8;
		} else {
			$dn_end_y = $dn_y - 10;
		}

		// ---- Special Care Tips (full-width) ----
		$tips_y = $dn_end_y - 12;
		if ( ! empty( $tips ) ) {
			$s .= $this->render_section_heading( "Special Care Tips", $mg, $tips_y, 10 );
			$lines = $this->wrap_text( $tips, 110 );
			$line_y = $tips_y - 16;
			foreach ( $lines as $line ) {
				$s .= "BT\n/F1 9 Tf\n0 0 0 rg\n" . $mg . " " . $line_y . " Td\n(" . $this->pdf_str( $line ) . ") Tj\nET\n";
				$line_y -= 13;
			}
		}

		// ---- Footer ----
		$footer_text = "Generated by PhytoCommerce  \xb7  phyto-evolution.github.io/PhytoCommerce";
		$footer_y    = 28;
		$s .= "q\n";
		$s .= "0.8 0.8 0.8 RG\n0.3 w\n";
		$s .= $mg . " " . ( $footer_y + 12 ) . " m\n";
		$s .= ( $w - $mg ) . " " . ( $footer_y + 12 ) . " l\n";
		$s .= "S\n";
		$s .= "Q\n";

		$s .= "BT\n";
		$s .= "/F1 7 Tf\n";
		$s .= "0.5 0.5 0.5 rg\n"; // Grey.
		$s .= $mg . " " . $footer_y . " Td\n";
		$s .= "(" . $this->pdf_str( $footer_text ) . ") Tj\n";
		$s .= "ET\n";

		return $s;
	}

	/**
	 * Render a label + value pair at the specified position.
	 *
	 * Label is output in Helvetica-Bold, value in Helvetica, below the label.
	 *
	 * @param string $label    Field label.
	 * @param string $value    Field value.
	 * @param float  $x        X position.
	 * @param float  $y        Y position (top of label).
	 * @param int    $label_sz Label font size in pt.
	 * @param int    $value_sz Value font size in pt.
	 * @return string PDF operators.
	 */
	private function render_label_value( $label, $value, $x, $y, $label_sz, $value_sz ) {
		$s  = '';
		// Label.
		$s .= "BT\n/F2 " . $label_sz . " Tf\n0.102 0.235 0.169 rg\n";
		$s .= $x . " " . $y . " Td\n";
		$s .= "(" . $this->pdf_str( strtoupper( $label ) ) . ") Tj\n";
		$s .= "ET\n";
		// Value.
		$val = ! empty( $value ) ? $value : '—';
		$s .= "BT\n/F1 " . $value_sz . " Tf\n0 0 0 rg\n";
		$s .= $x . " " . ( $y - 11 ) . " Td\n";
		$s .= "(" . $this->pdf_str( $val ) . ") Tj\n";
		$s .= "ET\n";
		return $s;
	}

	/**
	 * Render a section heading in Helvetica-Bold with a light sage underline.
	 *
	 * @param string $title    Heading text.
	 * @param float  $x        X position.
	 * @param float  $y        Y position.
	 * @param int    $font_sz  Font size in pt.
	 * @return string PDF operators.
	 */
	private function render_section_heading( $title, $x, $y, $font_sz ) {
		$s  = '';
		$s .= "BT\n/F2 " . $font_sz . " Tf\n0.102 0.235 0.169 rg\n";
		$s .= $x . " " . $y . " Td\n";
		$s .= "(" . $this->pdf_str( strtoupper( $title ) ) . ") Tj\n";
		$s .= "ET\n";
		return $s;
	}

	/**
	 * Escape a string for use in a PDF literal string token.
	 *
	 * Handles parentheses and backslash. Strips characters outside Latin-1
	 * (WinAnsiEncoding does not support them). Also maps common UTF-8 sequences
	 * for degree, en-dash, and bullet to their WinAnsi equivalents.
	 *
	 * @param string $text Input text.
	 * @return string Escaped PDF string content.
	 */
	private function pdf_str( $text ) {
		// Map common UTF-8 sequences to WinAnsi byte values.
		$map = array(
			"\xe2\x80\x93" => "\x96", // En dash.
			"\xe2\x80\x94" => "\x97", // Em dash.
			"\xc2\xb0"     => "\xb0", // Degree sign.
			"\xe2\x80\x99" => "\x92", // Right single quotation mark.
			"\xe2\x80\x98" => "\x91", // Left single quotation mark.
			"\xe2\x80\x9c" => "\x93", // Left double quotation mark.
			"\xe2\x80\x9d" => "\x94", // Right double quotation mark.
			"\xc2\xb7"     => "\xb7", // Middle dot.
			"\xc2\xb1"     => "\xb1", // Plus-minus.
		);
		$text = str_replace( array_keys( $map ), array_values( $map ), $text );

		// Strip any remaining non-Latin-1 multi-byte sequences.
		$text = preg_replace( '/[\x80-\xff][\x80-\xbf]+/', '', $text );

		// Escape PDF special characters.
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( '(', '\\(', $text );
		$text = str_replace( ')', '\\)', $text );

		return $text;
	}

	/**
	 * Naive word-wrap: split text into lines of at most $max_chars characters.
	 *
	 * @param string $text      Input text (may contain newlines).
	 * @param int    $max_chars Maximum characters per line.
	 * @return array Array of line strings.
	 */
	private function wrap_text( $text, $max_chars = 100 ) {
		$paragraphs = preg_split( '/\r\n|\r|\n/', $text );
		$lines      = array();
		foreach ( $paragraphs as $para ) {
			if ( '' === trim( $para ) ) {
				$lines[] = '';
				continue;
			}
			$wrapped = wordwrap( $para, $max_chars, "\n", true );
			foreach ( explode( "\n", $wrapped ) as $line ) {
				$lines[] = $line;
			}
		}
		return $lines;
	}
}
