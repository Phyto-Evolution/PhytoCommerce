{**
 * PhytoCommerce — admin_product_tab.tpl
 *
 * Rendered by hookDisplayAdminProductsExtra().
 * Shows a checkbox list of all badge definitions.
 * For "wild-rescue" badges a permit/reference field is revealed.
 * For "import" badges an origin-country field is revealed.
 * The form is submitted via AJAX — no full page reload.
 *
 * Smarty variables
 * ─────────────────
 *   $phyto_all_badges   — array of all badge definitions (from phyto_source_badge_def)
 *   $phyto_assigned     — associative array keyed by id_badge with assignment rows
 *   $phyto_id_product   — current product ID
 *   $phyto_ajax_url     — URL to AdminPhytoSourceBadgeProductController
 *   $phyto_module_token — CSRF token for the AJAX controller
 **}

<div id="phyto-source-badge-panel" class="panel">

    <div class="panel-heading">
        <i class="icon-tag"></i>
        {l s='Source &amp; Origin Badges' mod='phyto_source_badge'}
    </div>

    <div class="panel-body">

        {* ── Feedback messages ─────────────────────────────────────── *}
        <div id="phyto-badge-alert" class="alert" style="display:none;"></div>

        {* ── Badge checkboxes ──────────────────────────────────────── *}
        {if $phyto_all_badges}
            <table class="table table-bordered" id="phyto-badge-table">
                <thead>
                    <tr>
                        <th style="width:40px;">{l s='Active' mod='phyto_source_badge'}</th>
                        <th>{l s='Badge' mod='phyto_source_badge'}</th>
                        <th style="width:260px;">{l s='Extra information' mod='phyto_source_badge'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $phyto_all_badges as $badge}
                        {assign var='bid'      value=$badge.id_badge|intval}
                        {assign var='isChecked' value=isset($phyto_assigned[$bid])}
                        {assign var='assignRow' value=($isChecked ? $phyto_assigned[$bid] : [])}
                        {assign var='slug'     value=$badge.badge_slug|escape:'html'}

                        <tr class="phyto-badge-row" data-id="{$bid}" data-slug="{$slug}">

                            {* Checkbox *}
                            <td class="text-center">
                                <input
                                    type="checkbox"
                                    class="phyto-badge-check"
                                    name="phyto_id_badge[]"
                                    value="{$bid}"
                                    id="phyto_badge_{$bid}"
                                    {if $isChecked}checked="checked"{/if}
                                />
                            </td>

                            {* Label + colour pill *}
                            <td>
                                <label for="phyto_badge_{$bid}" style="cursor:pointer;margin-bottom:0;">
                                    <span class="phyto-badge-pill phyto-badge-{$badge.badge_color|escape:'html'}">
                                        {$badge.badge_label|escape:'html'}
                                    </span>
                                </label>
                                {if $badge.description}
                                    <small class="text-muted" style="display:block;margin-top:2px;">
                                        {$badge.description|escape:'html'|truncate:120:'…'}
                                    </small>
                                {/if}
                            </td>

                            {* Extra fields — shown only when the checkbox is ticked *}
                            <td>

                                {* Wild Rescue — permit / reference number *}
                                {if $slug === 'wild-rescue'}
                                    <div class="phyto-extra-field"
                                         style="{if !$isChecked}display:none;{/if}">
                                        <label class="control-label" style="font-size:12px;">
                                            {l s='Permit / reference number' mod='phyto_source_badge'}
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control input-sm phyto-permit-ref"
                                            name="phyto_permit_ref[{$bid}]"
                                            value="{if isset($assignRow.permit_ref)}{$assignRow.permit_ref|escape:'html'}{/if}"
                                            placeholder="{l s='e.g. CITES/2024/001' mod='phyto_source_badge'}"
                                        />
                                    </div>
                                {/if}

                                {* Import — origin country *}
                                {if $slug === 'import'}
                                    <div class="phyto-extra-field"
                                         style="{if !$isChecked}display:none;{/if}">
                                        <label class="control-label" style="font-size:12px;">
                                            {l s='Origin country' mod='phyto_source_badge'}
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control input-sm phyto-origin-country"
                                            name="phyto_origin_country[{$bid}]"
                                            value="{if isset($assignRow.origin_country)}{$assignRow.origin_country|escape:'html'}{/if}"
                                            placeholder="{l s='e.g. Thailand' mod='phyto_source_badge'}"
                                        />
                                    </div>
                                {/if}

                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        {else}
            <div class="alert alert-warning">
                {l s='No badge definitions found. Please add some in Catalog → Phyto Source Badges.' mod='phyto_source_badge'}
            </div>
        {/if}

    </div>{* /.panel-body *}

    <div class="panel-footer">
        <button type="button" id="phyto-badge-save" class="btn btn-default pull-right">
            <i class="process-icon-save"></i>
            {l s='Save badge assignments' mod='phyto_source_badge'}
        </button>
        <div class="clearfix"></div>
    </div>

