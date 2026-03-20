{**
 * product_extra_content.tpl
 *
 * Displays phytosanitary regulatory documents for a product in the
 * "Regulatory Documents" extra-content tab on the PrestaShop 8 product page.
 *
 * Available Smarty variables:
 *   {$phyto_docs}       – array of document rows from PhytoPhytosanitaryDoc::getByProduct()
 *   {$phyto_upload_url} – base URL to the front-office download endpoint
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 *}

{if $phyto_docs|@count > 0}
<div class="phyto-phyto-wrapper">

  <table class="phyto-phyto-table table table-striped table-hover">
    <thead>
      <tr>
        <th class="phyto-phyto-th">{l s='Document Type' mod='phyto_phytosanitary'}</th>
        <th class="phyto-phyto-th">{l s='Issuing Authority' mod='phyto_phytosanitary'}</th>
        <th class="phyto-phyto-th">{l s='Reference #' mod='phyto_phytosanitary'}</th>
        <th class="phyto-phyto-th">{l s='Issue Date' mod='phyto_phytosanitary'}</th>
        <th class="phyto-phyto-th">{l s='Expiry Date' mod='phyto_phytosanitary'}</th>
        <th class="phyto-phyto-th">{l s='File' mod='phyto_phytosanitary'}</th>
      </tr>
    </thead>
    <tbody>
      {foreach $phyto_docs as $doc}
        {* Determine expiry status *}
        {assign var='phyto_expiry_class' value=''}
        {assign var='phyto_expiry_label' value=''}
        {if $doc.expiry_date && $doc.expiry_date != '0000-00-00'}
          {assign var='phyto_expiry_ts' value=$doc.expiry_date|strtotime}
          {assign var='phyto_now_ts'    value='now'|strtotime}
          {assign var='phyto_soon_ts'   value='+30 days'|strtotime}
          {if $phyto_expiry_ts < $phyto_now_ts}
            {assign var='phyto_expiry_class' value='phyto-phyto-expired'}
            {assign var='phyto_expiry_label' value={l s='Expired' mod='phyto_phytosanitary'}}
          {elseif $phyto_expiry_ts <= $phyto_soon_ts}
            {assign var='phyto_expiry_class' value='phyto-phyto-expiring-soon'}
            {assign var='phyto_expiry_label' value={l s='Expiring soon' mod='phyto_phytosanitary'}}
          {/if}
        {/if}

        <tr class="phyto-phyto-row {$phyto_expiry_class}">

          {* Document type *}
          <td class="phyto-phyto-td phyto-phyto-doc-type">
            {$doc.doc_type|replace:'_':' '|ucfirst|escape:'html':'UTF-8'}
          </td>

          {* Issuing authority *}
          <td class="phyto-phyto-td phyto-phyto-authority">
            {if $doc.issuing_authority}
              {$doc.issuing_authority|escape:'html':'UTF-8'}
            {else}
              <span class="phyto-phyto-empty">—</span>
            {/if}
          </td>

          {* Reference number *}
          <td class="phyto-phyto-td phyto-phyto-reference">
            {if $doc.reference_number}
              <strong>{$doc.reference_number|escape:'html':'UTF-8'}</strong>
            {else}
              <span class="phyto-phyto-empty">—</span>
            {/if}
          </td>

          {* Issue date *}
          <td class="phyto-phyto-td phyto-phyto-date">
            {if $doc.issue_date && $doc.issue_date != '0000-00-00'}
              {$doc.issue_date|date_format:'%d %b %Y'}
            {else}
              <span class="phyto-phyto-empty">—</span>
            {/if}
          </td>

          {* Expiry date with badge *}
          <td class="phyto-phyto-td phyto-phyto-date">
            {if $doc.expiry_date && $doc.expiry_date != '0000-00-00'}
              {$doc.expiry_date|date_format:'%d %b %Y'}
              {if $phyto_expiry_label}
                <span class="phyto-phyto-badge {$phyto_expiry_class}">
                  {$phyto_expiry_label}
                </span>
              {/if}
            {else}
              <span class="phyto-phyto-empty">{l s='No expiry' mod='phyto_phytosanitary'}</span>
            {/if}
          </td>

          {* Download link – only shown when the document is public *}
          <td class="phyto-phyto-td phyto-phyto-download">
            {if $doc.is_public && $doc.filename}
              <a href="{$phyto_upload_url|escape:'html':'UTF-8'}{$doc.id_doc|intval}"
                 class="phyto-phyto-download-link"
                 target="_blank"
                 rel="noopener noreferrer"
                 aria-label="{l s='Download document' mod='phyto_phytosanitary'}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="phyto-phyto-icon" aria-hidden="true">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                {l s='Download PDF' mod='phyto_phytosanitary'}
              </a>
            {elseif $doc.filename}
              <span class="phyto-phyto-restricted">
                {l s='Available on request' mod='phyto_phytosanitary'}
              </span>
            {else}
              <span class="phyto-phyto-empty">—</span>
            {/if}
          </td>

        </tr>
      {/foreach}
    </tbody>
  </table>

  {* Expiry legend *}
  <div class="phyto-phyto-legend">
    <span class="phyto-phyto-badge phyto-phyto-expiring-soon">
      {l s='Expiring within 30 days' mod='phyto_phytosanitary'}
    </span>
    <span class="phyto-phyto-badge phyto-phyto-expired">
      {l s='Expired' mod='phyto_phytosanitary'}
    </span>
  </div>

</div>
{/if}
