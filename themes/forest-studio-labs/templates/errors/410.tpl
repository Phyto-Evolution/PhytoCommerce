{extends file='page.tpl'}

{block name='breadcrumb'}{/block}

{block name='page_title'}
  {$page.title}
{/block}

{capture assign='errorContent'}
  <span class="material-icons" style="font-size:72px;color:var(--fsl-gray-200);display:block;margin-bottom:20px;">inventory_2</span>
  <h1 style="font-family:var(--fsl-font-display);font-size:32px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 12px;">
    {l s='No products available' d='Shop.Theme.Catalog'}
  </h1>
  <p style="font-size:15px;color:var(--fsl-gray-500);margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto;">
    {l s='Stay tuned! More products will be shown here as they are added.' d='Shop.Theme.Catalog'}
  </p>
{/capture}

{block name='page_content_container'}
  <div class="container" style="padding:40px 20px;">
    {include file='errors/not-found.tpl' errorContent=$errorContent}
  </div>
{/block}