</div>{* /#phyto-source-badge-panel *}

{* ── Inline badge pill styles (admin only) ──────────────────── *}
<style>
.phyto-badge-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    letter-spacing: .3px;
}
.phyto-badge-green  { background-color: #2e7d32; }
.phyto-badge-blue   { background-color: #1565c0; }
.phyto-badge-amber  { background-color: #e65100; }
.phyto-badge-red    { background-color: #c62828; }
.phyto-badge-gray   { background-color: #546e7a; }
</style>

{* ── AJAX save script ────────────────────────────────────────── *}
<script>
(function ($) {
    "use strict";

    var ajaxUrl  = {$phyto_ajax_url|json_encode};
    var token    = {$phyto_module_token|json_encode};
    var idProduct = {$phyto_id_product|intval};

    // ── Show/hide extra fields when checkbox state changes ──────
    $(document).on('change', '.phyto-badge-check', function () {
        var $row   = $(this).closest('tr.phyto-badge-row');
        var $extra = $row.find('.phyto-extra-field');
        if (this.checked) {
            $extra.slideDown(150);
        } else {
            $extra.slideUp(150);
        }
    });

    // ── Save via AJAX ────────────────────────────────────────────
    $('#phyto-badge-save').on('click', function () {
        var $btn   = $(this).prop('disabled', true);
        var $alert = $('#phyto-badge-alert').hide();

        var idBadges       = [];
        var permitRefs     = [];
        var originCountries = [];

        // Collect checked rows
        $('.phyto-badge-check:checked').each(function () {
            var $row = $(this).closest('tr.phyto-badge-row');
            var bid  = parseInt($row.data('id'), 10);

            idBadges.push(bid);

            var permitRef     = $row.find('.phyto-permit-ref').val()     || '';
            var originCountry = $row.find('.phyto-origin-country').val() || '';

            permitRefs.push(permitRef);
            originCountries.push(originCountry);
        });

        $.ajax({
            url:    ajaxUrl,
            method: 'POST',
            data: {
                action:          'save',
                token:           token,
                id_product:      idProduct,
                'id_badge[]':    idBadges,
                'permit_ref[]':  permitRefs,
                'origin_country[]': originCountries
            },
            dataType: 'json'
        })
        .done(function (resp) {
            if (resp && resp.success) {
                $alert
                    .removeClass('alert-danger')
                    .addClass('alert alert-success')
                    .html('<i class="icon-check"></i> {l s='Badges saved successfully.' mod='phyto_source_badge' js=1}')
                    .show();
            } else {
                $alert
                    .removeClass('alert-success')
                    .addClass('alert alert-danger')
                    .html('<i class="icon-warning-sign"></i> ' + (resp.error || '{l s='Unknown error.' mod='phyto_source_badge' js=1}'))
                    .show();
            }
        })
        .fail(function () {
            $alert
                .removeClass('alert-success')
                .addClass('alert alert-danger')
                .html('<i class="icon-warning-sign"></i> {l s='AJAX request failed.' mod='phyto_source_badge' js=1}')
                .show();
        })
        .always(function () {
            $btn.prop('disabled', false);
            $('html, body').animate({ scrollTop: $('#phyto-source-badge-panel').offset().top - 80 }, 300);
        });
    });

}(jQuery));
</script>
