/* Phyto Restock Alert — front.js */
jQuery( function ( $ ) {
    $( '#phyto-restock-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $form = $( this );
        var $msg  = $form.find( '.phyto-restock-message' );

        $.post( phytoRestock.ajaxurl, {
            action:       'phyto_restock_subscribe',
            nonce:        phytoRestock.nonce,
            product_id:   $form.find( '[name="product_id"]' ).val(),
            variation_id: $form.find( '[name="variation_id"]' ).val(),
            email:        $form.find( '[name="email"]' ).val(),
            firstname:    $form.find( '[name="firstname"]' ).val(),
        } ).done( function ( res ) {
            $msg.removeClass( 'error success' )
                .addClass( res.success ? 'success' : 'error' )
                .text( res.data.message )
                .show();
            if ( res.success ) $form.find( 'input[type="email"], input[type="text"]' ).val( '' );
        } ).fail( function () {
            $msg.removeClass( 'success' ).addClass( 'error' ).text( phytoRestock.i18n.error ).show();
        } );
    } );
} );
