{assign var='kyc_icon' value='check_circle'}
{if $phyto_kyc_status === 'Verified'}
    {assign var='kyc_label' value={l s='KYC Verified' mod='phyto_kyc'}}
    {assign var='kyc_badge' value='badge badge-success'}
{elseif $phyto_kyc_status === 'Pending'}
    {assign var='kyc_label' value={l s='KYC — Under Review' mod='phyto_kyc'}}
    {assign var='kyc_badge' value='badge badge-warning'}
{elseif $phyto_kyc_status === 'Rejected'}
    {assign var='kyc_label' value={l s='KYC — Rejected (resubmit)' mod='phyto_kyc'}}
    {assign var='kyc_badge' value='badge badge-danger'}
{else}
    {assign var='kyc_label' value={l s='Complete KYC Verification' mod='phyto_kyc'}}
    {assign var='kyc_badge' value='badge badge-secondary'}
{/if}

<div class="phyto-kyc-account-link">
    <a href="{$phyto_kyc_url|escape:'htmlall':'UTF-8'}" class="account-link">
        <i class="material-icons">verified_user</i>
        {$kyc_label}
        {if $phyto_kyc_status !== 'Verified'}
            &nbsp;<span class="badge badge-warning" style="font-size:10px;">!</span>
        {/if}
    </a>
</div>
