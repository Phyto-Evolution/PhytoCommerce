{extends file='page.tpl'}

{block name='page_content'}
<div style="padding:40px 0 80px;">
  {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
  <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:36px">{l s='Sitemap' d='Shop.Theme.Global'}</h1>
  <div class="row g-4">
    {foreach $sitemapUrls as $group}
      <div class="col-md-3">
        <h4 style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:12px">
          {$group.name|escape:'htmlall':'UTF-8'}
        </h4>
        {include file='cms/_partials/sitemap-nested-list.tpl' links=$group.links}
      </div>
    {/foreach}
  </div>
</div>
{/block}
