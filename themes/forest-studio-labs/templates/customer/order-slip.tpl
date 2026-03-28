{extends file='customer/page.tpl'}

{block name='page_content'}
  <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin-bottom:8px;">
    {l s='Credit slips' d='Shop.Theme.Customeraccount'}
  </h1>
  <p style="font-size:14px;color:var(--fsl-gray-500);margin-bottom:24px;">{l s='Credit slips you have received after canceled orders.' d='Shop.Theme.Customeraccount'}</p>

  {if $credit_slips}
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
        <thead>
          <tr style="background:var(--fsl-gray-50);">
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Order' d='Shop.Theme.Customeraccount'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Credit slip' d='Shop.Theme.Customeraccount'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Date issued' d='Shop.Theme.Customeraccount'}</th>
            <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='View credit slip' d='Shop.Theme.Customeraccount'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$credit_slips item=slip}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:12px 16px;">
                <a href="{$slip.order_url_details}" data-link-action="view-order-details"
                   style="font-weight:500;color:var(--fsl-forest);text-decoration:none;">{$slip.order_reference}</a>
              </td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-700);">{$slip.credit_slip_number}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-600);">{$slip.credit_slip_date}</td>
              <td style="padding:12px 16px;text-align:center;">
                <a href="{$slip.url}" style="color:var(--fsl-forest);">
                  <i class="material-icons" style="font-size:20px;">picture_as_pdf</i>
                </a>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div style="text-align:center;padding:48px 20px;">
      <span class="material-icons" style="font-size:48px;color:var(--fsl-gray-200);">description</span>
      <p style="font-size:15px;color:var(--fsl-gray-500);margin-top:12px;">{l s='You have not received any credit slips.' d='Shop.Notifications.Warning'}</p>
    </div>
  {/if}
{/block}
