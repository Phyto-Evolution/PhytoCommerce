{if $phyto_ws_tiers}
<div class="phyto-ws-tiers alert alert-info" style="margin-top:10px;">
  <strong>{l s='Wholesale Tiered Pricing' mod='phyto_wholesale_portal'}</strong>
  {if $phyto_ws_moq > 0}
    <span class="label label-warning" style="margin-left:8px;">
      {l s='MOQ' mod='phyto_wholesale_portal'}: {$phyto_ws_moq}
    </span>
  {/if}
  <table class="table table-condensed" style="margin-top:8px;margin-bottom:0;">
    <thead>
      <tr>
        <th>{l s='Min. Qty' mod='phyto_wholesale_portal'}</th>
        <th>{l s='Unit Price' mod='phyto_wholesale_portal'}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$phyto_ws_tiers item='tier'}
      <tr>
        <td>{$tier.qty|intval}+</td>
        <td>{$tier.price|string_format:"%.2f"}</td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{/if}
