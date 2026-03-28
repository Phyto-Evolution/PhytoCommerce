{block name='cart_detailed_actions'}
  <div class="checkout cart-detailed-actions js-cart-detailed-actions" style="margin-top:20px;text-align:center;">
    {if $cart.minimalPurchaseRequired}
      <div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:var(--fsl-radius);padding:12px 16px;margin-bottom:12px;font-size:13px;color:#92400e;">
        {$cart.minimalPurchaseRequired}
      </div>
      <button type="button" disabled
              style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:var(--fsl-gray-200);color:var(--fsl-gray-400);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:15px;font-weight:500;cursor:not-allowed;width:100%;justify-content:center;">
        {l s='Proceed to checkout' d='Shop.Theme.Actions'}
      </button>
    {elseif empty($cart.products)}
      <button type="button" disabled
              style="display:inline-flex;align-items:center;gap:8px;padding:14px 32px;background:var(--fsl-gray-200);color:var(--fsl-gray-400);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:15px;font-weight:500;cursor:not-allowed;width:100%;justify-content:center;">
        {l s='Proceed to checkout' d='Shop.Theme.Actions'}
      </button>
    {else}
      <a href="{$urls.pages.order}"
         style="display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 32px;background:var(--fsl-forest);color:var(--fsl-white);border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:15px;font-weight:500;text-decoration:none;width:100%;">
        <span class="material-icons" style="font-size:18px;">lock</span>
        {l s='Proceed to checkout' d='Shop.Theme.Actions'}
      </a>
      {hook h='displayExpressCheckout'}
    {/if}
  </div>
{/block}
