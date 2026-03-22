{**
 * Admin product tab — Seasonal Availability settings
 * Rendered by hookDisplayAdminProductsExtra
 *}

<div id="phyto-seasonal-tab" class="panel product-tab">
  <input type="hidden" name="submitted_tabs[]" value="PhytoSeasonal" />

  <h3 class="tab">
    <i class="icon-leaf"></i>
    {l s='Seasonal Availability' mod='phyto_seasonal_availability'}
  </h3>

  <div class="form-wrapper">

    {* ── Shipping Months ─────────────────────────────────────────── *}
    <div class="form-group">
      <label class="control-label col-lg-3">
        {l s='Shipping Months' mod='phyto_seasonal_availability'}
        <span class="help-box" data-toggle="popover" data-content="{l s='Months in which this product can be shipped.' mod='phyto_seasonal_availability'}"></span>
      </label>
      <div class="col-lg-9">
        <div class="row" id="phyto-ship-months-grid">
          {foreach $phyto_seasonal_months as $num => $label}
            {assign var='ship_checked' value=in_array($num, $phyto_seasonal_ship_months)}
            <div class="col-xs-4 col-sm-3 col-md-2" style="margin-bottom:6px;">
              <div class="phyto-month-chip{if $ship_checked} phyto-month-chip--ship{/if}"
                   id="phyto-ship-chip-{$num}">
                <label style="margin:0;cursor:pointer;width:100%;">
                  <input type="checkbox"
                         name="phyto_ship_months[]"
                         value="{$num}"
                         class="phyto-ship-month-cb"
                         {if $ship_checked}checked="checked"{/if}
                         style="display:none;" />
                  <span class="phyto-month-label">{$label|escape:'html':'UTF-8'}</span>
                </label>
              </div>
            </div>
          {/foreach}
        </div>
        <p class="help-block">{l s='Check the months during which orders will be shipped.' mod='phyto_seasonal_availability'}</p>
      </div>
    </div>

    {* ── Dormancy Months ─────────────────────────────────────────── *}
    <div class="form-group">
      <label class="control-label col-lg-3">
        {l s='Dormancy Months' mod='phyto_seasonal_availability'}
        <span class="help-box" data-toggle="popover" data-content="{l s='Months when the plant is dormant (informational).' mod='phyto_seasonal_availability'}"></span>
      </label>
      <div class="col-lg-9">
        <div class="row" id="phyto-dorm-months-grid">
          {foreach $phyto_seasonal_months as $num => $label}
            {assign var='dorm_checked' value=in_array($num, $phyto_seasonal_dorm_months)}
            <div class="col-xs-4 col-sm-3 col-md-2" style="margin-bottom:6px;">
              <div class="phyto-month-chip phyto-month-chip--dorm-style{if $dorm_checked} phyto-month-chip--dorm{/if}"
                   id="phyto-dorm-chip-{$num}">
                <label style="margin:0;cursor:pointer;width:100%;">
                  <input type="checkbox"
                         name="phyto_dorm_months[]"
                         value="{$num}"
                         class="phyto-dorm-month-cb"
                         {if $dorm_checked}checked="checked"{/if}
                         style="display:none;" />
                  <span class="phyto-month-label">{$label|escape:'html':'UTF-8'}</span>
                </label>
              </div>
            </div>
          {/foreach}
        </div>
        <p class="help-block">{l s='Dormancy months are shown to customers as informational only.' mod='phyto_seasonal_availability'}</p>
      </div>
    </div>

    <hr />

    {* ── Block purchase ──────────────────────────────────────────── *}
    <div class="form-group">
      <label class="control-label col-lg-3">
        {l s='Block purchase when out of season' mod='phyto_seasonal_availability'}
      </label>
      <div class="col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio"
                 name="phyto_block_purchase"
                 id="phyto_block_purchase_on"
                 value="1"
                 {if $phyto_seasonal_block == 1}checked="checked"{/if} />
          <label for="phyto_block_purchase_on">{l s='Yes' mod='phyto_seasonal_availability'}</label>
          <input type="radio"
                 name="phyto_block_purchase"
                 id="phyto_block_purchase_off"
                 value="0"
                 {if $phyto_seasonal_block != 1}checked="checked"{/if} />
          <label for="phyto_block_purchase_off">{l s='No' mod='phyto_seasonal_availability'}</label>
          <a class="slide-button btn"></a>
        </span>
      </div>
    </div>

    {* ── Out-of-season message ───────────────────────────────────── *}
    <div class="form-group" id="phyto-out-msg-group"
         {if $phyto_seasonal_block != 1}style="display:none;"{/if}>
      <label class="control-label col-lg-3">
        {l s='Out of season message' mod='phyto_seasonal_availability'}
      </label>
      <div class="col-lg-9">
        <textarea name="phyto_out_of_season_msg"
                  id="phyto_out_of_season_msg"
                  class="form-control"
                  rows="3"
                  maxlength="1000">{$phyto_seasonal_msg|escape:'html':'UTF-8'}</textarea>
        <p class="help-block">{l s='Displayed to customers when purchase is blocked. Leave blank for a default message.' mod='phyto_seasonal_availability'}</p>
      </div>
    </div>

    {* ── Enable notify-me form ───────────────────────────────────── *}
    <div class="form-group">
      <label class="control-label col-lg-3">
        {l s='Enable notify-me form' mod='phyto_seasonal_availability'}
      </label>
      <div class="col-lg-9">
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio"
                 name="phyto_enable_notify"
                 id="phyto_enable_notify_on"
                 value="1"
                 {if $phyto_seasonal_notify == 1}checked="checked"{/if} />
          <label for="phyto_enable_notify_on">{l s='Yes' mod='phyto_seasonal_availability'}</label>
          <input type="radio"
                 name="phyto_enable_notify"
                 id="phyto_enable_notify_off"
                 value="0"
                 {if $phyto_seasonal_notify != 1}checked="checked"{/if} />
          <label for="phyto_enable_notify_off">{l s='No' mod='phyto_seasonal_availability'}</label>
          <a class="slide-button btn"></a>
        </span>
        <p class="help-block">{l s='Show an email capture form on the product page when purchase is blocked.' mod='phyto_seasonal_availability'}</p>
      </div>
    </div>

    <hr />

    {* ── Save button ─────────────────────────────────────────────── *}
    <div class="form-group">
      <div class="col-lg-9 col-lg-offset-3">
        <button type="button"
                id="phyto-seasonal-save-btn"
                class="btn btn-success">
          <i class="process-icon-save"></i>
          {l s='Save seasonal settings' mod='phyto_seasonal_availability'}
        </button>
        <span id="phyto-seasonal-save-feedback" style="margin-left:12px;display:none;"></span>
      </div>
    </div>

  </div>{* /.form-wrapper *}
