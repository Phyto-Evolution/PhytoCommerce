/**
 * Phyto Climate Zone — Front-Office Climate Check Widget
 *
 * Reads configuration from the .phyto-climate-widget[data-*] attributes
 * injected by the Smarty template, so this file requires no inline variables.
 *
 * Behaviour:
 *  - Validates that the entered pincode is exactly 6 digits
 *  - POSTs id_product + pincode to the check front controller
 *  - Displays the zone name and a suitability message in the result area
 *  - Manages a loading state on the button during the request
 *
 * Dependencies: none (vanilla JS, ES5-compatible)
 */

(function () {
    'use strict';

    /**
     * Initialise a single climate widget.
     *
     * @param {HTMLElement} widget
     */
    function initWidget(widget) {
        var checkUrl   = widget.getAttribute('data-check-url');
        var idProduct  = widget.getAttribute('data-id-product');

        var pincodeInput  = widget.querySelector('#phyto-climate-pincode');
        var checkBtn      = widget.querySelector('#phyto-climate-check-btn');
        var inputError    = widget.querySelector('#phyto-climate-input-error');
        var resultArea    = widget.querySelector('#phyto-climate-result');
        var zoneLabel     = widget.querySelector('#phyto-climate-zone-label');
        var messageEl     = widget.querySelector('#phyto-climate-message');
        var btnText       = checkBtn.querySelector('.phyto-climate-btn-text');
        var btnLoading    = checkBtn.querySelector('.phyto-climate-btn-loading');

        /** Show/hide the inline validation error. */
        function showInputError(show) {
            inputError.style.display = show ? 'block' : 'none';
        }

        /** Put the button into the loading state. */
        function setLoading(loading) {
            checkBtn.disabled         = loading;
            btnText.style.display     = loading ? 'none'   : '';
            btnLoading.style.display  = loading ? 'inline' : 'none';
        }

        /**
         * Display the API result in the result area.
         *
         * @param {Object} data  JSON response from the check controller
         */
        function showResult(data) {
            // Remove previous state classes
            resultArea.classList.remove('suitable', 'unsuitable', 'unknown');

            if (data.error) {
                resultArea.classList.add('unknown');
                if (zoneLabel) { zoneLabel.textContent = ''; }
                messageEl.textContent = data.error;
                resultArea.style.display = 'block';
                return;
            }

            if (data.zone_label) {
                zoneLabel.textContent = data.zone_label;
            } else {
                zoneLabel.textContent = '';
            }

            messageEl.textContent = data.message || '';

            if (data.zone === null) {
                resultArea.classList.add('unknown');
            } else if (data.suitable) {
                resultArea.classList.add('suitable');
            } else {
                resultArea.classList.add('unsuitable');
            }

            resultArea.style.display = 'block';
        }

        /** Main click handler. */
        function onCheckClick() {
            var pincode = pincodeInput.value.trim();

            // Client-side format validation
            if (!/^\d{6}$/.test(pincode)) {
                showInputError(true);
                pincodeInput.focus();
                return;
            }

            showInputError(false);
            resultArea.style.display = 'none';
            setLoading(true);

            // Build POST body
            var body = 'id_product=' + encodeURIComponent(idProduct)
                     + '&pincode='    + encodeURIComponent(pincode);

            fetch(checkUrl, {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    body
            })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Server responded with status ' + response.status);
                }
                return response.json();
            })
            .then(function (json) {
                showResult(json);
            })
            .catch(function (err) {
                showResult({ error: 'Could not complete the check. Please try again. (' + err.message + ')' });
            })
            .finally(function () {
                setLoading(false);
            });
        }

        // Attach event listeners
        checkBtn.addEventListener('click', onCheckClick);

        // Allow pressing Enter inside the pincode input to trigger the check
        pincodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                onCheckClick();
            }
        });

        // Clear the error on input
        pincodeInput.addEventListener('input', function () {
            showInputError(false);
        });
    }

    /**
     * Initialise all widgets present on the page.
     * Supports multiple widgets per page (future-proof).
     */
    function initAll() {
        var widgets = document.querySelectorAll('.phyto-climate-widget[data-check-url]');
        for (var i = 0; i < widgets.length; i++) {
            initWidget(widgets[i]);
        }
    }

    // Run after the DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

}());
