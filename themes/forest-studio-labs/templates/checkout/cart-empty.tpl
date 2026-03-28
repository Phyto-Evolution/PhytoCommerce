{extends file='page.tpl'}

{block name='page_content_container'}
<main id="cart">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}

    <div style="text-align:center;padding:80px 20px;">
      <span class="material-icons" style="font-size:64px;color:var(--fsl-gray-200);">shopping_cart</span>
      <h1 style="font-family:var(--fsl-font-display);font-size:28px;font-weight:400;color:var(--fsl-gray-700);margin:20px 0 12px;">
        {l s='Your cart is empty' d='Shop.Theme.Checkout'}
      </h1>
      <p style="font-size:15px;color:var(--fsl-gray-500);margin-bottom:32px;">
        {l s='There are no more items in your cart' d='Shop.Theme.Checkout'}
      </p>
      {block name='continue_shopping'}
        <a href="{$urls.pages.index}"
           style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;text-decoration:none;">
          <i class="material-icons" style="font-size:18px;">chevron_left</i>
          {l s='Continue shopping' d='Shop.Theme.Actions'}
        </a>
      {/block}
    </div>

    {block name='cart_actions'}{/block}
    {block name='cart_voucher'}{/block}
    {block name='display_reassurance'}{/block}
  </div>
</main>
{/block}
