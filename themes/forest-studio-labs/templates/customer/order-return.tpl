{extends file='customer/page.tpl'}

{block name='page_content'}
  {block name='order_return_infos'}
    <div id="order-return-infos" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;margin-bottom:24px;">
      <p style="font-size:15px;font-weight:600;color:var(--fsl-gray-800);margin-bottom:12px;">
        {l s='%number% on %date%' d='Shop.Theme.Customeraccount'
          sprintf=['%number%' => $return.return_number, '%date%' => $return.return_date]}
      </p>
      <p style="font-size:14px;color:var(--fsl-gray-600);margin-bottom:8px;">{l s='We have logged your return request.' d='Shop.Theme.Customeraccount'}</p>
      <p style="font-size:14px;color:var(--fsl-gray-600);margin-bottom:8px;">
        {l s='Your package must be returned to us within %number% days of receiving your order.'
          d='Shop.Theme.Customeraccount'
          sprintf=['%number%' => $configuration.number_of_days_for_return]}
      </p>
      <p style="font-size:14px;color:var(--fsl-gray-700);margin-bottom:16px;">
        {l s='The current status of your merchandise return is: [1] %status% [/1]'
          d='Shop.Theme.Customeraccount'
          sprintf=[
            '[1]' => '<strong style="color:var(--fsl-forest);">',
            '[/1]' => '</strong>',
            '%status%' => $return.state_name
          ]
        }
      </p>
      <p style="font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-500);margin-bottom:12px;">{l s='List of items to be returned:' d='Shop.Theme.Customeraccount'}</p>
      <table style="width:100%;border-collapse:collapse;font-size:14px;">
        <thead>
          <tr style="background:var(--fsl-gray-50);border-bottom:1px solid var(--fsl-gray-200);">
            <th style="padding:10px 14px;text-align:left;font-weight:600;color:var(--fsl-gray-600);">{l s='Product' d='Shop.Theme.Catalog'}</th>
            <th style="padding:10px 14px;text-align:center;font-weight:600;color:var(--fsl-gray-600);">{l s='Quantity' d='Shop.Theme.Checkout'}</th>
          </tr>
        </thead>
        <tbody>
          {foreach from=$products item=product}
            <tr style="border-bottom:1px solid var(--fsl-gray-100);">
              <td style="padding:10px 14px;">
                <strong style="color:var(--fsl-gray-800);">{$product.product_name}</strong>
                {if $product.product_reference}
                  <div style="font-size:12px;color:var(--fsl-gray-400);">{l s='Reference' d='Shop.Theme.Catalog'}: {$product.product_reference}</div>
                {/if}
              </td>
              <td style="padding:10px 14px;text-align:center;color:var(--fsl-gray-700);">{$product.product_quantity}</td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    </div>
  {/block}

  {if $return.state == 2}
    <div style="background:var(--fsl-light-green);border-radius:var(--fsl-radius-lg);padding:24px;">
      <h3 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 12px;">{l s='Reminder' d='Shop.Theme.Customeraccount'}</h3>
      <p style="font-size:14px;color:var(--fsl-gray-700);line-height:1.7;margin-bottom:8px;">
        {l s='All merchandise must be returned in its original packaging and in its original state.' d='Shop.Theme.Customeraccount'}
      </p>
      <p style="font-size:14px;color:var(--fsl-gray-700);line-height:1.7;margin-bottom:8px;">
        {l s='Please print out the [1]returns form[/1] and include it with your package.'
          d='Shop.Theme.Customeraccount'
          sprintf=['[1]' => '<a href="'|cat:$return.print_url|cat:'" style="color:var(--fsl-forest);">', '[/1]' => '</a>']
        }
      </p>
      <p style="font-size:14px;color:var(--fsl-gray-700);line-height:1.7;margin-bottom:8px;">
        {l s='Please check the [1]returns form[/1] for the correct address.'
          d='Shop.Theme.Customeraccount'
          sprintf=['[1]' => '<a href="'|cat:$return.print_url|cat:'" style="color:var(--fsl-forest);">', '[/1]' => '</a>']
        }
      </p>
      <p style="font-size:14px;color:var(--fsl-gray-700);line-height:1.7;">
        {l s='When we receive your package, we will notify you by email. We will then begin processing order reimbursement.' d='Shop.Theme.Customeraccount'}
        <a href="{$urls.pages.contact}" style="color:var(--fsl-forest);">{l s='Please let us know if you have any questions.' d='Shop.Theme.Customeraccount'}</a>
      </p>
    </div>
  {/if}
{/block}
