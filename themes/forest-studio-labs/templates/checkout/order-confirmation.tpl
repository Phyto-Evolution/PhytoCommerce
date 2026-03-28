{extends file='page.tpl'}

{block name='page_content_container'}
<main id="order-confirmation" style="background:var(--fsl-off-white);padding:60px 0 100px;">
  <div class="container" style="max-width:680px;">

    {* Success banner *}
    <div class="text-center mb-5">
      <div style="width:72px;height:72px;background:var(--fsl-light-green);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
        <span class="material-icons" style="font-size:36px;color:var(--fsl-forest)">eco</span>
      </div>
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:2.2rem;margin-bottom:10px">
        {l s='Your order is confirmed!' mod='fsl'}
      </h1>
      <p style="color:var(--fsl-gray-500);font-size:15px;">
        {l s='Thank you for your order. We will send a confirmation to' mod='fsl'}
        <strong>{$order.details.email|escape:'htmlall':'UTF-8'}</strong>
      </p>
    </div>

    {* Order detail card *}
    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);overflow:hidden;box-shadow:var(--fsl-shadow-sm);margin-bottom:24px;">
      <div style="background:var(--fsl-cream);padding:16px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--fsl-gray-200);">
        <span style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-600)">{l s='Order Reference' mod='fsl'}</span>
        <span style="font-weight:600;color:var(--fsl-forest)">{$order.details.reference|escape:'htmlall':'UTF-8'}</span>
      </div>
      <div style="padding:24px;">
        {foreach $order.products as $product}
          <div class="d-flex gap-3 mb-3">
            <img src="{$product.cover_url|default:$urls.no_picture_image.bySize.cart_default.url|escape:'htmlall':'UTF-8'}"
                 style="width:60px;height:60px;object-fit:cover;border-radius:var(--fsl-radius);background:var(--fsl-cream);"
                 alt="{$product.name|escape:'htmlall':'UTF-8'}">
            <div class="flex-grow-1 d-flex justify-content-between align-items-start">
              <div>
                <p style="font-size:14px;font-weight:500;margin:0 0 3px">{$product.name|escape:'htmlall':'UTF-8'}</p>
                <p style="font-size:12px;color:var(--fsl-gray-400);margin:0">{l s='Qty:' mod='fsl'} {$product.quantity}</p>
              </div>
              <span style="font-weight:600;color:var(--fsl-gray-700)">{$product.price}</span>
            </div>
          </div>
        {/foreach}

        <hr style="border-color:var(--fsl-gray-100);margin:16px 0;">

        <div class="d-flex justify-content-between" style="font-size:1rem;font-weight:700;">
          <span>{l s='Total' mod='fsl'}</span>
          <span style="color:var(--fsl-forest)">{$order.details.totals.total.value|escape:'htmlall':'UTF-8'}</span>
        </div>
      </div>
    </div>

    {* What's next *}
    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:24px;box-shadow:var(--fsl-shadow-sm);margin-bottom:32px;">
      <h4 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:16px">{l s="What's next?" mod='fsl'}</h4>
      <div class="d-flex flex-column gap-3">
        <div class="d-flex gap-3">
          <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">email</span>
          <span style="font-size:14px;color:var(--fsl-gray-600)">{l s='You will receive a confirmation email with tracking details once your order ships.' mod='fsl'}</span>
        </div>
        <div class="d-flex gap-3">
          <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">local_shipping</span>
          <span style="font-size:14px;color:var(--fsl-gray-600)">{l s='Orders are carefully packed and dispatched within 1–2 business days.' mod='fsl'}</span>
        </div>
        <div class="d-flex gap-3">
          <span class="material-icons" style="color:var(--fsl-sage);flex-shrink:0">verified</span>
          <span style="font-size:14px;color:var(--fsl-gray-600)">{l s='Your plant is covered by our Live Arrival Guarantee.' mod='fsl'}</span>
        </div>
      </div>
    </div>

    {hook h='displayOrderConfirmation' order=$order}

    <div class="text-center">
      <a href="{$urls.base_url}" class="btn btn-primary">{l s='Continue Shopping' mod='fsl'}</a>
      <a href="{$urls.pages.history}" class="btn btn-outline-primary ms-2">{l s='View Orders' mod='fsl'}</a>
    </div>

  </div>
</main>
{/block}
