{extends file='page.tpl'}

{block name='page_title'}
    {l s='Identity Verification (KYC)' mod='phyto_kyc'}
{/block}

{block name='page_content'}
<div class="phyto-kyc-page">

    {* ── Status bar ── *}
    <div class="kyc-status-bar mb-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card {if $phyto_kyc_l1_verified}border-success{elseif $phyto_kyc_l1_status == 'Pending'}border-warning{elseif $phyto_kyc_l1_status == 'Rejected'}border-danger{else}border-secondary{/if}">
                    <div class="card-body py-2">
                        <strong>{l s='Level 1 — PAN Verification' mod='phyto_kyc'}</strong><br>
                        {if $phyto_kyc_l1_verified}
                            <span class="text-success"><i class="material-icons" style="font-size:16px;vertical-align:middle;">check_circle</i> {l s='Verified' mod='phyto_kyc'}</span>
                        {elseif $phyto_kyc_l1_status == 'Pending'}
                            <span class="text-warning"><i class="material-icons" style="font-size:16px;vertical-align:middle;">hourglass_empty</i> {l s='Under Review' mod='phyto_kyc'}</span>
                        {elseif $phyto_kyc_l1_status == 'Rejected'}
                            <span class="text-danger"><i class="material-icons" style="font-size:16px;vertical-align:middle;">cancel</i> {l s='Rejected — Please resubmit' mod='phyto_kyc'}</span>
                        {else}
                            <span class="text-muted">{l s='Not started' mod='phyto_kyc'}</span>
                        {/if}
                    </div>
                </div>
            </div>
            {if $phyto_kyc_require_l2}
            <div class="col-md-6">
                <div class="card {if $phyto_kyc_l2_verified}border-success{elseif $phyto_kyc_l2_status == 'Pending'}border-warning{elseif $phyto_kyc_l2_status == 'Rejected'}border-danger{else}border-secondary{/if}">
                    <div class="card-body py-2">
                        <strong>{l s='Level 2 — GST / Business Verification' mod='phyto_kyc'}</strong><br>
                        {if $phyto_kyc_l2_verified}
                            <span class="text-success"><i class="material-icons" style="font-size:16px;vertical-align:middle;">check_circle</i> {l s='Verified' mod='phyto_kyc'}</span>
                        {elseif $phyto_kyc_l2_status == 'Pending'}
                            <span class="text-warning"><i class="material-icons" style="font-size:16px;vertical-align:middle;">hourglass_empty</i> {l s='Under Review' mod='phyto_kyc'}</span>
                        {elseif $phyto_kyc_l2_status == 'Rejected'}
                            <span class="text-danger"><i class="material-icons" style="font-size:16px;vertical-align:middle;">cancel</i> {l s='Rejected — Please resubmit' mod='phyto_kyc'}</span>
                        {else}
                            <span class="text-muted">{l s='Not started' mod='phyto_kyc'}</span>
                        {/if}
                    </div>
                </div>
            </div>
            {/if}
        </div>
    </div>

    {* ── Errors / success messages ── *}
    {if isset($phyto_kyc_errors) && $phyto_kyc_errors}
        <div class="alert alert-danger">
            <ul class="mb-0">
                {foreach $phyto_kyc_errors as $err}
                    <li>{$err|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ul>
        </div>
    {/if}
    {if isset($phyto_kyc_success) && $phyto_kyc_success}
        <div class="alert alert-success">{$phyto_kyc_success|escape:'htmlall':'UTF-8'}</div>
    {/if}

    {* ── Already fully verified ── *}
    {if $phyto_kyc_fully_verified}
        <div class="alert alert-success">
            <i class="material-icons" style="vertical-align:middle;">verified_user</i>
            {l s='Your account is fully verified. You can see all prices and place orders.' mod='phyto_kyc'}
        </div>

    {* ── Level 1 form (PAN) ── *}
    {elseif !$phyto_kyc_l1_verified && $phyto_kyc_l1_status != 'Pending'}
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">{l s='Step 1: PAN Card Verification' mod='phyto_kyc'}</h4></div>
            <div class="card-body">
                <p class="text-muted">{l s='Enter your PAN number. We will verify it instantly via the government database.' mod='phyto_kyc'}</p>
                <form action="{$phyto_kyc_form_url|escape:'htmlall':'UTF-8'}" method="post">
                    <input type="hidden" name="token" value="{$phyto_kyc_token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="submitKycL1" value="1">
                    <div class="form-group">
                        <label for="pan_number">{l s='PAN Number' mod='phyto_kyc'} <span class="text-danger">*</span></label>
                        <input type="text"
                               id="pan_number"
                               name="pan_number"
                               class="form-control"
                               placeholder="ABCDE1234F"
                               maxlength="10"
                               style="text-transform:uppercase;max-width:220px;"
                               value="{if isset($phyto_kyc_pan_value)}{$phyto_kyc_pan_value|escape:'htmlall':'UTF-8'}{/if}"
                               required>
                        <small class="form-text text-muted">{l s='Format: 5 letters, 4 digits, 1 letter (e.g. ABCDE1234F)' mod='phyto_kyc'}</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        {l s='Verify PAN' mod='phyto_kyc'}
                    </button>
                </form>
            </div>
        </div>

    {* ── Level 1 pending — waiting for admin review ── *}
    {elseif $phyto_kyc_l1_status == 'Pending'}
        <div class="alert alert-warning">
            <strong>{l s='PAN verification is under review.' mod='phyto_kyc'}</strong><br>
            {l s='Our team will review your submission within 24 hours. You will receive an email when it is approved.' mod='phyto_kyc'}
        </div>
    {/if}

    {* ── Level 2 form (GST / Business) — only shown after L1 is verified ── *}
    {if $phyto_kyc_require_l2 && $phyto_kyc_l1_verified && !$phyto_kyc_l2_verified && $phyto_kyc_l2_status != 'Pending'}
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0">{l s='Step 2: Business / GST Verification' mod='phyto_kyc'}</h4></div>
            <div class="card-body">
                <p class="text-muted">{l s='For wholesale pricing, please verify your GST number or business PAN.' mod='phyto_kyc'}</p>
                <form action="{$phyto_kyc_form_url|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="{$phyto_kyc_token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="submitKycL2" value="1">

                    <div class="form-group">
                        <label for="gst_number">{l s='GST Number (GSTIN)' mod='phyto_kyc'}</label>
                        <input type="text"
                               id="gst_number"
                               name="gst_number"
                               class="form-control"
                               placeholder="22ABCDE1234F1Z5"
                               maxlength="15"
                               style="text-transform:uppercase;max-width:280px;">
                        <small class="form-text text-muted">{l s='Leave blank if you do not have a GST number.' mod='phyto_kyc'}</small>
                    </div>

                    <div class="form-group">
                        <label for="business_pan">{l s='Business PAN (if different from personal PAN)' mod='phyto_kyc'}</label>
                        <input type="text"
                               id="business_pan"
                               name="business_pan"
                               class="form-control"
                               placeholder="ABCDE1234F"
                               maxlength="10"
                               style="text-transform:uppercase;max-width:220px;">
                    </div>

                    <div class="form-group">
                        <label>{l s='Supporting Document (optional)' mod='phyto_kyc'}</label>
                        <input type="file" name="kyc_doc" class="form-control-file"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">{l s='PDF, JPG or PNG, max 5 MB.' mod='phyto_kyc'}</small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {l s='Submit for Business Verification' mod='phyto_kyc'}
                    </button>
                </form>
            </div>
        </div>
    {/if}

    <div class="mt-3">
        <a href="{$urls.pages.my_account}" class="btn btn-outline-secondary btn-sm">
            &larr; {l s='Back to My Account' mod='phyto_kyc'}
        </a>
    </div>

</div>
{/block}
