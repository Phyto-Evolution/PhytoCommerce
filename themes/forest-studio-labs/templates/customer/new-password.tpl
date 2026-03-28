{extends file='page.tpl'}

{block name='page_content_container'}
<main style="background:var(--fsl-off-white);padding:60px 0 100px;">
  <div class="container" style="max-width:480px;">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}
    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:36px;box-shadow:var(--fsl-shadow);">
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.8rem;text-align:center;margin-bottom:28px">{l s='Set New Password' mod='fsl'}</h1>
      <form method="post" action="{$urls.pages.password}">
        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
        <input type="hidden" name="id_customer" value="{$id_customer|intval}">
        <div class="form-group mb-3">
          <label>{l s='New password' mod='fsl'}</label>
          <input type="password" class="form-control" name="passwd" required minlength="8" autofocus>
        </div>
        <div class="form-group mb-4">
          <label>{l s='Confirm password' mod='fsl'}</label>
          <input type="password" class="form-control" name="confirmation" required minlength="8">
        </div>
        <button type="submit" name="submitIdentity" class="btn btn-primary w-100">{l s='Set Password' mod='fsl'}</button>
      </form>
    </div>
  </div>
</main>
{/block}
