{extends file='checkout/_partials/order-confirmation-table.tpl'}

{block name='order_items_table_head'}
  <div id="order-items-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h3 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;color:var(--fsl-gray-800);margin:0;">
      {if $products_count == 1}
        {l s='%product_count% item in your cart' sprintf=['%product_count%' => $products_count] d='Shop.Theme.Checkout'}
      {else}
        {l s='%products_count% items in your cart' sprintf=['%products_count%' => $products_count] d='Shop.Theme.Checkout'}
      {/if}
    </h3>
    <a href="{url entity=cart params=['action' => 'show']}"
       style="font-size:12px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
      <i class="material-icons" style="font-size:14px;">edit</i> {l s='edit' d='Shop.Theme.Actions'}
    </a>
  </div>
{/block}
