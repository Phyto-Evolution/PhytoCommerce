{extends file='page.tpl'}

{block name='page_content_container'}
<main style="background:var(--fsl-off-white);padding:60px 0 100px;">
  <div class="container" style="max-width:480px;">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}
    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:36px;box-shadow:var(--fsl-shadow);">
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.8rem;text-align:center;margin-bottom:8px">{l s='Track Your Order' mod='fsl'}</h1>
      <p style="text-align:center;font-size:13px;color:var(--fsl-gray-400);margin-bottom:28px">{l s='Enter your order reference and email to access your order.' mod='fsl'}</p>
      <form method="post" action="{$urls.pages.guest_tracking}">
        <div class="form-group mb-3">
          <label>{l s='Order reference' mod='fsl'}</label>
          <input type="text" class="form-control" name="order_reference" required>
        </div>
        <div class="form-group mb-4">
          <label>{l s='Email address' mod='fsl'}</label>
          <input type="email" class="form-control" name="email" required>
        </div>
        <button type="submit" name="submitGuestTracking" class="btn btn-primary w-100">{l s='Find My Order' mod='fsl'}</button>
      </form>
    </div>
  </div>
</main>
{/block}
