{**
 * Phyto Live Arrival Guarantee — Claim Form Template
 *
 * Displayed by the claim front controller on GET request.
 * Bootstrap 3 form matching the PrestaShop 8 front-office style.
 *
 * Smarty variables:
 *   $phyto_lag_id_order      int     - order ID
 *   $phyto_lag_order_ref     string  - human-readable order reference
 *   $phyto_lag_claim_instr   string  - instructions from configuration
 *   $phyto_lag_claim_window  int     - claim window in days
 *   $phyto_lag_form_action   string  - POST URL for this form
 **}

{extends file='page.tpl'}

{block name='page_title'}
    {l s='Live Arrival Guarantee — File a Claim' mod='phyto_live_arrival'}
{/block}

{block name='page_content'}

<div class="phyto-lag-panel panel" id="phyto-lag-claim-form-panel">

    <div class="panel-heading">
        <span class="phyto-lag-badge">
            <i class="icon-leaf"></i>
            {l s='Live Arrival Guarantee Claim' mod='phyto_live_arrival'}
        </span>
    </div>

    <div class="panel-body">

        {* ── Order reference ────────────────────────────────────────── *}
        <p class="phyto-lag-claim-order-ref">
            {l s='Order reference:' mod='phyto_live_arrival'}
            <strong>{$phyto_lag_order_ref|escape:'html':'UTF-8'}</strong>
            &nbsp;
            <small class="text-muted">
                ({l s='Claim window:' mod='phyto_live_arrival'}
                {$phyto_lag_claim_window|intval}
                {l s='days from order date' mod='phyto_live_arrival'})
            </small>
        </p>

        {* ── Instructions ───────────────────────────────────────────── *}
        {if $phyto_lag_claim_instr}
            <div class="alert alert-info phyto-lag-claim-instructions">
                <i class="icon-info-sign"></i>
                {$phyto_lag_claim_instr|escape:'html':'UTF-8'|nl2br}
            </div>
        {/if}

        {* ── Error display ──────────────────────────────────────────── *}
        {if isset($errors) && $errors|@count > 0}
            <div class="alert alert-danger" role="alert">
                <ul class="list-unstyled" style="margin:0;">
                    {foreach from=$errors item=errorMsg}
                        <li>{$errorMsg|escape:'html':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {* ── Claim form ─────────────────────────────────────────────── *}
        <form id="phyto-lag-claim-form"
              method="post"
              action="{$phyto_lag_form_action|escape:'html':'UTF-8'}"
              enctype="multipart/form-data"
              novalidate>

            <input type="hidden" name="submit_claim"  value="1">
            <input type="hidden" name="id_order"      value="{$phyto_lag_id_order|intval}">

            {* ── Description ─────────────────────────────────────────── *}
            <div class="form-group">
                <label for="phyto-lag-description" class="required">
                    {l s='Description of the issue' mod='phyto_live_arrival'}
                    <span class="text-danger">*</span>
                </label>
                <textarea id="phyto-lag-description"
                          name="description"
                          class="form-control"
                          rows="6"
                          maxlength="4000"
                          required
                          placeholder="{l s='Please describe what happened to your order. Include the condition of the packaging and the plants on arrival.' mod='phyto_live_arrival'}"
                >{if isset($smarty.post.description)}{$smarty.post.description|escape:'html':'UTF-8'}{/if}</textarea>
                <span class="help-block">{l s='Maximum 4000 characters.' mod='phyto_live_arrival'}</span>
            </div>

            {* ── Photo uploads ───────────────────────────────────────── *}
            <fieldset>
                <legend>{l s='Photo Evidence (optional, max 3 photos)' mod='phyto_live_arrival'}</legend>

                <p class="help-block" style="margin-bottom:12px;">
                    {l s='Accepted formats: JPEG, PNG. Maximum file size: 2 MB per photo.' mod='phyto_live_arrival'}
                </p>

                <div class="form-group">
                    <label for="phyto-lag-photo-1">
                        {l s='Photo 1' mod='phyto_live_arrival'}
                    </label>
                    <input type="file"
                           id="phyto-lag-photo-1"
                           name="photo_1"
                           class="form-control"
                           accept="image/jpeg,image/png">
                </div>

                <div class="form-group">
                    <label for="phyto-lag-photo-2">
                        {l s='Photo 2' mod='phyto_live_arrival'}
                    </label>
                    <input type="file"
                           id="phyto-lag-photo-2"
                           name="photo_2"
                           class="form-control"
                           accept="image/jpeg,image/png">
                </div>

                <div class="form-group">
                    <label for="phyto-lag-photo-3">
                        {l s='Photo 3' mod='phyto_live_arrival'}
                    </label>
                    <input type="file"
                           id="phyto-lag-photo-3"
                           name="photo_3"
                           class="form-control"
                           accept="image/jpeg,image/png">
                </div>

            </fieldset>

            {* ── Actions ─────────────────────────────────────────────── *}
            <div class="form-group" style="margin-top:20px;">

                <button type="submit"
                        id="phyto-lag-submit-btn"
                        class="btn btn-primary">
                    <i class="icon-paper-plane"></i>
                    {l s='Submit Claim' mod='phyto_live_arrival'}
                </button>

                &nbsp;

                <a href="{$link->getPageLink('order-detail', true, null, "id_order={$phyto_lag_id_order|intval}")|escape:'html':'UTF-8'}"
                   class="btn btn-default">
                    <i class="icon-arrow-left"></i>
                    {l s='Back to Order' mod='phyto_live_arrival'}
                </a>

            </div>

        </form>

    </div>{* /.panel-body *}

</div>{* /#phyto-lag-claim-form-panel *}

{/block}
