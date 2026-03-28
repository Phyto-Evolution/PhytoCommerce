{extends file='page.tpl'}

{block name='page_content_container'}
<main id="checkout" style="background:var(--fsl-off-white);padding:40px 0 80px;">
  <div class="container">

    {* ── Checkout header ── *}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
      <a href="{$urls.base_url}" style="display:flex;align-items:center;gap:8px;">
        <span class="logo-text" style="font-size:1.3rem">{$shop.name|escape:'htmlall':'UTF-8'}</span>
      </a>
      <div style="display:flex;align-items:center;gap:8px;font-size:12px;color:var(--fsl-gray-400);">
        <span class="material-icons" style="font-size:16px;color:var(--fsl-sage)">lock</span>
        {l s='Secure checkout' mod='fsl'}
      </div>
    </div>

    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div class="row g-4">

      {* ── Steps ── *}
      <div class="col-lg-7">
        {foreach $checkoutProcess.steps as $step}
          <div class="checkout-step {if $step.current}-current{/if} {if $step.complete}-complete{/if}"
               id="{$step.identifier|escape:'htmlall':'UTF-8'}">
            <div class="step-title">
              <span style="width:28px;height:28px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;
                           {if $step.complete}background:var(--fsl-forest);color:var(--fsl-white);{elseif $step.current}background:var(--fsl-forest);color:var(--fsl-white);{else}background:var(--fsl-gray-200);color:var(--fsl-gray-500);{/if}">
                {if $step.complete}
                  <span class="material-icons" style="font-size:16px">check</span>
                {else}
                  {$step@iteration}
                {/if}
              </span>
              {$step.title|escape:'htmlall':'UTF-8'}
              {if $step.complete}
                <span style="margin-left:auto;font-size:12px;color:var(--fsl-sage);">{l s='Edit' mod='fsl'}</span>
              {/if}
            </div>
            {if $step.current || $step.complete}
              <div class="step-content" style="padding:24px;">
                {$step.render nofilter}
              </div>
            {/if}
          </div>
        {/foreach}
      </div>

      {* ── Order summary sidebar ── *}
      <div class="col-lg-5">
        <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:24px;position:sticky;top:90px;box-shadow:var(--fsl-shadow-sm);">
          <h4 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:20px;padding-bottom:12px;border-bottom:1px solid var(--fsl-gray-100);">
            {l s='Your Order' mod='fsl'}
            <span style="float:right;font-family:var(--fsl-font-body);font-size:13px;color:var(--fsl-gray-400);">
              {$cart.products_count} {l s='items' mod='fsl'}
            </span>
          </h4>

          {foreach $cart.products as $product}
            <div class="d-flex gap-3 mb-3">
              <div style="position:relative;flex-shrink:0;">
                <img src="{$product.cover.bySize.cart_default.url|escape:'htmlall':'UTF-8'}"
                     style="width:52px;height:52px;object-fit:cover;border-radius:var(--fsl-radius);background:var(--fsl-cream);"
                     alt="{$product.name|escape:'htmlall':'UTF-8'}">
                <span style="position:absolute;top:-6px;right:-6px;background:var(--fsl-forest);color:var(--fsl-white);border-radius:50%;width:18px;height:18px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:600;">
                  {$product.quantity}
                </span>
              </div>
              <div class="flex-grow-1">
                <p style="font-size:13px;font-weight:500;margin:0 0 2px">{$product.name|escape:'htmlall':'UTF-8'}</p>
                <p style="font-size:12px;color:var(--fsl-gray-400);margin:0">{$product.total}</p>
              </div>
            </div>
          {/foreach}

          <hr style="border-color:var(--fsl-gray-100);margin:16px 0;">

          {foreach $cart.subtotals as $subtotal}
            {if $subtotal.amount}
              <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
                <span style="color:var(--fsl-gray-500)">{$subtotal.label|escape:'htmlall':'UTF-8'}</span>
                <span>{$subtotal.value|escape:'htmlall':'UTF-8'}</span>
              </div>
            {/if}
          {/foreach}

          <hr style="border-color:var(--fsl-gray-200);margin:12px 0;">

          <div class="d-flex justify-content-between" style="font-size:1rem;font-weight:600;">
            <span>{l s='Total' mod='fsl'}</span>
            <span style="color:var(--fsl-forest)">{$cart.totals.total_including_tax.value|escape:'htmlall':'UTF-8'}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</main>
{/block}
