<div class="panel" id="phyto-tc-batch-panel">
  <div class="panel-heading">
    <i class="icon-leaf"></i>
    {l s='TC Batch Provenance' mod='phyto_tc_batch_tracker'}
  </div>
  <div class="panel-body">

    {* ── Linked batch summary ────────────────────────────────────────── *}
    <div id="phyto-tc-linked-summary" class="{if !$phyto_tc_linked_batch}hidden{/if}">
      <div class="alert alert-success phyto-tc-linked-badge">
        <strong>{l s='Linked batch:' mod='phyto_tc_batch_tracker'}</strong>
        <span id="phyto-tc-badge-code">{if $phyto_tc_linked_batch}{$phyto_tc_linked_batch.batch_code|escape:'html'}{/if}</span>
        &mdash;
        <span id="phyto-tc-badge-species">{if $phyto_tc_linked_batch}{$phyto_tc_linked_batch.species_name|escape:'html'}{/if}</span>
        &mdash;
        <span id="phyto-tc-badge-gen">{if $phyto_tc_linked_batch}{$phyto_tc_linked_batch.generation|escape:'html'}{/if}</span>
        <button type="button" id="phyto-tc-unlink-btn"
                class="btn btn-xs btn-danger pull-right"
                data-id-product="{$phyto_tc_id_product|intval}"
                data-ajax-url="{$phyto_tc_ajax_url|escape:'html'}">
          <i class="icon-unlink"></i> {l s='Unlink' mod='phyto_tc_batch_tracker'}
        </button>
      </div>

      {* Detail cards *}
      <div class="row" id="phyto-tc-detail-cards">
        <div class="col-md-4">
          <div class="phyto-tc-info-card">
            <label>{l s='Units Remaining' mod='phyto_tc_batch_tracker'}</label>
            <span id="phyto-tc-units-remaining">{if $phyto_tc_linked_batch}{$phyto_tc_linked_batch.units_remaining|intval}{/if}</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="phyto-tc-info-card">
            <label>{l s='Status' mod='phyto_tc_batch_tracker'}</label>
            <span id="phyto-tc-status">{if $phyto_tc_linked_batch}{$phyto_tc_linked_batch.batch_status|escape:'html'}{/if}</span>
          </div>
        </div>
        <div class="col-md-4">
          <div class="phyto-tc-info-card">
            <label>{l s='Deflask Date' mod='phyto_tc_batch_tracker'}</label>
            <span id="phyto-tc-deflask-date">{if $phyto_tc_linked_batch && $phyto_tc_linked_batch.date_deflask}{$phyto_tc_linked_batch.date_deflask|escape:'html'}{else}&mdash;{/if}</span>
          </div>
        </div>
      </div>
    </div>

    {* ── No batch linked yet ─────────────────────────────────────────── *}
    <div id="phyto-tc-no-link-notice" class="{if $phyto_tc_linked_batch}hidden{/if} alert alert-info">
      {l s='No batch is linked to this product.' mod='phyto_tc_batch_tracker'}
    </div>

    {* ── Search / select batch ───────────────────────────────────────── *}
    <hr>
    <h4>{l s='Link a Batch' mod='phyto_tc_batch_tracker'}</h4>

    <div class="form-group">
      <label class="control-label col-lg-2">{l s='Batch' mod='phyto_tc_batch_tracker'}</label>
      <div class="col-lg-5">
        <select id="phyto-tc-batch-select" class="form-control">
          <option value="">&mdash; {l s='Select a batch' mod='phyto_tc_batch_tracker'} &mdash;</option>
          {foreach from=$phyto_tc_all_batches item='b'}
          <option value="{$b.id_batch|intval}"
                  {if $phyto_tc_linked_batch && $phyto_tc_linked_batch.id_batch == $b.id_batch}selected="selected"{/if}>
            {$b.batch_code|escape:'html'} — {$b.species_name|escape:'html'} [{$b.batch_status|escape:'html'}]
          </option>
          {/foreach}
        </select>
      </div>
      <div class="col-lg-3">
        <button type="button" id="phyto-tc-link-btn" class="btn btn-success"
                data-id-product="{$phyto_tc_id_product|intval}"
                data-ajax-url="{$phyto_tc_ajax_url|escape:'html'}">
          <i class="icon-link"></i> {l s='Link Batch' mod='phyto_tc_batch_tracker'}
        </button>
      </div>
    </div>

    <div class="col-lg-offset-2 col-lg-9">
      <p class="help-block">
        {l s='Don\'t see the batch? ' mod='phyto_tc_batch_tracker'}
        <a href="{$phyto_tc_ajax_url|escape:'html'|replace:'AdminPhytoTcBatchProduct':'AdminPhytoTcBatches'}&addphyto_tc_batch=1"
           target="_blank">
          {l s='Create a new batch' mod='phyto_tc_batch_tracker'}
        </a>
      </p>
    </div>

    {* ── AJAX status message ─────────────────────────────────────────── *}
    <div id="phyto-tc-ajax-msg" class="col-lg-9 col-lg-offset-2" style="display:none;"></div>

  </div>{* /panel-body *}
</div>
