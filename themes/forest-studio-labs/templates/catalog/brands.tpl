{extends file='page.tpl'}

{block name='page_content_container'}
<main id="brands">

  <div class="fsl-listing-header" style="background:var(--fsl-light-green);padding:40px 0 32px;margin-bottom:40px;">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      {block name='brand_header'}
        <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin:0;">
          {l s='Brands' d='Shop.Theme.Catalog'}
        </h1>
      {/block}
    </div>
  </div>

  <div class="container">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    {block name='brand_miniature'}
      <ul style="list-style:none;padding:0;margin:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:24px;">
        {foreach from=$brands item=brand}
          {include file='catalog/_partials/miniatures/brand.tpl' brand=$brand}
        {/foreach}
      </ul>
    {/block}
  </div>

</main>
{/block}
