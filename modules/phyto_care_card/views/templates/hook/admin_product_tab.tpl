{**
 * Phyto Care Card — Admin Product Tab Template
 *
 * Rendered inside the product edit page via hookDisplayAdminProductsExtra.
 * Provides a Bootstrap 3 form for editing all care card fields with AJAX save.
 *
 * Smarty variables:
 *   $phyto_care_data            - array of saved care field values
 *   $phyto_care_ajax_url        - URL for the AdminPhytoCareCard controller
 *   $phyto_care_id_product      - current product ID (int)
 *   $phyto_care_pdf_preview_url - URL to download/preview the care card PDF
 *   $phyto_care_csrf_token      - PS CSRF token
 *   $phyto_light_options        - assoc array value => label
 *   $phyto_water_type_options   - assoc array value => label
 *   $phyto_water_method_options - assoc array value => label
 **}

<div id="phyto-care-card-tab" class="panel">

    <div class="panel-heading">
        <i class="icon-leaf"></i>
        {l s='Plant Care Card' mod='phyto_care_card'}
    </div>

    <div class="panel-body">

        {* ── Alert area (filled by JS) ─────────────────────────────── *}
        <div id="phyto-care-alert" style="display:none;" class="alert" role="alert"></div>

        <form id="phyto-care-form" novalidate>
            <input type="hidden" name="id_product"   value="{$phyto_care_id_product|intval}">
            <input type="hidden" name="phyto_ajax"   value="1">
            <input type="hidden" name="action"        value="save_care">
            <input type="hidden" name="token"         value="{$phyto_care_csrf_token|escape:'html':'UTF-8'}">

            <div class="row">

                {* ── Light ─────────────────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_light">
                            {l s='Light Requirement' mod='phyto_care_card'}
                        </label>
                        <select name="light" id="phyto_light" class="form-control">
                            {foreach from=$phyto_light_options key=val item=label}
                                <option value="{$val|escape:'html':'UTF-8'}"
                                    {if isset($phyto_care_data.light) && $phyto_care_data.light == $val}selected="selected"{/if}>
                                    {$label|escape:'html':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                {* ── Water Type ─────────────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_water_type">
                            {l s='Water Type' mod='phyto_care_card'}
                        </label>
                        <select name="water_type" id="phyto_water_type" class="form-control">
                            {foreach from=$phyto_water_type_options key=val item=label}
                                <option value="{$val|escape:'html':'UTF-8'}"
                                    {if isset($phyto_care_data.water_type) && $phyto_care_data.water_type == $val}selected="selected"{/if}>
                                    {$label|escape:'html':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                {* ── Watering Method ────────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_water_method">
                            {l s='Watering Method' mod='phyto_care_card'}
                        </label>
                        <select name="water_method" id="phyto_water_method" class="form-control">
                            {foreach from=$phyto_water_method_options key=val item=label}
                                <option value="{$val|escape:'html':'UTF-8'}"
                                    {if isset($phyto_care_data.water_method) && $phyto_care_data.water_method == $val}selected="selected"{/if}>
                                    {$label|escape:'html':'UTF-8'}
                                </option>
                            {/foreach}
                        </select>
                    </div>
                </div>

            </div>{* /.row *}

            <div class="row">

                {* ── Humidity Range ─────────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_humidity">
                            {l s='Humidity Range' mod='phyto_care_card'}
                        </label>
                        <input type="text"
                               id="phyto_humidity"
                               name="humidity"
                               class="form-control"
                               placeholder="{l s='e.g. 60–80%' mod='phyto_care_card'}"
                               value="{if isset($phyto_care_data.humidity)}{$phyto_care_data.humidity|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>

                {* ── Temperature Range ──────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_temperature">
                            {l s='Temperature Range' mod='phyto_care_card'}
                        </label>
                        <input type="text"
                               id="phyto_temperature"
                               name="temperature"
                               class="form-control"
                               placeholder="{l s='e.g. 18–28 °C' mod='phyto_care_card'}"
                               value="{if isset($phyto_care_data.temperature)}{$phyto_care_data.temperature|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>

                {* ── Soil / Media ───────────────────────────────────── *}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="control-label" for="phyto_media">
                            {l s='Soil / Media' mod='phyto_care_card'}
                        </label>
                        <input type="text"
                               id="phyto_media"
                               name="media"
                               class="form-control"
                               placeholder="{l s='e.g. Sphagnum moss, perlite mix' mod='phyto_care_card'}"
                               value="{if isset($phyto_care_data.media)}{$phyto_care_data.media|escape:'html':'UTF-8'}{/if}">
                    </div>
                </div>

            </div>{* /.row *}

            <div class="row">

                {* ── Feed Protocol ──────────────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phyto_feed">
                            {l s='Feed Protocol' mod='phyto_care_card'}
                        </label>
                        <textarea id="phyto_feed"
                                  name="feed"
                                  class="form-control"
                                  rows="2"
                                  placeholder="{l s='Describe fertilisation schedule and type…' mod='phyto_care_card'}"
                        >{if isset($phyto_care_data.feed)}{$phyto_care_data.feed|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>
                </div>

                {* ── Dormancy Instructions ──────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phyto_dormancy">
                            {l s='Dormancy Instructions' mod='phyto_care_card'}
                        </label>
                        <textarea id="phyto_dormancy"
                                  name="dormancy"
                                  class="form-control"
                                  rows="2"
                                  placeholder="{l s='Describe dormancy period care if applicable…' mod='phyto_care_card'}"
                        >{if isset($phyto_care_data.dormancy)}{$phyto_care_data.dormancy|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>
                </div>

            </div>{* /.row *}

            <div class="row">

                {* ── Potting Tips ───────────────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phyto_potting">
                            {l s='Potting Tips' mod='phyto_care_card'}
                        </label>
                        <textarea id="phyto_potting"
                                  name="potting"
                                  class="form-control"
                                  rows="2"
                                  placeholder="{l s='Describe repotting schedule and pot requirements…' mod='phyto_care_card'}"
                        >{if isset($phyto_care_data.potting)}{$phyto_care_data.potting|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>
                </div>

                {* ── Common Problems ────────────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phyto_problems">
                            {l s='Common Problems' mod='phyto_care_card'}
                        </label>
                        <textarea id="phyto_problems"
                                  name="problems"
                                  class="form-control"
                                  rows="2"
                                  placeholder="{l s='List pests, diseases or common issues and solutions…' mod='phyto_care_card'}"
                        >{if isset($phyto_care_data.problems)}{$phyto_care_data.problems|escape:'html':'UTF-8'}{/if}</textarea>
                    </div>
                </div>

            </div>{* /.row *}

            {* ── Actions ────────────────────────────────────────────── *}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" style="margin-top:8px;">

                        <button type="button"
                                id="phyto-care-save-btn"
                                class="btn btn-success">
                            <i class="icon-save"></i>
                            {l s='Save Care Card' mod='phyto_care_card'}
                        </button>

                        &nbsp;

                        <a href="{$phyto_care_pdf_preview_url|escape:'html':'UTF-8'}"
                           target="_blank"
                           class="btn btn-default"
                           id="phyto-care-preview-btn">
                            <i class="icon-file-pdf-o"></i>
                            {l s='Preview PDF' mod='phyto_care_card'}
                        </a>

                        <span id="phyto-care-saving-spinner"
                              style="display:none; margin-left:12px; color:#666;">
                            <i class="icon-spinner icon-spin"></i>
                            {l s='Saving…' mod='phyto_care_card'}
                        </span>

                    </div>
                </div>
            </div>

        </form>{* /#phyto-care-form *}

    </div>{* /.panel-body *}

</div>{* /#phyto-care-card-tab *}

<script>
(function () {
    'use strict';

    var AJAX_URL   = '{$phyto_care_ajax_url|escape:'javascript':'UTF-8'}';
    var form       = document.getElementById('phyto-care-form');
    var saveBtn    = document.getElementById('phyto-care-save-btn');
    var spinner    = document.getElementById('phyto-care-saving-spinner');
    var alertBox   = document.getElementById('phyto-care-alert');

    /**
     * Show a Bootstrap 3 alert above the form.
     *
     * @param {string}  message
     * @param {string}  type      'success' | 'danger' | 'warning' | 'info'
     */
    function showAlert(message, type) {
        alertBox.className  = 'alert alert-' + (type || 'info');
        alertBox.innerHTML  = message;
        alertBox.style.display = 'block';

        // Auto-dismiss success messages after 4 seconds
        if (type === 'success') {
            setTimeout(function () {
                alertBox.style.display = 'none';
            }, 4000);
        }
    }

    /** Collect all form field values into a plain object. */
    function collectFormData() {
        var data   = {};
        var fields = form.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
            var el = fields[i];
            if (el.name) {
                data[el.name] = el.value;
            }
        }
        return data;
    }

    /** Encode a plain-object as an application/x-www-form-urlencoded string. */
    function encodeFormData(obj) {
        return Object.keys(obj).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
        }).join('&');
    }

    saveBtn.addEventListener('click', function () {
        alertBox.style.display = 'none';
        saveBtn.disabled       = true;
        spinner.style.display  = 'inline';

        var payload = collectFormData();

        fetch(AJAX_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    encodeFormData(payload)
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.json();
        })
        .then(function (json) {
            if (json.success) {
                showAlert('{l s='Care card saved successfully.' mod='phyto_care_card' js=1}', 'success');
            } else {
                showAlert(json.error || '{l s='An error occurred while saving.' mod='phyto_care_card' js=1}', 'danger');
            }
        })
        .catch(function (err) {
            showAlert('{l s='Request failed: ' mod='phyto_care_card' js=1}' + err.message, 'danger');
        })
        .finally(function () {
            saveBtn.disabled      = false;
            spinner.style.display = 'none';
        });
    });

}());
</script>
