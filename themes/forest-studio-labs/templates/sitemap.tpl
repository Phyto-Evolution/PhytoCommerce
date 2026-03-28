{extends file='page.tpl'}

{block name='page_content'}
<div style="padding:40px 0 80px;">
  {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
  <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:36px">{l s='Sitemap' mod='fsl'}</h1>
  <div class="row g-4">
    {foreach $page_list as $page_cat}
      <div class="col-md-4">
        <h4 style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:12px">
          {$page_cat.name|escape:'htmlall':'UTF-8'}
        </h4>
        <ul style="list-style:none;padding:0;margin:0;">
          {foreach $page_cat.children as $page}
            <li style="margin-bottom:7px;">
              <a href="{$page.url|escape:'htmlall':'UTF-8'}" style="font-size:14px;color:var(--fsl-gray-600);">
                {$page.name|escape:'htmlall':'UTF-8'}
              </a>
            </li>
          {/foreach}
        </ul>
      </div>
    {/foreach}
  </div>
</div>
{/block}
