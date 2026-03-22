{**
 * Phyto Growth Stage — Admin Product Tab
 *
 * Renders the growth-stage assignment panel within the product edit page.
 * Bootstrap 3 / PrestaShop 8 back-office conventions.
 *}

<div id="phyto-growth-stage-panel" class="panel product-tab">
    <h3><i class="icon-leaf"></i> {l s='Growth Stage Assignments' mod='phyto_growth_stage'}</h3>

    <div class="alert alert-info">
        {l s='Assign a growth stage to each product combination (or to the base product if there are no combinations). Use the weeks override field to specify a custom transition time for this particular product.' mod='phyto_growth_stage'}
    </div>

    {if empty($stages)}
        <div class="alert alert-warning">
            {l s='No growth stage definitions found. Please create some stages in Catalog → Growth Stages before assigning them here.' mod='phyto_growth_stage'}
        </div>
    {else}

        <div id="phyto-gs-messages"></div>

        <input type="hidden" id="phyto_gs_id_product" value="{$id_product|intval}" />
        <input type="hidden" id="phyto_gs_ajax_url"   value="{$ajax_url|escape:'htmlall':'UTF-8'}" />
        <input type="hidden" id="phyto_gs_module"     value="{$module_name|escape:'htmlall':'UTF-8'}" />

        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Combination' mod='phyto_growth_stage'}</th>
                    <th>{l s='Growth Stage' mod='phyto_growth_stage'}</th>
                    <th style="width:130px;">{l s='Weeks Override' mod='phyto_growth_stage'}</th>
                    <th style="width:180px;">{l s='Actions' mod='phyto_growth_stage'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$combinations key=id_attr item=combo_label}
                    {assign var='current' value=null}
                    {if isset($mapped[$id_attr])}
                        {assign var='current' value=$mapped[$id_attr]}
                    {/if}

                    <tr class="phyto-gs-row" data-id-attr="{$id_attr|intval}">
                        <td class="phyto-gs-combo-label">
                            <strong>{$combo_label|escape:'htmlall':'UTF-8'}</strong>
                            {if $current}
                                <br /><small class="text-muted phyto-gs-current-label">
                                    {l s='Currently:' mod='phyto_growth_stage'}
                                    <em>{$current.stage_name|escape:'htmlall':'UTF-8'}</em>
                                    {if $current.difficulty}
                                        &mdash; {$current.difficulty|escape:'htmlall':'UTF-8'}
                                    {/if}
                                </small>
                            {/if}
                        </td>

                        <td>
                            <select class="form-control phyto-gs-stage-select">
                                <option value="">{l s='— No stage —' mod='phyto_growth_stage'}</option>
                                {foreach from=$stages item=stage}
                                    <option value="{$stage.id_stage|intval}"
                                        {if $current && $current.id_stage == $stage.id_stage}selected="selected"{/if}>
                                        {$stage.stage_name|escape:'htmlall':'UTF-8'}
                                        {if $stage.difficulty}
                                            ({$stage.difficulty|escape:'htmlall':'UTF-8'})
                                        {/if}
                                        {if $stage.weeks_to_next}
                                            — {$stage.weeks_to_next|intval} {l s='wks' mod='phyto_growth_stage'}
                                        {/if}
                                    </option>
                                {/foreach}
                            </select>
                        </td>

                        <td>
                            <input type="number"
                                   class="form-control phyto-gs-weeks-input"
                                   min="0"
                                   max="520"
                                   placeholder="{l s='Default' mod='phyto_growth_stage'}"
                                   value="{if $current && $current.weeks_override !== null}{$current.weeks_override|intval}{/if}" />
                        </td>

                        <td>
                            <button type="button"
                                    class="btn btn-default btn-sm phyto-gs-save-btn"
                                    title="{l s='Save assignment' mod='phyto_growth_stage'}">
                                <i class="icon-save"></i> {l s='Save' mod='phyto_growth_stage'}
                            </button>
                            {if $current}
                                <button type="button"
                                        class="btn btn-danger btn-sm phyto-gs-remove-btn"
                                        title="{l s='Remove assignment' mod='phyto_growth_stage'}">
                                    <i class="icon-trash"></i>
                                </button>
                            {else}
                                <button type="button"
                                        class="btn btn-danger btn-sm phyto-gs-remove-btn"
                                        title="{l s='Remove assignment' mod='phyto_growth_stage'}"
                                        style="display:none;">
                                    <i class="icon-trash"></i>
                                </button>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>

    {/if}
</div>

