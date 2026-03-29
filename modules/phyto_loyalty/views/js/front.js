/* phyto_loyalty — front-end JS */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        // --- Cart redeem widget ---
        var redeemForm = document.getElementById('phyto-loyalty-redeem-form');
        if (redeemForm) {
            redeemForm.addEventListener('submit', function (e) {
                e.preventDefault();
                var input   = redeemForm.querySelector('input[name="points_to_redeem"]');
                var btn     = redeemForm.querySelector('.btn-redeem');
                var msgBox  = document.getElementById('phyto-loyalty-redeem-msg');
                var points  = parseInt(input.value, 10);

                if (!points || points < 1) {
                    showMsg(msgBox, 'error', phytoLoyalty.i18n.invalid_points);
                    return;
                }

                btn.disabled = true;

                fetch(phytoLoyalty.redeemUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'points_to_redeem=' + points + '&token=' + phytoLoyalty.token
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        showMsg(msgBox, 'success',
                            phytoLoyalty.i18n.redeemed
                                .replace('{points}', points)
                                .replace('{amount}', data.discount_amount)
                        );
                        // Update displayed balance
                        var balEl = document.querySelector('.phyto-loyalty-widget__balance');
                        if (balEl) balEl.textContent = data.new_balance;
                        // Reload cart totals after short delay
                        setTimeout(function () { window.location.reload(); }, 1500);
                    } else {
                        showMsg(msgBox, 'error', data.message || phytoLoyalty.i18n.error);
                        btn.disabled = false;
                    }
                })
                .catch(function () {
                    showMsg(msgBox, 'error', phytoLoyalty.i18n.error);
                    btn.disabled = false;
                });
            });
        }

        // --- Tier progress bar animation ---
        var bars = document.querySelectorAll('.phyto-loyalty-progress__bar[data-width]');
        bars.forEach(function (bar) {
            var target = parseFloat(bar.getAttribute('data-width')) || 0;
            bar.style.width = '0%';
            setTimeout(function () { bar.style.width = target + '%'; }, 100);
        });

    });

    function showMsg(el, type, text) {
        if (!el) return;
        el.className = 'phyto-loyalty-widget__msg ' + type;
        el.textContent = text;
        el.style.display = 'block';
    }

})();
