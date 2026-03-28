{extends file='page.tpl'}

{block name='page_content_container'}
<main id="cart" style="background:var(--fsl-off-white);padding:40px 0 80px;">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
    <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:32px">
      {l s='Your Cart' mod='fsl'}
    </h1>
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    {if $cart.products|count}
      <div class="row g-4">

        {* ── Cart items ── *}
        <div class="col-lg-8">
          <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);overflow:hidden;box-shadow:var(--fsl-shadow-sm);">

            {* Header row *}
            <div class="d-none d-md-grid"
                 style="grid-template-columns:2fr 1fr 1fr 1fr;gap:16px;padding:14px 24px;background:var(--fsl-gray-50);border-bottom:1px solid var(--fsl-gray-200);">
              <span style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500)">{l s='Product' mod='fsl'}</span>
              <span style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:center">{l s='Price' mod='fsl'}</span>
              <span style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:center">{l s='Qty' mod='fsl'}</span>
              <span style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:right">{l s='Total' mod='fsl'}</span>
            </div>

            {foreach $cart.products as $product}
              <div class="cart-detailed-product-line"
                   style="display:grid;grid-template-columns:80px 1fr;gap:16px;padding:20px 24px;border-bottom:1px solid var(--fsl-gray-100);"
                   data-id-product="{$product.id_product}"
                   data-id-product-attribute="{$product.id_product_attribute}">

                {* Image *}
                <a href="{$product.url|escape:'htmlall':'UTF-8'}">
                  <img src="{$product.cover.bySize.cart_default.url|escape:'htmlall':'UTF-8'}"
                       alt="{$product.name|escape:'htmlall':'UTF-8'}"
                       style="width:80px;height:80px;object-fit:cover;border-radius:var(--fsl-radius);background:var(--fsl-cream);">
                </a>

                {* Details *}
                <div style="display:grid;grid-template-columns:1fr auto auto auto;gap:12px 20px;align-items:center;">
                  <div>
                    <a href="{$product.url|escape:'htmlall':'UTF-8'}"
                       style="font-family:var(--fsl-font-display);font-size:1rem;color:var(--fsl-gray-900);display:block;margin-bottom:4px;">
                      {$product.name|escape:'htmlall':'UTF-8'}
                    </a>
                    {foreach $product.attributes as $attr}
                      <span style="font-size:12px;color:var(--fsl-gray-400)">{$attr.name}: {$attr.value} &nbsp;</span>
                    {/foreach}
                    <a href="{$product.remove_from_cart_url|escape:'htmlall':'UTF-8'}"
                       class="js-cart-line-product-delete"
                       style="font-size:11px;color:var(--fsl-gray-400);text-decoration:underline;margin-top:4px;display:inline-block;">
                      {l s='Remove' mod='fsl'}
                    </a>
                  </div>

                  <span style="font-size:14px;font-weight:500;color:var(--fsl-gray-700);text-align:center;">
                    {$product.price}
                  </span>

                  {* Qty *}
                  <div style="display:flex;align-items:center;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-pill);overflow:hidden;height:36px;">
                    <button class="js-cart-line-product-quantity-down"
                            style="width:32px;height:100%;background:var(--fsl-gray-50);border:none;cursor:pointer;font-size:16px;color:var(--fsl-gray-600);">−</button>
                    <input class="js-cart-line-product-quantity"
                           data-product-id="{$product.id_product}"
                           data-product-attribute-id="{$product.id_product_attribute}"
                           data-up-url="{$product.up_quantity_url|escape:'htmlall':'UTF-8'}"
                           data-down-url="{$product.down_quantity_url|escape:'htmlall':'UTF-8'}"
                           data-refresh-url="{$product.refresh_url|escape:'htmlall':'UTF-8'}"
                           type="number" value="{$product.quantity}" min="1"
                           style="width:42px;text-align:center;border:none;font-size:14px;font-family:var(--fsl-font-body);font-weight:500;">
                    <button class="js-cart-line-product-quantity-up"
                            style="width:32px;height:100%;background:var(--fsl-gray-50);border:none;cursor:pointer;font-size:16px;color:var(--fsl-gray-600);">+</button>
                  </div>

                  <span style="font-size:14px;font-weight:600;color:var(--fsl-forest);text-align:right;">
                    {$product.total}
                  </span>
                </div>
              </div>
            {/foreach}

          </div>

          {* Continue shopping *}
          <div class="mt-3">
            <a href="{$urls.base_url}" style="font-size:13px;color:var(--fsl-gray-500);display:inline-flex;align-items:center;gap:6px;">
              <span class="material-icons" style="font-size:16px">arrow_back</span>
              {l s='Continue Shopping' mod='fsl'}
            </a>
          </div>
        </div>

        {* ── Order summary ── *}
        <div class="col-lg-4">
          <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:28px;box-shadow:var(--fsl-shadow-sm);position:sticky;top:90px;">
            <h3 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.3rem;margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--fsl-gray-100);">
              {l s='Order Summary' mod='fsl'}
            </h3>

            {* Totals *}
            {foreach $cart.subtotals as $subtotal}
              {if $subtotal.amount}
                <div class="d-flex justify-content-between mb-2" style="font-size:14px;">
                  <span style="color:var(--fsl-gray-600)">{$subtotal.label|escape:'htmlall':'UTF-8'}</span>
                  <span style="font-weight:500">{$subtotal.value|escape:'htmlall':'UTF-8'}</span>
                </div>
              {/if}
            {/foreach}

            <hr style="border-color:var(--fsl-gray-200);margin:16px 0;">

            <div class="d-flex justify-content-between mb-4" style="font-size:1.1rem;font-weight:600;">
              <span>{l s='Total' mod='fsl'}</span>
              <span style="color:var(--fsl-forest)">{$cart.totals.total.value|escape:'htmlall':'UTF-8'}</span>
            </div>

            {* Voucher *}
            {if isset($cart.vouchers)}
              <div class="mb-3">
                {foreach $cart.vouchers.added as $voucher}
                  <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:13px;background:var(--fsl-light-green);padding:8px 12px;border-radius:var(--fsl-radius);">
                    <span style="color:var(--fsl-forest)">🏷 {$voucher.name|escape:'htmlall':'UTF-8'}</span>
                    <a href="{$voucher.delete_url|escape:'htmlall':'UTF-8'}" style="color:var(--fsl-gray-400);font-size:11px">✕</a>
                  </div>
                {/foreach}
                <form action="{$urls.pages.cart}" method="post" class="d-flex gap-2 mt-2">
                  <input type="hidden" name="token" value="{$static_token}">
                  <input type="hidden" name="ajax" value="1">
                  <input type="hidden" name="action" value="addDiscount">
                  <input type="text" name="discount_name" placeholder="{l s='Voucher code' mod='fsl'}"
                         style="flex:1;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:9px 12px;font-family:var(--fsl-font-body);font-size:13px;">
                  <button type="submit" class="btn btn-outline-primary btn-sm">{l s='Apply' mod='fsl'}</button>
                </form>
              </div>
            {/if}

            <a href="{$urls.pages.order}" class="btn btn-primary w-100" style="font-size:13px;padding:14px;">
              <span class="material-icons" style="font-size:16px">lock</span>
              {l s='Proceed to Checkout' mod='fsl'}
            </a>

            {* Trust *}
            <div class="mt-3 text-center" style="font-size:11px;color:var(--fsl-gray-400);">
              <span class="material-icons" style="font-size:14px;vertical-align:middle">verified_user</span>
              {l s='Secure checkout · SSL encrypted' mod='fsl'}
            </div>
          </div>
        </div>

      </div>

    {else}
      {* Empty cart *}
      <div class="text-center py-5">
        <span class="material-icons" style="font-size:64px;color:var(--fsl-light-green)">shopping_bag</span>
        <h3 style="font-family:var(--fsl-font-display);font-weight:400;margin-top:16px">{l s='Your cart is empty.' mod='fsl'}</h3>
        <p style="color:var(--fsl-gray-500)">{l s="You haven't added any plants yet." mod='fsl'}</p>
        <a href="{$urls.base_url}" class="btn btn-primary mt-3">{l s='Explore Plants' mod='fsl'}</a>
      </div>
    {/if}

  </div>
</main>
{/block}
