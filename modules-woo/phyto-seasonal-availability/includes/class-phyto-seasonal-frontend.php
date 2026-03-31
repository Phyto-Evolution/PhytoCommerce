<?php
/**
 * Frontend class: blocks add-to-cart for out-of-season products and renders the subscribe form.
 *
 * @package PhytoSeasonalAvailability
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Phyto_Seasonal_Frontend
 *
 * Intercepts the WooCommerce purchase flow when a product is outside its
 * configured availability window and surfaces the "notify me" subscribe form.
 */
class Phyto_Seasonal_Frontend {

	/**
	 * Register WordPress/WooCommerce hooks.
	 */
	public function register_hooks() {
		// Archive / shop loop pill.
		add_filter( 'woocommerce_loop_add_to_cart_html', array( $this, 'block_add_to_cart' ), 10, 2 );

		// Single product page — remove button and show form.
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'block_single_button' ) );

		// Prevent cart manipulation via direct URL / API.
		add_filter( 'woocommerce_is_purchasable', array( $this, 'remove_button' ), 10, 2 );
	}

	/**
	 * Determine whether a product is currently in season.
	 *
	 * Returns true when:
	 * - The product is marked year-round, OR
	 * - No months have been configured (graceful default — allow all), OR
	 * - The current month is in the configured available months array.
	 *
	 * Applies the `phyto_sa_is_in_season` filter so third-party code can override.
	 *
	 * @param int $product_id WooCommerce product post ID.
	 * @return bool
	 */
	public function is_in_season( $product_id ) {
		$year_round = (bool) get_post_meta( $product_id, '_phyto_sa_year_round', true );
		if ( $year_round ) {
			/** This filter is documented in includes/class-phyto-seasonal-frontend.php */
			return apply_filters( 'phyto_sa_is_in_season', true, $product_id );
		}

		$months = get_post_meta( $product_id, '_phyto_sa_months', true );
		if ( ! is_array( $months ) || empty( $months ) ) {
			// No months configured — default to available.
			return apply_filters( 'phyto_sa_is_in_season', true, $product_id );
		}

		$months        = array_map( 'intval', $months );
		$current_month = (int) gmdate( 'n' );
		$result        = in_array( $current_month, $months, true );

		/**
		 * Filter whether a product is currently in season.
		 *
		 * @param bool $result     True when in season.
		 * @param int  $product_id WooCommerce product post ID.
		 */
		return apply_filters( 'phyto_sa_is_in_season', $result, $product_id );
	}

	/**
	 * Replace the add-to-cart button HTML in the shop/archive loop with an "Out of season" pill.
	 *
	 * @param string     $html    Original add-to-cart button HTML.
	 * @param WC_Product $product WooCommerce product object.
	 * @return string Modified HTML or original if in season.
	 */
	public function block_add_to_cart( $html, $product ) {
		if ( $this->is_in_season( $product->get_id() ) ) {
			return $html;
		}

		return '<span class="phyto-sa-blocked">'
			. esc_html__( 'Not in season', 'phyto-seasonal-availability' )
			. '</span>';
	}

	/**
	 * On the single product page: if out of season, hide the add-to-cart button
	 * and output the unavailability message + subscribe form.
	 *
	 * Hooked to `woocommerce_before_add_to_cart_button` (runs before the button).
	 * We use CSS/JS injected output_buffering technique via the woocommerce_single_add_to_cart_text
	 * filter alongside removing the form entirely via priority hook on quantity + button containers.
	 */
	public function block_single_button() {
		global $product;

		if ( ! $product instanceof WC_Product ) {
			return;
		}

		if ( $this->is_in_season( $product->get_id() ) ) {
			return;
		}

		$product_id = $product->get_id();

		// Retrieve the custom unavailable message (with filter hook for overrides).
		$default_message = __( 'This plant is not available for shipping this month.', 'phyto-seasonal-availability' );
		$saved_message   = get_post_meta( $product_id, '_phyto_sa_message', true );
		$message         = $saved_message ? $saved_message : $default_message;

		/**
		 * Filter the unavailable message shown on the single product page.
		 *
		 * @param string $message    The unavailability message text.
		 * @param int    $product_id WooCommerce product post ID.
		 */
		$message = apply_filters( 'phyto_sa_unavailable_message', $message, $product_id );

		echo '<div class="phyto-sa-message">';
		echo '<span class="phyto-sa-message__icon">&#127807;</span> '; // 🌱
		echo wp_kses_post( $message );
		echo '</div>';

		$this->render_subscribe_form( $product_id );

		// Suppress the actual add-to-cart button and quantity field that follow.
		// We hook onto woocommerce_quantity_input_args and override button via ob_start.
		add_filter( 'woocommerce_is_purchasable', '__return_false', 99 );
	}

	/**
	 * Filter woocommerce_is_purchasable to return false for out-of-season products.
	 * Prevents cart manipulation via direct URL or REST API.
	 *
	 * @param bool       $purchasable Whether the product is purchasable.
	 * @param WC_Product $product     WooCommerce product object.
	 * @return bool
	 */
	public function remove_button( $purchasable, $product ) {
		if ( ! $purchasable ) {
			return false;
		}

		if ( ! $this->is_in_season( $product->get_id() ) ) {
			return false;
		}

		return $purchasable;
	}

	/**
	 * Output the "Notify me when in season" subscribe form for a product.
	 * Also enqueues the required CSS and JS assets.
	 *
	 * @param int $product_id WooCommerce product post ID.
	 */
	public function render_subscribe_form( $product_id ) {
		$this->enqueue_assets();
		?>
		<div class="phyto-sa-subscribe">
			<p class="phyto-sa-subscribe__label">
				<?php esc_html_e( 'Get notified when this plant is back in season:', 'phyto-seasonal-availability' ); ?>
			</p>
			<form class="phyto-sa-subscribe__form" data-product-id="<?php echo esc_attr( $product_id ); ?>">
				<?php wp_nonce_field( 'phyto_sa_subscribe_nonce', 'phyto_sa_nonce_field' ); ?>
				<input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>" />
				<div class="phyto-sa-subscribe__row">
					<input
						type="email"
						name="phyto_sa_email"
						class="phyto-sa-subscribe__email"
						placeholder="<?php esc_attr_e( 'Your email address', 'phyto-seasonal-availability' ); ?>"
						required
					/>
					<button type="submit" class="phyto-sa-subscribe__btn">
						<?php esc_html_e( 'Notify me', 'phyto-seasonal-availability' ); ?>
					</button>
				</div>
				<div class="phyto-sa-subscribe__message" aria-live="polite"></div>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue frontend CSS and JS assets.
	 */
	private function enqueue_assets() {
		wp_enqueue_style(
			'phyto-sa-frontend',
			PHYTO_SA_URL . 'assets/css/frontend.css',
			array(),
			PHYTO_SA_VERSION
		);

		wp_enqueue_script(
			'phyto-sa-subscribe',
			PHYTO_SA_URL . 'assets/js/subscribe.js',
			array( 'jquery' ),
			PHYTO_SA_VERSION,
			true
		);

		wp_localize_script(
			'phyto-sa-subscribe',
			'phyto_sa_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'phyto_sa_subscribe_nonce' ),
				'i18n'     => array(
					'success' => __( 'You\'re on the list! We\'ll email you when this plant is back in season.', 'phyto-seasonal-availability' ),
					'error'   => __( 'Something went wrong. Please try again.', 'phyto-seasonal-availability' ),
					'already' => __( 'This email is already subscribed for this product.', 'phyto-seasonal-availability' ),
					'invalid' => __( 'Please enter a valid email address.', 'phyto-seasonal-availability' ),
				),
			)
		);
	}
}
