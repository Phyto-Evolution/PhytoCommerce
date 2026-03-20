<div class="panel panel-default" id="phyto-tc-contam-panel" style="margin-top:24px;">
  <div class="panel-heading">
    <i class="icon-warning-sign"></i>
    {l s='Contamination Incidents' mod='phyto_tc_batch_tracker'}
    {if $phyto_tc_contam_logs}
      {assign var='open' value=0}
      {foreach from=$phyto_tc_contam_logs item='log'}
        {if !$log.resolved}{assign var='open' value=$open+1}{/if}
      {/foreach}
      {if $open > 0}
        <span class="badge badge-danger" style="margin-left:6px;">{$open} {l s='open' mod='phyto_tc_batch_tracker'}</span>
      {/if}
    {/if}
  </div>
  <div class="panel-body">

    {if $phyto_tc_contam_logs}
    <table class="table table-condensed table-hover">
      <thead>
        <tr>
          <th>{l s='Date' mod='phyto_tc_batch_tracker'}</th>
          <th>{l s='Type' mod='phyto_tc_batch_tracker'}</th>
          <th>{l s='Affected Units' mod='phyto_tc_batch_tracker'}</th>
          <th>{l s='Status' mod='phyto_tc_batch_tracker'}</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$phyto_tc_contam_logs item='log'}
        <tr class="{if !$log.resolved}phyto-tc-contam-open{/if}">
          <td>{$log.incident_date|escape:'html'}</td>
          <td>
            <span class="badge {if $log.type == 'Bacterial' || $log.type == 'Viral'}badge-danger{elseif $log.type == 'Fungal' || $log.type == 'Pest'}badge-warning{else}badge-secondary{/if}">
              {$log.type|escape:'html'}
            </span>
          </td>
          <td>{$log.affected_units|intval}</td>
          <td>
            {if $log.resolved}
              <span class="badge badge-success">{l s='Resolved' mod='phyto_tc_batch_tracker'}</span>
            {else}
              <span class="badge badge-danger">{l s='Open' mod='phyto_tc_batch_tracker'}</span>
            {/if}
          </td>
          <td>
            {if !$log.resolved}
            <button type="button" class="btn btn-xs btn-default phyto-tc-resolve-log"
                    data-id-log="{$log.id_log|intval}"
                    data-contam-url="{$phyto_tc_contam_url|escape:'html'}">
              <i class="icon-check"></i> {l s='Mark Resolved' mod='phyto_tc_batch_tracker'}
            </button>
            {/if}
            <a href="{$phyto_tc_contam_url|escape:'html'}&id_log={$log.id_log|intval}&updatephyto_tc_contamination_log=1"
               class="btn btn-xs btn-default">
              <i class="icon-edit"></i>
            </a>
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {else}
    <p class="text-muted">{l s='No contamination incidents recorded for this batch.' mod='phyto_tc_batch_tracker'}</p>
    {/if}

    <a href="{$phyto_tc_contam_url|escape:'html'}&id_batch={$phyto_tc_id_batch|intval}&addphyto_tc_contamination_log=1"
       class="btn btn-warning btn-sm" style="margin-top:8px;">
      <i class="icon-plus"></i>
      {l s='Log New Incident' mod='phyto_tc_batch_tracker'}
    </a>

    <div id="phyto-tc-contam-msg" style="display:none;margin-top:8px;"></div>

  </div>
</div>

<script>
(function ($) {
  $('#phyto-tc-contam-panel').on('click', '.phyto-tc-resolve-log', function () {
    var $btn     = $(this);
    var idLog    = $btn.data('id-log');
    var url      = $btn.data('contam-url');

    $btn.prop('disabled', true);

    $.post(url, { action: 'resolveLog', ajax: 1, id_log: idLog }, 'json')
      .done(function (res) {
        if (res && res.success) {
          $btn.closest('tr').find('.badge-danger').first()
              .removeClass('badge-danger').addClass('badge-success').text('Resolved');
          $btn.closest('td').html('<span class="text-muted">' + (res.message || 'Resolved') + '</span>');

          var $panel = $btn.closest('.panel');
          var $badge = $panel.find('.panel-heading .badge-danger').first();
          var count  = parseInt($badge.text(), 10) - 1;
          if (count <= 0) {
            $badge.remove();
          } else {
            $badge.text(count + ' open');
          }
        } else {
          alert((res && res.message) ? res.message : 'Error.');
          $btn.prop('disabled', false);
        }
      })
      .fail(function () {
        alert('Network error.');
        $btn.prop('disabled', false);
      });
  });
}(jQuery));
</script>
