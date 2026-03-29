/* Phyto Wholesale — front.js */
jQuery( function ( $ ) {
    $( '#phyto-ws-apply-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var data = $( this ).serializeArray().reduce( function ( o, f ) { o[f.name] = f.value; return o; }, {} );
        data.action = 'phyto_ws_apply';
        data.nonce  = phytoWS.nonce;
        $.post( phytoWS.ajaxurl, data ).done( function ( res ) {
            $( '#phyto-ws-message' ).text( res.data.message ).css( 'color', res.success ? 'green' : 'red' );
            if ( res.success ) $( '#phyto-ws-apply-form' ).hide();
        } );
    } );
} );
