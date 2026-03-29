/* global wp, jQuery */
(function ($) {
    'use strict';

    // WP Media uploader for document attachment field
    var mediaUploader;

    $('#pps-upload-btn').on('click', function (e) {
        e.preventDefault();

        if ( mediaUploader ) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title:    'Select or Upload Document',
            button:   { text: 'Use this file' },
            library:  { type: [ 'application/pdf', 'image' ] },
            multiple: false
        });

        mediaUploader.on('select', function () {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#pps-attachment-id').val( attachment.id );
            $('#pps-attachment-label').text( attachment.filename || attachment.title );
        });

        mediaUploader.open();
    });

}(jQuery));
