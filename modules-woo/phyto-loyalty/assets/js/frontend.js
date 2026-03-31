/**
 * Phyto Loyalty — Front-end JS
 *
 * Handles AJAX apply / remove points on the cart page.
 *
 * @package PhytoLoyalty
 */

/* global phytoLoyalty, jQuery */
( function ( $ ) {
	'use strict';

	/**
	 * Show a message in the #phyto-loyalty-message element.
	 *
	 * @param {string}  text    Message to display.
	 * @param {boolean} isError True for error styling, false for success.
	 */
	function showMessage( text, isError ) {
		var $msg = $( '#phyto-loyalty-message' );
		$msg.removeClass( 'success error' )
			.addClass( isError ? 'error' : 'success' )
			.text( text )
			.fadeIn( 200 );
	}

	/**
	 * Trigger WooCommerce cart totals update.
	 */
	function refreshCart() {
		$( document.body ).trigger( 'wc_update_cart' );
		$( document.body ).trigger( 'update_checkout' );
	}

	// Apply points.
	$( document ).on( 'click', '#phyto-loyalty-apply-btn', function () {
		var $btn    = $( this );
		var points  = parseInt( $( '#phyto-loyalty-points-input' ).val(), 10 );

		if ( isNaN( points ) || points <= 0 ) {
			showMessage( 'Please enter a valid number of points.', true );
			return;
		}

		$btn.text( phytoLoyalty.applying ).prop( 'disabled', true );

		$.ajax( {
			url:    phytoLoyalty.ajaxUrl,
			method: 'POST',
			data:   {
				action: 'phyto_loyalty_apply_points',
				nonce:  phytoLoyalty.applyNonce,
				points: points,
			},
			success: function ( response ) {
				if ( response.success ) {
					showMessage( response.data.message, false );
					refreshCart();
				} else {
					showMessage( response.data.message, true );
					$btn.text( 'Apply Points' ).prop( 'disabled', false );
				}
			},
			error: function () {
				showMessage( 'An error occurred. Please try again.', true );
				$btn.text( 'Apply Points' ).prop( 'disabled', false );
			},
		} );
	} );

	// Remove points.
	$( document ).on( 'click', '#phyto-loyalty-remove-btn', function () {
		var $btn = $( this );
		$btn.text( phytoLoyalty.removing ).prop( 'disabled', true );

		$.ajax( {
			url:    phytoLoyalty.ajaxUrl,
			method: 'POST',
			data:   {
				action: 'phyto_loyalty_remove_points',
				nonce:  phytoLoyalty.removeNonce,
			},
			success: function ( response ) {
				if ( response.success ) {
					showMessage( response.data.message, false );
					refreshCart();
				} else {
					showMessage( response.data.message, true );
					$btn.text( 'Remove Points' ).prop( 'disabled', false );
				}
			},
			error: function () {
				showMessage( 'An error occurred. Please try again.', true );
				$btn.text( 'Remove Points' ).prop( 'disabled', false );
			},
		} );
	} );

} )( jQuery );
