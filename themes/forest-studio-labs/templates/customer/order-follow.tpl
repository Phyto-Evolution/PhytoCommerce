{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <h3>{l s='Order Tracking' mod='fsl'}</h3>
  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}
  {if isset($order)}
    <p style="font-size:14px;color:var(--fsl-gray-600)">
      {l s='Order reference:' mod='fsl'} <strong>{$order.details.reference|escape:'htmlall':'UTF-8'}</strong><br>
      {l s='Status:' mod='fsl'} <strong style="color:var(--fsl-forest)">{$order.history.current.ostate_name|escape:'htmlall':'UTF-8'}</strong>
    </p>
    {hook h='displayOrderDetail' order=$order}
  {else}
    <p style="color:var(--fsl-gray-400)">{l s='No order found.' mod='fsl'}</p>
  {/if}
</div>
{/block}
