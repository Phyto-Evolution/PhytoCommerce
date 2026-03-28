{extends file='page.tpl'}

{block name='page_content_container'}
<main id="cms-category" style="padding:48px 0 80px;">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
    <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:32px">
      {$cms_category.name|escape:'htmlall':'UTF-8'}
    </h1>
    {if $cms_category.description}
      <p style="color:var(--fsl-gray-500);max-width:600px;margin-bottom:36px">{$cms_category.description nofilter}</p>
    {/if}
    <div class="row g-3">
      {foreach $cms_pages as $cms_page}
        <div class="col-md-4">
          <a href="{$cms_page.url|escape:'htmlall':'UTF-8'}"
             style="display:block;background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;transition:all var(--fsl-transition);text-decoration:none;">
            <h3 style="font-family:var(--fsl-font-display);font-size:1.1rem;font-weight:400;color:var(--fsl-gray-900);margin-bottom:8px">
              {$cms_page.meta_title|escape:'htmlall':'UTF-8'}
            </h3>
            <span style="font-size:12px;color:var(--fsl-forest);">{l s='Read more →' mod='fsl'}</span>
          </a>
        </div>
      {/foreach}
    </div>
  </div>
</main>
{/block}
