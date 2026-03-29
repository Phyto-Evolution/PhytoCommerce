/**
 * front.js
 * Phyto Restock Alert — AJAX subscribe widget
 *
 * Depends on: phytoRestockAlert (global config injected by module via Media::addJsDef)
 * Config keys: ajaxUrl, token, msgOk, msgAlready, msgError
 */
(function () {
  'use strict';

  /**
   * Show a feedback message inside the widget.
   *
   * @param {HTMLElement} msgEl
   * @param {string}      text
   * @param {'success'|'error'|'info'} type
   */
  function showMessage(msgEl, text, type) {
    msgEl.textContent = text;
    msgEl.className   = 'phyto-restock-message is-' + type;
    msgEl.style.display = 'block';
  }

  /**
   * Set the submit button into a loading / ready state.
   *
   * @param {HTMLButtonElement} btn
   * @param {boolean}           loading
   * @param {string}            originalLabel  HTML to restore when loading = false
   */
  function setLoading(btn, loading, originalLabel) {
    if (loading) {
      btn.disabled   = true;
      btn.innerHTML  = '<span class="phyto-spinner"></span>';
    } else {
      btn.disabled  = false;
      btn.innerHTML = originalLabel;
    }
  }

  /**
   * Initialise the widget once the DOM is ready.
   */
  function init() {
    var form = document.getElementById('phyto-restock-form');
    if (!form) {
      return; // Widget not present on this page
    }

    var cfg        = (typeof phytoRestockAlert !== 'undefined') ? phytoRestockAlert : {};
    var ajaxUrl    = cfg.ajaxUrl    || '';
    var csrfToken  = cfg.token      || '';
    var msgEl      = document.getElementById('phyto-restock-message');
    var submitBtn  = document.getElementById('phyto-restock-submit');
    var emailInput = document.getElementById('phyto-restock-email');
    var originalLabel = submitBtn ? submitBtn.innerHTML : '';

    if (!ajaxUrl) {
      console.warn('[PhytoRestockAlert] ajaxUrl not configured.');
      return;
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      var email     = emailInput ? emailInput.value.trim() : '';
      var firstname = (document.getElementById('phyto-restock-firstname') || {}).value || '';
      var idProduct = (form.querySelector('[name="id_product"]') || {}).value || '0';
      var idAttr    = (form.querySelector('[name="id_product_attribute"]') || {}).value || '0';

      // Client-side email validation
      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        showMessage(msgEl, 'Please enter a valid email address.', 'error');
        if (emailInput) { emailInput.focus(); }
        return;
      }

      setLoading(submitBtn, true, originalLabel);
      if (msgEl) { msgEl.style.display = 'none'; }

      var formData = new FormData();
      formData.append('email',                email);
      formData.append('firstname',            firstname.trim());
      formData.append('id_product',           idProduct);
      formData.append('id_product_attribute', idAttr);
      formData.append('action',               'subscribe');
      formData.append('token',                csrfToken);

      fetch(ajaxUrl, {
        method:      'POST',
        body:        formData,
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
          }
          return response.json();
        })
        .then(function (data) {
          setLoading(submitBtn, false, originalLabel);

          if (data.success) {
            showMessage(msgEl, data.message || cfg.msgOk, 'success');
            form.style.display = 'none'; // Hide form on success
          } else if (data.already) {
            showMessage(msgEl, data.message || cfg.msgAlready, 'info');
          } else {
            showMessage(msgEl, data.message || cfg.msgError, 'error');
          }
        })
        .catch(function (err) {
          setLoading(submitBtn, false, originalLabel);
          showMessage(msgEl, cfg.msgError || 'An error occurred. Please try again.', 'error');
          console.error('[PhytoRestockAlert] AJAX error:', err);
        });
    });
  }

  // Initialise when the DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
}());
