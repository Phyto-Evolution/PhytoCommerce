{block name='my_account_links'}
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
    <a href="{$urls.pages.my_account}" class="account-link" data-role="back-to-your-account"
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--fsl-forest);text-decoration:none;">
      <i class="material-icons" style="font-size:18px;">chevron_left</i>
      <span>{l s='Back to your account' d='Shop.Theme.Customeraccount'}</span>
    </a>
    <a href="{$urls.pages.index}" class="account-link" data-role="home"
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--fsl-gray-500);text-decoration:none;">
      <i class="material-icons" style="font-size:18px;">home</i>
      <span>{l s='Home' d='Shop.Theme.Global'}</span>
    </a>
  </div>
{/block}
