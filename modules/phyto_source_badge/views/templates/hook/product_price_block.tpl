{**
 * PhytoCommerce — product_price_block.tpl
 *
 * Rendered by hookDisplayProductPriceBlock() when $params['type'] == 'after_price'.
 * Displays compact badge pills directly beneath the product price.
 *
 * Smarty variables
 * ─────────────────
 *   $phyto_badges — array of badge rows (id_badge, badge_label, badge_slug, badge_color,
 *                   description, permit_ref, origin_country)
 **}

{if $phyto_badges}
<div class="phyto-badge-price-row" aria-label="{l s='Sourcing badges' mod='phyto_source_badge'}">
    {foreach $phyto_badges as $badge}
        {assign var='colorClass' value='phyto-badge-'|cat:$badge.badge_color|escape:'html'}
        <span
            class="phyto-badge-pill {$colorClass}"
            title="{if $badge.description}{$badge.description|escape:'html'}{else}{$badge.badge_label|escape:'html'}{/if}"
            aria-label="{$badge.badge_label|escape:'html'}"
        >
            {$badge.badge_label|escape:'html'}
        </span>
    {/foreach}
</div>
{/if}
