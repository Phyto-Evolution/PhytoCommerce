/**
 * PhytoImageSec — admin batch watermark UI.
 *
 * Drives the progress bar on the module configuration page.
 * Chunks requests to avoid PHP time-out on large catalogues.
 */
(function ($) {
    'use strict';

    var $btn      = $('#phyto-batch-start');
    var $progress = $('#phyto-batch-progress');
    var $bar      = $('#phyto-batch-bar');
    var $status   = $('#phyto-batch-status');
    var ajaxUrl   = $btn.data('ajax-url');
    var csrfToken = $btn.data('token');
    var total     = 0;

    if (!$btn.length) {
        return;
    }

    // ── Init → get total image count ─────────────────────────────────────────
    function init() {
        $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i> Counting images…');

        $.post(ajaxUrl, { action: 'Init', ajax: 1, token: csrfToken })
            .done(function (resp) {
                if (!resp.success) {
                    showError(resp.error || 'Initialisation failed.');
                    resetButton();
                    return;
                }

                total = resp.total;

                if (total === 0) {
                    showError('No product images found in the catalogue.');
                    resetButton();
                    return;
                }

                $progress.show();
                updateBar(0);
                $status.text('Starting… 0 / ' + total + ' images processed.');
                processChunk(0);
            })
            .fail(function () {
                showError('Could not reach the server. Check your network and try again.');
                resetButton();
            });
    }

    // ── Recursive chunk processor ─────────────────────────────────────────────
    function processChunk(offset) {
        $.post(ajaxUrl, { action: 'Chunk', offset: offset, ajax: 1, token: csrfToken })
            .done(function (resp) {
                if (!resp.success) {
                    showError(resp.error || 'Chunk processing failed at offset ' + offset + '.');
                    resetButton();
                    return;
                }

                var done = resp.offset;
                updateBar(done);
                $status.text(done + ' / ' + total + ' images processed…');

                if (resp.done) {
                    onComplete(done);
                } else {
                    processChunk(resp.offset);
                }
            })
            .fail(function () {
                showError('Server error at offset ' + offset + '. You can retry — already-processed images are safe.');
                resetButton();
            });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function updateBar(done) {
        var pct = total > 0 ? Math.round((done / total) * 100) : 0;
        $bar.css('width', pct + '%').text(pct + '%');
    }

    function onComplete(done) {
        $bar.removeClass('active').addClass('progress-bar-success');
        $status.html(
            '<strong class="text-success">'
            + done + ' images watermarked successfully.'
            + '</strong>'
        );
        $btn.prop('disabled', false).html('<i class="icon-check"></i> Done — Run Again?');
    }

    function showError(msg) {
        $progress.show();
        $status.html('<span class="text-danger"><i class="icon-warning-sign"></i> ' + msg + '</span>');
    }

    function resetButton() {
        $btn.prop('disabled', false).html('<i class="icon-play"></i> Start Batch Watermark');
    }

    // ── Bind button ───────────────────────────────────────────────────────────
    $btn.on('click', function (e) {
        e.preventDefault();
        $bar.removeClass('progress-bar-success').addClass('active').css('width', '0%').text('0%');
        $status.text('');
        init();
    });

}(jQuery));
