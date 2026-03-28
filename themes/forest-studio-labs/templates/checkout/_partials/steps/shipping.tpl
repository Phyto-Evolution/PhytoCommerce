{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
  <div id="hook-display-before-carrier">
    {$hookDisplayBeforeCarrier nofilter}
  </div>

  <div class="delivery-options-list">
    {if $delivery_options|count}
      <form class="clearfix" id="js-delivery"
            data-url-update="{url entity='order' params=['ajax' => 1, 'action' => 'selectDeliveryOption']}"
            method="post">
        <div class="form-fields">
          {block name='delivery_options'}
            <div class="delivery-options" style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
              {foreach from=$delivery_options item=carrier key=carrier_id}
                <div class="delivery-option js-delivery-option"
                     style="border:1.5px solid {if $delivery_option == $carrier_id}var(--fsl-forest){else}var(--fsl-gray-200){/if};border-radius:var(--fsl-radius-lg);padding:16px;cursor:pointer;transition:border-color .2s;">
                  <label for="delivery_option_{$carrier.id}" style="display:flex;align-items:center;gap:16px;cursor:pointer;margin:0;">
                    <input type="radio" name="delivery_option[{$id_address}]"
                           id="delivery_option_{$carrier.id}"
                           value="{$carrier_id}"
                           {if $delivery_option == $carrier_id}checked{/if}
                           style="accent-color:var(--fsl-forest);width:16px;height:16px;flex-shrink:0;">
                    {if $carrier.logo}
                      <img src="{$carrier.logo}" alt="{$carrier.name}" loading="lazy" style="max-height:32px;width:auto;">
                    {/if}
                    <div style="flex:1;">
                      <span style="font-size:14px;font-weight:500;color:var(--fsl-gray-800);display:block;">{$carrier.name}</span>
                      <span style="font-size:13px;color:var(--fsl-gray-500);">{$carrier.delay}</span>
                    </div>
                    <span style="font-size:15px;font-weight:600;color:var(--fsl-forest);">{$carrier.price}</span>
                  </label>
                </div>
                <div class="carrier-extra-content js-carrier-extra-content"{if $delivery_option != $carrier_id} style="display:none;"{/if}>
                  {$carrier.extraContent nofilter}
                </div>
              {/foreach}
            </div>
          {/block}

          <div class="order-options" style="display:flex;flex-direction:column;gap:16px;margin-bottom:20px;">
            <div id="delivery">
              <label for="delivery_message"
                     style="display:block;font-size:13px;color:var(--fsl-gray-600);margin-bottom:6px;">
                {l s='If you would like to add a comment about your order, please write it in the field below.' d='Shop.Theme.Checkout'}
              </label>
              <textarea rows="3" id="delivery_message" name="delivery_message"
                        style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;resize:vertical;">{$delivery_message}</textarea>
            </div>

            {if $recyclablePackAllowed}
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--fsl-gray-700);">
                <input type="checkbox" id="input_recyclable" name="recyclable" value="1" {if $recyclable}checked{/if}
                       style="accent-color:var(--fsl-forest);width:16px;height:16px;">
                {l s='I would like to receive my order in recycled packaging.' d='Shop.Theme.Checkout'}
              </label>
            {/if}

            {if $gift.allowed}
              <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:14px;color:var(--fsl-gray-700);">
                <input class="js-gift-checkbox" id="input_gift" name="gift" type="checkbox" value="1"
                       {if $gift.isGift}checked="checked"{/if}
                       style="accent-color:var(--fsl-forest);width:16px;height:16px;">
                {$gift.label}
              </label>
              <div id="gift" class="collapse{if $gift.isGift} in{/if}" style="margin-top:-8px;">
                <label for="gift_message" style="display:block;font-size:13px;color:var(--fsl-gray-600);margin-bottom:6px;">
                  {l s="If you'd like, you can add a note to the gift:" d='Shop.Theme.Checkout'}
                </label>
                <textarea rows="3" id="gift_message" name="gift_message"
                          style="width:100%;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;resize:vertical;">{$gift.message}</textarea>
              </div>
            {/if}
          </div>
        </div>

        <div style="text-align:right;">
          <button type="submit" class="continue" name="confirmDeliveryOption" value="1"
                  style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
            {l s='Continue' d='Shop.Theme.Actions'}
          </button>
        </div>
      </form>
    {else}
      <p style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:12px 16px;font-size:14px;color:#dc2626;">
        {l s='Unfortunately, there are no carriers available for your delivery address.' d='Shop.Theme.Checkout'}
      </p>
    {/if}
  </div>

  <div id="hook-display-after-carrier">
    {$hookDisplayAfterCarrier nofilter}
  </div>

  <div id="extra_carrier"></div>
{/block}
