{extends file='customer/page.tpl'}

{block name='page_content'}
  <h1 style="font-family:var(--fsl-font-display);font-size:2rem;font-weight:400;color:var(--fsl-gray-800);margin-bottom:24px;">
    {l s='Your vouchers' d='Shop.Theme.Customeraccount'}
  </h1>

  {if $cart_rules}
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:14px;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
        <thead>
          <tr style="background:var(--fsl-gray-50);">
            {foreach ['{l s=\'Code\' d=\'Shop.Theme.Checkout\'}','{l s=\'Name\' d=\'Shop.Theme.Checkout\'}','{l s=\'Quantity\' d=\'Shop.Theme.Checkout\'}','{l s=\'Value\' d=\'Shop.Theme.Checkout\'}','{l s=\'Minimum\' d=\'Shop.Theme.Checkout\'}','{l s=\'Cumulative\' d=\'Shop.Theme.Checkout\'}','{l s=\'Expiration date\' d=\'Shop.Theme.Checkout\'}'] as $col}
            {/foreach}
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Code' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Name' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:center;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Quantity' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Value' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Minimum' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Cumulative' d='Shop.Theme.Checkout'}</th>
            <th style="padding:12px 16px;text-align:left;font-weight:600;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{l s='Expiration date' d='Shop.Theme.Checkout'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$cart_rules item=cart_rule}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:12px 16px;font-family:monospace;font-size:13px;font-weight:600;color:var(--fsl-forest);">{$cart_rule.code}</td>
              <td style="padding:12px 16px;font-size:14px;color:var(--fsl-gray-700);">{$cart_rule.name}</td>
              <td style="padding:12px 16px;text-align:center;color:var(--fsl-gray-700);">{$cart_rule.quantity_for_user}</td>
              <td style="padding:12px 16px;font-weight:500;color:var(--fsl-forest);">{$cart_rule.value}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-600);">{$cart_rule.voucher_minimal}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-600);">{$cart_rule.voucher_cumulable}</td>
              <td style="padding:12px 16px;font-size:13px;color:var(--fsl-gray-500);">{$cart_rule.voucher_date}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div style="text-align:center;padding:48px 20px;">
      <span class="material-icons" style="font-size:48px;color:var(--fsl-gray-200);">local_offer</span>
      <p style="font-size:15px;color:var(--fsl-gray-500);margin-top:12px;">{l s='You do not have any vouchers.' d='Shop.Notifications.Warning'}</p>
    </div>
  {/if}
{/block}
