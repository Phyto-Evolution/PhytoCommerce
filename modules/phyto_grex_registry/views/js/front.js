/**
 * Phyto Grex Registry — Front-End JS
 *
 * Handles collapsible "Scientific Profile" card toggling.
 * Vanilla JS only — no jQuery dependency.
 */
(function () {
    'use strict';

    /**
     * Initialise all toggle buttons inside .phyto-grex-card elements.
     * The template wraps content in .phyto-grex-body; the button carries
     * the class .phyto-grex-toggle.
     */
    function initGrexToggles() {
        var toggles = document.querySelectorAll('.phyto-grex-toggle');

        if (!toggles.length) {
            return;
        }

        toggles.forEach(function (btn) {
            // Find the associated body: look inside the parent card first,
            // then fall back to a sibling with class .phyto-grex-body.
            var card = btn.closest('.phyto-grex-card');
            var body = card
                ? card.querySelector('.phyto-grex-body')
                : btn.parentElement.querySelector('.phyto-grex-body');

            if (!body) {
                return;
            }

            // Read the initial state from an aria-expanded attribute if present,
            // otherwise default to expanded (visible).
            var expanded = btn.getAttribute('aria-expanded') !== 'false';

            // Apply initial visibility so the DOM state matches the attribute.
            body.style.display = expanded ? '' : 'none';

            btn.setAttribute('aria-expanded', String(expanded));

            btn.addEventListener('click', function () {
                expanded = !expanded;

                if (expanded) {
                    body.style.display = '';
                } else {
                    body.style.display = 'none';
                }

                btn.setAttribute('aria-expanded', String(expanded));

                // Optionally flip a chevron/indicator class on the button.
                btn.classList.toggle('phyto-grex-toggle--collapsed', !expanded);
            });
        });
    }

    // Run after the DOM is fully parsed.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGrexToggles);
    } else {
        // DOMContentLoaded has already fired (e.g. script loaded async/deferred).
        initGrexToggles();
    }
}());
