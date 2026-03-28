{extends file='page.tpl'}

{block name='page_content_container'}
<main>
  <div class="container" style="max-width:480px;padding:60px 16px;">

    <h1 style="font-family:var(--fsl-font-display);font-size:28px;font-weight:400;color:var(--fsl-gray-800);text-align:center;margin-bottom:8px;">
      {l s='Forgot your password?' d='Shop.Theme.Customeraccount'}
    </h1>
    <p style="text-align:center;font-size:14px;color:var(--fsl-gray-500);margin-bottom:32px;">
      {l s='Please enter the email address you used to register. You will receive a temporary link to reset your password.' d='Shop.Theme.Customeraccount'}
    </p>

    {if $errors}
      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:12px 16px;margin-bottom:20px;">
        {foreach $errors as $error}
          <p style="font-size:13px;color:#dc2626;margin:0;">{$error}</p>
        {/foreach}
      </div>
    {/if}

    <form action="{$urls.pages.password}" class="forgotten-password" method="post"
          style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;">
      <div style="margin-bottom:20px;">
        <label for="email" style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:6px;">
          {l s='Email address' d='Shop.Forms.Labels'} <span style="color:#e53935;">*</span>
        </label>
        <input type="email" name="email" id="email"
               value="{if isset($smarty.post.email)}{$smarty.post.email|stripslashes}{/if}"
               required
               style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;box-sizing:border-box;">
      </div>
      <button id="send-reset-link" name="submit" type="submit"
              style="width:100%;padding:12px 24px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
        {l s='Send reset link' d='Shop.Theme.Actions'}
      </button>
    </form>

    <div style="text-align:center;margin-top:20px;">
      <a id="back-to-login" href="{$urls.pages.my_account}"
         style="font-size:13px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="material-icons" style="font-size:16px;">chevron_left</i>
        {l s='Back to login' d='Shop.Theme.Actions'}
      </a>
    </div>
  </div>
</main>
{/block}
