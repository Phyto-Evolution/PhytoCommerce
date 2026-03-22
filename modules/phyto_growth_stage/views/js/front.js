/**
 * Phyto Growth Stage — Front-End JS
 *
 * Vanilla JS IIFE. Listens for combination changes on the product page and
 * ensures the stage card remains visible. Makes an AJAX call to refresh stage
 * data when the selected combination changes.
 *
 * No jQuery dependency.
 */
(function () {
    'use strict';

    /**
     * Return the value of a named URL query parameter.
     *
     * @param {string} name
     * @returns {string|null}
     */
    function getQueryParam(name) {
        var search = window.location.search;
        var regex  = new RegExp('[?&]' + encodeURIComponent(name) + '=([^&#]*)');
        var match  = regex.exec(search);
        return match ? decodeURIComponent(match[1].replace(/\+/g, ' ')) : null;
    }

    /**
     * Ensure the stage card element is visible (display not set to none).
     */
    function showStageCard() {
        var card = document.getElementById('phyto-stage-card-main');
        if (card) {
            card.style.display = '';
        }
    }

    /**
     * Update the progress bar and label inside the stage card.
     *
     * @param {number} stageIndex   0-based index of the current stage.
     * @param {number} stageTotal   Total number of stages.
     */
    function updateProgressBar(stageIndex, stageTotal) {
        var wrap = document.querySelector('.phyto-stage-card__progress-wrap');
        if (!wrap) {
            return;
        }

        var label = wrap.querySelector('.phyto-stage-card__progress-label');
        var bar   = wrap.querySelector('.phyto-stage-progress__bar');
        var prog  = wrap.querySelector('.phyto-stage-progress');

        var displayIndex = stageIndex + 1;
        var pct = stageTotal > 1
            ? (stageIndex / (stageTotal - 1)) * 100
            : 100;

        if (label) {
            label.textContent = 'Stage ' + displayIndex + ' of ' + stageTotal;
        }

        if (bar) {
            bar.style.width = pct.toFixed(1) + '%';
        }

        if (prog) {
            prog.setAttribute('aria-valuenow', String(displayIndex));
            prog.setAttribute('aria-valuemax', String(stageTotal));
        }
    }

    /**
     * Refresh stage card content after a combination change.
     * Falls back to showing the existing card if AJAX is unavailable.
     *
     * @param {number} idProductAttribute
     */
    function refreshStageCard(idProductAttribute) {
        showStageCard();

        var card = document.getElementById('phyto-stage-card-main');
        if (!card) {
            return;
        }

        // Read the product id from the page if available (PrestaShop embeds it
        // in various places; try a data attribute on the product-detail wrapper
        // or fall back to the URL parameter).
        var productWrapper = document.querySelector('[data-id-product]');
        var idProduct = productWrapper
            ? parseInt(productWrapper.getAttribute('data-id-product'), 10)
            : parseInt(getQueryParam('id_product'), 10);

        if (!idProduct || !idProductAttribute) {
            return;
        }

        // PrestaShop 8 themes expose window.prestashop.urls.base_url; fall back
        // to the page origin.
        var baseUrl = (window.prestashop && window.prestashop.urls && window.prestashop.urls.base_url)
            ? window.prestashop.urls.base_url
            : window.location.origin + '/';

        // Build the product page URL with the selected attribute so PS re-renders
        // the stage hook. We read the resulting HTML and replace the card.
        var targetUrl = baseUrl
            + 'index.php?id_product=' + idProduct
            + '&id_product_attribute=' + idProductAttribute
            + '&controller=product';

        var xhr = new XMLHttpRequest();
        xhr.open('GET', targetUrl, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    var parser = new DOMParser();
                    var doc    = parser.parseFromString(xhr.responseText, 'text/html');
                    var newCard = doc.getElementById('phyto-stage-card-main');

                    if (newCard && card) {
                        card.innerHTML  = newCard.innerHTML;
                        card.dataset.stageIndex = newCard.dataset.stageIndex || card.dataset.stageIndex;
                        card.dataset.stageTotal = newCard.dataset.stageTotal || card.dataset.stageTotal;
                    }
                } catch (e) {
                    // Parsing failed — simply keep the existing card visible.
                    showStageCard();
                }
            }
        };

        xhr.onerror = function () {
            // Network error — ensure the card remains visible.
            showStageCard();
        };

        xhr.send();
    }

    /**
     * Attach change listeners to all combination-selector elements on the page.
     *
     * PrestaShop 8 combination selects typically carry the class
     * .product-variants-item select or are identified by the
     * prestashop.on('updatedProduct') event. We handle both.
     */
    function initCombinationListeners() {
        // Approach 1: native change events on every combination <select>.
        var selects = document.querySelectorAll(
            '.product-variants select, .product-variants-item select, select[name="group[\\d+]"]'
        );

        selects.forEach(function (sel) {
            sel.addEventListener('change', function () {
                // Re-read the currently selected attribute from the form.
                var form = document.querySelector('form[data-product-id], #add-to-cart-or-refresh');
                var attrInput = form
                    ? form.querySelector('input[name="id_product_attribute"]')
                    : document.querySelector('input[name="id_product_attribute"]');

                var idAttr = attrInput ? parseInt(attrInput.value, 10) : 0;
                refreshStageCard(idAttr);
            });
        });

        // Approach 2: PrestaShop 8 global event bus (cleaner and more reliable).
        if (window.prestashop) {
            window.prestashop.on('updatedProduct', function (event) {
                var idAttr = (event && event.id_product_attribute)
                    ? parseInt(event.id_product_attribute, 10)
                    : 0;
                refreshStageCard(idAttr);
            });
        }
    }

    /**
     * Entry point — run after DOM is ready.
     */
    function init() {
        showStageCard();
        initCombinationListeners();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
