{**
 * Phyto Grex Registry — Front-End Taxonomy Card
 *
 * Renders a collapsible scientific profile card on the product page.
 *}

<div class="phyto-grex-card" id="phyto-grex-scientific-profile">

    {* ICPS Registration Badge *}
    {if isset($phyto_grex_data.icps_registered) && $phyto_grex_data.icps_registered}
        <div class="phyto-grex-badge-row">
            <span class="phyto-grex-badge phyto-grex-badge--icps">
                <i class="material-icons phyto-grex-badge-icon">verified</i>
                {l s='ICPS Registered' mod='phyto_grex_registry'}
                {if !empty($phyto_grex_data.icps_number)}
                    <span class="phyto-grex-badge-number">#{$phyto_grex_data.icps_number|escape:'htmlall':'UTF-8'}</span>
                {/if}
            </span>
        </div>
    {/if}

    {* Conservation Status Badge *}
    {if isset($phyto_grex_data.conservation_status) && $phyto_grex_data.conservation_status != ''}
        <div class="phyto-grex-badge-row">
            <span class="phyto-grex-badge phyto-grex-badge--conservation phyto-grex-badge--{$phyto_grex_data.conservation_status|lower|escape:'htmlall':'UTF-8'}">
                {if isset($phyto_grex_conservation_statuses[$phyto_grex_data.conservation_status])}
                    {$phyto_grex_conservation_statuses[$phyto_grex_data.conservation_status]|escape:'htmlall':'UTF-8'}
                {else}
                    {$phyto_grex_data.conservation_status|escape:'htmlall':'UTF-8'}
                {/if}
            </span>
        </div>
    {/if}

    {* Taxonomy Definition List *}
    {if !empty($phyto_grex_fields)}
        <dl class="phyto-grex-dl">
            {foreach from=$phyto_grex_fields item=field}
                <div class="phyto-grex-dl-row">
                    <dt class="phyto-grex-dt">{$field.label|escape:'htmlall':'UTF-8'}</dt>
                    <dd class="phyto-grex-dd">{$field.value nofilter}</dd>
                </div>
            {/foreach}
        </dl>
    {/if}

</div>
