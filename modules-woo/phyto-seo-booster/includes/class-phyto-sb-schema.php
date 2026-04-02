<?php
/**
 * JSON-LD schema injection for Phyto SEO Booster.
 *
 * @package PhytoSeoBooster
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Phyto_SB_Schema {

	public function register_hooks() {
		add_action( 'wp_head', array( $this, 'inject_product_schema' ) );
	}

	public function inject_product_schema() {
		if ( ! is_product() ) {
			return;
		}

		global $post;
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return;
		}

		$currency = get_option( 'phyto_sb_currency', get_woocommerce_currency() );
		$brand    = get_bloginfo( 'name' );
		$in_stock = $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock';
		$image    = wp_get_attachment_url( $product->get_image_id() );
		$alt_name = get_post_meta( $post->ID, '_phyto_botanical_name', true );

		$schema = array(
			'@context'    => 'https://schema.org/',
			'@type'       => 'Product',
			'name'        => $product->get_name(),
			'description' => wp_strip_all_tags( $product->get_description() ),
			'sku'         => $product->get_sku(),
			'brand'       => array( '@type' => 'Brand', 'name' => $brand ),
			'offers'      => array(
				'@type'         => 'Offer',
				'url'           => get_permalink( $post->ID ),
				'priceCurrency' => $currency,
				'price'         => wc_format_decimal( $product->get_price(), 2 ),
				'availability'  => $in_stock,
				'seller'        => array( '@type' => 'Organization', 'name' => $brand ),
			),
		);

		if ( $image ) {
			$schema['image'] = $image;
		}

		if ( $alt_name ) {
			$schema['alternateName'] = $alt_name;
		}

		echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
}
