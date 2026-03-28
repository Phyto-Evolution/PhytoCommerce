<section class="product-discounts js-product-discounts">
  {if $product.quantity_discounts}
    <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);margin-bottom:12px;">{l s='Volume discounts' d='Shop.Theme.Catalog'}</p>
    {block name='product_discount_table'}
      <div style="overflow-x:auto;">
        <table class="table-product-discounts"
               style="width:100%;border-collapse:collapse;font-size:13px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);overflow:hidden;">
          <thead>
            <tr style="background:var(--fsl-gray-50);">
              <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Quantity' d='Shop.Theme.Catalog'}</th>
              <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{$configuration.quantity_discount.label}</th>
              <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='You Save' d='Shop.Theme.Catalog'}</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$product.quantity_discounts item='quantity_discount' name='quantity_discounts'}
              <tr data-discount-type="{$quantity_discount.reduction_type}"
                  data-discount="{$quantity_discount.real_value}"
                  data-discount-quantity="{$quantity_discount.quantity}"
                  style="{if !$smarty.foreach.quantity_discounts.last}border-bottom:1px solid var(--fsl-gray-100);{/if}">
                <td style="padding:10px 14px;color:var(--fsl-gray-700);">{$quantity_discount.quantity}</td>
                <td style="padding:10px 14px;color:var(--fsl-forest);font-weight:500;">{$quantity_discount.discount}</td>
                <td style="padding:10px 14px;color:var(--fsl-gray-600);">{$quantity_discount.save}</td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>
    {/block}
  {/if}
</section>
