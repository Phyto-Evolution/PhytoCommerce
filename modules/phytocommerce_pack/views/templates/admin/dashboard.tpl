{*
 * PhytoCommerce Pack — Admin Dashboard
 * Bootstrap 3, PrestaShop 8 back-office style
 *}
<div class="panel phyto-pack-dashboard">
  <div class="panel-heading">
    <i class="icon-leaf"></i>
    PhytoCommerce Pack <small class="text-muted">v{$pack_version}</small>
    <span class="badge badge-success pull-right">{$installed}/{$total} installed</span>
  </div>

  {* ── Progress bar ────────────────────────────────────────────────────── *}
  <div class="row" style="padding:15px 20px 5px">
    <div class="col-xs-12">
      <div class="progress" style="height:10px;margin-bottom:8px">
        <div class="progress-bar progress-bar-success"
             style="width:{math equation='(a/b)*100' a=$installed b=$total}%"></div>
      </div>
      <p class="text-muted" style="font-size:12px">
        {$installed} of {$total} PhytoCommerce modules installed on this PrestaShop.
      </p>
    </div>
  </div>

  {* ── Install All button ──────────────────────────────────────────────── *}
  {if $installed < $total}
  <div style="padding:0 20px 15px">
    <button class="btn btn-primary btn-phyto-install-all">
      <i class="icon-download"></i> Install all remaining modules
    </button>
    <span class="phyto-installing-msg" style="display:none;margin-left:10px">
      <i class="icon-spinner icon-spin"></i> Installing…
    </span>
  </div>
  {/if}

  {* ── Module table ────────────────────────────────────────────────────── *}
  <table class="table" style="margin:0">
    <thead>
      <tr>
        <th>Module</th>
        <th>Status</th>
        <th>Version</th>
        <th style="width:180px">Actions</th>
      </tr>
    </thead>
    <tbody>
      {foreach $statuses as $mod}
      <tr data-module="{$mod.name}">
        <td>
          <code>{$mod.name}</code>
          {if !$mod.present}
            <span class="label label-default" title="Module files not found in /modules/">not found</span>
          {/if}
        </td>
        <td>
          {if $mod.installed && $mod.active}
            <span class="label label-success">Active</span>
          {elseif $mod.installed && !$mod.active}
            <span class="label label-warning">Disabled</span>
          {elseif $mod.present}
            <span class="label label-default">Not installed</span>
          {else}
            <span class="label label-danger">Missing</span>
          {/if}
        </td>
        <td>{$mod.version}</td>
        <td>
          {if $mod.installed}
            <button class="btn btn-xs btn-danger btn-phyto-uninstall"
                    data-module="{$mod.name}">
              <i class="icon-trash"></i> Uninstall
            </button>
          {elseif $mod.present}
            <button class="btn btn-xs btn-success btn-phyto-install"
                    data-module="{$mod.name}">
              <i class="icon-download"></i> Install
            </button>
          {else}
            <span class="text-muted">—</span>
          {/if}
        </td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>

{* ── Installation log ──────────────────────────────────────────────────── *}
{if $install_log}
<div class="panel" style="margin-top:15px">
  <div class="panel-heading"><i class="icon-list"></i> Installation Log</div>
  <table class="table table-condensed" style="font-size:12px;margin:0">
    <thead><tr><th>Module</th><th>Status</th><th>Message</th><th>Time</th></tr></thead>
    <tbody>
      {foreach $install_log as $row}
      <tr>
        <td><code>{$row.module_name}</code></td>
        <td>
          {if $row.status == 'installed'}
            <span class="label label-success">{$row.status}</span>
          {elseif $row.status == 'failed'}
            <span class="label label-danger">{$row.status}</span>
          {else}
            <span class="label label-default">{$row.status}</span>
          {/if}
        </td>
        <td>{$row.message}</td>
        <td>{$row.installed_at}</td>
      </tr>
      {/foreach}
    </tbody>
  </table>
</div>
{/if}

<script>
(function($) {
  var ajaxUrl = '{$ajax_url|addslashes}';

  function moduleAction(action, moduleName, $row) {
    $.post(ajaxUrl, {
      phyto_ajax: 1,
      phyto_action: action,
      module_name: moduleName
    }, function(res) {
      if (res.success) location.reload();
      else alert('Error: ' + (res.error || 'Unknown error'));
    }).fail(function() { alert('Request failed'); });
  }

  $(document).on('click', '.btn-phyto-install', function() {
    var $btn = $(this), mod = $btn.data('module');
    $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i>');
    moduleAction('install_module', mod, $btn.closest('tr'));
  });

  $(document).on('click', '.btn-phyto-uninstall', function() {
    if (!confirm('Uninstall ' + $(this).data('module') + '?')) return;
    var $btn = $(this), mod = $btn.data('module');
    $btn.prop('disabled', true).html('<i class="icon-spinner icon-spin"></i>');
    moduleAction('uninstall_module', mod, $btn.closest('tr'));
  });

  $('.btn-phyto-install-all').on('click', function() {
    if (!confirm('Install all remaining PhytoCommerce modules?')) return;
    $(this).prop('disabled', true);
    $('.phyto-installing-msg').show();
    $.post(ajaxUrl, { phyto_ajax: 1, phyto_action: 'install_all' }, function(res) {
      location.reload();
    }).fail(function() { alert('Request failed'); location.reload(); });
  });
}(jQuery));
</script>
