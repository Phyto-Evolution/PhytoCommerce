{block name='order_products_table'}
  <form id="order-return-form" class="js-order-return-form" action="{$urls.pages.order_follow}" method="post">

    <div style="overflow-x:auto;margin-bottom:24px;">
      <table id="order-products"
             style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
        <thead>
          <tr style="background:var(--fsl-gray-50);">
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);width:32px;">
              <input type="checkbox">
            </th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Product' d='Shop.Theme.Catalog'}</th>
            <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Quantity' d='Shop.Theme.Catalog'}</th>
            <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Returned' d='Shop.Theme.Customeraccount'}</th>
            <th style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Unit price' d='Shop.Theme.Catalog'}</th>
            <th style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Total price' d='Shop.Theme.Catalog'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$order.products item=product name=products}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:12px 16px;">
                {if !$product.is_virtual}
                  <span id="_desktop_product_line_{$product.id_order_detail}">
                    <input type="checkbox" id="cb_{$product.id_order_detail}"
                           name="ids_order_detail[{$product.id_order_detail}]"
                           value="{$product.id_order_detail}"
                           style="accent-color:var(--fsl-forest);">
                  </span>
                {/if}
              </td>
              <td style="padding:12px 16px;">
                <strong style="color:var(--fsl-gray-800);">{$product.name}</strong>
                {if $product.product_reference}
                  <div style="font-size:12px;color:var(--fsl-gray-400);">{l s='Reference' d='Shop.Theme.Catalog'}: {$product.product_reference}</div>
                {/if}
                {if $product.is_virtual}
                  <div style="font-size:12px;color:var(--fsl-gray-400);">{l s="Virtual products can't be returned." d='Shop.Theme.Customeraccount'}</div>
                {/if}
                {if isset($product.download_link)}
                  <a href="{$product.download_link}" style="font-size:12px;color:var(--fsl-forest);">{l s='Download' d='Shop.Theme.Actions'}</a>
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
                <div>{$product.quantity}</div>
                {if $product.quantity > $product.qty_returned && !$product.is_virtual}
                  <div id="_desktop_return_qty_{$product.id_order_detail}" style="margin-top:6px;">
                    <select name="order_qte_input[{$product.id_order_detail}]"
                            style="padding:4px 8px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-size:12px;">
                      {section name=quantity start=1 loop=$product.quantity+1-$product.qty_returned}
                        <option value="{$smarty.section.quantity.index}">{$smarty.section.quantity.index}</option>
                      {/section}
                    </select>
                    {if $product.customizations}
                      <input type="hidden" value="1" name="customization_qty_input[{$customization.id_customization}]">
                    {/if}
                  </div>
                {/if}
              </td>
              <td style="padding:12px 16px;text-align:center;color:var(--fsl-gray-600);">{if !$product.is_virtual}{$product.qty_returned}{/if}</td>
              <td style="padding:12px 16px;text-align:right;color:var(--fsl-gray-700);">{$product.price}</td>
              <td style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-800);">{$product.total}</td>
            </tr>
          {/foreach}
        </tbody>
        <tfoot>
          {foreach $order.subtotals as $line}
            {if $line.value}
              <tr style="background:var(--fsl-gray-50);">
                <td colspan="5" style="padding:10px 16px;text-align:right;font-size:13px;color:var(--fsl-gray-600);">{$line.label}</td>
                <td style="padding:10px 16px;text-align:right;font-size:13px;color:var(--fsl-gray-700);">{$line.value}</td>
              </tr>
            {/if}
          {/foreach}
          <tr style="background:var(--fsl-forest);">
            <td colspan="5" style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-white);">{$order.totals.total.label}</td>
            <td style="padding:12px 16px;text-align:right;font-weight:700;color:var(--fsl-white);">{$order.totals.total.value}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;margin-top:20px;">
      <h3 style="font-family:var(--fsl-font-display);font-size:20px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 8px;">{l s='Merchandise return' d='Shop.Theme.Customeraccount'}</h3>
      <p style="font-size:14px;color:var(--fsl-gray-500);margin-bottom:16px;">{l s='If you wish to return one or more products, please mark the corresponding boxes and provide an explanation for the return. When complete, click the button below.' d='Shop.Theme.Customeraccount'}</p>
      <textarea cols="67" rows="3" name="returnText"
                style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;resize:vertical;"></textarea>
      <div style="text-align:right;margin-top:16px;">
        <input type="hidden" name="id_order" value="{$order.details.id}">
        <button type="submit" name="submitReturnMerchandise"
                style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
          {l s='Request a return' d='Shop.Theme.Customeraccount'}
        </button>
      </div>
    </div>

  </form>
{/block}
