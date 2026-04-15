/* Phyto TC Batch Tracker — admin.js */
jQuery( function ( $ ) {
    // Suggest batch code from species name
    $( '#phyto-tcb-suggest' ).on( 'click', function () {
        var species = $( '#phyto-tcb-species' ).val();
        $.post( phytoTCB.ajaxurl, { action: 'phyto_tcb_suggest', nonce: phytoTCB.nonce, species: species } )
            .done( function ( res ) {
                if ( res.success ) $( '#phyto-tcb-code' ).val( res.data.code );
            } );
    } );

    // Create batch
    $( '#phyto-tcb-create-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var data = $( this ).serializeArray().reduce( function (o,f){ o[f.name]=f.value; return o; }, {} );
        data.action = 'phyto_tcb_create';
        data.nonce  = phytoTCB.nonce;
        $.post( phytoTCB.ajaxurl, data ).done( function ( res ) {
            var $r = $( '#phyto-tcb-result' );
            if ( res.success ) {
                $r.html( '<div class="notice notice-success"><p>' + res.data.message + ' (ID: ' + res.data.id_batch + ')</p></div>' );
                $( '#phyto-tcb-create-form' )[0].reset();
            } else {
                $r.html( '<div class="notice notice-error"><p>' + res.data.message + '</p></div>' );
            }
        } );
    } );
} );
