{* Phyto Acclimation Bundler — Cart widget (server-side fallback / no-JS) *}
{* The JS widget in acclimation.js shows/hides this based on trigger detection *}

<div id="phyto-acclim-widget" class="phyto-acclim-widget" style="display:none;" aria-live="polite">
    <div class="phyto-acclim-header">
        <span class="phyto-acclim-icon">🌱</span>
        <strong class="phyto-acclim-headline">{$phyto_acclim_headline|escape:'html':'UTF-8'}</strong>
        <button type="button" class="phyto-acclim-dismiss" aria-label="{l s='Dismiss' mod='phyto_acclimation_bundler'}">✕</button>
    </div>

    <div class="phyto-acclim-items">
        {foreach from=$phyto_acclim_kit_items item=item}
        <div class="phyto-acclim-item" data-id-product="{$item.id_product|intval}">
            {if $item.image_url}
            <img src="{$item.image_url|escape:'html':'UTF-8'}"
                 alt="{$item.name|escape:'html':'UTF-8'}"
                 class="phyto-acclim-item-img"
                 loading="lazy" width="60" height="60">
            {/if}
            <div class="phyto-acclim-item-info">
                <a href="{$item.url|escape:'html':'UTF-8'}" class="phyto-acclim-item-name">
                    {$item.name|escape:'html':'UTF-8'}
                </a>
                <span class="phyto-acclim-item-price">{$item.price_fmt|escape:'html':'UTF-8'}</span>
            </div>
            <button type="button"
                    class="phyto-acclim-add-one btn btn-sm btn-outline-primary"
                    data-id-product="{$item.id_product|intval}">
                {l s='Add' mod='phyto_acclimation_bundler'}
            </button>
        </div>
        {/foreach}
    </div>

    {if $phyto_acclim_discount > 0}
    <div class="phyto-acclim-footer">
        <button type="button" class="phyto-acclim-add-all btn btn-primary btn-block">
            {l s='Add all and save %discount%%' sprintf=['%discount%' => $phyto_acclim_discount] mod='phyto_acclimation_bundler'}
        </button>
    </div>
    {/if}
</div>
