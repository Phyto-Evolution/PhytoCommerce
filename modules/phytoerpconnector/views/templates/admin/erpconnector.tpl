{extends file='helpers/view/view.tpl'}

{block name="override_tpl"}

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="#tab-dashboard" data-toggle="tab"><i class="icon-dashboard"></i> Dashboard</a></li>
    <li><a href="#tab-log"       data-toggle="tab"><i class="icon-list"></i> Sync Log</a></li>
    <li><a href="#tab-settings"  data-toggle="tab"><i class="icon-cog"></i> Settings</a></li>
</ul>

<div class="tab-content">

{* ── Dashboard ─────────────────────────────────────────────────────────── *}
<div class="tab-pane active" id="tab-dashboard">
<div class="panel">
    <div class="panel-heading"><i class="icon-dashboard"></i> ERP Sync Dashboard</div>
    <div class="panel-body">

        <div class="row" style="margin-bottom:20px;">
            <div class="col-md-3">
                <div class="panel panel-default text-center" style="padding:15px;">
                    <div style="font-size:32px;font-weight:bold;color:#5cb85c;">{$sync_stats.success}</div>
                    <div class="text-muted">Successful syncs</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default text-center" style="padding:15px;">
                    <div style="font-size:32px;font-weight:bold;color:#d9534f;">{$sync_stats.errors}</div>
                    <div class="text-muted">Errors</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default text-center" style="padding:15px;">
                    <div style="font-size:32px;font-weight:bold;color:#f0ad4e;">{$sync_stats.skipped}</div>
                    <div class="text-muted">Skipped</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-default text-center" style="padding:15px;">
                    <div style="font-size:32px;font-weight:bold;color:#337ab7;">{$sync_stats.total}</div>
                    <div class="text-muted">Total events (last 50)</div>
                </div>
            </div>
        </div>

        <div id="erp_status" class="alert" style="display:none;"></div>

        <h4><i class="icon-refresh"></i> Manual Sync</h4>
        <p class="text-muted">Trigger a full sync of all records to ERPNext. Existing records are skipped.</p>
        <div class="row">
            <div class="col-md-3">
                <button class="btn btn-primary btn-block" onclick="erpSync('test_connection')">
                    <i class="icon-signal"></i> Test Connection
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info btn-block" onclick="erpSync('sync_customers')">
                    <i class="icon-user"></i> Sync All Customers
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info btn-block" onclick="erpSync('sync_products')">
                    <i class="icon-leaf"></i> Sync All Products
                </button>
            </div>
            <div class="col-md-3">
                <button class="btn btn-info btn-block" onclick="erpSync('sync_orders')">
                    <i class="icon-shopping-cart"></i> Sync All Orders
                </button>
            </div>
        </div>
        <div class="row" style="margin-top:10px;">
            <div class="col-md-3">
                <button class="btn btn-warning btn-block" onclick="erpSync('pull_invoices')">
                    <i class="icon-file-text"></i> Pull Invoices (30d)
                </button>
            </div>
        </div>

        <div id="erp_loading" style="display:none;margin-top:15px;text-align:center;">
            <i class="icon-spinner icon-spin icon-2x"></i>
            <p style="margin-top:8px;color:#888;">Syncing with ERPNext...</p>
        </div>

        <hr>
        <h5><i class="icon-info-sign"></i> Auto-sync Status</h5>
        <table class="table table-bordered" style="width:auto;">
            <tr><td>Orders</td>
                <td>{if $sync_orders}<span class="label label-success">Enabled</span>{else}<span class="label label-default">Disabled</span>{/if}</td></tr>
            <tr><td>Customers</td>
                <td>{if $sync_customers}<span class="label label-success">Enabled</span>{else}<span class="label label-default">Disabled</span>{/if}</td></tr>
            <tr><td>Products</td>
                <td>{if $sync_products}<span class="label label-success">Enabled</span>{else}<span class="label label-default">Disabled</span>{/if}</td></tr>
            <tr><td>Invoices (pull)</td>
                <td>{if $sync_invoices}<span class="label label-success">Enabled</span>{else}<span class="label label-default">Disabled</span>{/if}</td></tr>
        </table>

    </div>
</div>
</div>

{* ── Sync Log ───────────────────────────────────────────────────────────── *}
<div class="tab-pane" id="tab-log">
<div class="panel">
    <div class="panel-heading"><i class="icon-list"></i> Sync Log <small class="text-muted">(last 50 events)</small></div>
    <div class="panel-body" style="padding:0;">
        {if $sync_log}
        <table class="table table-striped table-hover" style="margin:0;font-size:13px;">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Dir</th>
                    <th>PS ID</th>
                    <th>ERP Name</th>
                    <th>Status</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
            {foreach $sync_log as $row}
                <tr>
                    <td style="white-space:nowrap;">{$row.created_at}</td>
                    <td><span class="label label-info">{$row.sync_type}</span></td>
                    <td>{$row.direction}</td>
                    <td>{$row.ps_id}</td>
                    <td><code style="font-size:11px;">{$row.erp_name}</code></td>
                    <td>
                        {if $row.status == 'success'}<span class="label label-success">success</span>
                        {elseif $row.status == 'error'}<span class="label label-danger">error</span>
                        {elseif $row.status == 'skipped'}<span class="label label-warning">skipped</span>
                        {else}<span class="label label-default">{$row.status}</span>{/if}
                    </td>
                    <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                        title="{$row.message|escape:'html'}">{$row.message}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        {else}
            <div style="padding:30px;text-align:center;color:#aaa;">
                <i class="icon-list icon-2x"></i>
                <p>No sync events yet. Configure ERPNext credentials and run a manual sync.</p>
            </div>
        {/if}
    </div>
