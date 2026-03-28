{extends file='catalog/listing/product-list.tpl'}

{block name='product_list_header'}
  <div class="fsl-listing-header" style="background:var(--fsl-light-green);padding:40px 0 32px;margin-bottom:32px;">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin:0;">
        {if $listing.keyword}
          {l s='Search results for: "%s"' sprintf=[$listing.keyword] d='Shop.Theme.Catalog'}
        {else}
          {l s='Search' d='Shop.Theme.Catalog'}
        {/if}
      </h1>
    </div>
  </div>
{/block}

{block name="error_content"}
  <div style="text-align:center;padding:60px 20px;">
    <span class="material-icons" style="font-size:48px;color:var(--fsl-gray-300);">search_off</span>
    <h4 id="product-search-no-matches" style="font-family:var(--fsl-font-display);font-size:22px;font-weight:400;color:var(--fsl-gray-700);margin:16px 0 8px;">
      {l s='No matches were found for your search' d='Shop.Theme.Catalog'}
    </h4>
    <p style="color:var(--fsl-gray-500);font-size:14px;">{l s='Please try other keywords to describe what you are looking for.' d='Shop.Theme.Catalog'}</p>
  </div>
{/block}

{block name='product_list'}
  {include file='catalog/_partials/products.tpl' listing=$listing}
{/block}
