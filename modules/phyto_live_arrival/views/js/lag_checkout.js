/**
 * Phyto Live Arrival Guarantee — Checkout JavaScript
 *
 * Handles the LAG opt-in toggle at checkout:
 *  - Persists the opt-in state in a cookie (phyto_lag_opted)
 *  - Shows/hides the fee amount from the toggle label
 *  - Expands/collapses the terms & conditions section
 *
 * No external libraries required. ES5-compatible.
 */

(function () {
    'use strict';

    // ── Cookie helpers ────────────────────────────────────────────────────────

    /**
     * Set a cookie value.
     *
     * @param {string} name
     * @param {string} value
     * @param {number} days   Lifetime in days. Omit or 0 for session cookie.
     */
    function setCookie(name, value, days) {
        var expires = '';
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/; SameSite=Lax';
    }

    /**
     * Read a cookie value by name.
     *
     * @param  {string}      name
     * @return {string|null}
     */
    function getCookie(name) {
        var nameEQ = name + '=';
        var parts  = document.cookie.split(';');
        for (var i = 0; i < parts.length; i++) {
            var part = parts[i].replace(/^\s+/, '');
            if (part.indexOf(nameEQ) === 0) {
                return decodeURIComponent(part.substring(nameEQ.length));
            }
        }
        return null;
    }

    /**
     * Delete a cookie by name.
     *
     * @param {string} name
     */
    function deleteCookie(name) {
        setCookie(name, '', -1);
    }

    // ── DOM helpers ───────────────────────────────────────────────────────────

    /**
     * Slide an element open (display:block + max-height animation).
     *
     * @param {HTMLElement} el
     */
    function slideDown(el) {
        el.style.display = 'block';
        el.style.overflow = 'hidden';
        el.style.maxHeight = '0';
        // Force reflow so the transition fires
        el.offsetHeight; // eslint-disable-line no-unused-expressions
        el.style.transition = 'max-height 0.3s ease';
        el.style.maxHeight  = el.scrollHeight + 'px';
        setTimeout(function () {
            el.style.maxHeight  = '';
            el.style.overflow   = '';
            el.style.transition = '';
        }, 320);
    }

    /**
     * Slide an element closed (max-height animation → display:none).
     *
     * @param {HTMLElement} el
     */
    function slideUp(el) {
        el.style.overflow   = 'hidden';
        el.style.maxHeight  = el.scrollHeight + 'px';
        // Force reflow
        el.offsetHeight; // eslint-disable-line no-unused-expressions
        el.style.transition = 'max-height 0.3s ease';
        el.style.maxHeight  = '0';
        setTimeout(function () {
            el.style.display    = 'none';
            el.style.maxHeight  = '';
            el.style.overflow   = '';
            el.style.transition = '';
        }, 320);
    }

    // ── Main init ─────────────────────────────────────────────────────────────

    function init() {
        var panel      = document.getElementById('phyto-lag-panel');
        if (!panel) { return; }  // Module not rendered on this page

        var checkbox   = document.getElementById('phyto-lag-optin-checkbox');
        var feeDisplay = document.getElementById('phyto-lag-fee-display');
        var termsToggle = document.getElementById('phyto-lag-terms-toggle');
        var termsBody  = document.getElementById('phyto-lag-terms-body');

        // ── Opt-in checkbox ──────────────────────────────────────────────────

        if (checkbox) {
            /**
             * Update visual state and persist cookie to match checkbox state.
             *
             * @param {boolean} opted
             */
            function applyOptIn(opted) {
                // Show fee display only when opted in (no point hiding it, but
                // some themes might want the label visually dimmed when off).
                if (feeDisplay) {
                    feeDisplay.style.opacity = opted ? '1' : '0.5';
                }

                if (opted) {
                    setCookie('phyto_lag_opted', '1', 1);  // 1-day session-ish cookie
                } else {
                    deleteCookie('phyto_lag_opted');
                }
            }

            // Restore state from cookie on page load (covers browser back navigation)
            var cookieVal = getCookie('phyto_lag_opted');
            if (cookieVal === '1' && !checkbox.checked) {
                checkbox.checked = true;
            } else if (cookieVal !== '1' && checkbox.checked) {
                // Already checked via Smarty (cookie was set before); keep it.
                // Do nothing — Smarty already rendered it checked.
            }

            // Apply initial visual state
            applyOptIn(checkbox.checked);

            checkbox.addEventListener('change', function () {
                applyOptIn(this.checked);
            });
        }

        // ── Terms toggle ─────────────────────────────────────────────────────

        if (termsToggle && termsBody) {
            termsToggle.addEventListener('click', function () {
                var expanded = termsToggle.getAttribute('aria-expanded') === 'true';

                if (expanded) {
                    slideUp(termsBody);
                    termsToggle.setAttribute('aria-expanded', 'false');
                } else {
                    slideDown(termsBody);
                    termsToggle.setAttribute('aria-expanded', 'true');
                }
            });
        }
    }

    // ── Bootstrap ─────────────────────────────────────────────────────────────

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}());
