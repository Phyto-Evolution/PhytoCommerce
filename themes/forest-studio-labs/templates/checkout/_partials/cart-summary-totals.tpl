<div class="cart-summary-totals js-cart-summary-totals"
     style="background:var(--fsl-forest);padding:16px 20px;border-top:1px solid var(--fsl-gray-200);">

  {block name='cart_summary_total'}
    {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
      <div class="cart-summary-line" style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;font-size:13px;color:rgba(255,255,255,.75);">
        <span class="label">{$cart.totals.total.label}&nbsp;{$cart.labels.tax_short}</span>
        <span class="value">{$cart.totals.total.value}</span>
      </div>
      <div class="cart-summary-line cart-total" style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:16px;font-weight:600;color:var(--fsl-white);">
        <span class="label">{$cart.totals.total_including_tax.label}</span>
        <span class="value">{$cart.totals.total_including_tax.value}</span>
      </div>
    {else}
      <div class="cart-summary-line cart-total" style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:16px;font-weight:600;color:var(--fsl-white);">
        <span class="label">{$cart.totals.total.label}&nbsp;{if $configuration.display_taxes_label && $configuration.taxes_enabled}{$cart.labels.tax_short}{/if}</span>
        <span class="value">{$cart.totals.total.value}</span>
      </div>
    {/if}
  {/block}

  {block name='cart_summary_tax'}
    {if $cart.subtotals.tax}
      <div class="cart-summary-line" style="display:flex;justify-content:space-between;align-items:center;padding:3px 0;font-size:12px;color:rgba(255,255,255,.6);">
        <span class="label sub">{l s='%label%:' sprintf=['%label%' => $cart.subtotals.tax.label] d='Shop.Theme.Global'}</span>
        <span class="value sub">{$cart.subtotals.tax.value}</span>
      </div>
    {/if}
  {/block}

</div>