</div>
</div>

{* ── Settings ───────────────────────────────────────────────────────────── *}
<div class="tab-pane" id="tab-settings">
<div class="panel">
    <div class="panel-heading"><i class="icon-cog"></i> ERPNext Connection Settings</div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="saveErpSettings" value="1">
            <div class="row">
                <div class="col-md-7">
                    <div class="form-group">
                        <label>ERPNext URL</label>
                        <input type="url" name="erp_url" class="form-control"
                               value="{$erp_url|escape:'html'}"
                               placeholder="https://erp.phytocommerce.com">
                        <small class="text-muted">Base URL of your ERPNext instance (no trailing slash)</small>
                    </div>
                    <div class="form-group">
                        <label>API Key</label>
                        <input type="text" name="erp_api_key" class="form-control"
                               value="{$erp_api_key|escape:'html'}"
                               placeholder="ERPNext API Key">
                        <small class="text-muted">From ERPNext → User → API Access</small>
                    </div>
                    <div class="form-group">
                        <label>API Secret</label>
                        <input type="password" name="erp_api_secret" class="form-control"
                               value="{$erp_api_secret|escape:'html'}"
                               placeholder="ERPNext API Secret">
                    </div>

                    <h5 style="margin-top:25px;"><i class="icon-toggle-on"></i> Auto-sync Triggers</h5>
                    <p class="text-muted">When enabled, records sync automatically on PrestaShop events.</p>

                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="sync_orders" value="1"
                                   {if $sync_orders}checked{/if}>
                            Sync orders to ERPNext (on status update)
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="sync_customers" value="1"
                                   {if $sync_customers}checked{/if}>
                            Sync customers to ERPNext (on account creation)
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="sync_products" value="1"
                                   {if $sync_products}checked{/if}>
                            Sync products to ERPNext (on add/update)
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="sync_invoices" value="1"
                                   {if $sync_invoices}checked{/if}>
                            Pull invoices from ERPNext on manual sync
                        </label>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="panel panel-info">
                        <div class="panel-heading"><i class="icon-info-sign"></i> Setup Guide</div>
                        <div class="panel-body" style="font-size:12px;">
                            <ol>
                                <li>In ERPNext, go to <strong>Settings → Users &amp; Permissions → User</strong></li>
                                <li>Select your API user and click <strong>Generate Keys</strong></li>
                                <li>Copy the <strong>API Key</strong> and <strong>API Secret</strong></li>
                                <li>Paste them above and click <strong>Save Settings</strong></li>
                                <li>Use <strong>Test Connection</strong> on the Dashboard tab</li>
                            </ol>
                            <hr>
                            <p><strong>Custom ERPNext fields required:</strong></p>
                            <ul>
                                <li>Sales Order: <code>custom_ps_order_id</code> (Int)</li>
                                <li>Sales Order: <code>custom_ps_reference</code> (Data)</li>
                                <li>Sales Invoice: <code>custom_ps_order_id</code> (Int)</li>
                            </ul>
                            <p>Add these via ERPNext Customize Form.</p>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="icon-save"></i> Save Settings
            </button>
        </form>
    </div>
</div>
</div>

</div>{* end tab-content *}

{literal}
<script>
var ERP_AJAX_URL = '{/literal}{$ajax_url}{literal}';

function erpSync(action) {
    var status  = document.getElementById('erp_status');
    var loading = document.getElementById('erp_loading');
    status.style.display  = 'none';
    loading.style.display = 'block';

    fetch(ERP_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phyto_erp_ajax=1&erp_action=' + encodeURIComponent(action)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        loading.style.display = 'none';
        status.style.display  = 'block';
        if (data.error) {
            status.className   = 'alert alert-danger';
            status.textContent = 'Error: ' + data.error;
        } else {
            status.className   = 'alert alert-success';
            status.textContent = data.message || 'Done.';
        }
    })
    .catch(function(e) {
        loading.style.display = 'none';
        status.className      = 'alert alert-danger';
        status.textContent    = 'Request failed: ' + e.message;
        status.style.display  = 'block';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var savedTab = sessionStorage.getItem('phyto_erp_tab');
    if (savedTab) {
        sessionStorage.removeItem('phyto_erp_tab');
        var link = document.querySelector('a[href="#' + savedTab + '"]');
        if (link) link.click();
    }
});
</script>
{/literal}

{/block}
