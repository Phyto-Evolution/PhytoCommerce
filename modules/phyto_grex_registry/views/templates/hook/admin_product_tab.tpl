{**
 * Phyto Grex Registry — Admin Product Tab
 *
 * Renders taxonomy form fields within the product edit page (back office).
 *}

<div id="phyto-grex-registry-panel" class="panel product-tab">
    <h3><i class="icon-leaf"></i> {l s='Scientific / Horticultural Taxonomy' mod='phyto_grex_registry'}</h3>

    <div class="alert alert-info">
        {l s='Enter the scientific taxonomy and registration data for this product. Fields left blank will not be shown on the product page.' mod='phyto_grex_registry'}
    </div>

    <div id="phyto-grex-messages"></div>

    <input type="hidden" id="phyto_grex_id_product" value="{$phyto_grex_id_product|intval}" />
    <input type="hidden" id="phyto_grex_ajax_url" value="{$phyto_grex_ajax_url|escape:'htmlall':'UTF-8'}" />

    {* --- Taxonomy Section --- *}
    <div class="form-group">
        <h4>{l s='Taxonomy' mod='phyto_grex_registry'}</h4>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_genus">
            {l s='Genus' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_genus"
                   name="genus"
                   class="form-control"
                   maxlength="100"
                   placeholder="{l s='e.g. Nepenthes' mod='phyto_grex_registry'}"
                   value="{if isset($phyto_grex_data.genus)}{$phyto_grex_data.genus|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_species">
            {l s='Species' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_species"
                   name="species"
                   class="form-control"
                   maxlength="100"
                   placeholder="{l s='e.g. rajah' mod='phyto_grex_registry'}"
                   value="{if isset($phyto_grex_data.species)}{$phyto_grex_data.species|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_subspecies">
            {l s='Subspecies / Variety' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_subspecies"
                   name="subspecies"
                   class="form-control"
                   maxlength="100"
                   value="{if isset($phyto_grex_data.subspecies)}{$phyto_grex_data.subspecies|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_cultivar">
            {l s='Cultivar Name' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_cultivar"
                   name="cultivar"
                   class="form-control"
                   maxlength="150"
                   value="{if isset($phyto_grex_data.cultivar)}{$phyto_grex_data.cultivar|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_grex_name">
            {l s='Grex Name' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_grex_name"
                   name="grex_name"
                   class="form-control"
                   maxlength="150"
                   placeholder="{l s='Hybrid seedling population name' mod='phyto_grex_registry'}"
                   value="{if isset($phyto_grex_data.grex_name)}{$phyto_grex_data.grex_name|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    {* --- Hybrid Section --- *}
    <hr />
    <div class="form-group">
        <h4>{l s='Hybrid Information' mod='phyto_grex_registry'}</h4>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_hybrid_formula">
            {l s='Hybrid Formula' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_hybrid_formula"
                   name="hybrid_formula"
                   class="form-control"
                   maxlength="255"
                   placeholder="{l s='e.g. N. rajah × N. lowii' mod='phyto_grex_registry'}"
                   value="{if isset($phyto_grex_data.hybrid_formula)}{$phyto_grex_data.hybrid_formula|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_mother">
            {l s='Primary Parentage — Mother (♀)' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_mother"
                   name="mother"
                   class="form-control"
                   maxlength="150"
                   value="{if isset($phyto_grex_data.mother)}{$phyto_grex_data.mother|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_father">
            {l s='Primary Parentage — Father (♂)' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_father"
                   name="father"
                   class="form-control"
                   maxlength="150"
                   value="{if isset($phyto_grex_data.father)}{$phyto_grex_data.father|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    {* --- Registration Section --- *}
    <hr />
    <div class="form-group">
        <h4>{l s='Registration' mod='phyto_grex_registry'}</h4>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_icps_registered">
            {l s='ICPS Registered?' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <span class="switch prestashop-switch fixed-width-lg">
                <input type="radio"
                       name="icps_registered"
                       id="phyto_grex_icps_registered_on"
                       value="1"
                       {if isset($phyto_grex_data.icps_registered) && $phyto_grex_data.icps_registered}checked="checked"{/if} />
                <label for="phyto_grex_icps_registered_on">{l s='Yes' mod='phyto_grex_registry'}</label>
                <input type="radio"
                       name="icps_registered"
                       id="phyto_grex_icps_registered_off"
                       value="0"
                       {if !isset($phyto_grex_data.icps_registered) || !$phyto_grex_data.icps_registered}checked="checked"{/if} />
                <label for="phyto_grex_icps_registered_off">{l s='No' mod='phyto_grex_registry'}</label>
                <a class="slide-button btn"></a>
            </span>
        </div>
    </div>

    <div class="form-group" id="phyto_grex_icps_number_row"
         style="{if !isset($phyto_grex_data.icps_registered) || !$phyto_grex_data.icps_registered}display:none;{/if}">
        <label class="control-label col-lg-3" for="phyto_grex_icps_number">
            {l s='ICPS Registration Number' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_icps_number"
                   name="icps_number"
                   class="form-control"
                   maxlength="50"
                   value="{if isset($phyto_grex_data.icps_number)}{$phyto_grex_data.icps_number|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    {* --- Habitat & Conservation Section --- *}
    <hr />
    <div class="form-group">
        <h4>{l s='Habitat & Conservation' mod='phyto_grex_registry'}</h4>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_habitat">
            {l s='Natural Habitat' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_habitat"
                   name="habitat"
                   class="form-control"
                   placeholder="{l s='e.g. Borneo highland, 1800–2600 m' mod='phyto_grex_registry'}"
                   value="{if isset($phyto_grex_data.habitat)}{$phyto_grex_data.habitat|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_endemic_region">
            {l s='Endemic Region' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <input type="text"
                   id="phyto_grex_endemic_region"
                   name="endemic_region"
                   class="form-control"
                   maxlength="200"
                   value="{if isset($phyto_grex_data.endemic_region)}{$phyto_grex_data.endemic_region|escape:'htmlall':'UTF-8'}{/if}" />
        </div>
    </div>

    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_conservation_status">
            {l s='Conservation Status' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-5">
            <select id="phyto_grex_conservation_status"
                    name="conservation_status"
                    class="form-control">
                {foreach from=$phyto_grex_conservation_statuses key=status_key item=status_label}
                    <option value="{$status_key|escape:'htmlall':'UTF-8'}"
                            {if isset($phyto_grex_data.conservation_status) && $phyto_grex_data.conservation_status == $status_key}selected="selected"{/if}>
                        {$status_label|escape:'htmlall':'UTF-8'}
                    </option>
                {/foreach}
            </select>
        </div>
    </div>

    {* --- Notes Section --- *}
    <hr />
    <div class="form-group">
        <label class="control-label col-lg-3" for="phyto_grex_notes">
            {l s='Taxonomic Notes' mod='phyto_grex_registry'}
        </label>
        <div class="col-lg-7">
            <textarea id="phyto_grex_notes"
                      name="notes"
                      class="form-control"
                      rows="4">{if isset($phyto_grex_data.notes)}{$phyto_grex_data.notes|escape:'htmlall':'UTF-8'}{/if}</textarea>
        </div>
    </div>

    {* --- Save Button --- *}
    <div class="panel-footer">
        <button type="button"
                id="phyto_grex_save_btn"
                class="btn btn-default pull-right"
                title="{l s='Save Taxonomy Data' mod='phyto_grex_registry'}">
            <i class="process-icon-save"></i> {l s='Save Taxonomy Data' mod='phyto_grex_registry'}
        </button>
    </div>
</div>

<script type="text/javascript">
(function($) {
    'use strict';

    // Toggle ICPS number field visibility
    $('input[name="icps_registered"]').on('change', function() {
        var isRegistered = $('input[name="icps_registered"]:checked').val() === '1';
        $('#phyto_grex_icps_number_row').toggle(isRegistered);
        if (!isRegistered) {
            $('#phyto_grex_icps_number').val('');
        }
    });

    // Save taxonomy data via AJAX
    $('#phyto_grex_save_btn').on('click', function() {
        var $btn = $(this);
        var $messages = $('#phyto-grex-messages');

        $btn.prop('disabled', true);
        $messages.html('');

        var formData = {
            ajax: 1,
            action: 'SaveGrexData',
            id_product: $('#phyto_grex_id_product').val(),
            genus: $('#phyto_grex_genus').val(),
            species: $('#phyto_grex_species').val(),
            subspecies: $('#phyto_grex_subspecies').val(),
            cultivar: $('#phyto_grex_cultivar').val(),
            grex_name: $('#phyto_grex_grex_name').val(),
            hybrid_formula: $('#phyto_grex_hybrid_formula').val(),
            mother: $('#phyto_grex_mother').val(),
            father: $('#phyto_grex_father').val(),
            icps_registered: $('input[name="icps_registered"]:checked').val(),
            icps_number: $('#phyto_grex_icps_number').val(),
            habitat: $('#phyto_grex_habitat').val(),
            endemic_region: $('#phyto_grex_endemic_region').val(),
            conservation_status: $('#phyto_grex_conservation_status').val(),
            notes: $('#phyto_grex_notes').val()
        };

        $.ajax({
            url: $('#phyto_grex_ajax_url').val(),
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $messages.html(
                        '<div class="alert alert-success">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        response.message +
                        '</div>'
                    );
                } else {
                    $messages.html(
                        '<div class="alert alert-danger">' +
                        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                        response.message +
                        '</div>'
                    );
                }
            },
            error: function() {
                $messages.html(
                    '<div class="alert alert-danger">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '{l s='An error occurred while saving. Please try again.' mod='phyto_grex_registry' js=1}' +
                    '</div>'
                );
            },
            complete: function() {
                $btn.prop('disabled', false);
            }
        });
    });

})(jQuery);
</script>
