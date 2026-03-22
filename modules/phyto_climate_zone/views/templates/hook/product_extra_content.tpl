{**
 * Phyto Climate Zone — Product Extra Content (v2)
 *
 * Smarty variables:
 *   $phyto_climate_check_url  - absolute HTTPS URL for the check front controller
 *   $phyto_climate_id_product - product ID (int)
 **}

<div class="phyto-climate-widget"
     data-check-url="{$phyto_climate_check_url|escape:'html':'UTF-8'}"
     data-id-product="{$phyto_climate_id_product|intval}">

    <p class="phyto-climate-intro">
        {l s='Enter your 6-digit pincode to check if this plant suits your local climate.' mod='phyto_climate_zone'}
    </p>

    <div class="phyto-climate-input-row">
        <input type="text"
               class="phyto-climate-pincode-input"
               maxlength="6"
               inputmode="numeric"
               pattern="\d{6}"
               placeholder="{l s='6-digit pincode' mod='phyto_climate_zone'}"
               aria-label="{l s='Enter your pincode' mod='phyto_climate_zone'}">

        <button type="button" class="phyto-climate-check-btn">
            <span class="phyto-climate-btn-text">{l s='Check Suitability' mod='phyto_climate_zone'}</span>
            <span class="phyto-climate-btn-loading" style="display:none;" aria-hidden="true">{l s='Checking…' mod='phyto_climate_zone'}</span>
        </button>
    </div>

    <div class="phyto-climate-input-error" style="display:none;" role="alert">
        {l s='Please enter a valid 6-digit pincode.' mod='phyto_climate_zone'}
    </div>

    {* Result area — revealed after a check *}
    <div class="phyto-climate-result" style="display:none;" role="status" aria-live="polite">

        {* Verdict banner *}
        <div class="phyto-climate-verdict">
            <span class="phyto-climate-zone-code"></span>
            <span class="phyto-climate-zone-name"></span>
            <span class="phyto-climate-verdict-icon" aria-hidden="true"></span>
        </div>

        <div class="phyto-climate-message"></div>

        {* Warnings (frost / rain / humidity) *}
        <ul class="phyto-climate-warnings" style="display:none;"></ul>

        {* Outdoor notes from admin *}
        <div class="phyto-climate-outdoor-notes" style="display:none;"></div>

        {* Monthly climate bar chart *}
        <div class="phyto-climate-chart-wrap" style="display:none;">
            <div class="phyto-climate-chart-title">
                {l s='Monthly climate in your zone' mod='phyto_climate_zone'}
                <span class="phyto-climate-chart-toggle">
                    <a href="#" class="phyto-climate-show-humidity">{l s='humidity' mod='phyto_climate_zone'}</a>
                    /
                    <a href="#" class="phyto-climate-show-temp phyto-climate-active">{l s='temperature' mod='phyto_climate_zone'}</a>
                </span>
            </div>
            <div class="phyto-climate-chart" role="img" aria-label="{l s='Monthly climate bar chart' mod='phyto_climate_zone'}">
                {* Bars injected by JS *}
            </div>
            <div class="phyto-climate-chart-legend">
                <span class="phyto-legend-temp">{l s='Avg temp (°C)' mod='phyto_climate_zone'}</span>
                <span class="phyto-legend-humidity" style="display:none;">{l s='Avg humidity (%)' mod='phyto_climate_zone'}</span>
            </div>

            {* Zone meta: cities, frost, monsoon *}
            <div class="phyto-climate-zone-meta"></div>
        </div>

    </div>{* /.phyto-climate-result *}

</div>{* /.phyto-climate-widget *}
