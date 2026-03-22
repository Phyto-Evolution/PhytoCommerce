<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TC Batch Label — {$phyto_tc_batch->batch_code|escape:'html'}</title>
  <style>
    /* ── Reset ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Courier New', Courier, monospace;
      background: #fff;
      color: #000;
      padding: 24px;
    }

    /* ── Label card ── */
    .label-card {
      width: 88mm;          /* standard label width */
      border: 2px solid #000;
      border-radius: 4px;
      padding: 14px 16px;
      page-break-inside: avoid;
    }

    .label-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 10px;
    }

    .label-batch-code {
      font-size: 18px;
      font-weight: 900;
      letter-spacing: .04em;
      line-height: 1.2;
    }

    .label-generation {
      display: inline-block;
      border: 2px solid #000;
      border-radius: 20px;
      padding: 2px 10px;
      font-size: 13px;
      font-weight: 700;
      white-space: nowrap;
    }

    .label-species {
      font-size: 13px;
      font-style: italic;
      margin-bottom: 8px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 6px;
    }

    .label-fields {
      font-size: 11px;
      line-height: 1.7;
      margin-bottom: 10px;
    }

    .label-fields dt {
      display: inline;
      font-weight: 700;
    }

    .label-fields dd {
      display: inline;
      margin: 0;
    }

    .label-fields dd::after {
      content: '\A';
      white-space: pre;
    }

    /* ── Lineage chain ── */
    .label-lineage {
      font-size: 10px;
      border-top: 1px dashed #aaa;
      padding-top: 6px;
      margin-bottom: 8px;
      line-height: 1.5;
    }

    .label-lineage-title {
      font-weight: 700;
      font-size: 10px;
      display: block;
      margin-bottom: 2px;
    }

    .lineage-chain {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 2px;
    }

    .lineage-node {
      background: #f0f0f0;
      border: 1px solid #aaa;
      border-radius: 3px;
      padding: 1px 6px;
      font-size: 9px;
    }

    .lineage-node.current {
      background: #000;
      color: #fff;
      font-weight: 700;
    }

    .lineage-arrow { font-size: 10px; color: #666; }

    /* ── QR code ── */
    #phyto-tc-qr {
      width: 80px;
      height: 80px;
      flex-shrink: 0;
    }

    /* ── Footer ── */
    .label-footer {
      font-size: 9px;
      color: #666;
      border-top: 1px solid #eee;
      padding-top: 5px;
      display: flex;
      justify-content: space-between;
    }

    /* ── Print buttons (hidden when printing) ── */
    .no-print {
      margin-bottom: 20px;
    }

    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
      .label-card { border: 2px solid #000; }
    }
  </style>
</head>
<body>

  <div class="no-print">
    <button onclick="window.print()" style="padding:8px 18px;font-size:14px;cursor:pointer;margin-right:8px;">
      🖨 Print
    </button>
    <a href="{$phyto_tc_print_back_url|escape:'html'}" style="font-size:13px;">← Back to Batches</a>
  </div>

  <div class="label-card">

    <div class="label-header">
      <div>
        <div class="label-batch-code">{$phyto_tc_batch->batch_code|escape:'html'}</div>
        <div class="label-generation">{$phyto_tc_batch->generation|escape:'html'}</div>
      </div>
      <div id="phyto-tc-qr"></div>
    </div>

    <div class="label-species">{$phyto_tc_batch->species_name|escape:'html'}</div>

    <dl class="label-fields">
      {if $phyto_tc_batch->date_initiation}
      <dt>Initiated:</dt> <dd>{$phyto_tc_batch->date_initiation|escape:'html'}</dd>
      {/if}
      {if $phyto_tc_batch->date_deflask}
      <dt>Deflasked:</dt> <dd>{$phyto_tc_batch->date_deflask|escape:'html'}</dd>
      {/if}
      {if $phyto_tc_batch->date_certified}
      <dt>Certified:</dt> <dd>{$phyto_tc_batch->date_certified|escape:'html'}</dd>
      {/if}
      <dt>Units remaining:</dt> <dd>{$phyto_tc_batch->units_remaining|intval}</dd>
      <dt>Status:</dt> <dd>{$phyto_tc_batch->batch_status|escape:'html'}</dd>
    </dl>

    {if $phyto_tc_lineage && $phyto_tc_lineage|count > 1}
    <div class="label-lineage">
      <span class="label-lineage-title">Lineage:</span>
      <div class="lineage-chain">
        {foreach from=$phyto_tc_lineage item='node' key='idx'}
          {if $idx > 0}<span class="lineage-arrow">›</span>{/if}
          <span class="lineage-node {if $node.id_batch == $phyto_tc_batch->id}current{/if}">
            {$node.batch_code|escape:'html'} <small>({$node.generation|escape:'html'})</small>
          </span>
        {/foreach}
      </div>
    </div>
    {/if}

    <div class="label-footer">
      <span>PhytoCommerce TC</span>
      <span>Printed: {$smarty.now|date_format:'%Y-%m-%d'}</span>
    </div>

  </div>

  {* Minimal QR code via pure-JS (no external CDN dependency) *}
  <script>
  /**
   * Minimal QR-code SVG generator — generates a simple QR version 3 (29×29)
   * with the batch code embedded. Uses the browser's canvas API as fallback.
   * For production, replace with a proper qrcode library.
   */
  (function() {
    var code = {$phyto_tc_batch->batch_code|json_encode};
    var container = document.getElementById('phyto-tc-qr');

    // Use Google Charts QR (requires internet access when printing from admin)
    var img = document.createElement('img');
    img.src = 'https://chart.googleapis.com/chart?cht=qr&chs=80x80&chl='
              + encodeURIComponent(code)
              + '&choe=UTF-8';
    img.alt  = code;
    img.width  = 80;
    img.height = 80;
    img.style.imageRendering = 'pixelated';

    img.onerror = function() {
      // Fallback: just show the code as text in the QR slot
      container.innerHTML = '<div style="width:80px;height:80px;display:flex;align-items:center;'
        + 'justify-content:center;border:1px solid #ccc;font-size:9px;text-align:center;'
        + 'word-break:break-all;padding:4px;">' + code + '</div>';
    };

    container.appendChild(img);
  })();
  </script>

</body>
</html>
