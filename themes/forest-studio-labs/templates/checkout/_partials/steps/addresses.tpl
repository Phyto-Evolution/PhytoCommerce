{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
  <div class="js-address-form">
    <form
      method="POST"
      data-id-address="{$id_address}"
      action="{url entity='order' params=['id_address' => $id_address]}"
      data-refresh-url="{url entity='order' params=['ajax' => 1, 'action' => 'addressForm']}"
    >

      {if $use_same_address}
        <p style="font-size:14px;color:var(--fsl-gray-600);margin-bottom:20px;padding:12px 16px;background:var(--fsl-light-green);border-radius:var(--fsl-radius);">
          {if $cart.is_virtual}
            {l s='The selected address will be used as your personal address (for invoice).' d='Shop.Theme.Checkout'}
          {else}
            {l s='The selected address will be used both as your personal address (for invoice) and as your delivery address.' d='Shop.Theme.Checkout'}
          {/if}
        </p>
      {else}
        <h2 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;color:var(--fsl-gray-800);margin-bottom:16px;">{l s='Shipping Address' d='Shop.Theme.Checkout'}</h2>
      {/if}

      {if $show_delivery_address_form}
        <div id="delivery-address">
          {render file='checkout/_partials/address-form.tpl'
            ui=$address_form
            use_same_address=$use_same_address
            type="delivery"
            form_has_continue_button=$form_has_continue_button
          }
        </div>
      {elseif $customer.addresses|count > 0}
        <div id="delivery-addresses" class="address-selector js-address-selector">
          {include file='checkout/_partials/address-selector-block.tpl'
            addresses=$customer.addresses
            name="id_address_delivery"
            selected=$id_address_delivery
            type="delivery"
            interactive=(!$show_delivery_address_form and !$show_invoice_address_form)
          }
        </div>

        {if isset($delivery_address_error)}
          <p class="alert alert-danger js-address-error" name="alert-delivery" id="id-failure-address-{$delivery_address_error.id_address}"
             style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;color:#dc2626;margin-top:8px;">
            {$delivery_address_error.exception}
          </p>
        {else}
          <p class="alert alert-danger js-address-error" name="alert-delivery" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;color:#dc2626;margin-top:8px;">
            {l s="Your address is incomplete, please update it." d="Shop.Notifications.Error"}
          </p>
        {/if}

        <p class="add-address" style="margin-top:12px;">
          <a href="{$new_address_delivery_url}"
             style="font-size:13px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <i class="material-icons" style="font-size:18px;">add_circle_outline</i>
            {l s='add new address' d='Shop.Theme.Actions'}
          </a>
        </p>

        {if $use_same_address && !$cart.is_virtual}
          <p style="margin-top:12px;">
            <a data-link-action="different-invoice-address" href="{$use_different_address_url}"
               style="font-size:13px;color:var(--fsl-forest);">
              {l s='Billing address differs from shipping address' d='Shop.Theme.Checkout'}
            </a>
          </p>
        {/if}
      {/if}

      {if !$use_same_address}
        <h2 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;color:var(--fsl-gray-800);margin:24px 0 16px;">{l s='Your Invoice Address' d='Shop.Theme.Checkout'}</h2>

        {if $show_invoice_address_form}
          <div id="invoice-address">
            {render file='checkout/_partials/address-form.tpl'
              ui=$address_form
              use_same_address=$use_same_address
              type="invoice"
              form_has_continue_button=$form_has_continue_button
            }
          </div>
        {else}
          <div id="invoice-addresses" class="address-selector js-address-selector">
            {include file='checkout/_partials/address-selector-block.tpl'
              addresses=$customer.addresses
              name="id_address_invoice"
              selected=$id_address_invoice
              type="invoice"
              interactive=(!$show_delivery_address_form and !$show_invoice_address_form)
            }
          </div>

          {if isset($invoice_address_error)}
            <p class="alert alert-danger js-address-error" name="alert-invoice" id="id-failure-address-{$invoice_address_error.id_address}"
               style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;color:#dc2626;margin-top:8px;">
              {$invoice_address_error.exception}
            </p>
          {else}
            <p class="alert alert-danger js-address-error" name="alert-invoice" style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;color:#dc2626;margin-top:8px;">
              {l s="Your address is incomplete, please update it." d="Shop.Notifications.Error"}
            </p>
          {/if}

          <p class="add-address" style="margin-top:12px;">
            <a href="{$new_address_invoice_url}"
               style="font-size:13px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
              <i class="material-icons" style="font-size:18px;">add_circle_outline</i>
              {l s='add new address' d='Shop.Theme.Actions'}
            </a>
          </p>
        {/if}
      {/if}

      {if !$form_has_continue_button}
        <div style="text-align:right;margin-top:20px;">
          <input type="hidden" id="not-valid-addresses" class="js-not-valid-addresses" value="{$not_valid_addresses}">
          <button type="submit" class="continue" name="confirm-addresses" value="1"
                  style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
            {l s='Continue' d='Shop.Theme.Actions'}
          </button>
        </div>
      {/if}

    </form>
    {hook h='displayAddressSelectorBottom'}
  </div>
{/block}
