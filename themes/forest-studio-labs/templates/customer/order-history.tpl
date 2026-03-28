{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <h3>{l s='Order History' mod='fsl'}</h3>
  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

  {if $orders|count}
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <thead>
          <tr style="border-bottom:2px solid var(--fsl-gray-200);">
            <th style="padding:10px 12px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:left">{l s='Order' mod='fsl'}</th>
            <th style="padding:10px 12px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:left">{l s='Date' mod='fsl'}</th>
            <th style="padding:10px 12px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:left">{l s='Status' mod='fsl'}</th>
            <th style="padding:10px 12px;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-500);text-align:right">{l s='Total' mod='fsl'}</th>
            <th style="padding:10px 12px"></th>
          </tr>
        </thead>
        <tbody>
          {foreach $orders as $order}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:14px 12px;font-weight:600;color:var(--fsl-gray-900)">#{$order.details.reference|escape:'htmlall':'UTF-8'}</td>
              <td style="padding:14px 12px;color:var(--fsl-gray-500)">{$order.details.order_date|escape:'htmlall':'UTF-8'}</td>
              <td style="padding:14px 12px;">
                <span style="background:var(--fsl-light-green);color:var(--fsl-forest);font-size:11px;font-weight:600;padding:3px 10px;border-radius:var(--fsl-radius-pill);">
                  {$order.history.current.ostate_name|escape:'htmlall':'UTF-8'}
                </span>
              </td>
              <td style="padding:14px 12px;font-weight:600;color:var(--fsl-forest);text-align:right">{$order.details.totals.total.value|escape:'htmlall':'UTF-8'}</td>
              <td style="padding:14px 12px;text-align:right">
                <a href="{$order.details.details_url|escape:'htmlall':'UTF-8'}"
                   class="btn btn-outline-primary btn-sm" style="font-size:11px;padding:5px 14px;">
                  {l s='View' mod='fsl'}
                </a>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {else}
    <div class="text-center py-4">
      <span class="material-icons" style="font-size:40px;color:var(--fsl-gray-300)">shopping_bag</span>
      <p style="color:var(--fsl-gray-400);margin-top:12px">{l s='No orders yet.' mod='fsl'}</p>
      <a href="{$urls.base_url}" class="btn btn-primary btn-sm mt-2">{l s='Start Shopping' mod='fsl'}</a>
    </div>
  {/if}
</div>
{/block}
