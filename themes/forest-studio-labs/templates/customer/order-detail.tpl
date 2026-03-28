{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 style="margin:0">{l s='Order' mod='fsl'} #{$order.details.reference|escape:'htmlall':'UTF-8'}</h3>
    <a href="{$urls.pages.history}" style="font-size:13px;color:var(--fsl-gray-400)">
      ← {l s='Back to orders' mod='fsl'}
    </a>
  </div>

  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

  {* Status timeline *}
  <div style="background:var(--fsl-cream);border-radius:var(--fsl-radius);padding:16px 20px;margin-bottom:24px;">
    <p style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:6px">{l s='Order Status' mod='fsl'}</p>
    <p style="font-size:1rem;font-weight:500;color:var(--fsl-forest);margin:0">
      {$order.history.current.ostate_name|escape:'htmlall':'UTF-8'}
    </p>
  </div>

  {* Products *}
  <h4 style="font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:16px">{l s='Items' mod='fsl'}</h4>
  {foreach $order.products as $product}
    <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px solid var(--fsl-gray-100)">
      <img src="{$product.cover_url|default:$urls.no_picture_image.bySize.cart_default.url|escape:'htmlall':'UTF-8'}"
           style="width:64px;height:64px;object-fit:cover;border-radius:var(--fsl-radius);background:var(--fsl-cream);"
           alt="{$product.product_name|escape:'htmlall':'UTF-8'}">
      <div class="flex-grow-1 d-flex justify-content-between">
        <div>
          <p style="font-weight:500;margin:0 0 4px">{$product.product_name|escape:'htmlall':'UTF-8'}</p>
          <p style="font-size:12px;color:var(--fsl-gray-400);margin:0">{l s='Qty:' mod='fsl'} {$product.product_quantity}</p>
        </div>
        <span style="font-weight:600;color:var(--fsl-gray-700)">{$product.product_price}</span>
      </div>
    </div>
  {/foreach}

  {* Totals *}
  <div class="mt-4" style="max-width:300px;margin-left:auto;">
    {foreach $order.details.subtotals as $subtotal}
      {if $subtotal.amount}
        <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
          <span style="color:var(--fsl-gray-500)">{$subtotal.label|escape:'htmlall':'UTF-8'}</span>
          <span>{$subtotal.value|escape:'htmlall':'UTF-8'}</span>
        </div>
      {/if}
    {/foreach}
    <div class="d-flex justify-content-between mt-2 pt-2" style="border-top:2px solid var(--fsl-gray-200);font-weight:700;font-size:1rem;">
      <span>{l s='Total' mod='fsl'}</span>
      <span style="color:var(--fsl-forest)">{$order.details.totals.total.value|escape:'htmlall':'UTF-8'}</span>
    </div>
  </div>

  {* Delivery address *}
  {if isset($order.addresses.delivery)}
    <div class="mt-4 pt-4" style="border-top:1px solid var(--fsl-gray-200)">
      <h4 style="font-size:13px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:12px">{l s='Delivery Address' mod='fsl'}</h4>
      <p style="font-size:13px;color:var(--fsl-gray-600);line-height:1.7;margin:0">
        {$order.addresses.delivery.firstname|escape:'htmlall':'UTF-8'} {$order.addresses.delivery.lastname|escape:'htmlall':'UTF-8'}<br>
        {$order.addresses.delivery.address1|escape:'htmlall':'UTF-8'}<br>
        {$order.addresses.delivery.city|escape:'htmlall':'UTF-8'}, {$order.addresses.delivery.postcode|escape:'htmlall':'UTF-8'}
      </p>
    </div>
  {/if}

  {hook h='displayOrderDetail' order=$order}
</div>
{/block}
