{**
 * calculator.tpl — Phyto TC Cost Calculator admin template
 *
 * Full interactive calculator with live JS calculations and saved estimates list.
 *
 * Smarty vars supplied by AdminPhytoTcCostCalcController:
 *   $module_dir       — URI path to the module directory
 *   $saved_estimates  — array of saved estimate rows from DB
 *   $form_action      — POST target URL with admin token
 *   $token            — PS admin token string
 **}

<div class="phyto-calc-wrapper">

  {* ── Flash messages ─────────────────────────────────────────────────────── *}
  {if isset($confirmations) && $confirmations|@count > 0}
    <div class="alert alert-success">
      {foreach from=$confirmations item=msg}<p>{$msg|escape:'html':'UTF-8'}</p>{/foreach}
    </div>
  {/if}
  {if isset($errors) && $errors|@count > 0}
    <div class="alert alert-danger">
      {foreach from=$errors item=err}<p>{$err|escape:'html':'UTF-8'}</p>{/foreach}
    </div>
  {/if}

  {* ═══════════════════════════════════════════════════════════════════════════
     CALCULATOR FORM
     ═══════════════════════════════════════════════════════════════════════ *}
  <div class="panel phyto-calc-panel">
    <div class="panel-heading">
      <i class="icon-calculator"></i>
      {l s='TC Batch Cost Calculator' mod='phyto_tc_cost_calculator'}
    </div>

    <div class="panel-body">
      <form id="phyto-calc-form" method="post" action="{$form_action|escape:'html':'UTF-8'}">
        <input type="hidden" name="action" value="save_estimate">
        <input type="hidden" name="token"  value="{$token|escape:'html':'UTF-8'}">

        {* Hidden serialised blobs updated by JS before submit *}
        <input type="hidden" id="inputs_json"  name="inputs_json"  value="{}">
        <input type="hidden" id="results_json" name="results_json" value="{}">

        <div class="row">

          {* ── LEFT COLUMN ─────────────────────────────────────────────── *}
          <div class="col-md-7">

            {* ─── 1. Substrate Costs ─────────────────────────────────── *}
            <div class="phyto-calc-section">
              <h4 class="phyto-calc-section-title">
                <span class="phyto-calc-section-number">1</span>
                {l s='Substrate Costs' mod='phyto_tc_cost_calculator'}
              </h4>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='MS Salts' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <input type="number" id="ms_qty" name="ms_qty"
                           class="form-control phyto-calc-input"
                           value="4.4" min="0" step="0.01" placeholder="0">
                    <span class="input-group-addon">g</span>
                  </div>
                  <p class="help-block">{l s='Qty per batch' mod='phyto_tc_cost_calculator'}</p>
                </div>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="ms_price" name="ms_price"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/g</span>
                  </div>
                  <p class="help-block">{l s='Price per gram' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Agar' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <input type="number" id="agar_qty" name="agar_qty"
                           class="form-control phyto-calc-input"
                           value="7" min="0" step="0.01" placeholder="0">
                    <span class="input-group-addon">g</span>
                  </div>
                  <p class="help-block">{l s='Qty per batch' mod='phyto_tc_cost_calculator'}</p>
                </div>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="agar_price" name="agar_price"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/g</span>
                  </div>
                  <p class="help-block">{l s='Price per gram' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Sucrose' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <input type="number" id="sucrose_qty" name="sucrose_qty"
                           class="form-control phyto-calc-input"
                           value="30" min="0" step="0.01" placeholder="0">
                    <span class="input-group-addon">g</span>
                  </div>
                  <p class="help-block">{l s='Qty per batch' mod='phyto_tc_cost_calculator'}</p>
                </div>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="sucrose_price_kg" name="sucrose_price_kg"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/kg</span>
                  </div>
                  <p class="help-block">{l s='Price per kg' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              {* Dynamic additives *}
              <div id="phyto-additives-container">
                {* Additive rows injected by JS *}
              </div>

              <button type="button" id="phyto-add-additive" class="btn btn-default btn-sm phyto-calc-add-btn">
                <i class="icon-plus-sign"></i>
                {l s='Add Additive' mod='phyto_tc_cost_calculator'}
              </button>
            </div>
            {* /Substrate Costs *}

            {* ─── 2. Overhead per Batch ──────────────────────────────── *}
            <div class="phyto-calc-section">
              <h4 class="phyto-calc-section-title">
                <span class="phyto-calc-section-number">2</span>
                {l s='Overhead per Batch' mod='phyto_tc_cost_calculator'}
              </h4>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Autoclave Cycles' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <input type="number" id="autoclave_cycles" name="autoclave_cycles"
                         class="form-control phyto-calc-input"
                         value="1" min="0" step="1" placeholder="1">
                  <p class="help-block">{l s='Number of cycles' mod='phyto_tc_cost_calculator'}</p>
                </div>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="autoclave_cost_per_cycle" name="autoclave_cost_per_cycle"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/cycle</span>
                  </div>
                  <p class="help-block">{l s='Cost per cycle' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Electricity' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="electricity" name="electricity"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                  </div>
                  <p class="help-block">{l s='Flat electricity cost for this batch' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Laminar Flow Hood' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <input type="number" id="lf_hours" name="lf_hours"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.25" placeholder="0">
                    <span class="input-group-addon">hrs</span>
                  </div>
                  <p class="help-block">{l s='Hours used' mod='phyto_tc_cost_calculator'}</p>
                </div>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="lf_rate" name="lf_rate"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/hr</span>
                  </div>
                  <p class="help-block">{l s='Rate per hour' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Glassware / Disposables' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-8">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="glassware" name="glassware"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                  </div>
                  <p class="help-block">{l s='Flat cost for consumables' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>
            </div>
            {* /Overhead *}

            {* ─── 3. Labor ───────────────────────────────────────────── *}
            <div class="phyto-calc-section">
              <h4 class="phyto-calc-section-title">
                <span class="phyto-calc-section-number">3</span>
                {l s='Labor' mod='phyto_tc_cost_calculator'}
              </h4>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Person-Hours' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <input type="number" id="person_hours" name="person_hours"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.25" placeholder="0">
                    <span class="input-group-addon">hrs</span>
                  </div>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Labor Rate' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="labor_rate" name="labor_rate"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                    <span class="input-group-addon">/hr</span>
                  </div>
                </div>
              </div>
            </div>
            {* /Labor *}

            {* ─── 4. Batch Outputs ───────────────────────────────────── *}
            <div class="phyto-calc-section">
              <h4 class="phyto-calc-section-title">
                <span class="phyto-calc-section-number">4</span>
                {l s='Batch Outputs' mod='phyto_tc_cost_calculator'}
              </h4>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Total Explants Initiated' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <input type="number" id="total_explants" name="total_explants"
                         class="form-control phyto-calc-input"
                         value="100" min="1" step="1" placeholder="100">
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Rejection / Contamination Rate' mod='phyto_tc_cost_calculator'}
                  <span id="rejection_rate_display" class="phyto-calc-range-value">20%</span>
                </label>
                <div class="col-sm-8">
                  <input type="range" id="rejection_rate" name="rejection_rate"
                         class="phyto-calc-range"
                         value="20" min="0" max="100" step="1">
                  <p class="help-block">
                    {l s='0 % = no losses &nbsp;|&nbsp; 100 % = total loss' mod='phyto_tc_cost_calculator'}
                  </p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Sellable Units' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <input type="number" id="sellable_units" name="sellable_units"
                         class="form-control" value="80" disabled>
                  <p class="help-block">{l s='Auto-calculated' mod='phyto_tc_cost_calculator'}</p>
                </div>
              </div>
            </div>
            {* /Batch Outputs *}

            {* ─── 5. Pricing Targets ─────────────────────────────────── *}
            <div class="phyto-calc-section">
              <h4 class="phyto-calc-section-title">
                <span class="phyto-calc-section-number">5</span>
                {l s='Pricing Targets' mod='phyto_tc_cost_calculator'}
              </h4>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Target Gross Margin' mod='phyto_tc_cost_calculator'}
                  <span id="target_margin_display" class="phyto-calc-range-value">40%</span>
                </label>
                <div class="col-sm-8">
                  <input type="range" id="target_margin" name="target_margin"
                         class="phyto-calc-range"
                         value="40" min="20" max="80" step="1">
                  <p class="help-block">
                    {l s='Gross margin target for suggested retail price' mod='phyto_tc_cost_calculator'}
                  </p>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Packaging Cost / Unit' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="packaging_cost" name="packaging_cost"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                  </div>
                </div>
              </div>

              <div class="form-group row phyto-calc-input-row">
                <label class="col-sm-4 control-label">
                  {l s='Shipping Material / Unit' mod='phyto_tc_cost_calculator'}
                </label>
                <div class="col-sm-4">
                  <div class="input-group">
                    <span class="input-group-addon">₹</span>
                    <input type="number" id="shipping_material" name="shipping_material"
                           class="form-control phyto-calc-input"
                           value="0" min="0" step="0.01" placeholder="0.00">
                  </div>
                </div>
              </div>
            </div>
            {* /Pricing Targets *}

          </div>
          {* /col-md-7 *}

          {* ── RIGHT COLUMN — Results ───────────────────────────────────── *}
          <div class="col-md-5">
            <div class="phyto-calc-results-panel" id="phyto-results-panel">
              <h4 class="phyto-calc-results-title">
                <i class="icon-signal"></i>
                {l s='Live Results' mod='phyto_tc_cost_calculator'}
              </h4>

              <table class="table phyto-calc-results-table">
                <tbody>
                  <tr>
                    <td class="phyto-calc-result-label">
                      {l s='Total Batch Cost' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_total_batch_cost">₹0.00</td>
                  </tr>
                  <tr class="phyto-calc-result-sub">
                    <td class="phyto-calc-result-label phyto-calc-result-indent">
                      — {l s='Substrate' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_substrate_cost">₹0.00</td>
                  </tr>
                  <tr class="phyto-calc-result-sub">
                    <td class="phyto-calc-result-label phyto-calc-result-indent">
                      — {l s='Overhead' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_overhead_cost">₹0.00</td>
                  </tr>
                  <tr class="phyto-calc-result-sub">
                    <td class="phyto-calc-result-label phyto-calc-result-indent">
                      — {l s='Labor' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_labor_cost">₹0.00</td>
                  </tr>
                  <tr class="phyto-calc-result-highlight">
                    <td class="phyto-calc-result-label">
                      {l s='Cost per Sellable Unit' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_cost_per_unit">₹0.00</td>
                  </tr>
                  <tr>
                    <td class="phyto-calc-result-label">
                      {l s='Break-even Price / Unit' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_breakeven">₹0.00</td>
                  </tr>
                  <tr class="phyto-calc-result-highlight phyto-calc-result-primary">
                    <td class="phyto-calc-result-label">
                      {l s='Suggested Retail Price' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_suggested_price">₹0.00</td>
                  </tr>
                  <tr>
                    <td class="phyto-calc-result-label">
                      {l s='Profit per Batch' mod='phyto_tc_cost_calculator'}
                    </td>
                    <td class="phyto-calc-result-value" id="res_profit_batch">₹0.00</td>
                  </tr>
                </tbody>
              </table>

              {* ── Bar Chart ─────────────────────────────────────────── *}
              <div class="phyto-calc-chart">
                <p class="phyto-calc-chart-title">
                  {l s='Cost vs Revenue Breakdown' mod='phyto_tc_cost_calculator'}
                </p>

                <div class="phyto-calc-bar-row">
                  <span class="phyto-calc-bar-label">
                    {l s='Substrate' mod='phyto_tc_cost_calculator'}
                  </span>
                  <div class="phyto-calc-bar-track">
                    <div class="phyto-calc-bar phyto-calc-bar-substrate" id="bar_substrate" style="width:0%"></div>
                  </div>
                  <span class="phyto-calc-bar-pct" id="bar_substrate_pct">0%</span>
                </div>

                <div class="phyto-calc-bar-row">
                  <span class="phyto-calc-bar-label">
                    {l s='Overhead' mod='phyto_tc_cost_calculator'}
                  </span>
                  <div class="phyto-calc-bar-track">
                    <div class="phyto-calc-bar phyto-calc-bar-overhead" id="bar_overhead" style="width:0%"></div>
                  </div>
                  <span class="phyto-calc-bar-pct" id="bar_overhead_pct">0%</span>
                </div>

                <div class="phyto-calc-bar-row">
                  <span class="phyto-calc-bar-label">
                    {l s='Labor' mod='phyto_tc_cost_calculator'}
                  </span>
                  <div class="phyto-calc-bar-track">
                    <div class="phyto-calc-bar phyto-calc-bar-labor" id="bar_labor" style="width:0%"></div>
                  </div>
                  <span class="phyto-calc-bar-pct" id="bar_labor_pct">0%</span>
                </div>

                <div class="phyto-calc-bar-row">
                  <span class="phyto-calc-bar-label">
                    {l s='Margin' mod='phyto_tc_cost_calculator'}
                  </span>
                  <div class="phyto-calc-bar-track">
                    <div class="phyto-calc-bar phyto-calc-bar-margin" id="bar_margin" style="width:0%"></div>
                  </div>
                  <span class="phyto-calc-bar-pct" id="bar_margin_pct">0%</span>
                </div>
              </div>
              {* /Bar Chart *}

            </div>
            {* /Results panel *}
          </div>
          {* /col-md-5 *}

        </div>
        {* /row *}

        {* ─── 6. Save as Estimate ────────────────────────────────────────── *}
        <div class="phyto-calc-section phyto-calc-save-section" id="phyto-save-section">
          <h4 class="phyto-calc-section-title">
            <span class="phyto-calc-section-number">6</span>
            {l s='Save as Estimate' mod='phyto_tc_cost_calculator'}
          </h4>

          <div class="row">
            <div class="col-sm-5">
              <div class="form-group">
                <label class="control-label" for="estimate_label">
                  {l s='Estimate Label' mod='phyto_tc_cost_calculator'}
                  <span class="required">*</span>
                </label>
                <input type="text" id="estimate_label" name="estimate_label"
                       class="form-control"
                       placeholder="{l s='e.g. Banana Stage-2 Batch #14' mod='phyto_tc_cost_calculator'}"
                       maxlength="200">
              </div>
            </div>
            <div class="col-sm-3">
              <div class="form-group">
                <label class="control-label" for="id_batch">
                  {l s='Linked Batch ID' mod='phyto_tc_cost_calculator'}
                </label>
                <input type="number" id="id_batch" name="id_batch"
                       class="form-control"
                       placeholder="{l s='Optional' mod='phyto_tc_cost_calculator'}"
                       min="0" step="1" value="0">
              </div>
            </div>
            <div class="col-sm-4 phyto-calc-save-btn-col">
              <button type="submit" id="phyto-save-btn" class="btn btn-primary btn-lg phyto-calc-save-btn">
                <i class="icon-save"></i>
                {l s='Save Estimate' mod='phyto_tc_cost_calculator'}
              </button>
            </div>
          </div>
        </div>
        {* /Save Estimate *}

      </form>
    </div>
    {* /panel-body *}
  </div>
  {* /panel *}

  {* ═══════════════════════════════════════════════════════════════════════════
     SAVED ESTIMATES TABLE
     ═══════════════════════════════════════════════════════════════════════ *}
  <div class="panel phyto-calc-panel">
    <div class="panel-heading">
      <i class="icon-list"></i>
      {l s='Saved Estimates' mod='phyto_tc_cost_calculator'}
      {if $saved_estimates|@count > 0}
        <span class="badge">{$saved_estimates|@count}</span>
      {/if}
    </div>

    <div class="panel-body">
      {if $saved_estimates|@count == 0}
        <div class="alert alert-info">
          {l s='No estimates saved yet. Use the calculator above and click "Save Estimate".' mod='phyto_tc_cost_calculator'}
        </div>
      {else}
        <div class="table-responsive">
          <table class="table table-striped phyto-calc-estimates-table">
            <thead>
              <tr>
                <th>{l s='ID' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Label' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Batch ID' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Total Cost' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Cost/Unit' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Suggested Price' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Sellable Units' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Date' mod='phyto_tc_cost_calculator'}</th>
                <th>{l s='Actions' mod='phyto_tc_cost_calculator'}</th>
              </tr>
            </thead>
            <tbody>
              {foreach from=$saved_estimates item=est}
                <tr class="phyto-calc-estimate-row" data-inputs="{$est.inputs_json|escape:'html':'UTF-8'}">
                  <td>{$est.id_estimate|intval}</td>
                  <td>
                    <strong>{$est.estimate_label|escape:'html':'UTF-8'}</strong>
                  </td>
                  <td>
                    {if $est.id_batch > 0}
                      <span class="label label-info">#{$est.id_batch|intval}</span>
                    {else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    {if isset($est.results.total_batch_cost)}
                      ₹{$est.results.total_batch_cost|string_format:'%.2f'}
                    {else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    {if isset($est.results.cost_per_unit)}
                      ₹{$est.results.cost_per_unit|string_format:'%.2f'}
                    {else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    {if isset($est.results.suggested_price)}
                      <strong class="phyto-calc-price-highlight">
                        ₹{$est.results.suggested_price|string_format:'%.2f'}
                      </strong>
                    {else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    {if isset($est.results.sellable_units)}
                      {$est.results.sellable_units|intval}
                    {else}
                      <span class="text-muted">—</span>
                    {/if}
                  </td>
                  <td>
                    <small class="text-muted">
                      {$est.date_add|escape:'html':'UTF-8'}
                    </small>
                  </td>
                  <td>
                    <div class="btn-group btn-group-xs">
                      <button type="button"
                              class="btn btn-default phyto-calc-load-btn"
                              data-inputs="{$est.inputs_json|escape:'html':'UTF-8'}"
                              title="{l s='Load into calculator' mod='phyto_tc_cost_calculator'}">
                        <i class="icon-upload"></i>
                        {l s='Load' mod='phyto_tc_cost_calculator'}
                      </button>
                      <a href="{$form_action|escape:'html':'UTF-8'}&amp;action=delete_estimate&amp;id_estimate={$est.id_estimate|intval}"
                         class="btn btn-danger phyto-calc-delete-btn"
                         onclick="return confirm('{l s='Delete this estimate?' mod='phyto_tc_cost_calculator' js=1}');"
                         title="{l s='Delete estimate' mod='phyto_tc_cost_calculator'}">
                        <i class="icon-trash"></i>
                        {l s='Delete' mod='phyto_tc_cost_calculator'}
                      </a>
                    </div>
                  </td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>
      {/if}
    </div>
    {* /panel-body *}
  </div>
  {* /Saved Estimates panel *}

</div>
{* /phyto-calc-wrapper *}
