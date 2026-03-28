<div class="cart-summary-subtotals-container js-cart-summary-subtotals-container"
     style="border-top:1px solid var(--fsl-gray-100);padding-top:12px;margin-top:8px;">
  {foreach from=$cart.subtotals item="subtotal"}
    {if $subtotal && $subtotal.value|count_characters > 0 && $subtotal.type !== 'tax'}
      <div class="cart-summary-line cart-summary-subtotals" id="cart-subtotal-{$subtotal.type}"
           style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:13px;color:var(--fsl-gray-600);">
        <span class="label">{$subtotal.label}</span>
        <span class="value">
          {if 'discount' == $subtotal.type}<span style="color:#e53935;">-&nbsp;</span>{/if}{$subtotal.value}
        </span>
      </div>
    {/if}
  {/foreach}
</div>
