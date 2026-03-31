/**
 * Phyto Seasonal Availability — Subscribe form AJAX handler.
 *
 * Submits the "Notify me when in season" form via WordPress admin-ajax,
 * then displays an inline success or error message to the customer.
 *
 * Depends on: jQuery, phyto_sa_ajax (localised via wp_localize_script)
 *   phyto_sa_ajax.ajax_url  — WordPress admin-ajax.php URL
 *   phyto_sa_ajax.nonce     — wp_nonce for 'phyto_sa_subscribe_nonce'
 *   phyto_sa_ajax.i18n      — translated strings object
 */
( function ( $ ) {
	'use strict';

	$( document ).on( 'submit', '.phyto-sa-subscribe__form', function ( e ) {
		e.preventDefault();

		var $form      = $( this );
		var $emailInput = $form.find( '.phyto-sa-subscribe__email' );
		var $btn        = $form.find( '.phyto-sa-subscribe__btn' );
		var $msg        = $form.find( '.phyto-sa-subscribe__message' );
		var productId   = $form.data( 'product-id' );
		var email       = $emailInput.val().trim();

		// Client-side email check before hitting the server.
		if ( ! email || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
			$msg
				.removeClass( 'phyto-sa-subscribe__message--success' )
				.addClass( 'phyto-sa-subscribe__message--error' )
				.text( phyto_sa_ajax.i18n.invalid );
			return;
		}

		// Disable button during request.
		$btn.prop( 'disabled', true );
		$msg.removeClass( 'phyto-sa-subscribe__message--success phyto-sa-subscribe__message--error' ).text( '' );

		$.ajax( {
			url:    phyto_sa_ajax.ajax_url,
			method: 'POST',
			data:   {
				action:     'phyto_sa_subscribe',
				nonce:      phyto_sa_ajax.nonce,
				email:      email,
				product_id: productId
			},
			success: function ( response ) {
				if ( response.success ) {
					$msg
						.addClass( 'phyto-sa-subscribe__message--success' )
						.text( phyto_sa_ajax.i18n.success );
					$emailInput.val( '' );
					$btn.prop( 'disabled', true ); // keep disabled after success
				} else {
					var code = response.data && response.data.code ? response.data.code : '';
					var text = phyto_sa_ajax.i18n.error;

					if ( 'already_subscribed' === code ) {
						text = phyto_sa_ajax.i18n.already;
					} else if ( 'invalid_email' === code ) {
						text = phyto_sa_ajax.i18n.invalid;
					}

					$msg
						.addClass( 'phyto-sa-subscribe__message--error' )
						.text( text );
					$btn.prop( 'disabled', false );
				}
			},
			error: function () {
				$msg
					.addClass( 'phyto-sa-subscribe__message--error' )
					.text( phyto_sa_ajax.i18n.error );
				$btn.prop( 'disabled', false );
			}
		} );
	} );
}( jQuery ) );