<script type="text/javascript">
(function ($) {
    'use strict';

    var ajaxUrl   = $('#phyto_gs_ajax_url').val();
    var idProduct = parseInt($('#phyto_gs_id_product').val(), 10);

    /**
     * Show a dismissible message inside the panel message area.
     *
     * @param {string}  message
     * @param {boolean} isSuccess
     */
    function showMessage(message, isSuccess) {
        var type = isSuccess ? 'success' : 'danger';
        $('#phyto-gs-messages').html(
            '<div class="alert alert-' + type + '">' +
            '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
            message +
            '</div>'
        );
    }

    /**
     * Serialise query string from a plain object.
     *
     * @param {Object} data
     * @returns {string}
     */
    function buildQuery(data) {
        return Object.keys(data).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(data[key]);
        }).join('&');
    }

    // -----------------------------------------------------------------
    //  Save button
    // -----------------------------------------------------------------
    $(document).on('click', '.phyto-gs-save-btn', function () {
        var $btn  = $(this);
        var $row  = $btn.closest('.phyto-gs-row');
        var idAttr   = parseInt($row.data('id-attr'), 10);
        var idStage  = parseInt($row.find('.phyto-gs-stage-select').val(), 10) || 0;
        var weeksVal = $row.find('.phyto-gs-weeks-input').val();

        if (!idStage) {
            showMessage('{l s='Please select a growth stage before saving.' mod='phyto_growth_stage' js=1}', false);
            return;
        }

        $btn.prop('disabled', true);

        var payload = {
            ajax:                 1,
            action:               'save_assignment',
            id_product:           idProduct,
            id_product_attribute: idAttr,
            id_stage:             idStage
        };

        if (weeksVal !== '') {
            payload.weeks_override = parseInt(weeksVal, 10);
        }

        $.ajax({
            url:      ajaxUrl,
            type:     'POST',
            dataType: 'json',
            data:     payload,
            success: function (response) {
                if (response && response.success) {
                    showMessage(response.message || '{l s='Assignment saved.' mod='phyto_growth_stage' js=1}', true);

                    // Update the "Currently:" label without a page reload.
                    var stageName = $row.find('.phyto-gs-stage-select option:selected').text();
                    var $current  = $row.find('.phyto-gs-current-label');

                    if ($current.length) {
                        $current.html('{l s='Currently:' mod='phyto_growth_stage' js=1} <em>' + $('<span>').text(stageName).html() + '</em>');
                    } else {
                        $row.find('.phyto-gs-combo-label strong').after(
                            '<br /><small class="text-muted phyto-gs-current-label">' +
                            '{l s='Currently:' mod='phyto_growth_stage' js=1} <em>' +
                            $('<span>').text(stageName).html() +
                            '</em></small>'
                        );
                    }

                    // Show the remove button now that a mapping exists.
                    $row.find('.phyto-gs-remove-btn').show();

                } else {
                    showMessage((response && response.message)
                        ? response.message
                        : '{l s='An error occurred while saving.' mod='phyto_growth_stage' js=1}',
                        false
                    );
                }
            },
            error: function () {
                showMessage('{l s='A network error occurred. Please try again.' mod='phyto_growth_stage' js=1}', false);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // -----------------------------------------------------------------
    //  Remove button
    // -----------------------------------------------------------------
    $(document).on('click', '.phyto-gs-remove-btn', function () {
        if (!window.confirm('{l s='Remove this growth stage assignment?' mod='phyto_growth_stage' js=1}')) {
            return;
        }

        var $btn   = $(this);
        var $row   = $btn.closest('.phyto-gs-row');
        var idAttr = parseInt($row.data('id-attr'), 10);

        $btn.prop('disabled', true);

        $.ajax({
            url:      ajaxUrl,
            type:     'POST',
            dataType: 'json',
            data: {
                ajax:                 1,
                action:               'remove_assignment',
                id_product:           idProduct,
                id_product_attribute: idAttr
            },
            success: function (response) {
                if (response && response.success) {
                    showMessage(response.message || '{l s='Assignment removed.' mod='phyto_growth_stage' js=1}', true);

                    // Reset the row UI.
                    $row.find('.phyto-gs-stage-select').val('');
                    $row.find('.phyto-gs-weeks-input').val('');
                    $row.find('.phyto-gs-current-label').remove();
                    $btn.hide();

                } else {
                    showMessage((response && response.message)
                        ? response.message
                        : '{l s='An error occurred while removing.' mod='phyto_growth_stage' js=1}',
                        false
                    );
                }
            },
            error: function () {
                showMessage('{l s='A network error occurred. Please try again.' mod='phyto_growth_stage' js=1}', false);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

}(jQuery));
</script>
