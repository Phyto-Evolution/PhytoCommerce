{block name='cart_summary_items_subtotal'}
  <div class="cart-summary-line cart-summary-items-subtotal" id="items-subtotal"
       style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;font-size:14px;color:var(--fsl-gray-700);">
    <span class="label">{$cart.summary_string}</span>
    <span class="value" style="font-weight:500;">{$cart.subtotals.products.amount}</span>
  </div>
{/block}
