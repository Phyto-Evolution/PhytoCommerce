{block name='cart_detailed_product'}
  <div class="cart-overview js-cart" data-refresh-url="{url entity='cart' params=['ajax' => true, 'action' => 'refresh']}">
    {if $cart.products}
      <ul class="cart-items" style="list-style:none;padding:0;margin:0;">
        {foreach from=$cart.products item=product}
          <li class="cart-item" style="padding:16px 0;border-bottom:1px solid var(--fsl-gray-100);">
            {block name='cart_detailed_product_line'}
              {include file='checkout/_partials/cart-detailed-product-line.tpl' product=$product}
            {/block}
          </li>
          {if is_array($product.customizations) && $product.customizations|count > 1}
            <hr style="border-color:var(--fsl-gray-100);">
          {/if}
        {/foreach}
      </ul>
    {else}
      <p class="no-items" style="font-size:14px;color:var(--fsl-gray-500);text-align:center;padding:32px 0;">
        {l s='There are no more items in your cart' d='Shop.Theme.Checkout'}
      </p>
    {/if}
  </div>
{/block}
