<div class="panel" id="phyto-ws-product-tab">
  <div class="panel-heading">
    <i class="icon-briefcase"></i>
    {l s='Wholesale Settings' mod='phyto_wholesale_portal'}
  </div>
  <div class="panel-body">
    <form id="phyto-ws-product-form" method="post"
          action="{$phyto_ws_admin_url|escape:'html'}&token={$phyto_ws_admin_token|escape:'html'}&id_product={$phyto_ws_id_product|intval}">
      <input type="hidden" name="action" value="saveWholesaleProduct">
      <input type="hidden" name="id_product" value="{$phyto_ws_id_product|intval}">

      <div class="form-group">
        <label class="control-label col-lg-3">
          {l s='Minimum Order Quantity (MOQ)' mod='phyto_wholesale_portal'}
        </label>
        <div class="col-lg-4">
          <input type="number" name="moq" value="{$phyto_ws_moq|intval}" min="0" class="form-control fixed-width-sm">
          <p class="help-block">{l s='Leave 0 for no minimum.' mod='phyto_wholesale_portal'}</p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">
          {l s='Wholesale Only' mod='phyto_wholesale_portal'}
        </label>
        <div class="col-lg-4">
          <span class="switch prestashop-switch fixed-width-lg">
            <input type="radio" name="wholesale_only" id="ws_only_on" value="1"
                   {if $phyto_ws_wholesale_only}checked="checked"{/if}>
            <label for="ws_only_on">{l s='Yes' mod='phyto_wholesale_portal'}</label>
            <input type="radio" name="wholesale_only" id="ws_only_off" value="0"
                   {if !$phyto_ws_wholesale_only}checked="checked"{/if}>
            <label for="ws_only_off">{l s='No' mod='phyto_wholesale_portal'}</label>
            <a class="slide-button btn"></a>
          </span>
          <p class="help-block">{l s='Hide this product from retail customers.' mod='phyto_wholesale_portal'}</p>
        </div>
      </div>

      <div class="form-group">
        <label class="control-label col-lg-3">
          {l s='Tiered Pricing' mod='phyto_wholesale_portal'}
        </label>
        <div class="col-lg-9">
          <table class="table table-condensed" id="phyto-ws-tiers-table">
            <thead>
              <tr>
                <th>{l s='Min Qty' mod='phyto_wholesale_portal'}</th>
                <th>{l s='Price (excl. tax)' mod='phyto_wholesale_portal'}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$phyto_ws_price_tiers item='tier' key='i'}
              <tr>
                <td><input type="number" name="tier_qty[]" value="{$tier.qty|intval}" min="1" class="form-control input-sm fixed-width-sm"></td>
                <td><input type="number" name="tier_price[]" value="{$tier.price|floatval}" min="0" step="0.01" class="form-control input-sm fixed-width-md"></td>
                <td><button type="button" class="btn btn-xs btn-danger phyto-ws-remove-tier"><i class="icon-trash"></i></button></td>
              </tr>
              {/foreach}
            </tbody>
          </table>
          <button type="button" id="phyto-ws-add-tier" class="btn btn-default btn-sm">
            <i class="icon-plus"></i> {l s='Add Tier' mod='phyto_wholesale_portal'}
          </button>
        </div>
      </div>

      <div class="panel-footer">
        <button type="submit" class="btn btn-default pull-right">
          <i class="process-icon-save"></i>
          {l s='Save Wholesale Settings' mod='phyto_wholesale_portal'}
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function () {
  document.getElementById('phyto-ws-add-tier').addEventListener('click', function () {
    var tbody = document.querySelector('#phyto-ws-tiers-table tbody');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="number" name="tier_qty[]" value="" min="1" class="form-control input-sm fixed-width-sm"></td>'
      + '<td><input type="number" name="tier_price[]" value="" min="0" step="0.01" class="form-control input-sm fixed-width-md"></td>'
      + '<td><button type="button" class="btn btn-xs btn-danger phyto-ws-remove-tier"><i class="icon-trash"></i></button></td>';
    tbody.appendChild(tr);
  });

  document.querySelector('#phyto-ws-tiers-table').addEventListener('click', function (e) {
    var btn = e.target.closest('.phyto-ws-remove-tier');
    if (btn) { btn.closest('tr').remove(); }
  });
})();
</script>
