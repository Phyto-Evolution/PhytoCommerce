{**
 * Phyto Live Arrival Guarantee — Order Detail Panel Template
 *
 * Rendered by hookDisplayOrderDetail on the customer's order detail page.
 * Shows the LAG status, fee charged, and any existing or available claim.
 *
 * Smarty variables:
 *   $phyto_lag_opted          bool    - whether LAG was opted in (always true here)
 *   $phyto_lag_fee_charged    string  - formatted fee that was charged
 *   $phyto_lag_can_claim      bool    - whether the claim window is still open
 *   $phyto_lag_claim_window   int     - number of days in the claim window
 *   $phyto_lag_claim_url      string  - URL to the claim front controller
 *   $phyto_lag_existing_claim array|false
 *   $phyto_lag_claim_status   string  - 'pending'|'approved'|'rejected'|''
 **}

<div class="phyto-lag-panel panel" id="phyto-lag-order-detail-panel">

    <div class="panel-heading">
        <span class="phyto-lag-badge phyto-lag-badge--active">
            <i class="icon-leaf"></i>
            {l s='Live Arrival Guarantee: Active' mod='phyto_live_arrival'}
        </span>
    </div>

    <div class="panel-body">

        {* ── Fee charged ────────────────────────────────────────────── *}
        <p class="phyto-lag-fee-charged">
            {l s='LAG Fee charged:' mod='phyto_live_arrival'}
            <strong>{$phyto_lag_fee_charged|escape:'html':'UTF-8'}</strong>
        </p>

        {* ── Claim section ──────────────────────────────────────────── *}
        {if $phyto_lag_existing_claim}

            {* A claim has already been filed *}
            <div class="phyto-lag-claim-status-row">
                <span>{l s='Claim status:' mod='phyto_live_arrival'}</span>

                {if $phyto_lag_claim_status == 'approved'}
                    <span class="phyto-lag-status-badge phyto-lag-status-badge--approved">
                        <i class="icon-check-circle"></i>
                        {l s='Approved' mod='phyto_live_arrival'}
                    </span>
                {elseif $phyto_lag_claim_status == 'rejected'}
                    <span class="phyto-lag-status-badge phyto-lag-status-badge--rejected">
                        <i class="icon-times-circle"></i>
                        {l s='Rejected' mod='phyto_live_arrival'}
                    </span>
                {else}
                    <span class="phyto-lag-status-badge phyto-lag-status-badge--pending">
                        <i class="icon-clock-o"></i>
                        {l s='Pending' mod='phyto_live_arrival'}
                    </span>
                {/if}
            </div>

        {elseif $phyto_lag_can_claim}

            {* Claim window open and no claim yet *}
            <div class="phyto-lag-claim-action">
                <p class="phyto-lag-claim-hint">
                    {l s='If your live goods arrived in poor condition, you may file a claim within your claim window.' mod='phyto_live_arrival'}
                </p>
                <a href="{$phyto_lag_claim_url|escape:'html':'UTF-8'}"
                   class="btn btn-warning phyto-lag-claim-btn">
                    <i class="icon-exclamation-triangle"></i>
                    {l s='File a Claim' mod='phyto_live_arrival'}
                </a>
                <small class="phyto-lag-claim-window-note">
                    {l s='(Claim window:' mod='phyto_live_arrival'}
                    {$phyto_lag_claim_window|intval}
                    {l s='days from order date)' mod='phyto_live_arrival'}
                </small>
            </div>

        {else}

            {* Claim window expired, no existing claim *}
            <div class="phyto-lag-claim-expired">
                <span class="phyto-lag-expired-badge">
                    <i class="icon-lock"></i>
                    {l s='Claim window closed' mod='phyto_live_arrival'}
                </span>
                <small class="phyto-lag-claim-window-note">
                    {l s='The' mod='phyto_live_arrival'}
                    {$phyto_lag_claim_window|intval}
                    {l s='-day claim window has passed.' mod='phyto_live_arrival'}
                </small>
            </div>

        {/if}

    </div>{* /.panel-body *}

</div>{* /#phyto-lag-order-detail-panel *}
