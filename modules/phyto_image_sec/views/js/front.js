/**
 * PhytoImageSec — front-office image theft protection.
 *
 * Strategy:
 *  1. Block contextmenu (right-click) on <img> elements globally.
 *  2. Block dragstart on <img> elements globally.
 *  3. Block Ctrl+S / Cmd+S keyboard shortcut.
 *  4. Inject transparent pointer-events:none overlay divs over product images
 *     so the browser never presents a "Save image as…" option on hover,
 *     while still allowing click-through for lightbox / zoom functionality.
 */
(function () {
    'use strict';

    // ── 1. Block right-click on images ───────────────────────────────────────
    document.addEventListener('contextmenu', function (e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            e.stopPropagation();
        }
    }, true);

    // ── 2. Block drag-to-save ─────────────────────────────────────────────────
    document.addEventListener('dragstart', function (e) {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
        }
    }, true);

    // ── 3. Block Ctrl+S / Cmd+S ───────────────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            e.preventDefault();
        }
    }, true);

    // ── 4. Transparent overlay on product images ──────────────────────────────
    //  The overlay sits above the image with pointer-events:none so it is
    //  invisible to click handlers (lightbox, zoom links still work), but the
    //  browser shows no image context menu because the underlying <img> has
    //  oncontextmenu blocked at the document level (step 1 above).

    var SELECTORS = [
        '.product-cover img',
        '.product-images img',
        '.js-qv-product-cover',
        '.images-container img',
        '#product img',
        '.product-image img',
        '[data-image-type] img',
    ].join(', ');

    function shieldImage(img) {
        if (img.dataset.phytoShielded) {
            return;
        }

        img.dataset.phytoShielded = '1';

        // Prevent native drag
        img.setAttribute('ondragstart', 'return false;');
        img.style.userSelect       = 'none';
        img.style.webkitUserSelect = 'none';
        img.style.webkitUserDrag   = 'none';

        // Wrap in a relative container if needed
        var parent = img.parentElement;

        if (!parent) {
            return;
        }

        if (getComputedStyle(parent).position === 'static') {
            parent.style.position = 'relative';
        }

        var overlay                = document.createElement('div');
        overlay.className          = 'phyto-img-overlay';
        overlay.style.cssText      =
            'position:absolute;top:0;left:0;width:100%;height:100%;' +
            'z-index:10;pointer-events:none;';

        // Belt-and-braces: block contextmenu even if it reaches the overlay
        overlay.addEventListener('contextmenu', function (e) {
            e.preventDefault();
        });

        parent.appendChild(overlay);
    }

    function shieldAll() {
        var imgs = document.querySelectorAll(SELECTORS);
        for (var i = 0; i < imgs.length; i++) {
            shieldImage(imgs[i]);
        }
    }

    // Run on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', shieldAll);
    } else {
        shieldAll();
    }

    // Re-run when the DOM changes (e.g. AJAX gallery swaps, quick-view)
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            for (var i = 0; i < mutations.length; i++) {
                if (mutations[i].addedNodes.length) {
                    shieldAll();
                    break;
                }
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }
}());
