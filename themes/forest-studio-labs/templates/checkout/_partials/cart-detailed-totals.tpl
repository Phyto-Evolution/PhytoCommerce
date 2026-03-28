{block name='cart_detailed_totals'}
<div class="cart-detailed-totals js-cart-detailed-totals"
     style="background:var(--fsl-gray-50);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px;margin-top:20px;">

  <div class="cart-detailed-subtotals js-cart-detailed-subtotals" style="margin-bottom:12px;">
    {foreach from=$cart.subtotals item="subtotal"}
      {if $subtotal && $subtotal.value|count_characters > 0 && $subtotal.type !== 'tax'}
        <div class="cart-summary-line" id="cart-subtotal-{$subtotal.type}"
             style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:14px;color:var(--fsl-gray-600);">
          <span class="label{if 'products' === $subtotal.type} js-subtotal{/if}">
            {if 'products' == $subtotal.type}{$cart.summary_string}{else}{$subtotal.label}{/if}
          </span>
          <span class="value">
            {if 'discount' == $subtotal.type}<span style="color:#e53935;">-&nbsp;</span>{/if}{$subtotal.value}
          </span>
          {if $subtotal.type === 'shipping'}
            <div style="width:100%;"><small style="font-size:11px;color:var(--fsl-gray-400);">{hook h='displayCheckoutSubtotalDetails' subtotal=$subtotal}</small></div>
          {/if}
        </div>
      {/if}
    {/foreach}
  </div>

  {block name='cart_summary_totals'}
    {include file='checkout/_partials/cart-summary-totals.tpl' cart=$cart}
  {/block}

  {block name='cart_voucher'}
    {include file='checkout/_partials/cart-voucher.tpl'}
  {/block}
</div>
{/block}
