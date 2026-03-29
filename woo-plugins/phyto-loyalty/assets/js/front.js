/* Phyto Loyalty — front.js */
jQuery( function ( $ ) {
    $( '#phyto-loyalty-apply-btn' ).on( 'click', function () {
        var points = parseInt( $( '#phyto-loyalty-redeem-input' ).val(), 10 );
        if ( ! points || points < 1 ) return;
        $.post( phytoLoyalty.ajaxurl, {
            action: 'phyto_loyalty_redeem',
            nonce:  phytoLoyalty.nonce,
            points: points,
        } ).done( function ( res ) {
            $( '#phyto-loyalty-message' ).text( res.data.message )
                .css( 'color', res.success ? 'green' : 'red' );
            if ( res.success ) location.reload();
        } );
    } );
} );
