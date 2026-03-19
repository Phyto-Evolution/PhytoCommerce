{extends file='helpers/view/view.tpl'}

{block name="override_tpl"}

<ul class="nav nav-tabs" style="margin-bottom:20px;">
    <li class="active"><a href="#tab-audit"   data-toggle="tab"><i class="icon-search"></i> SEO Audit</a></li>
    <li><a href="#tab-bulk"     data-toggle="tab"><i class="icon-magic"></i> Bulk Generate</a></li>
    <li><a href="#tab-settings" data-toggle="tab"><i class="icon-cog"></i> Settings</a></li>
</ul>

<div class="tab-content">

{* ── SEO Audit ──────────────────────────────────────────────────────────── *}
<div class="tab-pane active" id="tab-audit">
<div class="panel">
    <div class="panel-heading">
        <i class="icon-search"></i> SEO Audit
        <button class="btn btn-primary btn-sm pull-right" onclick="runAudit()">
            <i class="icon-refresh"></i> Run Audit
        </button>
    </div>
    <div class="panel-body">
        <p class="text-muted">Scans all active products and flags missing or thin SEO content.</p>

        <div id="audit_loading" style="display:none;text-align:center;padding:20px;">
            <i class="icon-spinner icon-spin icon-2x"></i>
            <p style="margin-top:8px;color:#888;">Auditing products...</p>
        </div>

        <div id="audit_results" style="display:none;">
            <div id="audit_summary" class="row" style="margin-bottom:15px;"></div>
            <div id="audit_table_wrap"></div>
        </div>

        <div id="audit_empty" style="display:none;padding:20px;text-align:center;">
            <i class="icon-check-circle icon-2x" style="color:#5cb85c;"></i>
            <p style="margin-top:8px;color:#5cb85c;font-weight:bold;">All products have complete SEO metadata!</p>
        </div>
    </div>
</div>
</div>

{* ── Bulk Generate ──────────────────────────────────────────────────────── *}
<div class="tab-pane" id="tab-bulk">
<div class="panel">
    <div class="panel-heading"><i class="icon-magic"></i> Bulk AI Meta Generation</div>
    <div class="panel-body">

        <div class="well" style="background:#f8fff8;border-color:#c3e6cb;">
            <p><i class="icon-info-sign"></i> <strong>How it works:</strong> Claude AI will generate SEO-optimized
            meta titles and descriptions for all products that are missing them.
            Only products with empty meta fields will be updated.</p>
            <p class="text-muted" style="margin:0;">
                Uses <code>claude-haiku-4-5-20251001</code> — fast and cost-effective.
                Requires Claude AI key in Settings.
            </p>
        </div>

        <div id="bulk_status" class="alert" style="display:none;"></div>

        <div id="bulk_loading" style="display:none;text-align:center;padding:20px;">
            <i class="icon-spinner icon-spin icon-2x"></i>
            <p style="margin-top:8px;color:#888;">Generating meta for products... this may take a few minutes.</p>
        </div>

        <button class="btn btn-primary btn-lg" onclick="bulkGenerate()">
            <i class="icon-magic"></i> Generate Meta for All Products Missing It
        </button>

        <hr>
        <h5>Schema Markup</h5>
        <p class="text-muted">
            Product JSON-LD schema is automatically injected on all product pages when this module is active.
            No configuration needed — it reads product data dynamically.
        </p>
        <div class="panel panel-default">
            <div class="panel-body" style="font-size:12px;">
                <pre style="font-size:11px;background:#f9f9f9;">{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Nepenthes rajah",
  "sku": "NEP-001",
  "brand": { "@type": "Brand", "name": "Phyto Evolution" },
  "offers": {
    "@type": "Offer",
    "price": "2999.00",
    "priceCurrency": "INR",
    "availability": "https://schema.org/InStock"
  }
}</pre>
            </div>
        </div>
    </div>
</div>
</div>

{* ── Settings ───────────────────────────────────────────────────────────── *}
<div class="tab-pane" id="tab-settings">
<div class="panel">
    <div class="panel-heading"><i class="icon-cog"></i> SEO Booster Settings</div>
    <div class="panel-body">
        <form method="post">
            <input type="hidden" name="saveSeoSettings" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="icon-magic"></i> Claude AI API Key</label>
                        <input type="password" name="ai_key" class="form-control"
                               value="{if isset($ai_key)}{$ai_key}{/if}"
                               placeholder="sk-ant-...">
                        <small class="text-muted">
                            Shared with phytoquickadd (stored as <code>PHYTO_AI_KEY</code>).
                            Get yours at <a href="https://console.anthropic.com/settings/keys" target="_blank">console.anthropic.com</a>.
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="checkbox-inline" style="font-size:14px;">
                            <input type="checkbox" name="auto_meta" value="1"
                                   {if $auto_meta}checked{/if}>
                            &nbsp;<strong>Auto-generate meta on product save</strong>
                        </label>
                        <p class="text-muted" style="margin-top:5px;">
                            When enabled, meta title and description are automatically generated
                            by Claude AI whenever a product is saved with empty meta fields.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="panel panel-info">
                        <div class="panel-heading"><i class="icon-info-sign"></i> Features</div>
                        <div class="panel-body" style="font-size:12px;">
                            <ul>
                                <li><strong>Auto meta:</strong> Generates meta title + description via Claude AI on product save (if empty)</li>
                                <li><strong>Bulk generate:</strong> Fill meta for all products with empty fields in one click</li>
                                <li><strong>SEO audit:</strong> Flags products missing meta, images, or short descriptions</li>
                                <li><strong>Schema markup:</strong> Injects JSON-LD Product schema on all product pages automatically</li>
                            </ul>
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
var SEO_AJAX_URL = '{/literal}{$ajax_url}{literal}';

