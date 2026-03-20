<div class="phyto-tc-provenance">

  <div class="phyto-tc-prov-header">
    <h3 class="phyto-tc-prov-title">
      {l s='Tissue Culture Provenance' mod='phyto_tc_batch_tracker'}
    </h3>
    <span class="phyto-tc-prov-code">{$phyto_tc_batch.batch_code|escape:'html'}</span>
  </div>

  {* Generation badge *}
  <div class="phyto-tc-prov-gen-badge phyto-tc-gen-{$phyto_tc_batch.generation|lower|regex_replace:'/[^a-z0-9]/':'-'}">
    {$phyto_tc_batch.generation_label|escape:'html'}
  </div>

  {* Species *}
  <p class="phyto-tc-prov-species">
    <em>{$phyto_tc_batch.species_name|escape:'html'}</em>
  </p>

  {* Timeline *}
  <ol class="phyto-tc-timeline">
    {if $phyto_tc_batch.date_initiation}
    <li class="phyto-tc-tl-item phyto-tc-tl-done">
      <span class="phyto-tc-tl-dot"></span>
      <div class="phyto-tc-tl-body">
        <strong>{l s='Initiated' mod='phyto_tc_batch_tracker'}</strong>
        <time>{$phyto_tc_batch.date_initiation|escape:'html'}</time>
      </div>
    </li>
    {/if}

    {if $phyto_tc_batch.date_deflask_formatted}
    <li class="phyto-tc-tl-item phyto-tc-tl-done">
      <span class="phyto-tc-tl-dot"></span>
      <div class="phyto-tc-tl-body">
        <strong>{l s='Deflasked' mod='phyto_tc_batch_tracker'}</strong>
        <time>{$phyto_tc_batch.date_deflask_formatted|escape:'html'}</time>
      </div>
    </li>
    {/if}

    {if $phyto_tc_batch.date_certified_formatted}
    <li class="phyto-tc-tl-item phyto-tc-tl-done">
      <span class="phyto-tc-tl-dot"></span>
      <div class="phyto-tc-tl-body">
        <strong>{l s='Certified' mod='phyto_tc_batch_tracker'}</strong>
        <time>{$phyto_tc_batch.date_certified_formatted|escape:'html'}</time>
      </div>
    </li>
    {/if}
  </ol>

  {* Sterility protocol *}
  {if $phyto_tc_batch.sterility_protocol}
  <details class="phyto-tc-prov-protocol">
    <summary>{l s='Sterility Protocol' mod='phyto_tc_batch_tracker'}</summary>
    <div class="phyto-tc-prov-protocol-body">
      {$phyto_tc_batch.sterility_protocol nofilter}
    </div>
  </details>
  {/if}

  {* Status & availability *}
  <div class="phyto-tc-prov-footer">
    <span class="phyto-tc-status phyto-tc-status-{$phyto_tc_batch.batch_status|lower|escape:'html'}">
      {$phyto_tc_batch.batch_status|escape:'html'}
    </span>
    {if $phyto_tc_batch.units_remaining > 0}
    <span class="phyto-tc-units">
      {l s='%d units available from this batch' sprintf=[$phyto_tc_batch.units_remaining] mod='phyto_tc_batch_tracker'}
    </span>
    {/if}
  </div>

</div>
