{extends file='catalog/listing/product-list.tpl'}

{block name='product_list_header'}
  <div class="fsl-listing-header" style="background:var(--fsl-light-green);padding:40px 0 32px;margin-bottom:32px;">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin:4px 0 12px;">
        {l s='List of products by brand %brand_name%' sprintf=['%brand_name%' => $manufacturer.name] d='Shop.Theme.Catalog'}
      </h1>
      {if $manufacturer.short_description}
        <div id="manufacturer-short_description" style="font-size:15px;color:var(--fsl-gray-600);line-height:1.6;">{$manufacturer.short_description nofilter}</div>
      {/if}
    </div>
  </div>
  {if $manufacturer.description}
    <div class="container mb-4">
      <div id="manufacturer-description" style="font-size:14px;color:var(--fsl-gray-600);line-height:1.7;">{$manufacturer.description nofilter}</div>
    </div>
  {/if}
{/block}

{block name='product_list'}
  {include file='catalog/_partials/products.tpl' listing=$listing}
{/block}
