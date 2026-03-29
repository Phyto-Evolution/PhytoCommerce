/* Phyto KYC — front.js */
jQuery( function ( $ ) {
    function kycSubmit( formId, action, msgId ) {
        $( formId ).on( 'submit', function ( e ) {
            e.preventDefault();
            var field = $( this ).find( 'input[name]' );
            $.post( phytoKYC.ajaxurl, {
                action: action,
                nonce:  phytoKYC.nonce,
                [ field.attr( 'name' ) ]: field.val().toUpperCase(),
            } ).done( function ( res ) {
                $( msgId ).text( res.data.message ).css( 'color', res.success ? 'green' : 'red' );
                if ( res.success ) setTimeout( function () { location.reload(); }, 1500 );
            } );
        } );
    }
    kycSubmit( '#phyto-kyc-pan-form', 'phyto_kyc_submit_pan', '#phyto-kyc-pan-message' );
    kycSubmit( '#phyto-kyc-gst-form', 'phyto_kyc_submit_gst', '#phyto-kyc-gst-message' );
} );
