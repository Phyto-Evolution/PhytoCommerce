{**
 * PhytoCommerce — product_extra_content.tpl
 *
 * Rendered by hookDisplayProductExtraContent().
 * Appears as a named tab ("Source & Origin") on the product page.
 * Each badge is shown as a labelled pill with an optional description block
 * and supplementary fields (permit reference / origin country).
 *
 * Smarty variables
 * ─────────────────
 *   $phyto_badges — array of badge rows:
 *     id_badge, badge_label, badge_slug, badge_color,
 *     description, permit_ref, origin_country
 **}

{if $phyto_badges}
<section class="phyto-extra-content" aria-labelledby="phyto-origin-heading">

    <h3 id="phyto-origin-heading" class="h4 phyto-origin-heading">
        {l s='Source &amp; Origin' mod='phyto_source_badge'}
    </h3>

    <p class="phyto-origin-intro text-muted">
        {l s='This plant has been assigned one or more sourcing badges that describe how it was propagated or obtained.' mod='phyto_source_badge'}
    </p>

    <ul class="phyto-badge-detail-list list-unstyled">
        {foreach $phyto_badges as $badge}
            {assign var='colorClass' value='phyto-badge-'|cat:$badge.badge_color|escape:'html'}

            <li class="phyto-badge-detail-item">

                {* Coloured pill *}
                <span class="phyto-badge-pill {$colorClass}" aria-label="{$badge.badge_label|escape:'html'}">
                    {$badge.badge_label|escape:'html'}
                </span>

                {* Description *}
                {if $badge.description}
                    <p class="phyto-badge-desc">
                        {$badge.description|escape:'html'}
                    </p>
                {/if}

                {* Permit / reference number (Wild Rescue) *}
                {if $badge.permit_ref}
                    <p class="phyto-badge-meta">
                        <strong>{l s='Permit / reference:' mod='phyto_source_badge'}</strong>
                        <span class="phyto-badge-permit">{$badge.permit_ref|escape:'html'}</span>
                    </p>
                {/if}

                {* Origin country (Import) *}
                {if $badge.origin_country}
                    <p class="phyto-badge-meta">
                        <strong>{l s='Origin country:' mod='phyto_source_badge'}</strong>
                        <span class="phyto-badge-country">{$badge.origin_country|escape:'html'}</span>
                    </p>
                {/if}

            </li>
        {/foreach}
    </ul>

</section>
{/if}
