/* Phyto Quick Add — admin.js */
jQuery( function ( $ ) {
    // Create product
    $( '#phyto-qa-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var data = $( this ).serializeArray().reduce( function (o,f){ o[f.name]=f.value; return o; }, {} );
        data.categories = $( '.phyto-qa-cat-select' ).val();
        data.action = 'phyto_qa_create_product';
        data.nonce  = phytoQA.nonce;
        $.post( phytoQA.ajaxurl, data ).done( function ( res ) {
            var $r = $( '#phyto-qa-result' );
            if ( res.success ) {
                $r.html( '<div class="notice notice-success"><p>' + res.data.message + ' — <a href="' + res.data.edit_url + '">' + 'Edit product</a></p></div>' );
                $( '#phyto-qa-form' )[0].reset();
            } else {
                $r.html( '<div class="notice notice-error"><p>' + res.data.message + '</p></div>' );
            }
        } );
    } );

    // AI Description
    $( '#phyto-qa-ai-btn' ).on( 'click', function () {
        var name  = $( '#phyto-qa-name' ).val();
        var notes = $( '#phyto-qa-notes' ).val();
        if ( ! name ) { alert( 'Enter a product name first.' ); return; }
        $( this ).prop( 'disabled', true ).text( 'Generating…' );
        $.post( phytoQA.ajaxurl, { action: 'phyto_qa_ai_description', nonce: phytoQA.nonce, name: name, notes: notes } )
            .done( function ( res ) {
                if ( res.success ) $( '#phyto-qa-description' ).val( res.data.description );
                else alert( res.data.message );
            } )
            .always( function () { $( '#phyto-qa-ai-btn' ).prop( 'disabled', false ).text( '✦ AI Description' ); } );
    } );

    // Import taxonomy pack
    $( document ).on( 'click', '.phyto-qa-import-btn', function () {
        var $btn    = $( this );
        var $status = $btn.siblings( '.phyto-qa-import-status' );
        var pack    = $btn.data( 'pack' );
        $btn.prop( 'disabled', true );
        $status.text( 'Importing…' );
        $.post( phytoQA.ajaxurl, { action: 'phyto_qa_import_pack', nonce: phytoQA.nonce, pack_file: pack } )
            .done( function ( res ) {
                if ( res.success ) {
                    $status.css( 'color', 'green' ).text( '✓ ' + res.data.imported + ' terms imported' );
                } else {
                    $status.css( 'color', 'red' ).text( '✗ ' + ( res.data.error || 'Failed' ) );
                    $btn.prop( 'disabled', false );
                }
            } );
    } );
} );
