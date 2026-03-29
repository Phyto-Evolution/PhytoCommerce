/**
 * Phyto Bundle Builder — Front-End JavaScript
 *
 * Handles:
 *  - Product selection per slot (click to select / deselect)
 *  - AJAX product search per slot (debounced)
 *  - Running total calculation (subtotal, savings, bundle total)
 *  - Submit button enable/disable based on required slots
 *
 * Requires no external dependencies beyond a modern browser.
 */

(function () {
  'use strict';

  /* ------------------------------------------------------------------ */
  /* Utilities                                                            */
  /* ------------------------------------------------------------------ */

  /**
   * Simple debounce utility.
   * @param {Function} fn
   * @param {number}   delay  milliseconds
   * @returns {Function}
   */
  function debounce(fn, delay) {
    var timer;
    return function () {
      var ctx  = this;
      var args = arguments;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
  }

  /**
   * Format a number as a currency string.
   * Falls back to a simple 2-decimal representation when Intl is not available.
   *
   * @param {number} amount
   * @param {string} [currencySymbol]
   * @returns {string}
   */
  function formatPrice(amount, currencySymbol) {
    var sym = currencySymbol || '₹';
    if (typeof Intl !== 'undefined' && Intl.NumberFormat) {
      try {
        return sym + new Intl.NumberFormat('en-IN', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(amount);
      } catch (e) { /* fall through */ }
    }
    return sym + parseFloat(amount).toFixed(2);
  }

  /* ------------------------------------------------------------------ */
  /* Core class                                                           */
  /* ------------------------------------------------------------------ */

  /**
   * BundleBuilder — manages state for one bundle builder form.
   *
   * @param {HTMLFormElement} form
   */
  function BundleBuilder(form) {
    this.form          = form;
    this.bundleId      = parseInt(form.getAttribute('data-bundle-id'), 10) || 0;
    this.discountType  = form.getAttribute('data-discount-type') || 'percent';
    this.discountValue = parseFloat(form.getAttribute('data-discount-value')) || 0;
    this.productsUrl   = form.getAttribute('data-products-url') || '';
    this.showSavings   = form.getAttribute('data-show-savings') === '1';

    /** @type {Object.<number, {id_product: number, price: number, name: string}>} */
    this.selections    = {}; // keyed by slot id

    /** @type {Object.<number, boolean>} */
    this.requiredSlots = {};

    this._init();
  }

  BundleBuilder.prototype._init = function () {
    var self = this;

    // Collect slot metadata
    this.form.querySelectorAll('.phyto-bb-slot').forEach(function (slotEl) {
      var slotId   = parseInt(slotEl.getAttribute('data-slot-id'), 10);
      var required = slotEl.getAttribute('data-required') === '1';
      self.requiredSlots[slotId] = required;
    });

    // Bind product card clicks
    this.form.querySelectorAll('.phyto-bb-product-card').forEach(function (card) {
      card.addEventListener('click', function () {
        self._selectProduct(card);
      });
    });

    // Bind search inputs
    var debouncedSearch = debounce(function (e) {
      var input  = e.target;
      var slotId = parseInt(input.getAttribute('data-slot-id'), 10);
      self._searchProducts(slotId, input.value.trim());
    }, 320);

    this.form.querySelectorAll('.phyto-bb-search').forEach(function (input) {
      input.addEventListener('input', debouncedSearch);
    });

    // Initial totals/button state
    this._updateTotals();
    this._updateSubmitButton();
  };

  /* ------------------------------------------------------------------ */
  /* Selection                                                            */
  /* ------------------------------------------------------------------ */

  /**
   * Toggle selection for a product card within its slot.
   * @param {HTMLElement} card
   */
  BundleBuilder.prototype._selectProduct = function (card) {
    var slotId    = parseInt(card.getAttribute('data-slot'), 10);
    var productId = parseInt(card.getAttribute('data-id'), 10);
    var price     = parseFloat(card.getAttribute('data-price')) || 0;
    var name      = card.getAttribute('data-name') || '';

    var grid = document.getElementById('phyto-grid-' + slotId);

    // Deselect any previously selected card in this slot
    if (grid) {
      grid.querySelectorAll('.phyto-bb-product-card.phyto-bb-selected').forEach(function (c) {
        c.classList.remove('phyto-bb-selected');
      });
    }

    var wasSelected = this.selections[slotId] && this.selections[slotId].id_product === productId;

    if (wasSelected) {
      // Clicking the already-selected card deselects it
      delete this.selections[slotId];
      this._setHiddenInput(slotId, 0);
      this._updateSlotStatus(slotId, null);
    } else {
      card.classList.add('phyto-bb-selected');
      this.selections[slotId] = { id_product: productId, price: price, name: name };
      this._setHiddenInput(slotId, productId);
      this._updateSlotStatus(slotId, name);
    }

    this._updateTotals();
    this._updateSubmitButton();
  };

  /**
   * Write the selected product ID into the hidden form input for a slot.
   * @param {number} slotId
   * @param {number} productId  0 to clear
   */
  BundleBuilder.prototype._setHiddenInput = function (slotId, productId) {
    var input = document.getElementById('phyto-selection-' + slotId);
    if (input) {
      input.value = productId > 0 ? productId : '';
    }
  };

  /**
   * Update the slot header status text.
   * @param {number}      slotId
   * @param {string|null} name   null = no selection
   */
  BundleBuilder.prototype._updateSlotStatus = function (slotId, name) {
    var slotEl = document.getElementById('phyto-slot-' + slotId);
    if (!slotEl) { return; }

    var statusEl = slotEl.querySelector('.phyto-bb-slot-status');
    if (!statusEl) { return; }

    if (name) {
      statusEl.textContent = name;
      statusEl.classList.remove('phyto-bb-slot-empty');
      statusEl.classList.add('phyto-bb-slot-selected');
    } else {
      statusEl.textContent = statusEl.getAttribute('data-empty-text') || 'No product selected';
      statusEl.classList.remove('phyto-bb-slot-selected');
      statusEl.classList.add('phyto-bb-slot-empty');
    }
  };

  /* ------------------------------------------------------------------ */
  /* AJAX product search                                                  */
  /* ------------------------------------------------------------------ */

  /**
   * Fetch products for a slot matching the search query and re-render the grid.
   * @param {number} slotId
   * @param {string} query
   */
  BundleBuilder.prototype._searchProducts = function (slotId, query) {
    var self  = this;
    var grid  = document.getElementById('phyto-grid-' + slotId);
    if (!grid) { return; }

    var url = this.productsUrl
      + (this.productsUrl.indexOf('?') === -1 ? '?' : '&')
      + 'id_slot=' + slotId
      + '&q=' + encodeURIComponent(query);

    grid.innerHTML = '<p class="phyto-bb-loading">&#8230;</p>';

    fetch(url, { credentials: 'same-origin' })
      .then(function (res) { return res.json(); })
      .then(function (products) {
        self._renderProductGrid(grid, slotId, products);
      })
      .catch(function () {
        grid.innerHTML = '<p class="alert alert-danger">Error loading products.</p>';
      });
  };

  /**
   * Render product cards into a grid element.
   * @param {HTMLElement} grid
   * @param {number}      slotId
   * @param {Array}       products
   */
  BundleBuilder.prototype._renderProductGrid = function (grid, slotId, products) {
    var self    = this;
    var current = this.selections[slotId] ? this.selections[slotId].id_product : 0;

    if (!products || products.length === 0) {
      grid.innerHTML = '<p class="phyto-bb-no-products alert alert-warning">No products found.</p>';
      return;
    }

    var html = '';
    products.forEach(function (p) {
      var selected = current === p.id_product ? ' phyto-bb-selected' : '';
      var imgHtml  = p.image_url
        ? '<img src="' + _esc(p.image_url) + '" alt="' + _esc(p.name) + '" loading="lazy">'
        : '<div class="phyto-bb-no-image"><i class="material-icons">image_not_supported</i></div>';

      html += '<div class="phyto-bb-product-card' + selected + '"'
        + ' data-id="' + p.id_product + '"'
        + ' data-name="' + _esc(p.name) + '"'
        + ' data-price="' + parseFloat(p.price) + '"'
        + ' data-slot="' + slotId + '">'
        + '<div class="phyto-bb-product-image">'
        + imgHtml
        + '<span class="phyto-bb-checkmark"><i class="material-icons">check_circle</i></span>'
        + '</div>'
        + '<div class="phyto-bb-product-info">'
        + '<p class="phyto-bb-product-name">' + _esc(p.name) + '</p>'
        + (p.reference ? '<p class="phyto-bb-product-ref">' + _esc(p.reference) + '</p>' : '')
        + '<p class="phyto-bb-product-price">' + (p.price_formatted || formatPrice(p.price)) + '</p>'
        + '</div>'
        + '</div>';
    });

    grid.innerHTML = html;

    // Rebind click handlers for new cards
    grid.querySelectorAll('.phyto-bb-product-card').forEach(function (card) {
      card.addEventListener('click', function () {
        self._selectProduct(card);
      });
    });
  };

  /* ------------------------------------------------------------------ */
  /* Totals                                                               */
  /* ------------------------------------------------------------------ */

  BundleBuilder.prototype._updateTotals = function () {
    var subtotal = 0;
    var self     = this;

    Object.keys(this.selections).forEach(function (slotId) {
      subtotal += self.selections[slotId].price || 0;
    });

    var savingsAmount = 0;
    if (this.discountValue > 0) {
      if (this.discountType === 'percent') {
        savingsAmount = Math.round(subtotal * this.discountValue / 100 * 100) / 100;
      } else {
        savingsAmount = Math.min(this.discountValue, subtotal);
      }
    }
    var bundleTotal = Math.max(0, subtotal - savingsAmount);

    var subtotalEl    = document.getElementById('phyto-subtotal');
    var savingsEl     = document.getElementById('phyto-savings');
    var grandTotalEl  = document.getElementById('phyto-grand-total');
    var savingsRowEl  = document.getElementById('phyto-savings-row');

    if (subtotalEl)   { subtotalEl.textContent   = formatPrice(subtotal); }
    if (grandTotalEl) { grandTotalEl.textContent  = formatPrice(bundleTotal); }

    if (savingsRowEl && savingsEl) {
      if (this.showSavings && savingsAmount > 0) {
        savingsRowEl.style.display = '';
        savingsEl.textContent = '- ' + formatPrice(savingsAmount);
        if (this.discountType === 'percent') {
          savingsEl.textContent += ' (' + this.discountValue + '%)';
        }
      } else {
        savingsRowEl.style.display = 'none';
      }
    }
  };

  /* ------------------------------------------------------------------ */
  /* Submit button state                                                  */
  /* ------------------------------------------------------------------ */

  BundleBuilder.prototype._updateSubmitButton = function () {
    var self       = this;
    var allFilled  = true;
    var hintEl     = document.getElementById('phyto-submit-hint');

    Object.keys(this.requiredSlots).forEach(function (slotId) {
      if (self.requiredSlots[slotId] && !self.selections[slotId]) {
        allFilled = false;
      }
    });

    var btn = document.getElementById('phyto-add-to-cart-btn');
    if (btn) {
      btn.disabled = !allFilled;
    }

    if (hintEl) {
      hintEl.style.display = allFilled ? 'none' : '';
    }
  };

  /* ------------------------------------------------------------------ */
  /* HTML escaping helper                                                 */
  /* ------------------------------------------------------------------ */

  function _esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  /* ------------------------------------------------------------------ */
  /* Bootstrap                                                            */
  /* ------------------------------------------------------------------ */

  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('phyto-bundle-form');
    if (form) {
      new BundleBuilder(form);
    }
  });

}());
