{**
 * Phyto Invoice Customizer — PDF invoice branded header block.
 * Rendered by hookDisplayPDFInvoiceHeader (PS8 where available).
 *
 * Variables:
 *   {$phyto_inv_brand_name} — escaped brand name string
 *}
<table style="width:100%;border-bottom:2px solid #2e7d32;margin-bottom:8px;font-family:sans-serif;">
    <tr>
        <td style="padding:6px 0;font-size:16px;font-weight:bold;color:#2e7d32;letter-spacing:0.5px;">
            {$phyto_inv_brand_name|escape:'html':'UTF-8'}
        </td>
        <td style="padding:6px 0;text-align:right;font-size:10px;color:#666666;">
            {l s='Specialist Plant Supplier' mod='phyto_invoice_customizer'}
        </td>
    </tr>
</table>
