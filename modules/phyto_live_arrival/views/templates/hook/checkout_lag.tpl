{**
 * Phyto Live Arrival Guarantee — Checkout Panel Template
 *
 * Rendered by hookDisplayBeforeCarrier at the PrestaShop checkout.
 * Shows the LAG opt-in toggle, fee (or FREE badge), terms (collapsible),
 * the next valid ship date, and a warning when today is not a ship day.
 *
 * Smarty variables:
 *   $phyto_lag_fee             float   - effective fee (0 when free)
 *   $phyto_lag_fee_formatted   string  - formatted price string
 *   $phyto_lag_is_free         bool
 *   $phyto_lag_free_above      float   - threshold above which LAG is free
 *   $phyto_lag_terms           string  - terms body text
 *   $phyto_lag_today_allowed   bool    - is today a valid ship day?
 *   $phyto_lag_next_ship_date  string  - human-readable next ship date
 *   $phyto_lag_opted_in        bool    - current opt-in state (from cookie)
 *   $phyto_lag_currency_sign   string  - currency symbol
 **}

<div class="phyto-lag-panel panel" id="phyto-lag-panel">

    <div class="panel-heading">
        <span class="phyto-lag-badge">
            <i class="icon-leaf"></i>
            {l s='Live Arrival Guarantee' mod='phyto_live_arrival'}
        </span>
    </div>

    <div class="panel-body">

        {* ── Today-not-a-ship-day warning ───────────────────────────── *}
        {if !$phyto_lag_today_allowed}
            <div class="alert alert-warning phyto-lag-ship-warning">
                <i class="icon-warning-sign"></i>
                {l s='Today is not a valid dispatch day for live shipments.' mod='phyto_live_arrival'}
                {l s='Your order will ship on:' mod='phyto_live_arrival'}
                <strong>{$phyto_lag_next_ship_date|escape:'html':'UTF-8'}</strong>
            </div>
        {else}
            <div class="phyto-lag-ship-notice">
                <i class="icon-calendar"></i>
                {l s='Next valid ship date:' mod='phyto_live_arrival'}
                <strong>{$phyto_lag_next_ship_date|escape:'html':'UTF-8'}</strong>
            </div>
        {/if}

        {* ── Opt-in toggle row ──────────────────────────────────────── *}
        <div class="phyto-lag-toggle-row">

            <label class="phyto-lag-toggle" for="phyto-lag-optin-checkbox">
                <input type="checkbox"
                       id="phyto-lag-optin-checkbox"
                       name="phyto_lag_opted"
                       value="1"
                       {if $phyto_lag_opted_in}checked="checked"{/if}
                       aria-describedby="phyto-lag-fee-display">

                <span class="phyto-lag-toggle-label">
                    {l s='Add Live Arrival Guarantee to my order' mod='phyto_live_arrival'}
                </span>
            </label>

            <span id="phyto-lag-fee-display" class="phyto-lag-fee-display">
                {if $phyto_lag_is_free}
                    <span class="phyto-lag-free-badge">{l s='FREE' mod='phyto_live_arrival'}</span>
                    {if $phyto_lag_free_above > 0}
                        <small class="phyto-lag-free-note">
                            {l s='(free on orders over' mod='phyto_live_arrival'}
                            {$phyto_lag_currency_sign|escape:'html':'UTF-8'}{$phyto_lag_free_above|string_format:'%.2f'}
                            {l s=')' mod='phyto_live_arrival'}
                        </small>
                    {/if}
                {else}
                    <span class="phyto-lag-fee-amount">+ {$phyto_lag_fee_formatted|escape:'html':'UTF-8'}</span>
                {/if}
            </span>

        </div>{* /.phyto-lag-toggle-row *}

        {* ── Collapsible terms ──────────────────────────────────────── *}
        {if $phyto_lag_terms}
            <div class="phyto-lag-terms-section">
                <button type="button"
                        class="btn btn-link phyto-lag-terms-toggle"
                        id="phyto-lag-terms-toggle"
                        aria-expanded="false"
                        aria-controls="phyto-lag-terms-body">
                    <i class="icon-info-sign"></i>
                    {l s='View terms & conditions' mod='phyto_live_arrival'}
                    <i class="icon-angle-down phyto-lag-terms-caret"></i>
                </button>

                <div id="phyto-lag-terms-body"
                     class="phyto-lag-terms"
                     style="display:none;"
                     role="region"
                     aria-labelledby="phyto-lag-terms-toggle">
                    <p>{$phyto_lag_terms|escape:'html':'UTF-8'|nl2br}</p>
                </div>
            </div>
        {/if}

    </div>{* /.panel-body *}

</div>{* /#phyto-lag-panel *}
