/**
 * Phyto Restock Alert — Front-end AJAX subscribe handler
 *
 * Submits the subscribe form via AJAX and displays an inline
 * confirmation or error message without a page reload.
 *
 * @package PhytoRestockAlert
 */

/* global phytoRs, jQuery */
(function ( $ ) {
	'use strict';

	$( document ).ready( function () {

		var $form    = $( '#phyto-rs-form' );
		var $wrap    = $( '#phyto-rs-form-wrap' );
		var $message = $( '#phyto-rs-message' );
		var $submit  = $form.find( '.phyto-rs-submit' );

		if ( ! $form.length ) {
			return;
		}

		$form.on( 'submit', function ( e ) {
			e.preventDefault();

			var email = $.trim( $( '#phyto-rs-email' ).val() );

			// Basic client-side email check.
			if ( ! email || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
				showMessage( phytoRs.msgInvalidEmail, 'error' );
				return;
			}

			$submit.prop( 'disabled', true ).text( phytoRs.msgSubscribing );
			clearMessage();

			$.ajax({
				url:    phytoRs.ajaxUrl,
				method: 'POST',
				data:   $form.serialize() + '&action=phyto_restock_subscribe',
				success: function ( response ) {
					if ( response.success ) {
						showMessage( response.data.message, 'success' );
						$wrap.addClass( 'phyto-rs-subscribed' );
					} else {
						var code = response.data && response.data.code ? response.data.code : '';
						if ( 'already_subscribed' === code ) {
							showMessage( phytoRs.msgAlready, 'error' );
						} else {
							var msg = ( response.data && response.data.message ) ? response.data.message : phytoRs.msgError;
							showMessage( msg, 'error' );
						}
						$submit.prop( 'disabled', false ).text( phytoRs.msgSubmitLabel || 'Notify Me' );
					}
				},
				error: function () {
					showMessage( phytoRs.msgError, 'error' );
					$submit.prop( 'disabled', false ).text( phytoRs.msgSubmitLabel || 'Notify Me' );
				}
			});
		});

		/**
		 * Show a message in the #phyto-rs-message element.
		 *
		 * @param {string} text    Message text.
		 * @param {string} type    'success' or 'error'.
		 */
		function showMessage( text, type ) {
			$message
				.removeClass( 'phyto-rs-success phyto-rs-error' )
				.addClass( 'phyto-rs-' + type )
				.text( text );
		}

		/**
		 * Clear the message element.
		 */
		function clearMessage() {
			$message.removeClass( 'phyto-rs-success phyto-rs-error' ).text( '' );
		}

	});

}( jQuery ));
