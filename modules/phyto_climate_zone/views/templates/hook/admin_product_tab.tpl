{**
 * Phyto Climate Zone — Admin Product Tab Template
 *
 * Rendered inside the product edit page via hookDisplayAdminProductsExtra.
 * Provides a Bootstrap 3 form for configuring per-product climate suitability.
 *
 * Smarty variables:
 *   $phyto_climate_zones      - assoc array slug => label  (all available zones)
 *   $phyto_intolerances       - assoc array slug => label  (all intolerances)
 *   $phyto_selected_zones     - array of currently selected zone slugs
 *   $phyto_selected_intol     - array of currently selected intolerance slugs
 *   $phyto_min_temp           - string
 *   $phyto_max_temp           - string
 *   $phyto_outdoor_notes      - string
 *   $phyto_id_product         - int
 *   $phyto_climate_ajax_url   - string URL
 **}

<div id="phyto-climate-zone-tab" class="panel">

    <div class="panel-heading">
        <i class="icon-map-marker"></i>
        {l s='Climate Zone Suitability' mod='phyto_climate_zone'}
    </div>

    <div class="panel-body">

        {* ── Alert area (filled by JS) ─────────────────────────────── *}
        <div id="phyto-climate-alert" style="display:none;" class="alert" role="alert"></div>

        <form id="phyto-climate-form" novalidate>
            <input type="hidden" name="id_product" value="{$phyto_id_product|intval}">
            <input type="hidden" name="phyto_ajax"  value="1">
            <input type="hidden" name="action"       value="save_climate">

            <div class="row">

                {* ── Suitable Climate Zones ──────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">
                            {l s='Suitable Climate Zones' mod='phyto_climate_zone'}
                        </label>
                        <p class="help-block">
                            {l s='Select all climate zones where this plant can thrive. Leave all unchecked to indicate it can grow anywhere.' mod='phyto_climate_zone'}
                        </p>
                        <div id="phyto-zones-checkboxes">
                            {foreach from=$phyto_climate_zones key=slug item=label}
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                               name="suitable_zones[]"
                                               value="{$slug|escape:'html':'UTF-8'}"
                                               {if in_array($slug, $phyto_selected_zones)}checked="checked"{/if}>
                                        {$label|escape:'html':'UTF-8'}
                                    </label>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>

                {* ── Cannot Tolerate ─────────────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">
                            {l s='Cannot Tolerate' mod='phyto_climate_zone'}
                        </label>
                        <p class="help-block">
                            {l s='Select conditions this plant cannot withstand.' mod='phyto_climate_zone'}
                        </p>
                        <div id="phyto-intol-checkboxes">
                            {foreach from=$phyto_intolerances key=slug item=label}
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox"
                                               name="cannot_tolerate[]"
                                               value="{$slug|escape:'html':'UTF-8'}"
                                               {if in_array($slug, $phyto_selected_intol)}checked="checked"{/if}>
                                        {$label|escape:'html':'UTF-8'}
                                    </label>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>

            </div>{* /.row *}

            <div class="row">

                {* ── Min Temperature ────────────────────────────────── *}
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="phyto_min_temp">
                            {l s='Min Temperature' mod='phyto_climate_zone'}
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   id="phyto_min_temp"
                                   name="min_temp"
                                   class="form-control"
                                   placeholder="{l s='e.g. 10' mod='phyto_climate_zone'}"
                                   value="{$phyto_min_temp|escape:'html':'UTF-8'}">
                            <span class="input-group-addon">&deg;C</span>
                        </div>
                    </div>
                </div>

                {* ── Max Temperature ────────────────────────────────── *}
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="control-label" for="phyto_max_temp">
                            {l s='Max Temperature' mod='phyto_climate_zone'}
                        </label>
                        <div class="input-group">
                            <input type="text"
                                   id="phyto_max_temp"
                                   name="max_temp"
                                   class="form-control"
                                   placeholder="{l s='e.g. 38' mod='phyto_climate_zone'}"
                                   value="{$phyto_max_temp|escape:'html':'UTF-8'}">
                            <span class="input-group-addon">&deg;C</span>
                        </div>
                    </div>
                </div>

                {* ── Outdoor Notes ──────────────────────────────────── *}
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label" for="phyto_outdoor_notes">
                            {l s='Outdoor Notes' mod='phyto_climate_zone'}
                        </label>
                        <textarea id="phyto_outdoor_notes"
                                  name="outdoor_notes"
                                  class="form-control"
                                  rows="3"
                                  placeholder="{l s='Any special notes about outdoor growing conditions…' mod='phyto_climate_zone'}"
                        >{$phyto_outdoor_notes|escape:'html':'UTF-8'}</textarea>
                    </div>
                </div>

            </div>{* /.row *}

            {* ── Save button ─────────────────────────────────────────── *}
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group" style="margin-top:8px;">

                        <button type="button"
                                id="phyto-climate-save-btn"
                                class="btn btn-success">
                            <i class="icon-save"></i>
                            {l s='Save Climate Settings' mod='phyto_climate_zone'}
                        </button>

                        <span id="phyto-climate-spinner"
                              style="display:none; margin-left:12px; color:#666;">
                            <i class="icon-spinner icon-spin"></i>
                            {l s='Saving…' mod='phyto_climate_zone'}
                        </span>

                    </div>
                </div>
            </div>

        </form>{* /#phyto-climate-form *}

    </div>{* /.panel-body *}

</div>{* /#phyto-climate-zone-tab *}

<script>
(function () {
    'use strict';

    var AJAX_URL = '{$phyto_climate_ajax_url|escape:'javascript':'UTF-8'}';
    var form     = document.getElementById('phyto-climate-form');
    var saveBtn  = document.getElementById('phyto-climate-save-btn');
    var spinner  = document.getElementById('phyto-climate-spinner');
    var alertBox = document.getElementById('phyto-climate-alert');

    function showAlert(message, type) {
        alertBox.className     = 'alert alert-' + (type || 'info');
        alertBox.innerHTML     = message;
        alertBox.style.display = 'block';
        if (type === 'success') {
            setTimeout(function () { alertBox.style.display = 'none'; }, 4000);
        }
    }

    /**
     * Serialise the form including multi-valued checkboxes.
     * Returns an application/x-www-form-urlencoded string.
     */
    function serializeForm(frm) {
        var parts  = [];
        var fields = frm.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
            var el = fields[i];
            if (!el.name) { continue; }
            if ((el.type === 'checkbox' || el.type === 'radio') && !el.checked) { continue; }
            parts.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(el.value));
        }
        return parts.join('&');
    }

    saveBtn.addEventListener('click', function () {
        alertBox.style.display = 'none';
        saveBtn.disabled       = true;
        spinner.style.display  = 'inline';

        fetch(AJAX_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    serializeForm(form)
        })
        .then(function (response) {
            if (!response.ok) { throw new Error('HTTP ' + response.status); }
            return response.json();
        })
        .then(function (json) {
            if (json.success) {
                showAlert('{l s='Climate settings saved.' mod='phyto_climate_zone' js=1}', 'success');
            } else {
                showAlert(json.error || '{l s='An error occurred.' mod='phyto_climate_zone' js=1}', 'danger');
            }
        })
        .catch(function (err) {
            showAlert('{l s='Request failed: ' mod='phyto_climate_zone' js=1}' + err.message, 'danger');
        })
        .finally(function () {
            saveBtn.disabled      = false;
            spinner.style.display = 'none';
        });
    });

}());
</script>
