{**
 * views/templates/hook/cart_widget.tpl
 * Redeem points widget — displayShoppingCartFooter
 *
 * @author PhytoCommerce
 *}

<div class="phyto-loyalty-cart-widget card mb-3" id="phyto-loyalty-cart-widget">
  <div class="card-header d-flex align-items-center">
    <span class="phyto-loyalty-star mr-2">&#9733;</span>
    <strong>{l s='Redeem Loyalty Points' mod='phyto_loyalty'}</strong>
  </div>
  <div class="card-body">
    <p class="mb-2">
      {l s='Your balance:' mod='phyto_loyalty'}
      <strong id="phyto-loyalty-balance-display">{$phyto_loyalty_balance|intval}</strong>
      {l s='points' mod='phyto_loyalty'}
    </p>

    {if $phyto_loyalty_can_redeem}
      <p class="text-muted small mb-3">
        {l s='Min' mod='phyto_loyalty'} {$phyto_loyalty_min_redeem|intval} {l s='pts — max' mod='phyto_loyalty'}
        {$phyto_loyalty_max_redeem|intval} {l s='pts for this order.' mod='phyto_loyalty'}
        {l s='Each point = ₹' mod='phyto_loyalty'}{$phyto_loyalty_redeem_rate|string_format:'%.2f'} {l s='discount.' mod='phyto_loyalty'}
      </p>
      <div class="input-group">
        <input type="number"
               id="phyto-loyalty-points-input"
               class="form-control"
               placeholder="{l s='Points to redeem' mod='phyto_loyalty'}"
               min="{$phyto_loyalty_min_redeem|intval}"
               max="{$phyto_loyalty_max_redeem|intval}"
               value="{$phyto_loyalty_min_redeem|intval}" />
        <div class="input-group-append">
          <button id="phyto-loyalty-redeem-btn" class="btn btn-primary" type="button">
            {l s='Redeem' mod='phyto_loyalty'}
          </button>
        </div>
      </div>
      <div id="phyto-loyalty-redeem-msg" class="mt-2" style="display:none;"></div>
    {else}
      <p class="text-muted small mb-0">
        {if $phyto_loyalty_balance < $phyto_loyalty_min_redeem}
          {l s='You need at least' mod='phyto_loyalty'} {$phyto_loyalty_min_redeem|intval}
          {l s='points to redeem. Keep shopping to earn more!' mod='phyto_loyalty'}
        {else}
          {l s='Point redemption is not available for this order.' mod='phyto_loyalty'}
        {/if}
      </p>
    {/if}
  </div>
</div>

<script>
(function() {
  var redeemUrl = {$phyto_loyalty_redeem_url|json_encode};
  var staticToken = '{$static_token|escape:'javascript'}';

  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('phyto-loyalty-redeem-btn');
    if (!btn) return;
    btn.addEventListener('click', function() {
      var points = parseInt(document.getElementById('phyto-loyalty-points-input').value, 10);
      var msgEl  = document.getElementById('phyto-loyalty-redeem-msg');
      msgEl.style.display = 'none';
      msgEl.className     = 'mt-2';

      fetch(redeemUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'points_to_redeem=' + encodeURIComponent(points) + '&token=' + encodeURIComponent(staticToken)
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        msgEl.style.display = 'block';
        if (data.success) {
          msgEl.className += ' alert alert-success';
          msgEl.textContent = '\u20b9' + data.discount_amount.toFixed(2) + ' discount applied (voucher: ' + data.voucher_code + '). New balance: ' + data.new_balance + ' pts.';
          document.getElementById('phyto-loyalty-balance-display').textContent = data.new_balance;
          // Reload cart totals
          setTimeout(function() { location.reload(); }, 1500);
        } else {
          msgEl.className += ' alert alert-danger';
          msgEl.textContent = data.error || 'Redemption failed.';
        }
      })
      .catch(function() {
        msgEl.style.display = 'block';
        msgEl.className += ' alert alert-danger';
        msgEl.textContent = 'Network error. Please try again.';
      });
    });
  });
}());
</script>
