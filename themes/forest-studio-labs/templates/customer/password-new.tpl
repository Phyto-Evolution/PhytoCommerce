{extends file='page.tpl'}

{block name='page_content_container'}
<main>
  <div class="container" style="max-width:480px;padding:60px 16px;">

    <h1 style="font-family:var(--fsl-font-display);font-size:28px;font-weight:400;color:var(--fsl-gray-800);text-align:center;margin-bottom:32px;">
      {l s='Reset your password' d='Shop.Theme.Customeraccount'}
    </h1>

    {if $errors}
      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:12px 16px;margin-bottom:20px;">
        {foreach $errors as $error}
          <p style="font-size:13px;color:#dc2626;margin:0;">{$error}</p>
        {/foreach}
      </div>
    {/if}

    <form action="{$urls.pages.password}" method="post"
          style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;">

      <div style="margin-bottom:16px;padding:10px 14px;background:var(--fsl-gray-50);border-radius:var(--fsl-radius);font-size:13px;color:var(--fsl-gray-600);">
        {l s='Email address: %email%' d='Shop.Theme.Customeraccount' sprintf=['%email%' => $customer_email|stripslashes]}
      </div>

      <div class="field-password-policy">
        <div style="margin-bottom:16px;">
          <label style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:6px;">
            {l s='New password' d='Shop.Forms.Labels'} <span style="color:#e53935;">*</span>
          </label>
          <input type="password" class="fsl-input" data-validate="isPasswd" name="passwd" value=""
                 {if isset($configuration.password_policy.minimum_length)}data-minlength="{$configuration.password_policy.minimum_length}"{/if}
                 {if isset($configuration.password_policy.maximum_length)}data-maxlength="{$configuration.password_policy.maximum_length}"{/if}
                 {if isset($configuration.password_policy.minimum_score)}data-minscore="{$configuration.password_policy.minimum_score}"{/if}
                 style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;box-sizing:border-box;">
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:6px;">
            {l s='Confirmation' d='Shop.Forms.Labels'} <span style="color:#e53935;">*</span>
          </label>
          <input type="password" class="fsl-input" data-validate="isPasswd" name="confirmation" value=""
                 style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;box-sizing:border-box;">
        </div>

        <input type="hidden" name="token" id="token" value="{$customer_token}">
        <input type="hidden" name="id_customer" id="id_customer" value="{$id_customer}">
        <input type="hidden" name="reset_token" id="reset_token" value="{$reset_token}">

        <button type="submit" name="submit"
                style="width:100%;padding:12px 24px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
          {l s='Change Password' d='Shop.Theme.Actions'}
        </button>
      </div>
    </form>

    <div style="text-align:center;margin-top:20px;">
      <a href="{$urls.pages.authentication}"
         style="font-size:13px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="material-icons" style="font-size:16px;">chevron_left</i>
        {l s='Back to Login' d='Shop.Theme.Actions'}
      </a>
    </div>
  </div>
</main>
{/block}
