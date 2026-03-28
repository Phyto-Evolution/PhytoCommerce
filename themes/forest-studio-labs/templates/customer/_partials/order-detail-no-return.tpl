{block name='order_products_table'}
  <div style="overflow-x:auto;margin-bottom:24px;">
    <table id="order-products"
           style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
      <thead>
        <tr style="background:var(--fsl-gray-50);">
          <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Product' d='Shop.Theme.Catalog'}</th>
          <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Quantity' d='Shop.Theme.Catalog'}</th>
          <th style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Unit price' d='Shop.Theme.Catalog'}</th>
          <th style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Total price' d='Shop.Theme.Catalog'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach from=$order.products item=product}
          <tr style="border-bottom:1px solid var(--fsl-gray-100);">
            <td style="padding:12px 16px;">
              <a href="{$urls.pages.product}&id_product={$product.id_product}"
                 style="font-weight:500;color:var(--fsl-gray-800);text-decoration:none;">{$product.name}</a>
              {if $product.product_reference}
                <div style="font-size:12px;color:var(--fsl-gray-400);">{l s='Reference' d='Shop.Theme.Catalog'}: {$product.product_reference}</div>
              {/if}
              {if isset($product.download_link)}
                <a href="{$product.download_link}" style="font-size:12px;color:var(--fsl-forest);">{l s='Download' d='Shop.Theme.Actions'}</a>
              {/if}
              {if $product.is_virtual}
                <div style="font-size:12px;color:var(--fsl-gray-400);">{l s="Virtual products can't be returned." d='Shop.Theme.Customeraccount'}</div>
              {/if}
              {if $product.customizations}
                {foreach from=$product.customizations item="customization"}
                  <div style="margin-top:4px;">
                    <a href="#" data-toggle="modal" data-target="#product-customizations-modal-{$customization.id_customization}"
                       style="font-size:12px;color:var(--fsl-forest);">{l s='Product customization' d='Shop.Theme.Catalog'}</a>
                  </div>
                {/foreach}
              {/if}
            </td>
            <td style="padding:12px 16px;text-align:center;color:var(--fsl-gray-700);">
              {if $product.customizations}
                {foreach $product.customizations as $customization}{$customization.quantity}{/foreach}
              {else}
                {$product.quantity}
              {/if}
            </td>
            <td style="padding:12px 16px;text-align:right;color:var(--fsl-gray-700);">{$product.price}</td>
            <td style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-800);">{$product.total}</td>
          </tr>
        {/foreach}
      </tbody>
      <tfoot>
        {foreach $order.subtotals as $line}
          {if $line.value}
            <tr style="background:var(--fsl-gray-50);">
              <td colspan="3" style="padding:10px 16px;text-align:right;font-size:13px;color:var(--fsl-gray-600);">{$line.label}</td>
              <td style="padding:10px 16px;text-align:right;font-size:13px;color:var(--fsl-gray-700);">{$line.value}</td>
            </tr>
          {/if}
        {/foreach}
        <tr style="background:var(--fsl-forest);">
          <td colspan="3" style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-white);">{$order.totals.total.label}</td>
          <td style="padding:12px 16px;text-align:right;font-weight:700;color:var(--fsl-white);">{$order.totals.total.value}</td>
        </tr>
      </tfoot>
    </table>
  </div>
{/block}
