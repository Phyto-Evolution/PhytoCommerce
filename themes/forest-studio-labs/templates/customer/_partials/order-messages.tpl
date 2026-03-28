{block name='order_messages_table'}
  {if $order.messages}
    <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;margin-bottom:24px;">
      <h3 style="font-family:var(--fsl-font-display);font-size:20px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 16px;">{l s='Messages' d='Shop.Theme.Customeraccount'}</h3>
      {foreach from=$order.messages item=message}
        <div style="display:flex;gap:16px;padding:12px 0;border-bottom:1px solid var(--fsl-gray-100);">
          <div style="flex-shrink:0;width:140px;">
            <p style="font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin:0 0 4px;">{$message.name}</p>
            <p style="font-size:12px;color:var(--fsl-gray-400);margin:0;">{$message.message_date}</p>
          </div>
          <div style="flex:1;font-size:14px;color:var(--fsl-gray-700);line-height:1.6;">
            {$message.message|escape:'html'|nl2br nofilter}
          </div>
        </div>
      {/foreach}
    </div>
  {/if}
{/block}

{block name='order_message_form'}
  <section class="order-message-form" style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;">
    <form action="{$urls.pages.order_detail}" method="post">
      <h3 style="font-family:var(--fsl-font-display);font-size:20px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 8px;">{l s='Add a message' d='Shop.Theme.Customeraccount'}</h3>
      <p style="font-size:14px;color:var(--fsl-gray-500);margin-bottom:20px;">{l s='If you would like to add a comment about your order, please write it in the field below.' d='Shop.Theme.Customeraccount'}</p>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:6px;">{l s='Product' d='Shop.Forms.Labels'}</label>
        <select name="id_product" data-role="product"
                style="width:100%;max-width:360px;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;">
          <option value="0">{l s='-- please choose --' d='Shop.Forms.Labels'}</option>
          {foreach from=$order.products item=product}
            <option value="{$product.id_product}">{$product.name}</option>
          {/foreach}
        </select>
      </div>

      <div style="margin-bottom:20px;">
        <textarea rows="4" name="msgText" data-role="msg-text"
                  style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;resize:vertical;"></textarea>
      </div>

      <div style="text-align:right;">
        <input type="hidden" name="id_order" value="{$order.details.id}">
        <button type="submit" name="submitMessage"
                style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
          {l s='Send' d='Shop.Theme.Actions'}
        </button>
      </div>
    </form>
  </section>
{/block}
