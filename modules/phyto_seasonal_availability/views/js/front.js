/**
 * Phyto Seasonal Availability — front-end script.
 *
 * Validates the notify-me email form before submission.
 * Vanilla JS, no dependencies, no async required.
 */
(function () {
  'use strict';

  /** Basic RFC 5322-inspired email pattern (matches browser `type="email"` behaviour). */
  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  /**
   * Validate the notify-me form.
   *
   * @param {HTMLFormElement} form
   * @returns {boolean} true when the form may be submitted
   */
  function validateNotifyForm(form) {
    var emailInput = form.querySelector('.phyto-notify-email');
    var errorSpan  = form.querySelector('#phyto-notify-error');

    if (!emailInput) {
      return true; // nothing to validate — let the browser handle it
    }

    var val = emailInput.value.trim();

    // 1. Non-empty check
    if (val === '') {
      showError(errorSpan, emailInput);
      return false;
    }

    // 2. Pattern check (HTML5 validity API with regex fallback)
    var valid = (typeof emailInput.validity !== 'undefined')
      ? emailInput.validity.valid
      : EMAIL_RE.test(val);

    if (!valid) {
      showError(errorSpan, emailInput);
      return false;
    }

    hideError(errorSpan, emailInput);
    return true;
  }

  function showError(errorSpan, input) {
    if (errorSpan) {
      errorSpan.style.display = 'inline';
    }
    if (input) {
      input.setAttribute('aria-invalid', 'true');
      input.classList.add('has-error');
    }
  }

  function hideError(errorSpan, input) {
    if (errorSpan) {
      errorSpan.style.display = 'none';
    }
    if (input) {
      input.removeAttribute('aria-invalid');
      input.classList.remove('has-error');
    }
  }

  /**
   * Attach submit handler to a notify form element.
   *
   * @param {HTMLFormElement} form
   */
  function bindNotifyForm(form) {
    form.addEventListener('submit', function (e) {
      if (!validateNotifyForm(form)) {
        e.preventDefault();
      }
    });

    // Clear error state as soon as the user starts correcting the value
    var emailInput = form.querySelector('.phyto-notify-email');
    var errorSpan  = form.querySelector('#phyto-notify-error');
    if (emailInput) {
      emailInput.addEventListener('input', function () {
        hideError(errorSpan, emailInput);
      });
    }
  }

  /**
   * Initialise — called once the DOM is ready.
   */
  function init() {
    // There may be more than one form on the page in theory, so use querySelectorAll.
    var forms = document.querySelectorAll('.phyto-notify-form');
    for (var i = 0; i < forms.length; i++) {
      bindNotifyForm(forms[i]);
    }
  }

  // Bootstrap when the DOM is available
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

}());
