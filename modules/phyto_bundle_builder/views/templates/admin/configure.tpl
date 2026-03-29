{**
 * Admin configure template — embedded in the bundle edit form.
 * Renders the slot manager panel below the main bundle form fields.
 *
 * Variables assigned from AdminPhytoBundleBuilderController::renderForm():
 *   $phyto_id_bundle          — current bundle ID (0 if new)
 *   $phyto_slots              — array of existing slot rows
 *   $phyto_categories         — category options [{id_category, name}]
 *   $phyto_max_slots          — max allowed slots
 *   $phyto_add_slot_url       — URL to POST new slot
 *   $phyto_delete_slot_url    — URL to delete a slot
 *   $phyto_reorder_slot_url   — URL to reorder slots (AJAX)
 *   $phyto_admin_token        — admin token
 *   $phyto_current_index      — controller URL
 **}

{if $phyto_id_bundle}
<div class="panel phyto-bb-admin-slots" id="phyto-slots-panel">
  <div class="panel-heading">
    <i class="icon icon-list"></i>
    {l s='Bundle Slots' mod='phyto_bundle_builder'}
    <span class="badge">{$phyto_slots|@count} / {$phyto_max_slots}</span>
  </div>

  <div class="panel-body">

    {* ---- Existing slots ---- *}
    {if $phyto_slots|@count > 0}
    <table class="table phyto-bb-slot-table" id="phyto-slot-sortable">
      <thead>
        <tr>
          <th style="width:30px;">&nbsp;</th>
          <th>{l s='Slot Name' mod='phyto_bundle_builder'}</th>
          <th>{l s='Type' mod='phyto_bundle_builder'}</th>
          <th>{l s='Category' mod='phyto_bundle_builder'}</th>
          <th>{l s='Required' mod='phyto_bundle_builder'}</th>
          <th>{l s='Actions' mod='phyto_bundle_builder'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$phyto_slots item=slot}
        <tr class="phyto-bb-slot-row" data-id="{$slot.id_slot|intval}">
          <td class="phyto-bb-drag-handle" title="{l s='Drag to reorder' mod='phyto_bundle_builder'}">
            <i class="icon icon-reorder"></i>
          </td>
          <td>{$slot.slot_name|escape:'html':'UTF-8'}</td>
          <td>
            <span class="badge">{$slot.slot_type|escape:'html':'UTF-8'|default:'-'}</span>
          </td>
          <td>
            {if $slot.id_category > 0}
              {foreach from=$phyto_categories item=cat}
                {if $cat.id_category == $slot.id_category}
                  {$cat.name|escape:'html':'UTF-8'}
                {/if}
              {/foreach}
            {else}
              <em>{l s='Any' mod='phyto_bundle_builder'}</em>
            {/if}
          </td>
          <td>
            {if $slot.required}
              <span class="label label-success">{l s='Yes' mod='phyto_bundle_builder'}</span>
            {else}
              <span class="label label-default">{l s='No' mod='phyto_bundle_builder'}</span>
            {/if}
          </td>
          <td>
            <a href="{$phyto_delete_slot_url|escape:'html':'UTF-8'}&id_slot={$slot.id_slot|intval}"
               class="btn btn-danger btn-xs phyto-bb-delete-slot"
               onclick="return confirm('{l s='Delete this slot?' mod='phyto_bundle_builder' js=true}');">
              <i class="icon icon-trash"></i>
              {l s='Delete' mod='phyto_bundle_builder'}
            </a>
          </td>
        </tr>
        {/foreach}
      </tbody>
    </table>
    {else}
      <p class="alert alert-info">{l s='No slots defined yet. Add your first slot below.' mod='phyto_bundle_builder'}</p>
    {/if}

    {* ---- Add slot form ---- *}
    {if $phyto_slots|@count < $phyto_max_slots}
    <div class="phyto-bb-add-slot-form well">
      <h4>{l s='Add a New Slot' mod='phyto_bundle_builder'}</h4>
      <form method="post" action="{$phyto_add_slot_url|escape:'html':'UTF-8'}">
        <input type="hidden" name="addSlot" value="1">
        <input type="hidden" name="id_bundle" value="{$phyto_id_bundle|intval}">

        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label class="control-label">{l s='Slot Name' mod='phyto_bundle_builder'} *</label>
              <input type="text" name="slot_name" class="form-control"
                     placeholder="{l s='e.g. Choose your plant' mod='phyto_bundle_builder'}"
                     required maxlength="100">
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label class="control-label">{l s='Slot Type' mod='phyto_bundle_builder'}</label>
              <input type="text" name="slot_type" class="form-control"
                     placeholder="{l s='e.g. plant, pot' mod='phyto_bundle_builder'}"
                     maxlength="50">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label class="control-label">{l s='Restrict to Category' mod='phyto_bundle_builder'}</label>
              <select name="id_category" class="form-control">
                {foreach from=$phyto_categories item=cat}
                  <option value="{$cat.id_category|intval}">{$cat.name|escape:'html':'UTF-8'}</option>
                {/foreach}
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label class="control-label">{l s='Required' mod='phyto_bundle_builder'}</label>
              <select name="required" class="form-control">
                <option value="1">{l s='Yes' mod='phyto_bundle_builder'}</option>
                <option value="0">{l s='No' mod='phyto_bundle_builder'}</option>
              </select>
            </div>
          </div>
          <div class="col-md-2">
            <div class="form-group">
              <label class="control-label">&nbsp;</label><br>
              <button type="submit" class="btn btn-success">
                <i class="icon icon-plus"></i>
                {l s='Add Slot' mod='phyto_bundle_builder'}
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    {else}
      <p class="alert alert-warning">
        {l s='Maximum number of slots (%s) reached.' sprintf=[$phyto_max_slots] mod='phyto_bundle_builder'}
      </p>
    {/if}

  </div>{* /panel-body *}