</div>{* /#phyto-seasonal-tab *}

<style>
  .phyto-month-chip {
    border: 2px solid #ccc;
    border-radius: 4px;
    padding: 6px 4px;
    text-align: center;
    background: #f5f5f5;
    color: #555;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    user-select: none;
  }
  .phyto-month-chip--ship {
    background: #dff0d8;
    border-color: #4cae4c;
    color: #2d6a2d;
    font-weight: 600;
  }
  .phyto-month-chip--dorm-style {
    background: #f5f5f5;
    border-color: #ccc;
    color: #555;
  }
  .phyto-month-chip--dorm {
    background: #fcf8e3;
    border-color: #d4a017;
    color: #7a5800;
    font-weight: 600;
  }
  .phyto-month-label {
    display: block;
    font-size: 12px;
    pointer-events: none;
  }
</style>

<script>
(function () {
  'use strict';

  var ajaxUrl    = {$phyto_seasonal_ajax_url|json_encode};
  var adminToken = {$phyto_seasonal_admin_token|json_encode};
  var idProduct  = {$phyto_seasonal_id_product|intval};

  /* ── Month chip toggling ─────────────────────────────────────── */
  function bindChips(cbClass, chipPrefix, activeClass) {
    document.querySelectorAll('.' + cbClass).forEach(function (cb) {
      var chip = document.getElementById(chipPrefix + cb.value);
      if (!chip) { return; }

      // Sync initial state
      if (cb.checked) { chip.classList.add(activeClass); }

      chip.addEventListener('click', function () {
        cb.checked = !cb.checked;
        chip.classList.toggle(activeClass, cb.checked);
      });
    });
  }

  bindChips('phyto-ship-month-cb', 'phyto-ship-chip-', 'phyto-month-chip--ship');
  bindChips('phyto-dorm-month-cb', 'phyto-dorm-chip-', 'phyto-month-chip--dorm');

  /* ── Block-purchase toggle shows/hides message field ─────────── */
  function syncBlockToggle() {
    var checked = document.getElementById('phyto_block_purchase_on');
    var group   = document.getElementById('phyto-out-msg-group');
    if (checked && group) {
      group.style.display = checked.checked ? '' : 'none';
    }
  }

  ['phyto_block_purchase_on', 'phyto_block_purchase_off'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) { el.addEventListener('change', syncBlockToggle); }
  });

  /* ── AJAX Save ───────────────────────────────────────────────── */
  document.getElementById('phyto-seasonal-save-btn').addEventListener('click', function () {
    var btn      = this;
    var feedback = document.getElementById('phyto-seasonal-save-feedback');

    // Collect checked ship months
    var shipMonths = [];
    document.querySelectorAll('.phyto-ship-month-cb:checked').forEach(function (cb) {
      shipMonths.push(cb.value);
    });

    // Collect checked dorm months
    var dormMonths = [];
    document.querySelectorAll('.phyto-dorm-month-cb:checked').forEach(function (cb) {
      dormMonths.push(cb.value);
    });

    var blockEl  = document.getElementById('phyto_block_purchase_on');
    var notifyEl = document.getElementById('phyto_enable_notify_on');
    var msgEl    = document.getElementById('phyto_out_of_season_msg');

    var body = new URLSearchParams();
    body.append('action',          'save_seasonal');
    body.append('ajax',            '1');
    body.append('token',           adminToken);
    body.append('id_product',      idProduct);
    body.append('block_purchase',  blockEl  && blockEl.checked  ? '1' : '0');
    body.append('enable_notify',   notifyEl && notifyEl.checked ? '1' : '0');
    body.append('out_of_season_msg', msgEl ? msgEl.value : '');

    shipMonths.forEach(function (m) { body.append('ship_months[]', m); });
    dormMonths.forEach(function (m) { body.append('dorm_months[]', m); });

    btn.disabled = true;
    feedback.style.display  = 'none';
    feedback.className       = '';

    fetch(ajaxUrl, {
      method:      'POST',
      credentials: 'same-origin',
      headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
      body:        body.toString(),
    })
      .then(function (res) {
        if (!res.ok) { throw new Error('HTTP ' + res.status); }
        return res.json();
      })
      .then(function (data) {
        feedback.style.display = 'inline';
        if (data && data.success) {
          feedback.className   = 'text-success';
          feedback.textContent = data.message || '{l s='Saved.' mod='phyto_seasonal_availability' js=1}';
        } else {
          feedback.className   = 'text-danger';
          feedback.textContent = (data && data.message)
            ? data.message
            : '{l s='An error occurred.' mod='phyto_seasonal_availability' js=1}';
        }
      })
      .catch(function (err) {
        feedback.style.display = 'inline';
        feedback.className     = 'text-danger';
        feedback.textContent   = '{l s='Request failed.' mod='phyto_seasonal_availability' js=1}' + ' (' + err.message + ')';
      })
      .finally(function () {
        btn.disabled = false;
      });
  });

}());
</script>
