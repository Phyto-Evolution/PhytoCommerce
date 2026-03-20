/**
 * Phyto TC Cost Calculator — Live Calculation Logic
 *
 * Wrapped in an IIFE to avoid polluting the global namespace.
 * All calculations happen client-side; no server round-trip is required
 * until the user explicitly submits the "Save Estimate" form.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */
(function ($) {
    'use strict';

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------

    /** Maximum number of additive rows the user can add */
    var MAX_ADDITIVES = 3;

    /** Counter used to generate unique IDs for additive rows */
    var additiveCount = 0;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Read a numeric value from an input element; returns 0 for blank/NaN.
     *
     * @param  {string} selector  jQuery selector
     * @return {number}
     */
    function num(selector) {
        var v = parseFloat($(selector).val());
        return isNaN(v) ? 0 : v;
    }

    /**
     * Format a number as an Indian-Rupee string: ₹1,234.56
     *
     * @param  {number} value
     * @return {string}
     */
    function formatInr(value) {
        return '\u20b9' + value.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Clamp a value between min and max.
     *
     * @param  {number} val
     * @param  {number} min
     * @param  {number} max
     * @return {number}
     */
    function clamp(val, min, max) {
        return Math.min(Math.max(val, min), max);
    }

    // -------------------------------------------------------------------------
    // Core Calculation
    // -------------------------------------------------------------------------

    /**
     * Run all calculations and return an object containing every intermediate
     * and final value.
     *
     * @return {Object}
     */
    function calculate() {
        // ── Substrate ──────────────────────────────────────────────────────────
        var ms_qty            = num('#ms_qty');
        var ms_price          = num('#ms_price');
        var agar_qty          = num('#agar_qty');
        var agar_price        = num('#agar_price');
        var sucrose_qty       = num('#sucrose_qty');
        var sucrose_price_kg  = num('#sucrose_price_kg');

        var additive_total = 0;
        $('.phyto-calc-additive-row').each(function () {
            var qty  = parseFloat($(this).find('.additive-qty').val())  || 0;
            var cost = parseFloat($(this).find('.additive-cost').val()) || 0;
            additive_total += qty * cost;
        });

        var substrate_cost = (ms_qty * ms_price)
            + (agar_qty * agar_price)
            + (sucrose_qty * (sucrose_price_kg / 1000))
            + additive_total;

        // ── Overhead ───────────────────────────────────────────────────────────
        var autoclave_cycles         = num('#autoclave_cycles');
        var autoclave_cost_per_cycle = num('#autoclave_cost_per_cycle');
        var electricity              = num('#electricity');
        var lf_hours                 = num('#lf_hours');
        var lf_rate                  = num('#lf_rate');
        var glassware                = num('#glassware');

        var overhead_cost = (autoclave_cycles * autoclave_cost_per_cycle)
            + electricity
            + (lf_hours * lf_rate)
            + glassware;

        // ── Labor ──────────────────────────────────────────────────────────────
        var person_hours = num('#person_hours');
        var labor_rate   = num('#labor_rate');
        var labor_cost   = person_hours * labor_rate;

        // ── Totals ─────────────────────────────────────────────────────────────
        var total_batch_cost = substrate_cost + overhead_cost + labor_cost;

        // ── Batch outputs ──────────────────────────────────────────────────────
        var total_explants  = Math.max(1, Math.floor(num('#total_explants')));
        var rejection_range = num('#rejection_rate');                 // 0–100
        var rejection_rate  = rejection_range / 100;                  // 0.0–1.0
        var sellable_units  = Math.floor(total_explants * (1 - rejection_rate));
        sellable_units      = Math.max(0, sellable_units);

        // ── Per-unit economics ─────────────────────────────────────────────────
        var cost_per_unit  = sellable_units > 0 ? total_batch_cost / sellable_units : 0;
        var packaging_cost    = num('#packaging_cost');
        var shipping_material = num('#shipping_material');
        var unit_extra        = packaging_cost + shipping_material;
        var full_unit_cost    = cost_per_unit + unit_extra;

        // ── Pricing ────────────────────────────────────────────────────────────
        var target_margin_pct = num('#target_margin');                // 20–80
        var margin            = target_margin_pct / 100;              // 0.20–0.80
        var suggested_price   = (margin < 1)
            ? full_unit_cost / (1 - margin)
            : full_unit_cost;
        var breakeven_price   = full_unit_cost;
        var profit_per_batch  = (suggested_price - full_unit_cost) * sellable_units;

        return {
            substrate_cost:   substrate_cost,
            overhead_cost:    overhead_cost,
            labor_cost:       labor_cost,
            total_batch_cost: total_batch_cost,
            sellable_units:   sellable_units,
            cost_per_unit:    cost_per_unit,
            full_unit_cost:   full_unit_cost,
            suggested_price:  suggested_price,
            breakeven_price:  breakeven_price,
            profit_per_batch: profit_per_batch,
            rejection_range:  rejection_range,
            target_margin_pct: target_margin_pct
        };
    }

    // -------------------------------------------------------------------------
    // Display updater
    // -------------------------------------------------------------------------

    /**
     * Re-run the calculation and push updated values into the results panel
     * and bar chart.
     */
    function updateDisplay() {
        var r = calculate();

        // ── Result table ───────────────────────────────────────────────────────
        $('#res_total_batch_cost').text(formatInr(r.total_batch_cost));
        $('#res_substrate_cost').text(formatInr(r.substrate_cost));
        $('#res_overhead_cost').text(formatInr(r.overhead_cost));
        $('#res_labor_cost').text(formatInr(r.labor_cost));
        $('#res_cost_per_unit').text(formatInr(r.cost_per_unit));
        $('#res_breakeven').text(formatInr(r.breakeven_price));
        $('#res_suggested_price').text(formatInr(r.suggested_price));
        $('#res_profit_batch').text(formatInr(r.profit_per_batch));

        // ── Sellable units reflected back into disabled field ──────────────────
        $('#sellable_units').val(r.sellable_units);

        // ── Range display labels ───────────────────────────────────────────────
        $('#rejection_rate_display').text(r.rejection_range + '%');
        $('#target_margin_display').text(r.target_margin_pct + '%');

        // ── Bar chart ──────────────────────────────────────────────────────────
        updateBarChart(r);
    }

    /**
     * Update the inline CSS bar widths and percentage labels.
     *
     * @param {Object} r  Results object from calculate()
     */
    function updateBarChart(r) {
        var total = r.total_batch_cost;

        if (total <= 0) {
            // Zero state: collapse all bars
            setBar('substrate', 0, 0);
            setBar('overhead',  0, 0);
            setBar('labor',     0, 0);
            setBar('margin',    0, 0);
            return;
        }

        // The "revenue" reference for proportion is suggested_price * sellable_units
        // We use total_batch_cost as the 100% baseline for cost bars,
        // then add a margin bar proportional to profit vs revenue.
        var subPct     = clamp((r.substrate_cost / total) * 100, 0, 100);
        var ovhPct     = clamp((r.overhead_cost  / total) * 100, 0, 100);
        var labPct     = clamp((r.labor_cost     / total) * 100, 0, 100);

        // Margin bar relative to total revenue (suggested price * sellable units)
        var revenue    = r.suggested_price * r.sellable_units;
        var marginPct  = revenue > 0
            ? clamp((r.profit_per_batch / revenue) * 100, 0, 100)
            : 0;

        setBar('substrate', subPct,    r.substrate_cost);
        setBar('overhead',  ovhPct,    r.overhead_cost);
        setBar('labor',     labPct,    r.labor_cost);
        setBar('margin',    marginPct, r.profit_per_batch);
    }

    /**
     * Set a single bar's width and percentage label.
     *
     * @param {string} key      Bar key (matches IDs: bar_{key}, bar_{key}_pct)
     * @param {number} pct      Percentage width 0–100
     * @param {number} value    Monetary value for title tooltip
     */
    function setBar(key, pct, value) {
        var pctRounded = Math.round(pct);
        $('#bar_' + key).css('width', pctRounded + '%')
            .attr('title', formatInr(value));
        $('#bar_' + key + '_pct').text(pctRounded + '%');
    }

    // -------------------------------------------------------------------------
    // Additive rows
    // -------------------------------------------------------------------------

    /**
     * Append a new additive input row to the additives container.
     */
    function addAdditiveRow() {
        if (additiveCount >= MAX_ADDITIVES) {
            return;
        }

        additiveCount++;
        var idx = additiveCount;

        var row = $(
            '<div class="form-group row phyto-calc-input-row phyto-calc-additive-row" data-idx="' + idx + '">' +
                '<label class="col-sm-4 control-label">' +
                    '<input type="text" class="form-control additive-name" ' +
                           'placeholder="Additive name" maxlength="100">' +
                '</label>' +
                '<div class="col-sm-3">' +
                    '<div class="input-group">' +
                        '<input type="number" class="form-control phyto-calc-input additive-qty" ' +
                               'value="0" min="0" step="0.01" placeholder="0">' +
                        '<span class="input-group-addon">units</span>' +
                    '</div>' +
                    '<p class="help-block">Qty</p>' +
                '</div>' +
                '<div class="col-sm-3">' +
                    '<div class="input-group">' +
                        '<span class="input-group-addon">\u20b9</span>' +
                        '<input type="number" class="form-control phyto-calc-input additive-cost" ' +
                               'value="0" min="0" step="0.01" placeholder="0.00">' +
                        '<span class="input-group-addon">/unit</span>' +
                    '</div>' +
                    '<p class="help-block">Unit cost</p>' +
                '</div>' +
                '<div class="col-sm-2">' +
                    '<button type="button" class="btn btn-danger btn-sm phyto-calc-remove-additive">' +
                        '<i class="icon-trash"></i>' +
                    '</button>' +
                '</div>' +
            '</div>'
        );

        $('#phyto-additives-container').append(row);

        // Re-evaluate add button visibility
        if (additiveCount >= MAX_ADDITIVES) {
            $('#phyto-add-additive').prop('disabled', true)
                .attr('title', 'Maximum ' + MAX_ADDITIVES + ' additives allowed');
        }

        // Bind live calculation to new inputs
        row.find('.phyto-calc-input').on('input change', updateDisplay);

        updateDisplay();
    }

    /**
     * Remove an additive row.
     *
     * @param {jQuery} $btn  The remove button that was clicked
     */
    function removeAdditiveRow($btn) {
        $btn.closest('.phyto-calc-additive-row').remove();
        additiveCount--;

        // Re-enable the add button if we dropped below the limit
        if (additiveCount < MAX_ADDITIVES) {
            $('#phyto-add-additive').prop('disabled', false).removeAttr('title');
        }

        updateDisplay();
    }

    // -------------------------------------------------------------------------
    // JSON serialisation (for save)
    // -------------------------------------------------------------------------

    /**
     * Collect all current form inputs into a plain object and serialise to JSON.
     *
     * @return {string}  JSON string of input values
     */
    function serializeInputs() {
        var additives = [];
        $('.phyto-calc-additive-row').each(function () {
            additives.push({
                name: $(this).find('.additive-name').val() || '',
                qty:  parseFloat($(this).find('.additive-qty').val())  || 0,
                cost: parseFloat($(this).find('.additive-cost').val()) || 0
            });
        });

        return JSON.stringify({
            ms_qty:                    num('#ms_qty'),
            ms_price:                  num('#ms_price'),
            agar_qty:                  num('#agar_qty'),
            agar_price:                num('#agar_price'),
            sucrose_qty:               num('#sucrose_qty'),
            sucrose_price_kg:          num('#sucrose_price_kg'),
            additives:                 additives,
            autoclave_cycles:          num('#autoclave_cycles'),
            autoclave_cost_per_cycle:  num('#autoclave_cost_per_cycle'),
            electricity:               num('#electricity'),
            lf_hours:                  num('#lf_hours'),
            lf_rate:                   num('#lf_rate'),
            glassware:                 num('#glassware'),
            person_hours:              num('#person_hours'),
            labor_rate:                num('#labor_rate'),
            total_explants:            num('#total_explants'),
            rejection_rate:            num('#rejection_rate'),
            target_margin:             num('#target_margin'),
            packaging_cost:            num('#packaging_cost'),
            shipping_material:         num('#shipping_material')
        });
    }

    /**
     * Serialise the latest calculated results to JSON.
     *
     * @return {string}
     */
    function serializeResults() {
        var r = calculate();
        return JSON.stringify({
            substrate_cost:   parseFloat(r.substrate_cost.toFixed(4)),
            overhead_cost:    parseFloat(r.overhead_cost.toFixed(4)),
            labor_cost:       parseFloat(r.labor_cost.toFixed(4)),
            total_batch_cost: parseFloat(r.total_batch_cost.toFixed(4)),
            sellable_units:   r.sellable_units,
            cost_per_unit:    parseFloat(r.cost_per_unit.toFixed(4)),
            breakeven_price:  parseFloat(r.breakeven_price.toFixed(4)),
            suggested_price:  parseFloat(r.suggested_price.toFixed(4)),
            profit_per_batch: parseFloat(r.profit_per_batch.toFixed(4))
        });
    }

    // -------------------------------------------------------------------------
    // Load saved estimate into the form
    // -------------------------------------------------------------------------

    /**
     * Populate all calculator fields from a previously saved inputs JSON blob.
     *
     * @param {string} jsonStr  Raw JSON from the saved estimate
     */
    function loadEstimate(jsonStr) {
        var data;
        try {
            data = JSON.parse(jsonStr);
        } catch (e) {
            alert('Could not load estimate: invalid data.');
            return;
        }

        // Simple fields
        var fieldMap = {
            '#ms_qty':                   'ms_qty',
            '#ms_price':                 'ms_price',
            '#agar_qty':                 'agar_qty',
            '#agar_price':               'agar_price',
            '#sucrose_qty':              'sucrose_qty',
            '#sucrose_price_kg':         'sucrose_price_kg',
            '#autoclave_cycles':         'autoclave_cycles',
            '#autoclave_cost_per_cycle': 'autoclave_cost_per_cycle',
            '#electricity':              'electricity',
            '#lf_hours':                 'lf_hours',
            '#lf_rate':                  'lf_rate',
            '#glassware':                'glassware',
            '#person_hours':             'person_hours',
            '#labor_rate':               'labor_rate',
            '#total_explants':           'total_explants',
            '#rejection_rate':           'rejection_rate',
            '#target_margin':            'target_margin',
            '#packaging_cost':           'packaging_cost',
            '#shipping_material':        'shipping_material'
        };

        $.each(fieldMap, function (selector, key) {
            if (typeof data[key] !== 'undefined') {
                $(selector).val(data[key]).trigger('change');
            }
        });

        // Rebuild additive rows
        $('#phyto-additives-container').empty();
        additiveCount = 0;
        $('#phyto-add-additive').prop('disabled', false).removeAttr('title');

        if (Array.isArray(data.additives)) {
            $.each(data.additives, function (i, additive) {
                if (i >= MAX_ADDITIVES) {
                    return false; // break
                }
                addAdditiveRow();
                var $lastRow = $('.phyto-calc-additive-row').last();
                $lastRow.find('.additive-name').val(additive.name || '');
                $lastRow.find('.additive-qty').val(additive.qty  || 0);
                $lastRow.find('.additive-cost').val(additive.cost || 0);
            });
        }

        updateDisplay();

        // Scroll to calculator top
        $('html, body').animate({
            scrollTop: $('#phyto-calc-form').offset().top - 80
        }, 400);
    }

    // -------------------------------------------------------------------------
    // Document ready — bind all events
    // -------------------------------------------------------------------------

    $(document).ready(function () {

        // ── Live recalculation on any calculator input change ──────────────────
        $(document).on(
            'input change',
            '.phyto-calc-input, .phyto-calc-range',
            updateDisplay
        );

        // ── Add additive row ───────────────────────────────────────────────────
        $('#phyto-add-additive').on('click', function (e) {
            e.preventDefault();
            addAdditiveRow();
        });

        // ── Remove additive row (delegated) ───────────────────────────────────
        $(document).on('click', '.phyto-calc-remove-additive', function () {
            removeAdditiveRow($(this));
        });

        // ── Load saved estimate into form ──────────────────────────────────────
        $(document).on('click', '.phyto-calc-load-btn', function (e) {
            e.preventDefault();
            var jsonStr = $(this).attr('data-inputs');
            if (
                jsonStr
                && confirm('Load this estimate into the calculator? Current values will be overwritten.')
            ) {
                loadEstimate(jsonStr);
            }
        });

        // ── Pre-submit: serialise inputs + results into hidden fields ──────────
        $('#phyto-calc-form').on('submit', function (e) {
            var label = $.trim($('#estimate_label').val());
            if (!label) {
                e.preventDefault();
                alert('Please enter an estimate label before saving.');
                $('#estimate_label').focus();
                return false;
            }

            $('#inputs_json').val(serializeInputs());
            $('#results_json').val(serializeResults());
        });

        // ── Initial render ─────────────────────────────────────────────────────
        updateDisplay();
    });

}(jQuery));
