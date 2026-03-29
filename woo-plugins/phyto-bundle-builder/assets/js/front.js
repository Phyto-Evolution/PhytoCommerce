/* global phytoBundle, jQuery */
(function ($) {
    'use strict';

    /**
     * For each .phyto-bundle-builder on the page:
     *  1. Load products for each slot via AJAX.
     *  2. Track selections (slot_id => product_id).
     *  3. Update running total on selection.
     *  4. Submit all slot selections as a bundle add-to-cart.
     */
    function initBuilder($builder) {
        var bundleId   = $builder.data('bundle');
        var selections = {};   // slot_id => { product_id, price }
        var $addBtn    = $builder.find('.phyto-bundle-add-btn');
        var $total     = $builder.find('.phyto-bundle-total');
        var $msg       = $builder.find('.phyto-bundle-message');

        // ── Load each slot ──────────────────────────────────────────────────
        $builder.find('.phyto-bundle-slot').each(function () {
            var $slot    = $(this);
            var slotId   = $slot.data('slot');
            var catId    = $slot.data('category');
            var $grid    = $slot.find('.phyto-bundle-slot-products');

            $.post(phytoBundle.ajaxurl, {
                action:      'phyto_bundle_products',
                nonce:       phytoBundle.nonce,
                category_id: catId
            }).done(function (res) {
                $grid.empty();
                if (!res.success || !res.data.length) {
                    $grid.html('<p class="phyto-bundle-loading">' +
                        (phytoBundle.i18n ? phytoBundle.i18n.no_products : 'No products available.') +
                    '</p>');
                    return;
                }
                $.each(res.data, function (_, product) {
                    var $card = $(
                        '<div class="phyto-bundle-product-card"' +
                        ' data-id="'    + product.id    + '"' +
                        ' data-price="' + (product.raw_price || 0) + '">' +
                        '<img src="' + escAttr(product.img)  + '" alt="' + escAttr(product.name) + '">' +
                        '<div class="phyto-bundle-product-name">' + escHtml(product.name)  + '</div>' +
                        '<div class="phyto-bundle-product-price">' + product.price + '</div>' +
                        '</div>'
                    );
                    $card.on('click', function () {
                        $slot.find('.phyto-bundle-product-card').removeClass('selected');
                        $card.addClass('selected');
                        selections[slotId] = {
                            product_id: product.id,
                            price:      parseFloat(product.raw_price) || 0
                        };
                        updateTotal();
                    });
                    $grid.append($card);
                });
            }).fail(function () {
                $grid.html('<p class="phyto-bundle-loading">Error loading products.</p>');
            });
        });

        // ── Running total ────────────────────────────────────────────────────
        function updateTotal() {
            var sum = 0;
            $.each(selections, function (_, v) { sum += v.price; });
            if (sum > 0) {
                $total.text(phytoBundle.i18n && phytoBundle.i18n.subtotal
                    ? phytoBundle.i18n.subtotal.replace('%s', formatPrice(sum))
                    : 'Subtotal: ' + formatPrice(sum));
            } else {
                $total.text('');
            }
        }

        function formatPrice(val) {
            return phytoBundle.currency_symbol
                ? phytoBundle.currency_symbol + val.toFixed(2)
                : val.toFixed(2);
        }

        // ── Add to cart ──────────────────────────────────────────────────────
        $addBtn.on('click', function () {
            $msg.removeClass('success error').hide();
            $addBtn.prop('disabled', true).text(
                phytoBundle.i18n ? phytoBundle.i18n.adding : 'Adding…'
            );

            // Build selections map slot_id => product_id
            var selMap = {};
            $.each(selections, function (slotId, v) {
                selMap[slotId] = v.product_id;
            });

            $.post(phytoBundle.ajaxurl, {
                action:     'phyto_bundle_add_to_cart',
                nonce:      phytoBundle.nonce,
                bundle_id:  bundleId,
                selections: selMap
            }).done(function (res) {
                if (res.success) {
                    $msg.addClass('success').text(res.data.message).show();
                    if (res.data.cart_url) {
                        setTimeout(function () {
                            window.location.href = res.data.cart_url;
                        }, 800);
                    }
                } else {
                    $msg.addClass('error').text(
                        res.data && res.data.message ? res.data.message : 'Something went wrong.'
                    ).show();
                    $addBtn.prop('disabled', false).text(
                        phytoBundle.i18n ? phytoBundle.i18n.add_btn : 'Add Bundle to Cart'
                    );
                }
            }).fail(function () {
                $msg.addClass('error').text('Server error. Please try again.').show();
                $addBtn.prop('disabled', false).text(
                    phytoBundle.i18n ? phytoBundle.i18n.add_btn : 'Add Bundle to Cart'
                );
            });
        });
    }

    // ── Tiny helpers ─────────────────────────────────────────────────────────
    function escAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // ── Boot ─────────────────────────────────────────────────────────────────
    $(function () {
        $('.phyto-bundle-builder').each(function () {
            initBuilder($(this));
        });
    });

}(jQuery));
