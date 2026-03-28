{extends file='page.tpl'}
{block name='page_content'}
<div style="text-align:center;padding:100px 20px;">
  <p style="font-family:var(--fsl-font-display);font-size:6rem;font-weight:300;color:var(--fsl-light-green);line-height:1;margin:0">404</p>
  <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin:16px 0 12px">
    {l s='This page has gone to seed.' mod='fsl'}
  </h1>
  <p style="color:var(--fsl-gray-500);font-size:15px;margin-bottom:32px">
    {l s="The page you're looking for doesn't exist or has been moved." mod='fsl'}
  </p>
  <a href="{$urls.base_url}" class="btn btn-primary">{l s='Back to the Garden' mod='fsl'}</a>
</div>
{/block}
