<section id="js-checkout-summary" class="js-cart"
         data-refresh-url="{$urls.pages.cart}?ajax=1&action=refresh"
         style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">

  <div style="padding:20px;">
    {block name='hook_checkout_summary_top'}
      {include file='checkout/_partials/cart-summary-top.tpl' cart=$cart}
    {/block}

    {block name='cart_summary_products'}
      {include file='checkout/_partials/cart-summary-products.tpl' cart=$cart}
    {/block}

    {block name='cart_summary_subtotals'}
      {include file='checkout/_partials/cart-summary-subtotals.tpl' cart=$cart}
    {/block}
  </div>

  {block name='cart_summary_totals'}
    {include file='checkout/_partials/cart-summary-totals.tpl' cart=$cart}
  {/block}

  {block name='cart_summary_voucher'}
    <div style="padding:0 20px 20px;">
      {include file='checkout/_partials/cart-voucher.tpl'}
    </div>
  {/block}

</section>
