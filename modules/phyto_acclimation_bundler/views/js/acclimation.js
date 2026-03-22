;(function ($) {
    'use strict';

    var DISMISS_KEY = 'phytoAcclimDismissed';

    function init() {
        var data = window.phytoAcclimData;
        if (!data || !data.kitItems || data.kitItems.length === 0) {
            return;
        }

        // Session-based dismiss
        if (sessionStorage.getItem(DISMISS_KEY) === '1') {
            return;
        }

        // Check if any cart product is a trigger
        var triggered = false;
        if (data.cartTriggers && data.cartTriggers.length > 0) {
            for (var i = 0; i < data.cartTriggers.length; i++) {
                if (data.cartTriggers[i].triggered) {
                    triggered = true;
                    break;
                }
            }
        }

        if (!triggered) {
            return;
        }

        // Check if all kit items are already in cart
        var allInCart = true;
        if (data.cartProductIds && data.cartProductIds.length > 0) {
            for (var j = 0; j < data.kitItems.length; j++) {
                if (data.cartProductIds.indexOf(data.kitItems[j].id_product) === -1) {
                    allInCart = false;
                    break;
                }
            }
        } else {
            allInCart = false;
        }

        if (allInCart) {
            return;
        }

        // Show the widget
        var $widget = $('#phyto-acclim-widget');
        $widget.show();

        // Dismiss button
        $widget.on('click', '.phyto-acclim-dismiss', function () {
            sessionStorage.setItem(DISMISS_KEY, '1');
            $widget.fadeOut(200);
        });

        // Add individual item
        $widget.on('click', '.phyto-acclim-add-one', function () {
            var idProduct = $(this).data('id-product');
            addToCart(idProduct, 1);
        });

        // Add all items
        $widget.on('click', '.phyto-acclim-add-all', function () {
            var $btn = $(this);
            $btn.prop('disabled', true);

            var promises = [];
            for (var k = 0; k < data.kitItems.length; k++) {
                promises.push(addToCart(data.kitItems[k].id_product, 1));
            }

            $.when.apply($, promises).done(function () {
                sessionStorage.setItem(DISMISS_KEY, '1');
                $widget.fadeOut(300);
            }).always(function () {
                $btn.prop('disabled', false);
            });
        });
    }

    function addToCart(idProduct, qty) {
        var data = window.phytoAcclimData;
        return $.ajax({
            type: 'POST',
            url: data.addToCartUrl,
            data: {
                id_product: idProduct,
                qty: qty,
                add: 1,
                action: 'update',
                static_token: data.staticToken
            },
            dataType: 'json'
        }).done(function (response) {
            if (response && response.success) {
                // Update cart count if prestashop event system is available
                if (typeof prestashop !== 'undefined') {
                    prestashop.emit('updateCart', { reason: { idProduct: idProduct } });
                }
            }
        });
    }

    $(document).ready(init);

}(jQuery));