</div>{* /panel *}

{* ---- Drag-and-drop reorder JS ---- *}
<script>
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var tbody = document.getElementById('phyto-slot-sortable');
    if (!tbody) { return; }

    var rows = [].slice.call(tbody.querySelectorAll('tbody tr.phyto-bb-slot-row'));
    var dragging = null;

    rows.forEach(function (row) {
      var handle = row.querySelector('.phyto-bb-drag-handle');
      if (!handle) { return; }

      handle.setAttribute('draggable', 'true');

      handle.addEventListener('dragstart', function (e) {
        dragging = row;
        row.classList.add('phyto-bb-dragging');
        e.dataTransfer.effectAllowed = 'move';
      });

      handle.addEventListener('dragend', function () {
        row.classList.remove('phyto-bb-dragging');
        dragging = null;
        saveOrder();
      });

      row.addEventListener('dragover', function (e) {
        e.preventDefault();
        if (dragging && dragging !== row) {
          var tbodyEl = row.parentNode;
          var allRows = [].slice.call(tbodyEl.querySelectorAll('tr.phyto-bb-slot-row'));
          var draggingIdx = allRows.indexOf(dragging);
          var targetIdx = allRows.indexOf(row);

          if (draggingIdx < targetIdx) {
            tbodyEl.insertBefore(dragging, row.nextSibling);
          } else {
            tbodyEl.insertBefore(dragging, row);
          }
        }
      });
    });

    function saveOrder() {
      var tbodyEl = document.querySelector('#phyto-slot-sortable tbody');
      if (!tbodyEl) { return; }
      var order = [].slice.call(tbodyEl.querySelectorAll('tr.phyto-bb-slot-row')).map(function (r) {
        return r.getAttribute('data-id');
      });

      var url = '{$phyto_reorder_slot_url|escape:'javascript':'UTF-8'}&slot_order=' + order.join(',');
      fetch(url, { method: 'GET', credentials: 'same-origin' });
    }
  });
}());
</script>

{else}
  <div class="alert alert-info">
    {l s='Save the bundle first to manage its slots.' mod='phyto_bundle_builder'}
  </div>
{/if}
