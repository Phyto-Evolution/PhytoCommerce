{extends file='page.tpl'}

{block name='page_content_container'}
<main id="search">
  <div class="fsl-listing-header">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      <h1>
        {if $searchQuery}
          {l s='Results for' mod='fsl'} <em style="font-style:italic;color:var(--fsl-forest)">&ldquo;{$searchQuery|escape:'htmlall':'UTF-8'}&rdquo;</em>
        {else}
          {l s='Search' mod='fsl'}
        {/if}
      </h1>
      {if $listing.pagination.total_items}
        <p class="category-description">
          {$listing.pagination.total_items} {l s='plants found' mod='fsl'}
        </p>
      {/if}
    </div>
  </div>

  <div class="container">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    {if $listing.products|count}
      <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-md-4 mt-2" id="products">
        {foreach $listing.products as $product}
          <div class="col">
            {include file='catalog/listing/product-miniature.tpl' product=$product}
          </div>
        {/foreach}
      </div>
      <div class="mt-5">
        {include file='_partials/pagination.tpl' pagination=$listing.pagination}
      </div>
    {else}
      <div class="text-center py-5">
        <span class="material-icons" style="font-size:64px;color:var(--fsl-light-green)">search_off</span>
        <h3 style="font-family:var(--fsl-font-display);font-weight:400;margin-top:16px">{l s='No plants found.' mod='fsl'}</h3>
        <p style="color:var(--fsl-gray-500)">{l s='Try a different search term or browse our categories.' mod='fsl'}</p>
        <a href="{$urls.base_url}" class="btn btn-primary mt-3">{l s='Browse All Plants' mod='fsl'}</a>
      </div>
    {/if}
  </div>
</main>
{/block}
