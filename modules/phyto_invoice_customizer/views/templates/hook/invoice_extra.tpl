{**
 * Phyto Invoice Customizer — PDF invoice body extra block.
 * Rendered by hookDisplayPDFInvoice.
 *
 * Variables:
 *   {$phyto_inv_show_lag}    — bool: render LAG block
 *   {$phyto_inv_lag_text}    — escaped LAG statement
 *   {$phyto_inv_show_batch}  — bool: batch data was requested
 *   {$phyto_inv_batch_data}  — array of batch rows or empty
 *   {$phyto_inv_show_phyto}  — bool: phyto data was requested
 *   {$phyto_inv_phyto_refs}  — array of phyto ref rows or empty
 *   {$phyto_inv_brand_name}  — escaped brand name
 *}

{* ── Wrapper ─────────────────────────────────────────────────────────────── *}
<table style="width:100%;margin-top:12px;border-top:1px solid #cccccc;font-family:sans-serif;font-size:11px;color:#333333;">
    <tr>
        <td style="padding:8px 0 4px 0;">

            {* ── TC Batch Numbers ─────────────────────────────────────────── *}
            {if $phyto_inv_show_batch && $phyto_inv_batch_data|@count > 0}
            <table style="width:100%;margin-bottom:8px;">
                <tr>
                    <td colspan="4" style="font-weight:bold;font-size:12px;color:#2e7d32;padding-bottom:4px;border-bottom:1px solid #eeeeee;">
                        {l s='Tissue Culture Batch Information' mod='phyto_invoice_customizer'}
                    </td>
                </tr>
                <tr style="background:#f5f5f5;">
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Product' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Batch Code' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Species' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Generation' mod='phyto_invoice_customizer'}</th>
                </tr>
                {foreach from=$phyto_inv_batch_data item=batch}
                <tr>
                    <td style="padding:3px 6px;">{$batch.product_name|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;font-family:monospace;">{$batch.batch_code|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;font-style:italic;">{$batch.species_name|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;">{$batch.generation|escape:'html':'UTF-8'}</td>
                </tr>
                {/foreach}
            </table>
            {/if}

            {* ── Phytosanitary Certificate References ─────────────────────── *}
            {if $phyto_inv_show_phyto && $phyto_inv_phyto_refs|@count > 0}
            <table style="width:100%;margin-bottom:8px;">
                <tr>
                    <td colspan="4" style="font-weight:bold;font-size:12px;color:#2e7d32;padding-bottom:4px;border-bottom:1px solid #eeeeee;">
                        {l s='Phytosanitary Compliance' mod='phyto_invoice_customizer'}
                    </td>
                </tr>
                <tr style="background:#f5f5f5;">
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Product' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Certificate Ref.' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Document Type' mod='phyto_invoice_customizer'}</th>
                    <th style="padding:3px 6px;text-align:left;font-size:10px;">{l s='Issuing Authority' mod='phyto_invoice_customizer'}</th>
                </tr>
                {foreach from=$phyto_inv_phyto_refs item=ref}
                <tr>
                    <td style="padding:3px 6px;">{$ref.product_name|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;font-family:monospace;">{$ref.reference_number|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;">{$ref.doc_type|escape:'html':'UTF-8'}</td>
                    <td style="padding:3px 6px;">{$ref.issuing_authority|escape:'html':'UTF-8'}</td>
                </tr>
                {/foreach}
            </table>
            {/if}

            {* ── Live Arrival Guarantee Statement ────────────────────────── *}
            {if $phyto_inv_show_lag && $phyto_inv_lag_text}
            <table style="width:100%;margin-top:4px;">
                <tr>
                    <td style="padding:6px 8px;background:#f0f7f0;border:1px solid #a5d6a7;border-radius:3px;">
                        <span style="font-weight:bold;color:#2e7d32;">
                            {l s='Live Arrival Guarantee' mod='phyto_invoice_customizer'}:
                        </span>
                        {$phyto_inv_lag_text|escape:'html':'UTF-8'}
                    </td>
                </tr>
            </table>
            {/if}

        </td>
    </tr>
</table>
