{**
 * Phyto Climate Zone — Product Extra Content (front-end widget)
 *
 * Displayed as a product tab via hookDisplayProductExtraContent.
 * Lets the customer enter their pincode and immediately see whether
 * the plant is suitable for their climate zone.
 *
 * Smarty variables:
 *   $phyto_climate_check_url  - absolute HTTPS URL for the check front controller
 *   $phyto_climate_id_product - product ID (int)
 **}

<div class="phyto-climate-widget" data-check-url="{$phyto_climate_check_url|escape:'html':'UTF-8'}" data-id-product="{$phyto_climate_id_product|intval}">

    <p class="phyto-climate-intro">
        {l s='Enter your pincode to find out if this plant is suited to your local climate.' mod='phyto_climate_zone'}
    </p>

    <div class="phyto-climate-input-row">
        <input type="text"
               id="phyto-climate-pincode"
               class="phyto-climate-pincode-input"
               maxlength="6"
               inputmode="numeric"
               pattern="\d{6}"
               placeholder="{l s='6-digit pincode' mod='phyto_climate_zone'}"
               aria-label="{l s='Enter your pincode' mod='phyto_climate_zone'}">

        <button type="button"
                id="phyto-climate-check-btn"
                class="phyto-climate-check-btn">
            <span class="phyto-climate-btn-text">{l s='Check Suitability' mod='phyto_climate_zone'}</span>
            <span class="phyto-climate-btn-loading" style="display:none;" aria-hidden="true">{l s='Checking…' mod='phyto_climate_zone'}</span>
        </button>
    </div>

    {* Error message for invalid pincode format *}
    <div id="phyto-climate-input-error" class="phyto-climate-input-error" style="display:none;" role="alert">
        {l s='Please enter a valid 6-digit pincode.' mod='phyto_climate_zone'}
    </div>

    {* Result area — hidden until a check has been performed *}
    <div id="phyto-climate-result"
         class="phyto-climate-result"
         style="display:none;"
         role="status"
         aria-live="polite">

        <div class="phyto-climate-zone-name">
            {l s='Your zone:' mod='phyto_climate_zone'}
            <strong id="phyto-climate-zone-label"></strong>
        </div>

        <div id="phyto-climate-message" class="phyto-climate-message"></div>

    </div>

</div>
