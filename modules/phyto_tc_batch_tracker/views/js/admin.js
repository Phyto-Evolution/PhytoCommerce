/* phyto_tc_batch_tracker — admin JS for product batch linking tab */
(function ($) {
    'use strict';

    function showMsg(type, text) {
        var $msg = $('#phyto-tc-ajax-msg');
        $msg.html('<div class="alert alert-' + type + '">' + $('<div/>').text(text).html() + '</div>').show();
        setTimeout(function () { $msg.fadeOut(400); }, 4000);
    }

    function updateLinkedSummary(batch) {
        if (!batch) {
            $('#phyto-tc-linked-summary').addClass('hidden');
            $('#phyto-tc-no-link-notice').removeClass('hidden');
            return;
        }
        $('#phyto-tc-badge-code').text(batch.batch_code);
        $('#phyto-tc-badge-species').text(batch.species_name);
        $('#phyto-tc-badge-gen').text(batch.generation_label || batch.generation);
        $('#phyto-tc-units-remaining').text(batch.units_remaining);
        $('#phyto-tc-status').text(batch.batch_status);
        $('#phyto-tc-deflask-date').text(batch.date_deflask || '—');
        $('#phyto-tc-linked-summary').removeClass('hidden');
        $('#phyto-tc-no-link-notice').addClass('hidden');
    }

    $(document).ready(function () {

        /* ── Link batch ───────────────────────────────────────────── */
        $('#phyto-tc-link-btn').on('click', function () {
            var $btn      = $(this);
            var idProduct = $btn.data('id-product');
            var ajaxUrl   = $btn.data('ajax-url');
            var idBatch   = parseInt($('#phyto-tc-batch-select').val(), 10);

            if (!idBatch) {
                showMsg('warning', phytoTcI18n.selectBatch);
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'linkBatch',
                ajax: 1,
                id_product: idProduct,
                id_batch: idBatch
            }, 'json')
            .done(function (res) {
                if (res && res.success) {
                    updateLinkedSummary(res.data);
                    showMsg('success', res.message || phytoTcI18n.linked);
                } else {
                    showMsg('danger', (res && res.message) ? res.message : phytoTcI18n.error);
                }
            })
            .fail(function () {
                showMsg('danger', phytoTcI18n.error);
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
        });

        /* ── Unlink batch ─────────────────────────────────────────── */
        $('#phyto-tc-unlink-btn').on('click', function () {
            var $btn      = $(this);
            var idProduct = $btn.data('id-product');
            var ajaxUrl   = $btn.data('ajax-url');

            if (!confirm(phytoTcI18n.confirmUnlink)) {
                return;
            }

            $btn.prop('disabled', true);

            $.post(ajaxUrl, {
                action: 'unlinkBatch',
                ajax: 1,
                id_product: idProduct
            }, 'json')
            .done(function (res) {
                if (res && res.success) {
                    updateLinkedSummary(null);
                    $('#phyto-tc-batch-select').val('');
                    showMsg('success', res.message || phytoTcI18n.unlinked);
                } else {
                    showMsg('danger', (res && res.message) ? res.message : phytoTcI18n.error);
                }
            })
            .fail(function () {
                showMsg('danger', phytoTcI18n.error);
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
        });

        /* ── Preview details on dropdown change ───────────────────── */
        $('#phyto-tc-batch-select').on('change', function () {
            var idBatch   = parseInt($(this).val(), 10);
            var ajaxUrl   = $('#phyto-tc-link-btn').data('ajax-url');

            if (!idBatch) {
                return;
            }

            $.post(ajaxUrl, {
                action: 'getBatch',
                ajax: 1,
                id_batch: idBatch
            }, 'json')
            .done(function (res) {
                if (res && res.success) {
                    updateLinkedSummary(res.data);
                }
            });
        });

    });

}(jQuery));

/* i18n strings injected by PHP hookDisplayBackOfficeHeader */
var phytoTcI18n = phytoTcI18n || {
    selectBatch:   'Please select a batch.',
    linked:        'Batch linked.',
    unlinked:      'Batch unlinked.',
    confirmUnlink: 'Remove the batch link for this product?',
    error:         'An error occurred. Please try again.'
};
