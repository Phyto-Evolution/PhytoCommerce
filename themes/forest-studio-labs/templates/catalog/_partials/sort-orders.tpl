<div class="fsl-sort-wrap d-flex align-items-center gap-2">
  <label style="font-size:12px;font-weight:500;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-500);margin:0;white-space:nowrap;">
    {l s='Sort by:' d='Shop.Theme.Global'}
  </label>
  <div class="products-sort-order dropdown" style="position:relative;">
    <button
      class="btn-unstyle select-title js-sort-button"
      rel="nofollow"
      data-toggle="dropdown"
      aria-label="{l s='Sort by selection' d='Shop.Theme.Global'}"
      aria-haspopup="true"
      aria-expanded="false"
      style="background:var(--fsl-white);border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:7px 14px;font-family:var(--fsl-font-body);font-size:13px;color:var(--fsl-gray-700);cursor:pointer;display:flex;align-items:center;gap:6px;">
      {if $listing.sort_selected}{$listing.sort_selected}{else}{l s='Choose' d='Shop.Theme.Actions'}{/if}
      <i class="material-icons" style="font-size:16px">expand_more</i>
    </button>
    <div class="dropdown-menu fsl-sort-menu" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:8px 0;min-width:180px;box-shadow:var(--fsl-shadow-sm);display:none;position:absolute;z-index:200;right:0;">
      {foreach from=$listing.sort_orders item=sort_order}
        <a
          rel="nofollow"
          href="{$sort_order.url}"
          class="js-search-link{if $sort_order.current} current{/if}"
          style="display:block;padding:8px 16px;font-size:13px;color:{if $sort_order.current}var(--fsl-forest){else}var(--fsl-gray-600){/if};text-decoration:none;font-weight:{if $sort_order.current}600{else}400{/if};"
        >
          {$sort_order.label}
        </a>
      {/foreach}
    </div>
  </div>
</div>
