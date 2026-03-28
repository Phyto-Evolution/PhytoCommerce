{extends file='page.tpl'}

{block name='page_content_container'}
<main id="category">

  {* ── Category header ── *}
  <div class="fsl-listing-header" style="background:var(--fsl-light-green);padding:40px 0 32px;margin-bottom:32px;">
    <div class="container">
      {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
      {block name='product_list_header'}
        {include file='catalog/_partials/category-header.tpl' listing=$listing category=$category}
      {/block}
    </div>
  </div>

  <div class="container">
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    {* Subcategories *}
    {if $subcategories}
      <div class="mb-5">
        {include file='catalog/_partials/subcategories.tpl'}
      </div>
    {/if}

    <div class="row">

      {* ── Left sidebar: facets ── *}
      {if $listing.facets}
        <aside class="col-lg-3 col-md-4 mb-4" id="left-column">
          <div class="fsl-facets-sidebar">
            {block name='left_column'}
              {hook h='displayLeftColumn'}
              {include file='catalog/_partials/facets.tpl' facets=$listing.facets}
              {if $listing.activeFilters|count}
                <div class="mt-3">
                  {include file='catalog/_partials/active_filters.tpl' activeFilters=$listing.activeFilters}
                </div>
              {/if}
            {/block}
          </div>
        </aside>
      {/if}

      {* ── Product grid ── *}
      <div class="{if $listing.facets}col-lg-9 col-md-8{else}col-12{/if}" id="content-wrapper">
        {block name='product_list_top'}
          {include file='catalog/_partials/products-top.tpl' listing=$listing}
        {/block}

        {block name='product_list'}
          {include file='catalog/_partials/products.tpl' listing=$listing}
        {/block}

        {block name='product_list_bottom'}
          {include file='catalog/_partials/products-bottom.tpl' listing=$listing}
        {/block}

        {block name='product_list_footer'}
          {include file='catalog/_partials/category-footer.tpl' listing=$listing category=$category}
        {/block}

        {hook h='displayRightColumn'}
      </div>

    </div>
  </div>
</main>
{/block}
