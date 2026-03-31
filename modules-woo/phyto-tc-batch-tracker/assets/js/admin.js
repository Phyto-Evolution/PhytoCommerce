/**
 * Phyto TC Batch Tracker — Admin JS
 *
 * Initialises the Select2-powered batch picker on the product edit screen.
 * Depends on jQuery and Select2 (both bundled with WooCommerce).
 */
/* global jQuery */

( function ( $ ) {
	'use strict';

	/**
	 * Initialise Select2 on the TC batch multi-select.
	 *
	 * Called on DOM ready. Attaches Select2 to every element carrying the
	 * `.phyto-tcb-select2` class, enabling search and keyboard navigation.
	 */
	function initBatchSelect2() {
		$( '.phyto-tcb-select2' ).each( function () {
			var $select = $( this );

			// Avoid double-initialisation.
			if ( $select.hasClass( 'select2-hidden-accessible' ) ) {
				return;
			}

			$select.select2( {
				placeholder:        $select.data( 'placeholder' ) || '',
				allowClear:         true,
				width:              '100%',
				closeOnSelect:      false,
				templateResult:     formatBatchOption,
				templateSelection:  formatBatchSelection,
			} );
		} );
	}

	/**
	 * Format individual option items in the Select2 dropdown.
	 *
	 * Splits the option text on ' — ' to visually separate batch code from
	 * the status label for easier scanning in the dropdown list.
	 *
	 * @param  {object} state Select2 state object.
	 * @return {string|jQuery} HTML string or jQuery element.
	 */
	function formatBatchOption( state ) {
		if ( ! state.id ) {
			return state.text; // Placeholder.
		}

		var parts      = state.text.split( ' \u2014 ' ); // ' — '
		var batchCode  = parts[0] ? parts[0].trim() : state.text;
		var statusText = parts[1] ? parts[1].trim() : '';

		var $item = $(
			'<span class="phyto-tcb-option">' +
				'<strong class="phyto-tcb-option__code">' + escapeHtml( batchCode ) + '</strong>' +
				( statusText ? ' <em class="phyto-tcb-option__status">(' + escapeHtml( statusText ) + ')</em>' : '' ) +
			'</span>'
		);

		return $item;
	}

	/**
	 * Format selected item tags in the Select2 input box.
	 *
	 * Shows only the batch code (before the dash) to keep the tag compact.
	 *
	 * @param  {object} state Select2 state object.
	 * @return {string} Display text.
	 */
	function formatBatchSelection( state ) {
		var parts = state.text.split( ' \u2014 ' ); // ' — '
		return parts[0] ? parts[0].trim() : state.text;
	}

	/**
	 * Simple HTML entity escaper for building markup safely.
	 *
	 * @param  {string} str Raw string.
	 * @return {string} HTML-escaped string.
	 */
	function escapeHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	// Initialise on DOM ready.
	$( document ).ready( function () {
		initBatchSelect2();
	} );

}( jQuery ) );
