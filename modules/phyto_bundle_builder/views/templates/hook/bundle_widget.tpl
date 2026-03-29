{**
 * Bundle widget — shown on homepage (displayHome) and sidebars
 * (displayLeftColumn / displayRightColumn).
 *
 * Variables:
 *   $phyto_bundles       — array of active bundle rows
 *   $phyto_builder_base  — base URL to the builder controller
 *   $phyto_sidebar_mode  — bool: true when rendering in a column widget
 **}

<div class="phyto-bb-widget{if isset($phyto_sidebar_mode) && $phyto_sidebar_mode} phyto-bb-widget--sidebar{/if}">

  <div class="phyto-bb-widget-header">
    <h3 class="phyto-bb-widget-title">
      {l s='Build Your Bundle' mod='phyto_bundle_builder'}
    </h3>
    <p class="phyto-bb-widget-subtitle">
      {l s='Mix and match products to get a special discount.' mod='phyto_bundle_builder'}
    </p>
  </div>

  {if $phyto_bundles|@count > 0}
    <ul class="phyto-bb-widget-list">
      {foreach from=$phyto_bundles item=bundle}
        <li class="phyto-bb-widget-item">
          <a href="{$phyto_builder_base|escape:'html':'UTF-8'}?id_bundle={$bundle.id_bundle|intval}"
             class="phyto-bb-widget-link">
            <span class="phyto-bb-widget-name">{$bundle.name|escape:'html':'UTF-8'}</span>
            {if $bundle.discount_value > 0}
              <span class="phyto-bb-widget-badge">
                {if $bundle.discount_type == 'percent'}
                  -{$bundle.discount_value|string_format:'%g'}%
                {else}
                  -{$bundle.discount_value|displayPrice}
                {/if}
              </span>
            {/if}
          </a>
        </li>
      {/foreach}
    </ul>

    {if !isset($phyto_sidebar_mode) || !$phyto_sidebar_mode}
      <div class="phyto-bb-widget-cta">
        <a href="{$phyto_builder_base|escape:'html':'UTF-8'}"
           class="btn btn-primary phyto-bb-widget-btn">
          {l s='View All Bundles' mod='phyto_bundle_builder'}
        </a>
      </div>
    {/if}
  {else}
    <p class="phyto-bb-widget-empty">
      {l s='No bundles available right now.' mod='phyto_bundle_builder'}
    </p>
  {/if}

</div>
