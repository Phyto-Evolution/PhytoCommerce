{if $facets|count}
  <div id="search_filters" class="js-search-filters fsl-facets" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px 20px 4px;">
    {block name='facets_title'}
      <p style="font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--fsl-gray-600);margin-bottom:16px;">
        {l s='Filter By' d='Shop.Theme.Actions'}
      </p>
    {/block}

    {block name='facets_clearall_button'}
      {if $activeFilters|count}
        <div id="_desktop_search_filters_clear_all" style="margin-bottom:12px;">
          <button data-search-url="{$clear_all_link}" class="btn js-search-filters-clear-all"
                  style="font-size:12px;color:var(--fsl-gray-500);background:none;border:none;padding:0;display:flex;align-items:center;gap:4px;cursor:pointer;">
            <i class="material-icons" style="font-size:14px">delete_sweep</i>
            {l s='Clear all' d='Shop.Theme.Actions'}
          </button>
        </div>
      {/if}
    {/block}

    {foreach from=$facets item="facet"}
      {if !$facet.displayed}
        {continue}
      {/if}

      <section class="facet fsl-facet" style="margin-bottom:16px;">
        {assign var=_expand_id value=10|mt_rand:100000}
        {assign var=_collapse value=true}
        {foreach from=$facet.filters item="filter"}
          {if $filter.active}{assign var=_collapse value=false}{/if}
        {/foreach}

        <p class="facet-title" style="font-size:12px;font-weight:600;color:var(--fsl-gray-700);margin-bottom:8px;cursor:pointer;"
           data-target="#facet_{$_expand_id}" data-toggle="collapse">
          {$facet.label}
        </p>

        {if $facet.widgetType !== 'dropdown'}
          {block name='facet_item_other'}
            <ul id="facet_{$_expand_id}" class="collapse{if !$_collapse} in{/if}" style="list-style:none;padding:0;margin:0;">
              {foreach from=$facet.filters key=filter_key item="filter"}
                {if !$filter.displayed}
                  {continue}
                {/if}
                <li>
                  <label class="facet-label{if $filter.active} active{/if}" for="facet_input_{$_expand_id}_{$filter_key}"
                         style="display:flex;align-items:center;gap:8px;padding:3px 0;cursor:pointer;font-size:13px;color:var(--fsl-gray-600);">
                    {if $facet.multipleSelectionAllowed}
                      <input
                        id="facet_input_{$_expand_id}_{$filter_key}"
                        data-search-url="{$filter.nextEncodedFacetsURL}"
                        type="checkbox"
                        {if $filter.active}checked{/if}
                        style="accent-color:var(--fsl-forest);width:14px;height:14px;"
                      >
                      {if isset($filter.properties.texture)}
                        <span class="color texture" style="width:18px;height:18px;border-radius:50%;background-image:url({$filter.properties.texture});display:inline-block;"></span>
                      {elseif isset($filter.properties.color)}
                        <span class="color" style="width:18px;height:18px;border-radius:50%;background-color:{$filter.properties.color};display:inline-block;border:1px solid var(--fsl-gray-200);"></span>
                      {/if}
                    {else}
                      <input
                        id="facet_input_{$_expand_id}_{$filter_key}"
                        data-search-url="{$filter.nextEncodedFacetsURL}"
                        type="radio"
                        name="filter {$facet.label}"
                        {if $filter.active}checked{/if}
                        style="accent-color:var(--fsl-forest);"
                      >
                    {/if}
                    <a href="{$filter.nextEncodedFacetsURL}" class="js-search-link" rel="nofollow" style="color:inherit;text-decoration:none;">
                      {$filter.label}
                      {if $filter.magnitude}
                        <span style="color:var(--fsl-gray-400);font-size:11px;"> ({$filter.magnitude})</span>
                      {/if}
                    </a>
                  </label>
                </li>
              {/foreach}
            </ul>
          {/block}

        {else}

          {block name='facet_item_dropdown'}
            <div id="facet_{$_expand_id}" class="fsl-facet-dropdown">
              <div class="fsl-dropdown" style="position:relative;">
                <button class="fsl-dropdown-toggle" data-toggle="dropdown" rel="nofollow" aria-haspopup="true" aria-expanded="false"
                        style="width:100%;text-align:left;background:var(--fsl-off-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:8px 12px;font-size:13px;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
                  <span>
                    {$active_found = false}
                    {foreach from=$facet.filters item="filter"}
                      {if $filter.active}
                        {$filter.label}{if $filter.magnitude} ({$filter.magnitude}){/if}
                        {$active_found = true}
                      {/if}
                    {/foreach}
                    {if !$active_found}{l s='(no filter)' d='Shop.Theme.Global'}{/if}
                  </span>
                  <i class="material-icons" style="font-size:16px">expand_more</i>
                </button>
                <div class="dropdown-menu fsl-dropdown-menu" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:8px 0;min-width:100%;box-shadow:var(--fsl-shadow-sm);display:none;position:absolute;z-index:100;">
                  {foreach from=$facet.filters item="filter"}
                    {if !$filter.active}
                      <a rel="nofollow" href="{$filter.nextEncodedFacetsURL}" class="js-search-link"
                         style="display:block;padding:8px 16px;font-size:13px;color:var(--fsl-gray-600);text-decoration:none;">
                        {$filter.label}
                        {if $filter.magnitude}({$filter.magnitude}){/if}
                      </a>
                    {/if}
                  {/foreach}
                </div>
              </div>
            </div>
          {/block}
        {/if}
      </section>
    {/foreach}
  </div>
{/if}
