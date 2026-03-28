{extends file='customer/_partials/address-form.tpl'}

{block name='form_field'}
  {if $field.name eq "alias" and $customer.is_guest}
    {* we don't ask for alias here if customer is not registered *}
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

{block name="address_form_url"}
  <form
    method="POST"
    action="{url entity='order' params=['id_address' => $id_address]}"
    data-id-address="{$id_address}"
    data-refresh-url="{url entity='order' params=['ajax' => 1, 'action' => 'addressForm']}"
  >
{/block}

{block name='form_fields' append}
  <input type="hidden" name="saveAddress" value="{$type}">
  {if $type === "delivery"}
    <div class="fsl-form-group" style="display:flex;align-items:center;gap:10px;margin-top:16px;">
      <input name="use_same_address" id="use_same_address" type="checkbox" value="1" {if $use_same_address}checked{/if}
             style="accent-color:var(--fsl-forest);width:16px;height:16px;">
      <label for="use_same_address" style="font-size:14px;color:var(--fsl-gray-700);cursor:pointer;">
        {l s='Use this address for invoice too' d='Shop.Theme.Checkout'}
      </label>
    </div>
  {/if}
{/block}

{block name='form_buttons'}
  <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:20px;">
    {if !$form_has_continue_button}
      <a class="js-cancel-address cancel-address"
         href="{url entity='order' params=['cancelAddress' => {$type}]}"
         style="padding:10px 24px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-pill);font-size:14px;color:var(--fsl-gray-600);text-decoration:none;">
        {l s='Cancel' d='Shop.Theme.Actions'}
      </a>
      <button type="submit"
              style="padding:10px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
        {l s='Save' d='Shop.Theme.Actions'}
      </button>
    {else}
      <form>
        {if $customer.addresses|count > 0}
          <a class="js-cancel-address cancel-address"
             href="{url entity='order' params=['cancelAddress' => {$type}]}"
             style="padding:10px 24px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-pill);font-size:14px;color:var(--fsl-gray-600);text-decoration:none;margin-right:8px;">
            {l s='Cancel' d='Shop.Theme.Actions'}
          </a>
        {/if}
        <button type="submit" class="continue" name="confirm-addresses" value="1"
                style="padding:10px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
          {l s='Continue' d='Shop.Theme.Actions'}
        </button>
      </form>
    {/if}
  </div>
{/block}
