{**
 * Phyto Invoice Customizer — Admin configuration page wrapper.
 * The HelperForm output is rendered directly by getContent(); this template
 * provides the surrounding layout chrome used when the tab is visited directly.
 *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-file-text"></i>
        {l s='Phyto Invoice Customizer' mod='phyto_invoice_customizer'}
    </div>

    <div class="panel-body">
        <div class="alert alert-info">
            <strong>{l s='How it works' mod='phyto_invoice_customizer'}</strong><br>
            {l s='This module injects phytosanitary certificate references, TC batch codes, Live Arrival Guarantee text, and a branded header/footer into PrestaShop 8 PDF invoices.' mod='phyto_invoice_customizer'}
            <ul style="margin-top:8px;">
                <li>{l s='Batch data is read from phyto_tc_batch_tracker (skipped if not installed).' mod='phyto_invoice_customizer'}</li>
                <li>{l s='Certificate references are read from phyto_phytosanitary (skipped if not installed).' mod='phyto_invoice_customizer'}</li>
                <li>{l s='Each section can be toggled on or off independently.' mod='phyto_invoice_customizer'}</li>
            </ul>
        </div>

        {* HelperForm is rendered inline by getContent() and appended here *}
        {$smarty.capture.phyto_inv_form|default:''|nofilter}
    </div>
</div>
