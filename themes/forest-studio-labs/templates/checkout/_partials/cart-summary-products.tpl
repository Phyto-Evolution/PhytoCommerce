<div class="cart-summary-products js-cart-summary-products" style="margin-bottom:16px;">
  <p style="font-size:14px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:8px;">{$cart.summary_string}</p>

  <p style="margin-bottom:8px;">
    <a href="#" data-toggle="collapse" data-target="#cart-summary-product-list" class="js-show-details"
       style="font-size:12px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
      {l s='show details' d='Shop.Theme.Actions'}
      <i class="material-icons" style="font-size:16px;">expand_more</i>
    </a>
  </p>

  {block name='cart_summary_product_list'}
    <div class="collapse" id="cart-summary-product-list">
      <ul style="list-style:none;padding:0;margin:0;">
        {foreach from=$cart.products item=product}
          <li style="padding:10px 0;border-bottom:1px solid var(--fsl-gray-100);">
            {include file='checkout/_partials/cart-summary-product-line.tpl' product=$product}
          </li>
        {/foreach}
      </ul>
    </div>
  {/block}
</div>
