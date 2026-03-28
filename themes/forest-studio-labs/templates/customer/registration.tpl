{extends file='page.tpl'}

{block name='page_content_container'}
<main id="registration" style="background:var(--fsl-off-white);padding:60px 0 100px;">
  <div class="container" style="max-width:560px;">

    <div class="text-center mb-4">
      <span class="logo-text" style="font-size:1.6rem">{$shop.name|escape:'htmlall':'UTF-8'}</span>
    </div>

    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:36px;box-shadow:var(--fsl-shadow);">
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.8rem;text-align:center;margin-bottom:8px">
        {l s='Create Account' mod='fsl'}
      </h1>
      <p class="text-center" style="font-size:13px;color:var(--fsl-gray-400);margin-bottom:28px">
        {l s='Join thousands of plant lovers.' mod='fsl'}
      </p>

      <form action="{$urls.pages.register}" method="post" id="customer-form">
        <input type="hidden" name="submitCreate" value="1">
        <input type="hidden" name="token" value="{$token|default:''|escape:'htmlall':'UTF-8'}">

        <div class="row g-3">
          <div class="col-6">
            <div class="form-group">
              <label for="field-firstname">{l s='First name' mod='fsl'}</label>
              <input type="text" class="form-control" id="field-firstname" name="firstname"
                     value="{if isset($customer.firstname)}{$customer.firstname|escape:'htmlall':'UTF-8'}{/if}" required>
            </div>
          </div>
          <div class="col-6">
            <div class="form-group">
              <label for="field-lastname">{l s='Last name' mod='fsl'}</label>
              <input type="text" class="form-control" id="field-lastname" name="lastname"
                     value="{if isset($customer.lastname)}{$customer.lastname|escape:'htmlall':'UTF-8'}{/if}" required>
            </div>
          </div>
        </div>

        <div class="form-group mt-3">
          <label for="field-email">{l s='Email address' mod='fsl'}</label>
          <input type="email" class="form-control" id="field-email" name="email"
                 value="{if isset($customer.email)}{$customer.email|escape:'htmlall':'UTF-8'}{/if}"
                 autocomplete="email" required>
        </div>

        <div class="form-group mt-3">
          <label for="field-password">{l s='Password' mod='fsl'}</label>
          <input type="password" class="form-control" id="field-password" name="password"
                 autocomplete="new-password" required minlength="8">
          <small style="font-size:11px;color:var(--fsl-gray-400)">{l s='Minimum 8 characters.' mod='fsl'}</small>
        </div>

        <div class="form-group mt-3">
          <label class="d-flex align-items-center gap-2" style="text-transform:none;font-size:13px;font-weight:400;">
            <input type="checkbox" name="newsletter" value="1" style="accent-color:var(--fsl-forest);">
            {l s='Subscribe to our newsletter for plant drops and care tips.' mod='fsl'}
          </label>
        </div>

        <div class="form-group mt-2">
          <label class="d-flex align-items-start gap-2" style="text-transform:none;font-size:12px;font-weight:400;color:var(--fsl-gray-500);">
            <input type="checkbox" name="customer_privacy" value="1" required style="accent-color:var(--fsl-forest);margin-top:2px;flex-shrink:0;">
            {l s='I agree to the' mod='fsl'}
            <a href="{$urls.pages.cms|@array_key_first|escape:'htmlall':'UTF-8'}" target="_blank" style="color:var(--fsl-forest)">{l s='Privacy Policy' mod='fsl'}</a>
            {l s='and' mod='fsl'}
            <a href="{$urls.pages.cms|@array_key_first|escape:'htmlall':'UTF-8'}" target="_blank" style="color:var(--fsl-forest)">{l s='Terms of Use' mod='fsl'}</a>
          </label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-4">
          {l s='Create Account' mod='fsl'}
        </button>
      </form>

      <hr style="border-color:var(--fsl-gray-200);margin:24px 0;">
      <p class="text-center" style="font-size:13px;color:var(--fsl-gray-500);">
        {l s='Already have an account?' mod='fsl'}
        <a href="{$urls.pages.authentication}" style="font-weight:600;color:var(--fsl-forest);">{l s='Sign in' mod='fsl'}</a>
      </p>
    </div>
  </div>
</main>
{/block}
