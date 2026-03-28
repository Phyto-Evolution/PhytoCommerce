<section id="js-active-search-filters" class="{if $activeFilters|count}active_filters{else}hide{/if}">
  {block name='active_filters_title'}
    {if $activeFilters|count}
      <p style="font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);margin-bottom:8px">
        {l s='Active filters' d='Shop.Theme.Global'}
      </p>
    {/if}
  {/block}

  {if $activeFilters|count}
    <div class="fsl-active-filters" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
      {foreach from=$activeFilters item="filter"}
        {block name='active_filters_item'}
          <a class="js-search-link" href="{$filter.nextEncodedFacetsURL}"
             style="display:inline-flex;align-items:center;gap:4px;background:var(--fsl-light-green);color:var(--fsl-forest);font-size:12px;padding:4px 10px;border-radius:20px;text-decoration:none;">
            <span>{l s='%1$s:' d='Shop.Theme.Catalog' sprintf=[$filter.facetLabel]} {$filter.label}</span>
            <i class="material-icons" style="font-size:13px">close</i>
          </a>
        {/block}
      {/foreach}
    </div>
  {/if}
</section>
