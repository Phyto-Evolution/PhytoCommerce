{**
 * PhytoCommerce — product_list.tpl
 *
 * Rendered by hookDisplayProductListItem().
 * Displays mini badge pills on product listing / category-page cards.
 *
 * Smarty variables
 * ─────────────────
 *   $phyto_badges — array of badge rows (id_badge, badge_label, badge_slug, badge_color, description)
 **}

{if $phyto_badges}
<div class="phyto-badge-list-row" aria-label="{l s='Sourcing badges' mod='phyto_source_badge'}">
    {foreach $phyto_badges as $badge}
        {assign var='colorClass' value='phyto-badge-'|cat:$badge.badge_color|escape:'html'}
        <span
            class="phyto-badge-mini {$colorClass}"
            title="{if $badge.description}{$badge.description|escape:'html'}{else}{$badge.badge_label|escape:'html'}{/if}"
            aria-label="{$badge.badge_label|escape:'html'}"
        >
            {$badge.badge_label|escape:'html'}
        </span>
    {/foreach}
</div>
{/if}
