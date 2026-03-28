{extends file='page.tpl'}

{block name='page_content_container'}
<main>
  <div class="container" style="max-width:480px;padding:60px 16px;text-align:center;">

    <h1 style="font-family:var(--fsl-font-display);font-size:28px;font-weight:400;color:var(--fsl-gray-800);margin-bottom:24px;">
      {l s='Forgot your password?' d='Shop.Theme.Customeraccount'}
    </h1>

    {if $successes}
      <div style="background:var(--fsl-light-green);border:1px solid var(--fsl-sage);border-radius:var(--fsl-radius-lg);padding:20px;margin-bottom:24px;">
        {foreach $successes as $success}
          <p style="font-size:14px;color:var(--fsl-forest);margin:0;">{$success}</p>
        {/foreach}
      </div>
    {/if}

    <a href="{$urls.pages.authentication}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:14px;color:var(--fsl-forest);text-decoration:none;font-weight:500;">
      <i class="material-icons" style="font-size:18px;">chevron_left</i>
      {l s='Back to Login' d='Shop.Theme.Actions'}
    </a>
  </div>
</main>
{/block}
