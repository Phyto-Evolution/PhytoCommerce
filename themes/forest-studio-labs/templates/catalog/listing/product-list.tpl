{extends file='page.tpl'}

{block name='page_content_container'}
<main id="category">

  {* ── Category header ── *}
  <div class="fsl-listing-header">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      <h1>{$category.name|escape:'htmlall':'UTF-8'}</h1>
      {if $category.description}
        <div class="category-description">{$category.description nofilter}</div>
      {/if}
    </div>
  </div>

  <div class="container">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div class="row">

      {* ── Left sidebar: facets ── *}
      {if $listing.facets}
        <aside class="col-lg-3 col-md-4 mb-4" id="left-column">
          <div class="fsl-facets-sidebar">
            {block name='left_column'}
              {hook h='displayLeftColumn'}

              <div id="search_filters" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px 20px 4px;">
                <h6 style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--fsl-gray-600);margin-bottom:16px;">
                  {l s='Filter' mod='fsl'}
                </h6>
                {foreach $listing.facets as $facet}
                  {if $facet.filters|count}
                    <div class="facet mb-3">
                      <p class="facet-title mb-2">{$facet.label|escape:'htmlall':'UTF-8'}</p>
                      {foreach $facet.filters as $filter}
                        <label class="facet-label d-flex align-items-center gap-2 py-1" style="cursor:pointer">
                          <input type="checkbox"
                                 data-search-url="{$filter.nextEncodedFacetsURL|escape:'htmlall':'UTF-8'}"
                                 {if $filter.active}checked{/if}
                                 style="accent-color:var(--fsl-forest);width:14px;height:14px;">
                          <span style="font-size:13px;color:var(--fsl-gray-600)">
                            {$filter.label|escape:'htmlall':'UTF-8'}
                            {if $filter.magnitude}
                              <span style="color:var(--fsl-gray-400);font-size:11px;">({$filter.magnitude})</span>
                            {/if}
                          </span>
                        </label>
                      {/foreach}
                    </div>
                  {/if}
                {/foreach}
              </div>

              {if $listing.activeFilters|count}
                <div class="mt-3 mb-4">
                  <p style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:8px">{l s='Active Filters' mod='fsl'}</p>
                  {foreach $listing.activeFilters as $activeFilter}
                    <a href="{$activeFilter.nextEncodedFacetsURL|escape:'htmlall':'UTF-8'}"
                       style="display:inline-flex;align-items:center;gap:6px;background:var(--fsl-light-green);color:var(--fsl-forest);font-size:12px;padding:4px 10px;border-radius:var(--fsl-radius-pill);margin:0 4px 4px 0;text-decoration:none;">
                      {$activeFilter.label|escape:'htmlall':'UTF-8'}
                      <span class="material-icons" style="font-size:14px">close</span>
                    </a>
                  {/foreach}
                </div>
              {/if}
            {/block}
          </div>
        </aside>
      {/if}

      {* ── Product grid ── *}
      <div class="{if $listing.facets}col-lg-9 col-md-8{else}col-12{/if}" id="content-wrapper">

        {* Sort / count bar *}
        <div id="js-product-list-top" class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2"
             style="background:var(--fsl-gray-50);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:12px 18px;">
          <span style="font-size:13px;color:var(--fsl-gray-500);">
            {$listing.pagination.total_items} {l s='plants' mod='fsl'}
          </span>
          <div class="d-flex align-items-center gap-2">
            <label style="font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-500);margin:0">
              {l s='Sort' mod='fsl'}
            </label>
            <select id="js-sort-select"
                    style="border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:7px 14px;font-family:var(--fsl-font-body);font-size:13px;background:var(--fsl-white);color:var(--fsl-gray-700);cursor:pointer;">
              {foreach $listing.sort_orders as $sort_order}
                <option value="{$sort_order.url|escape:'htmlall':'UTF-8'}" {if $sort_order.current}selected{/if}>
                  {$sort_order.label|escape:'htmlall':'UTF-8'}
                </option>
              {/foreach}
            </select>
          </div>
        </div>

        {* Products *}
        <div id="js-product-list">
          {if $listing.products|count}
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 g-3 g-md-4" id="products">
              {foreach $listing.products as $product}
                <div class="col">
                  {include file='catalog/listing/product-miniature.tpl' product=$product}
                </div>
              {/foreach}
            </div>
          {else}
            <div class="text-center py-5">
              <span class="material-icons" style="font-size:48px;color:var(--fsl-gray-300)">eco</span>
              <p style="color:var(--fsl-gray-500);margin-top:12px">{l s='No plants found matching your criteria.' mod='fsl'}</p>
              <a href="{$urls.base_url}" class="btn btn-outline-primary mt-2">{l s='Clear filters' mod='fsl'}</a>
            </div>
          {/if}
        </div>

        {* Pagination *}
        <div class="mt-5">
          {include file='_partials/pagination.tpl' pagination=$listing.pagination}
        </div>

        {hook h='displayRightColumn'}
      </div>

    </div>
  </div>
</main>

<script>
document.getElementById('js-sort-select')?.addEventListener('change', function() {
  window.location.href = this.value;
});
document.querySelectorAll('#search_filters input[type="checkbox"]').forEach(function(cb) {
  cb.addEventListener('change', function() {
    window.location.href = this.dataset.searchUrl;
  });
});
</script>
{/block}
