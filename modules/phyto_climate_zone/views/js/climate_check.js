/**
 * Phyto Climate Zone — Front Widget (v2)
 *
 * Reads config from .phyto-climate-widget[data-*] attributes.
 * On check: shows PCC-IN zone code, climate summary card, monthly bar chart
 * (temperature or humidity), frost/rain/humidity warnings, outdoor notes.
 *
 * Dependencies: none — vanilla JS, ES5-compatible.
 */

(function () {
    'use strict';

    var MONTH_KEYS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    // ── Bar chart ─────────────────────────────────────────────────────────────

    /**
     * Build/replace bar chart inside chartEl.
     * @param {HTMLElement} chartEl
     * @param {number[]}    values      12 values
     * @param {string[]}    monthLabels 12 month strings
     * @param {string}      mode        'temp' | 'humidity'
     */
    function renderChart(chartEl, values, monthLabels, mode) {
        var isTemp    = mode !== 'humidity';
        var minVal    = isTemp ? Math.min.apply(null, values) : 0;
        var maxVal    = isTemp ? Math.max.apply(null, values) : 100;
        var range     = maxVal - minVal || 1;
        var baseColor = isTemp ? '#4caf50' : '#42a5f5';
        var html      = '';

        for (var i = 0; i < 12; i++) {
            var val    = values[i];
            var pct    = Math.round(((val - minVal) / range) * 100);
            var label  = monthLabels[i] || MONTH_KEYS[i];
            var suffix = isTemp ? '°C' : '%';
            html += '<div class="phyto-chart-col">'
                  +   '<div class="phyto-chart-bar-wrap">'
                  +     '<div class="phyto-chart-bar" style="height:' + pct + '%;background:' + baseColor + ';" title="' + label + ': ' + val + suffix + '"></div>'
                  +   '</div>'
                  +   '<div class="phyto-chart-label">' + label.charAt(0) + '</div>'
                  +   '<div class="phyto-chart-value">' + val + '</div>'
                  + '</div>';
        }

        chartEl.innerHTML = html;
    }

    // ── Result renderer ───────────────────────────────────────────────────────

    function showResult(widget, data) {
        var resultArea   = widget.querySelector('.phyto-climate-result');
        var verdictEl    = widget.querySelector('.phyto-climate-verdict');
        var codeEl       = widget.querySelector('.phyto-climate-zone-code');
        var nameEl       = widget.querySelector('.phyto-climate-zone-name');
        var iconEl       = widget.querySelector('.phyto-climate-verdict-icon');
        var messageEl    = widget.querySelector('.phyto-climate-message');
        var warningsList = widget.querySelector('.phyto-climate-warnings');
        var notesEl      = widget.querySelector('.phyto-climate-outdoor-notes');
        var chartWrap    = widget.querySelector('.phyto-climate-chart-wrap');
        var chartEl      = widget.querySelector('.phyto-climate-chart');
        var metaEl       = widget.querySelector('.phyto-climate-zone-meta');
        var legendTemp   = widget.querySelector('.phyto-legend-temp');
        var legendHum    = widget.querySelector('.phyto-legend-humidity');

        // Reset state classes
        resultArea.classList.remove('suitable', 'unsuitable', 'unknown');

        if (data.error) {
            resultArea.classList.add('unknown');
            codeEl.textContent  = '';
            nameEl.textContent  = '';
            iconEl.textContent  = '⚠';
            messageEl.textContent = data.error;
            warningsList.style.display = 'none';
            chartWrap.style.display    = 'none';
            notesEl.style.display      = 'none';
            resultArea.style.display   = 'block';
            return;
        }

        // Verdict class + icon
        if (data.zone === null) {
            resultArea.classList.add('unknown');
            iconEl.textContent = '?';
        } else if (data.suitable) {
            resultArea.classList.add('suitable');
            iconEl.textContent = '✔';
        } else {
            resultArea.classList.add('unsuitable');
            iconEl.textContent = '✘';
        }

        // Zone code + name
        codeEl.textContent = data.zone_code ? data.zone_code : '';
        var zd = data.zone_data;
        nameEl.textContent = zd ? zd.label : (data.zone_code || '');
        messageEl.textContent = data.message || '';

        // Warnings
        var warnings = data.warnings || [];
        if (warnings.length) {
            var wHtml = '';
            for (var w = 0; w < warnings.length; w++) {
                wHtml += '<li>' + escapeHtml(warnings[w]) + '</li>';
            }
            warningsList.innerHTML    = wHtml;
            warningsList.style.display = 'block';
        } else {
            warningsList.style.display = 'none';
        }

        // Outdoor notes
        if (data.outdoor_notes) {
            notesEl.innerHTML      = '<strong>Growing notes:</strong> ' + escapeHtml(data.outdoor_notes);
            notesEl.style.display  = 'block';
        } else {
            notesEl.style.display = 'none';
        }

        // Monthly chart + zone meta
        if (zd && zd.monthly_temp) {
            var months = zd.months || MONTH_KEYS;
            renderChart(chartEl, zd.monthly_temp, months, 'temp');

            // Toggle handlers (temp / humidity)
            var showTempLink = widget.querySelector('.phyto-climate-show-temp');
            var showHumLink  = widget.querySelector('.phyto-climate-show-humidity');
            var currentMode  = 'temp';

            if (showTempLink) {
                showTempLink.onclick = function (e) {
                    e.preventDefault();
                    if (currentMode === 'temp') { return; }
                    currentMode = 'temp';
                    renderChart(chartEl, zd.monthly_temp, months, 'temp');
                    showTempLink.classList.add('phyto-climate-active');
                    showHumLink.classList.remove('phyto-climate-active');
                    legendTemp.style.display = '';
                    legendHum.style.display  = 'none';
                };
            }
            if (showHumLink) {
                showHumLink.onclick = function (e) {
                    e.preventDefault();
                    if (currentMode === 'humidity') { return; }
                    currentMode = 'humidity';
                    renderChart(chartEl, zd.monthly_humidity, months, 'humidity');
                    showHumLink.classList.add('phyto-climate-active');
                    showTempLink.classList.remove('phyto-climate-active');
                    legendTemp.style.display = 'none';
                    legendHum.style.display  = '';
                };
            }

            // Zone meta
            var metaHtml = '';
            if (zd.example_cities && zd.example_cities.length) {
                metaHtml += '<span class="phyto-meta-cities">📍 ' + escapeHtml(zd.example_cities.join(', ')) + '</span>';
            }
            metaHtml += zd.frost_risk
                ? ' <span class="phyto-meta-frost phyto-meta-bad">❄ Frost risk</span>'
                : ' <span class="phyto-meta-frost phyto-meta-ok">✔ Frost-free</span>';
            if (zd.monsoon_months && zd.monsoon_months.length) {
                var mNames = zd.monsoon_months.map(function (m) { return MONTH_KEYS[m - 1]; });
                metaHtml += ' <span class="phyto-meta-monsoon">🌧 Monsoon: ' + escapeHtml(mNames.join('–')) + '</span>';
            }
            metaEl.innerHTML   = metaHtml;
            chartWrap.style.display = 'block';
        } else {
            chartWrap.style.display = 'none';
        }

        resultArea.style.display = 'block';
    }

    // ── Widget init ───────────────────────────────────────────────────────────

    function initWidget(widget) {
        var checkUrl  = widget.getAttribute('data-check-url');
        var idProduct = widget.getAttribute('data-id-product');

        var pincodeInput = widget.querySelector('.phyto-climate-pincode-input');
        var checkBtn     = widget.querySelector('.phyto-climate-check-btn');
        var inputError   = widget.querySelector('.phyto-climate-input-error');
        var btnText      = checkBtn.querySelector('.phyto-climate-btn-text');
        var btnLoading   = checkBtn.querySelector('.phyto-climate-btn-loading');

        function setLoading(on) {
            checkBtn.disabled        = on;
            btnText.style.display    = on ? 'none'   : '';
            btnLoading.style.display = on ? 'inline' : 'none';
        }

        function onCheck() {
            var pincode = pincodeInput.value.trim();
            if (!/^\d{6}$/.test(pincode)) {
                inputError.style.display = 'block';
                pincodeInput.focus();
                return;
            }

            inputError.style.display = 'none';
            setLoading(true);

            fetch(checkUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_product=' + encodeURIComponent(idProduct)
                    + '&pincode='   + encodeURIComponent(pincode)
            })
            .then(function (r) {
                if (!r.ok) { throw new Error('HTTP ' + r.status); }
                return r.json();
            })
            .then(function (json) { showResult(widget, json); })
            .catch(function (err) { showResult(widget, { error: 'Could not complete check. ' + err.message }); })
            .finally(function () { setLoading(false); });
        }

        checkBtn.addEventListener('click', onCheck);
        pincodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) { e.preventDefault(); onCheck(); }
        });
        pincodeInput.addEventListener('input', function () { inputError.style.display = 'none'; });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    function initAll() {
        var widgets = document.querySelectorAll('.phyto-climate-widget[data-check-url]');
        for (var i = 0; i < widgets.length; i++) { initWidget(widgets[i]); }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

}());
