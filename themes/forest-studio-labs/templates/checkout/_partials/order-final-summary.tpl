<section id="order-summary-content" style="background:var(--fsl-gray-50);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;margin-bottom:24px;">

  <h4 style="font-family:var(--fsl-font-display);font-size:20px;font-weight:500;color:var(--fsl-gray-800);margin:0 0 20px;">
    {l s='Please check your order before payment' d='Shop.Theme.Checkout'}
  </h4>

  <div class="row" style="margin-bottom:20px;">
    <h4 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-500);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
      {l s='Addresses' d='Shop.Theme.Checkout'}
      <a href="#" class="step-edit step-to-addresses js-edit-addresses"
         style="font-size:12px;font-weight:400;color:var(--fsl-forest);text-decoration:none;text-transform:none;letter-spacing:0;display:inline-flex;align-items:center;gap:4px;">
        <i class="material-icons" style="font-size:14px;">edit</i> {l s='edit' d='Shop.Theme.Actions'}
      </a>
    </h4>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
      <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:16px;">
        <h4 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0 0 8px;">{l s='Your Delivery Address' d='Shop.Theme.Checkout'}</h4>
        <div style="font-size:13px;color:var(--fsl-gray-700);line-height:1.6;">{$customer.addresses[$cart.id_address_delivery]['formatted'] nofilter}</div>
      </div>
      <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:16px;">
        <h4 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0 0 8px;">{l s='Your Invoice Address' d='Shop.Theme.Checkout'}</h4>
        <div style="font-size:13px;color:var(--fsl-gray-700);line-height:1.6;">{$customer.addresses[$cart.id_address_invoice]['formatted'] nofilter}</div>
      </div>
    </div>
  </div>

  {if !$cart.is_virtual}
    <div style="margin-bottom:20px;">
      <h4 style="font-size:14px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-500);margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        {l s='Shipping Method' d='Shop.Theme.Checkout'}
        <a href="#" class="step-edit step-to-delivery js-edit-delivery"
           style="font-size:12px;font-weight:400;color:var(--fsl-forest);text-decoration:none;text-transform:none;letter-spacing:0;display:inline-flex;align-items:center;gap:4px;">
          <i class="material-icons" style="font-size:14px;">edit</i> {l s='edit' d='Shop.Theme.Actions'}
        </a>
      </h4>
      <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);padding:16px;display:flex;align-items:center;gap:16px;">
        {if $selected_delivery_option.logo}
          <img src="{$selected_delivery_option.logo}" alt="{$selected_delivery_option.name}" loading="lazy" style="max-height:32px;width:auto;">
        {/if}
        <div style="flex:1;">
          <span style="font-size:14px;font-weight:500;color:var(--fsl-gray-800);">{$selected_delivery_option.name}</span>
          <span style="font-size:13px;color:var(--fsl-gray-500);margin-left:12px;">{$selected_delivery_option.delay}</span>
        </div>
        <span style="font-size:14px;font-weight:600;color:var(--fsl-forest);">{$selected_delivery_option.price}</span>
      </div>
      {if $is_recyclable_packaging}
        <p style="font-size:12px;color:var(--fsl-gray-500);font-style:italic;margin-top:8px;">
          {l s='You have given permission to receive your order in recycled packaging.' d="Shop.Theme.Customeraccount"}
        </p>
      {/if}
    </div>
  {/if}

  {block name='order_confirmation_table'}
    {include file='checkout/_partials/order-final-summary-table.tpl'
       products=$cart.products
       products_count=$cart.products_count
       subtotals=$cart.subtotals
       totals=$cart.totals
       labels=$cart.labels
       add_product_link=true
    }
  {/block}

</section>
