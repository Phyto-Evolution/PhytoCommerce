/* global jQuery */
(function ($) {
    'use strict';

    var $body  = $('body');
    var $table = $('#phyto-slots-table');
    var $tbody = $('#phyto-slots-body');
    var tmpl   = $('#phyto-slot-template').html() || '';
    var idx    = $tbody.find('.phyto-slot-row').length;

    // ── Add slot row ──────────────────────────────────────────────────────────
    $('#phyto-add-slot').on('click', function () {
        var html = tmpl.replace(/__IDX__/g, idx++);
        $tbody.append(html);
    });

    // ── Remove slot row (delegated) ───────────────────────────────────────────
    $body.on('click', '.phyto-remove-slot', function () {
        $(this).closest('tr').remove();
        reindex();
    });

    // ── Reindex name attributes after removal so PHP receives a dense array ──
    function reindex() {
        $tbody.find('.phyto-slot-row').each(function (i) {
            $(this).find('[name]').each(function () {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/slots\[\d+\]/, 'slots[' + i + ']'));
            });
        });
        idx = $tbody.find('.phyto-slot-row').length;
    }

}(jQuery));
