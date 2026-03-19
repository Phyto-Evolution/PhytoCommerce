{**
 * Phyto Dispatch Logger — admin order detail tab content panel
 *
 * Injected via hookDisplayAdminOrderTabContent.
 * Shows a summary of the dispatch log if one exists, otherwise a prompt
 * to create one.
 *}

<div id="phyto_dispatch_logger_content"
     class="tab-pane"
     role="tabpanel">

    <div class="panel panel-default" style="margin: 15px;">

        <div class="panel-heading">
            <i class="icon icon-truck"></i>
            {l s='Dispatch Log' mod='phyto_dispatch_logger'}
        </div>

        <div class="panel-body">

            {if $pdl_log}

                {* ── Existing log summary ── *}
                <table class="table table-bordered table-striped" style="margin-bottom: 15px;">
                    <tbody>

                        {if $pdl_log.dispatch_date}
                        <tr>
                            <th style="width:35%">{l s='Dispatch Date' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.dispatch_date|escape:'html':'UTF-8'}</td>
                        </tr>
                        {/if}

                        {if $pdl_log.staff_name}
                        <tr>
                            <th>{l s='Packed by' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.staff_name|escape:'html':'UTF-8'}</td>
                        </tr>
                        {/if}

                        {if $pdl_log.temp_celsius !== null && $pdl_log.temp_celsius !== ''}
                        <tr>
                            <th>{l s='Temperature' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.temp_celsius|escape:'html':'UTF-8'}&nbsp;°C</td>
                        </tr>
                        {/if}

                        {if $pdl_log.humidity_pct !== null && $pdl_log.humidity_pct !== ''}
                        <tr>
                            <th>{l s='Humidity' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.humidity_pct|escape:'html':'UTF-8'}&nbsp;%</td>
                        </tr>
                        {/if}

                        {if $pdl_log.packing_method}
                        <tr>
                            <th>{l s='Packing Method' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.packing_method|escape:'html':'UTF-8'}</td>
                        </tr>
                        {/if}

                        <tr>
                            <th>{l s='Gel Pack' mod='phyto_dispatch_logger'}</th>
                            <td>
                                {if $pdl_log.gel_pack}
                                    <span class="label label-success">{l s='Yes' mod='phyto_dispatch_logger'}</span>
                                {else}
                                    <span class="label label-default">{l s='No' mod='phyto_dispatch_logger'}</span>
                                {/if}
                            </td>
                        </tr>

                        <tr>
                            <th>{l s='Heat Pack' mod='phyto_dispatch_logger'}</th>
                            <td>
                                {if $pdl_log.heat_pack}
                                    <span class="label label-warning">{l s='Yes' mod='phyto_dispatch_logger'}</span>
                                {else}
                                    <span class="label label-default">{l s='No' mod='phyto_dispatch_logger'}</span>
                                {/if}
                            </td>
                        </tr>

                        {if $pdl_log.transit_days !== null && $pdl_log.transit_days !== ''}
                        <tr>
                            <th>{l s='Estimated Transit' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.transit_days|escape:'html':'UTF-8'}&nbsp;{l s='days' mod='phyto_dispatch_logger'}</td>
                        </tr>
                        {/if}

                        {if $pdl_log.notes}
                        <tr>
                            <th>{l s='Notes' mod='phyto_dispatch_logger'}</th>
                            <td>{$pdl_log.notes|escape:'html':'UTF-8'|nl2br}</td>
                        </tr>
                        {/if}

                    </tbody>
                </table>

                {* ── Photo thumbnail ── *}
                {if $pdl_photo_url}
                <div style="margin-bottom: 15px;">
                    <strong>{l s='Dispatch Photo' mod='phyto_dispatch_logger'}</strong><br>
                    <a href="{$pdl_photo_url|escape:'html':'UTF-8'}" target="_blank"
                       title="{l s='View full size' mod='phyto_dispatch_logger'}">
                        <img src="{$pdl_photo_url|escape:'html':'UTF-8'}"
                             alt="{l s='Dispatch photo' mod='phyto_dispatch_logger'}"
                             style="max-width:200px; max-height:200px; border:1px solid #ddd; border-radius:4px; padding:3px;">
                    </a>
                </div>
                {/if}

                {* ── Edit link ── *}
                <a href="{$pdl_admin_link|escape:'html':'UTF-8'}"
                   class="btn btn-default btn-sm">
                    <i class="icon icon-pencil"></i>
                    {l s='Edit Dispatch Log' mod='phyto_dispatch_logger'}
                </a>

            {else}

                {* ── No log yet ── *}
                <div class="alert alert-info" style="margin-bottom: 15px;">
                    <i class="icon icon-info-circle"></i>
                    {l s='No dispatch log has been created for this order yet.' mod='phyto_dispatch_logger'}
                </div>

                <a href="{$pdl_admin_link|escape:'html':'UTF-8'}"
                   class="btn btn-primary btn-sm">
                    <i class="icon icon-plus"></i>
                    {l s='Create Dispatch Log' mod='phyto_dispatch_logger'}
                </a>

            {/if}

        </div>{* /panel-body *}
    </div>{* /panel *}
</div>{* /tab-pane *}
