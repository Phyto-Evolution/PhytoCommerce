{extends file='customer/page.tpl'}

{block name='page_content'}
  <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin-bottom:8px;">
    {l s='Order history' d='Shop.Theme.Customeraccount'}
  </h1>
  <p style="font-size:14px;color:var(--fsl-gray-500);margin-bottom:24px;">{l s="Here are the orders you've placed since your account was created." d='Shop.Theme.Customeraccount'}</p>

  {if $orders}
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
        <thead>
          <tr style="background:var(--fsl-gray-50);">
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Order reference' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Date' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Total price' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Payment' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Status' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Invoice' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;border-bottom:1px solid var(--fsl-gray-200);"></th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$orders item=order}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:12px 16px;font-weight:500;color:var(--fsl-gray-800);">{$order.details.reference}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-600);">{$order.details.order_date}</td>
              <td style="padding:12px 16px;text-align:right;font-weight:600;color:var(--fsl-gray-800);">{$order.totals.total.value}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-600);">{$order.details.payment}</td>
              <td style="padding:12px 16px;">
                <span style="display:inline-block;padding:3px 10px;border-radius:var(--fsl-radius-pill);font-size:11px;font-weight:600;background-color:{$order.history.current.color};color:{if $order.history.current.contrast == 'bright'}#fff{else}#333{/if};">
                  {$order.history.current.ostate_name}
                </span>
              </td>
              <td style="padding:12px 16px;text-align:center;">
                {if $order.details.invoice_url}
                  <a href="{$order.details.invoice_url}" style="color:var(--fsl-forest);">
                    <i class="material-icons" style="font-size:20px;">picture_as_pdf</i>
                  </a>
                {else}
                  <span style="color:var(--fsl-gray-300);">-</span>
                {/if}
              </td>
              <td style="padding:12px 16px;">
                <div style="display:flex;gap:12px;align-items:center;">
                  <a class="view-order-details-link" href="{$order.details.details_url}" data-link-action="view-order-details"
                     style="font-size:13px;color:var(--fsl-forest);font-weight:500;text-decoration:none;">
                    {l s='Details' d='Shop.Theme.Customeraccount'}
                  </a>
                  {if $order.details.reorder_url}
                    <a class="reorder-link" href="{$order.details.reorder_url}"
                       style="font-size:13px;color:var(--fsl-gray-500);text-decoration:none;">
                      {l s='Reorder' d='Shop.Theme.Actions'}
                    </a>
                  {/if}
                </div>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div style="text-align:center;padding:48px 20px;">
      <span class="material-icons" style="font-size:48px;color:var(--fsl-gray-200);">receipt_long</span>
      <p style="font-size:15px;color:var(--fsl-gray-500);margin-top:12px;">{l s='You have not placed any orders.' d='Shop.Notifications.Warning'}</p>
    </div>
  {/if}
{/block}
