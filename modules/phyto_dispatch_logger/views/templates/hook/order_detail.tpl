{**
 * Phyto Dispatch Logger — front-office order detail dispatch info card
 *
 * Displayed via hookDisplayOrderDetail on the customer order history
 * detail page and on the order confirmation page.
 *
 * Available Smarty variables (assigned in hookDisplayOrderDetail):
 *   {$pdl_log}           — associative array of all log columns
 *   {$pdl_dispatch_date} — formatted dispatch date string
 *   {$pdl_photo_url}     — full URL to the photo (empty string if none)
 *   {$pdl_module_dir}    — module base URL (for assets)
 *}

<link rel="stylesheet"
      href="{$pdl_module_dir|escape:'html':'UTF-8'}views/css/front.css">

<section class="phyto-dispatch-card" aria-labelledby="phyto-dispatch-title">

    <header class="phyto-dispatch-header">
        <h3 id="phyto-dispatch-title" class="phyto-dispatch-title">
            {l s='Dispatch Conditions' mod='phyto_dispatch_logger'}
        </h3>
    </header>

    <div class="phyto-dispatch-body">

        <table class="phyto-dispatch-table" aria-label="{l s='Dispatch Conditions' mod='phyto_dispatch_logger'}">
            <tbody>

                {if $pdl_dispatch_date}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Dispatch Date' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {$pdl_dispatch_date|escape:'html':'UTF-8'}
                    </td>
                </tr>
                {/if}

                {if $pdl_log.temp_celsius !== null && $pdl_log.temp_celsius !== ''}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Temperature at Packing' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {$pdl_log.temp_celsius|escape:'html':'UTF-8'}&thinsp;°C
                    </td>
                </tr>
                {/if}

                {if $pdl_log.humidity_pct !== null && $pdl_log.humidity_pct !== ''}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Humidity at Packing' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {$pdl_log.humidity_pct|escape:'html':'UTF-8'}&thinsp;%
                    </td>
                </tr>
                {/if}

                {if $pdl_log.packing_method}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Packing Method' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {$pdl_log.packing_method|escape:'html':'UTF-8'}
                    </td>
                </tr>
                {/if}

                {if $pdl_log.gel_pack || $pdl_log.heat_pack}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Temperature Protection' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {if $pdl_log.gel_pack}
                            <span class="phyto-dispatch-badge phyto-dispatch-badge--cool">
                                {l s='Gel Pack' mod='phyto_dispatch_logger'}
                            </span>
                        {/if}
                        {if $pdl_log.heat_pack}
                            <span class="phyto-dispatch-badge phyto-dispatch-badge--warm">
                                {l s='Heat Pack' mod='phyto_dispatch_logger'}
                            </span>
                        {/if}
                        {if !$pdl_log.gel_pack && !$pdl_log.heat_pack}
                            {l s='None' mod='phyto_dispatch_logger'}
                        {/if}
                    </td>
                </tr>
                {/if}

                {if $pdl_log.transit_days !== null && $pdl_log.transit_days !== ''}
                <tr class="phyto-dispatch-row">
                    <th class="phyto-dispatch-label" scope="row">
                        {l s='Estimated Transit Time' mod='phyto_dispatch_logger'}
                    </th>
                    <td class="phyto-dispatch-value">
                        {$pdl_log.transit_days|escape:'html':'UTF-8'}
                        {l s='business day(s)' mod='phyto_dispatch_logger'}
                    </td>
                </tr>
                {/if}

            </tbody>
        </table>

        {* ── Dispatch photo ── *}
        {if $pdl_photo_url}
        <figure class="phyto-dispatch-photo">
            <a href="{$pdl_photo_url|escape:'html':'UTF-8'}"
               target="_blank"
               rel="noopener"
               title="{l s='View full-size dispatch photo' mod='phyto_dispatch_logger'}">
                <img src="{$pdl_photo_url|escape:'html':'UTF-8'}"
                     alt="{l s='Photo of your packed parcel at dispatch' mod='phyto_dispatch_logger'}"
                     class="phyto-dispatch-photo__img"
                     loading="lazy">
            </a>
            <figcaption class="phyto-dispatch-photo__caption">
                {l s='Parcel photo taken at point of dispatch' mod='phyto_dispatch_logger'}
            </figcaption>
        </figure>
        {/if}

        {* ── LAG notice ── *}
        <p class="phyto-dispatch-lag-notice">
            <svg class="phyto-dispatch-lag-icon" xmlns="http://www.w3.org/2000/svg"
                 viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10
                         10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
            </svg>
            {l s='This information is provided to support Live Arrival Guarantee claims.' mod='phyto_dispatch_logger'}
        </p>

    </div>{* /phyto-dispatch-body *}

</section>{* /phyto-dispatch-card *}
