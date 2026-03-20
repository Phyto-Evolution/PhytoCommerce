{**
 * Wholesale Account Application Form
 *
 * Variables:
 *   $phyto_ws_customer       — Customer object
 *   $phyto_ws_existing       — Existing PhytoWholesaleApplication object or false
 *   $phyto_ws_form_url       — POST target URL
 *   $phyto_ws_token          — CSRF token
 *   $phyto_ws_errors         — array of error strings (optional)
 *   $phyto_ws_form_values    — repopulation array after failed submit (optional)
 *}

{extends file='page.tpl'}

{block name='page_title'}
    {l s='Apply for a Wholesale Account' mod='phyto_wholesale_portal'}
{/block}

{block name='page_content'}

<div class="phyto-ws-wrap">

    {* ------------------------------------------------------------------ *}
    {* Existing application status notice                                   *}
    {* ------------------------------------------------------------------ *}
    {if $phyto_ws_existing}
        {assign var="existingStatus" value=$phyto_ws_existing->status}

        {if $existingStatus == 'Pending'}
            <div class="alert alert-info phyto-ws-alert">
                <i class="material-icons" aria-hidden="true">hourglass_empty</i>
                {l s='Your application is currently under review. We will notify you once a decision has been made.' mod='phyto_wholesale_portal'}
            </div>
        {elseif $existingStatus == 'Approved'}
            <div class="alert alert-success phyto-ws-alert">
                <i class="material-icons" aria-hidden="true">check_circle</i>
                {l s='Your wholesale account is active. Thank you for being a valued wholesale partner.' mod='phyto_wholesale_portal'}
            </div>
        {elseif $existingStatus == 'Rejected'}
            <div class="alert alert-warning phyto-ws-alert">
                <i class="material-icons" aria-hidden="true">cancel</i>
                {l s='Your previous application was not approved. You may submit a new application below.' mod='phyto_wholesale_portal'}
            </div>
        {/if}
    {/if}

    {* ------------------------------------------------------------------ *}
    {* Validation errors                                                    *}
    {* ------------------------------------------------------------------ *}
    {if isset($phyto_ws_errors) && $phyto_ws_errors|@count > 0}
        <div class="alert alert-danger phyto-ws-alert">
            <ul class="phyto-ws-error-list">
                {foreach from=$phyto_ws_errors item=err}
                    <li>{$err|escape:'htmlall':'UTF-8'}</li>
                {/foreach}
            </ul>
        </div>
    {/if}

    {* ------------------------------------------------------------------ *}
    {* Only show form if no pending/approved application exists             *}
    {* ------------------------------------------------------------------ *}
    {if !$phyto_ws_existing || $phyto_ws_existing->status == 'Rejected'}

        <div class="phyto-ws-card card">
            <div class="card-header">
                <h3 class="phyto-ws-card-title">
                    {l s='Wholesale Account Application' mod='phyto_wholesale_portal'}
                </h3>
                <p class="phyto-ws-card-subtitle text-muted">
                    {l s='Please fill in your business details below. Fields marked with * are required.' mod='phyto_wholesale_portal'}
                </p>
            </div>

            <div class="card-body">
                <form action="{$phyto_ws_form_url|escape:'htmlall':'UTF-8'}"
                      method="POST"
                      class="phyto-ws-form"
                      novalidate>

                    <input type="hidden" name="token" value="{$phyto_ws_token|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" name="submitWholesaleApp" value="1">

                    {* Business Name *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label required" for="phyto_ws_business_name">
                            {l s='Business Name' mod='phyto_wholesale_portal'} *
                        </label>
                        <div class="col-md-9">
                            <input type="text"
                                   id="phyto_ws_business_name"
                                   name="business_name"
                                   class="form-control"
                                   maxlength="200"
                                   required
                                   value="{if isset($phyto_ws_form_values.business_name)}{$phyto_ws_form_values.business_name|escape:'htmlall':'UTF-8'}{/if}">
                        </div>
                    </div>

                    {* GST / Tax Number *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="phyto_ws_gst_number">
                            {l s='GST / Tax Number' mod='phyto_wholesale_portal'}
                        </label>
                        <div class="col-md-9">
                            <input type="text"
                                   id="phyto_ws_gst_number"
                                   name="gst_number"
                                   class="form-control"
                                   maxlength="30"
                                   value="{if isset($phyto_ws_form_values.gst_number)}{$phyto_ws_form_values.gst_number|escape:'htmlall':'UTF-8'}{/if}">
                        </div>
                    </div>

                    {* Phone *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label required" for="phyto_ws_phone">
                            {l s='Phone' mod='phyto_wholesale_portal'} *
                        </label>
                        <div class="col-md-9">
                            <input type="tel"
                                   id="phyto_ws_phone"
                                   name="phone"
                                   class="form-control"
                                   maxlength="30"
                                   required
                                   value="{if isset($phyto_ws_form_values.phone)}{$phyto_ws_form_values.phone|escape:'htmlall':'UTF-8'}{/if}">
                        </div>
                    </div>

                    {* Business Address *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="phyto_ws_address">
                            {l s='Business Address' mod='phyto_wholesale_portal'}
                        </label>
                        <div class="col-md-9">
                            <textarea id="phyto_ws_address"
                                      name="address"
                                      class="form-control"
                                      rows="3">{if isset($phyto_ws_form_values.address)}{$phyto_ws_form_values.address|escape:'htmlall':'UTF-8'}{/if}</textarea>
                        </div>
                    </div>

                    {* Website *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="phyto_ws_website">
                            {l s='Website' mod='phyto_wholesale_portal'}
                        </label>
                        <div class="col-md-9">
                            <input type="url"
                                   id="phyto_ws_website"
                                   name="website"
                                   class="form-control"
                                   maxlength="200"
                                   placeholder="https://"
                                   value="{if isset($phyto_ws_form_values.website)}{$phyto_ws_form_values.website|escape:'htmlall':'UTF-8'}{/if}">
                        </div>
                    </div>

                    {* Additional Message *}
                    <div class="form-group row">
                        <label class="col-md-3 col-form-label" for="phyto_ws_message">
                            {l s='Tell us about your business' mod='phyto_wholesale_portal'}
                        </label>
                        <div class="col-md-9">
                            <textarea id="phyto_ws_message"
                                      name="message"
                                      class="form-control"
                                      rows="5"
                                      placeholder="{l s='Describe your business, estimated monthly order volume, product interests, etc.' mod='phyto_wholesale_portal'}">{if isset($phyto_ws_form_values.message)}{$phyto_ws_form_values.message|escape:'htmlall':'UTF-8'}{/if}</textarea>
                        </div>
                    </div>

                    {* Submit *}
                    <div class="form-group row">
                        <div class="col-md-9 offset-md-3">
                            <button type="submit" class="btn btn-primary phyto-ws-btn-submit">
                                <i class="material-icons" aria-hidden="true">send</i>
                                {l s='Submit Application' mod='phyto_wholesale_portal'}
                            </button>
                        </div>
                    </div>

                </form>
            </div>{* /.card-body *}
        </div>{* /.phyto-ws-card *}

    {/if}{* end form condition *}

</div>{* /.phyto-ws-wrap *}

{/block}
