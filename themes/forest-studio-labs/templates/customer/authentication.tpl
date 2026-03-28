{extends file='page.tpl'}

{block name='page_content_container'}
<main id="authentication" style="background:var(--fsl-off-white);padding:60px 0 100px;">
  <div class="container" style="max-width:480px;">

    <div class="text-center mb-4">
      <span class="logo-text" style="font-size:1.6rem">{$shop.name|escape:'htmlall':'UTF-8'}</span>
    </div>

    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:36px;box-shadow:var(--fsl-shadow);">
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.8rem;text-align:center;margin-bottom:28px">
        {l s='Sign In' mod='fsl'}
      </h1>

      <form action="{$urls.pages.authentication}" method="post" id="login-form">
        <input type="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}">
        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">

        <div class="form-group mb-3">
          <label for="field-email">{l s='Email address' mod='fsl'}</label>
          <input type="email" class="form-control" id="field-email" name="email"
                 value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall':'UTF-8'}{/if}"
                 autocomplete="email" required autofocus>
        </div>

        <div class="form-group mb-1">
          <label for="field-password">{l s='Password' mod='fsl'}</label>
          <input type="password" class="form-control" id="field-password" name="password"
                 autocomplete="current-password" required>
        </div>

        <div class="text-end mb-4">
          <a href="{$urls.pages.password}" style="font-size:12px;color:var(--fsl-gray-400);">
            {l s='Forgot your password?' mod='fsl'}
          </a>
        </div>

        <button type="submit" class="btn btn-primary w-100" name="submitLogin" id="submit-login">
          {l s='Sign In' mod='fsl'}
        </button>
      </form>

      <hr style="border-color:var(--fsl-gray-200);margin:24px 0;">

      <p class="text-center" style="font-size:13px;color:var(--fsl-gray-500);">
        {l s="Don't have an account?" mod='fsl'}
        <a href="{$urls.pages.register}" style="font-weight:600;color:var(--fsl-forest);">{l s='Create one' mod='fsl'}</a>
      </p>
    </div>

  </div>
</main>
{/block}