var FLAG_LABELS = {
    'no_meta_title': '<span class="label label-danger">No meta title</span>',
    'no_meta_desc':  '<span class="label label-warning">No meta desc</span>',
    'short_desc':    '<span class="label label-info">Thin description</span>',
    'no_image':      '<span class="label label-default">No image</span>'
};

function runAudit() {
    document.getElementById('audit_loading').style.display = 'block';
    document.getElementById('audit_results').style.display = 'none';
    document.getElementById('audit_empty').style.display   = 'none';

    fetch(SEO_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phyto_seo_ajax=1&seo_action=audit'
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('audit_loading').style.display = 'none';
        if (data.error) { alert('Error: ' + data.error); return; }
        if (!data.issues || data.issues.length === 0) {
            document.getElementById('audit_empty').style.display = 'block';
            return;
        }
        renderAuditResults(data.issues);
        document.getElementById('audit_results').style.display = 'block';
    })
    .catch(function(e) {
        document.getElementById('audit_loading').style.display = 'none';
        alert('Request failed: ' + e.message);
    });
}

function renderAuditResults(issues) {
    var noMeta = issues.filter(function(i) { return i.flags.indexOf('no_meta_title') > -1 || i.flags.indexOf('no_meta_desc') > -1; }).length;
    var noImg  = issues.filter(function(i) { return i.flags.indexOf('no_image') > -1; }).length;
    var thin   = issues.filter(function(i) { return i.flags.indexOf('short_desc') > -1; }).length;

    document.getElementById('audit_summary').innerHTML =
        '<div class="col-md-3"><div class="panel panel-danger text-center" style="padding:12px;">' +
        '<div style="font-size:28px;font-weight:bold;">' + issues.length + '</div><div>Products with issues</div></div></div>' +
        '<div class="col-md-3"><div class="panel panel-warning text-center" style="padding:12px;">' +
        '<div style="font-size:28px;font-weight:bold;">' + noMeta + '</div><div>Missing meta</div></div></div>' +
        '<div class="col-md-3"><div class="panel panel-info text-center" style="padding:12px;">' +
        '<div style="font-size:28px;font-weight:bold;">' + thin + '</div><div>Thin description</div></div></div>' +
        '<div class="col-md-3"><div class="panel panel-default text-center" style="padding:12px;">' +
        '<div style="font-size:28px;font-weight:bold;">' + noImg + '</div><div>No image</div></div></div>';

    var rows = issues.map(function(issue) {
        var badges = issue.flags.map(function(f) { return FLAG_LABELS[f] || f; }).join(' ');
        var needsMeta = issue.flags.indexOf('no_meta_title') > -1 || issue.flags.indexOf('no_meta_desc') > -1;
        var genBtn = needsMeta
            ? '<button class="btn btn-xs btn-primary" onclick="generateMeta(' + issue.id_product + ', this)">' +
              '<i class="icon-magic"></i> Generate</button>'
            : '';
        return '<tr>' +
            '<td>' + issue.id_product + '</td>' +
            '<td>' + escHtml(issue.name) + '</td>' +
            '<td>' + escHtml(issue.reference || '-') + '</td>' +
            '<td>' + badges + '</td>' +
            '<td>' + genBtn + '</td>' +
            '</tr>';
    }).join('');

    document.getElementById('audit_table_wrap').innerHTML =
        '<table class="table table-striped table-hover" style="font-size:13px;">' +
        '<thead><tr><th>ID</th><th>Name</th><th>Ref</th><th>Issues</th><th>Action</th></tr></thead>' +
        '<tbody>' + rows + '</tbody></table>';
}

function generateMeta(id_product, btn) {
    btn.disabled = true;
    btn.innerHTML = '<i class="icon-spinner icon-spin"></i>';

    fetch(SEO_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phyto_seo_ajax=1&seo_action=generate_meta&id_product=' + encodeURIComponent(id_product)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            btn.disabled = false;
            btn.innerHTML = '<i class="icon-magic"></i> Generate';
            alert('Error: ' + data.error);
        } else {
            btn.parentElement.innerHTML = '<span class="label label-success"><i class="icon-check"></i> Done</span>';
            btn.closest('tr').style.opacity = '0.5';
        }
    })
    .catch(function(e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="icon-magic"></i> Generate';
        alert('Failed: ' + e.message);
    });
}

function bulkGenerate() {
    if (!confirm('Generate meta for all products missing it? This uses Claude AI and may take a few minutes.')) return;
    var status  = document.getElementById('bulk_status');
    var loading = document.getElementById('bulk_loading');
    status.style.display  = 'none';
    loading.style.display = 'block';

    fetch(SEO_AJAX_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'phyto_seo_ajax=1&seo_action=bulk_generate'
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
            status.textContent = data.message || 'Bulk generation complete.';
        }
    })
    .catch(function(e) {
        loading.style.display = 'none';
        status.className      = 'alert alert-danger';
        status.textContent    = 'Failed: ' + e.message;
        status.style.display  = 'block';
    });
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}
</script>
{/literal}

{/block}
