<div id="js-product-list-top" class="fsl-products-top d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4"
     style="background:var(--fsl-off-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:12px 18px;">
  <span class="total-products" style="font-size:13px;color:var(--fsl-gray-500);">
    {if $listing.pagination.total_items > 1}
      {l s='There are %product_count% products.' d='Shop.Theme.Catalog' sprintf=['%product_count%' => $listing.pagination.total_items]}
    {elseif $listing.pagination.total_items > 0}
      {l s='There is 1 product.' d='Shop.Theme.Catalog'}
    {/if}
  </span>
  <div class="d-flex align-items-center gap-3 flex-wrap">
    {block name='sort_by'}
      {include file='catalog/_partials/sort-orders.tpl' sort_orders=$listing.sort_orders}
    {/block}
    {if !empty($listing.rendered_facets)}
      <button id="search_filter_toggler" class="btn btn-outline-secondary js-search-toggler"
              style="font-size:12px;padding:7px 14px;">
        <i class="material-icons" style="font-size:14px;vertical-align:middle;">filter_list</i>
        {l s='Filter' d='Shop.Theme.Actions'}
      </button>
    {/if}
  </div>
</div>
