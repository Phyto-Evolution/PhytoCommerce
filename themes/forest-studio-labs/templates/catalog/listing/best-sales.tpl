{extends file='catalog/listing/product-list.tpl'}

{block name='product_list_header'}
  <div class="fsl-listing-header" style="background:var(--fsl-light-green);padding:40px 0 32px;margin-bottom:32px;">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin:0;">
        {l s='Best sellers' d='Shop.Theme.Catalog'}
      </h1>
    </div>
  </div>
{/block}
