{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}

  {hook h='displayPaymentTop'}

  {* used by javascript to correctly handle cart updates when we are on payment step *}
  <div style="display:none" class="js-cart-payment-step-refresh"></div>

  {if !empty($display_transaction_updated_info)}
    <p class="cart-payment-step-refreshed-info"
       style="background:var(--fsl-light-green);padding:10px 14px;border-radius:var(--fsl-radius);font-size:13px;color:var(--fsl-forest);margin-bottom:16px;">
      {l s='Transaction amount has been correctly updated' d='Shop.Theme.Checkout'}
    </p>
  {/if}

  {if $is_free}
    <p class="cart-payment-step-not-needed-info"
       style="background:var(--fsl-light-green);padding:12px 16px;border-radius:var(--fsl-radius);font-size:14px;color:var(--fsl-forest);margin-bottom:16px;">
      {l s='No payment needed for this order' d='Shop.Theme.Checkout'}
    </p>
  {/if}

  <div class="payment-options {if $is_free}hidden-xs-up{/if}" style="display:flex;flex-direction:column;gap:12px;margin-bottom:24px;">
    {foreach from=$payment_options item="module_options"}
      {foreach from=$module_options item="option"}
        <div>
          <div id="{$option.id}-container" class="payment-option"
               style="border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:16px;display:flex;align-items:center;gap:12px;">
            <input
              class="ps-shown-by-js {if $option.binary}binary{/if}"
              id="{$option.id}"
              data-module-name="{$option.module_name}"
              name="payment-option"
              type="radio"
              required
              {if ($selected_payment_option == $option.id || $is_free) || ($payment_options|@count === 1 && $module_options|@count === 1)}checked{/if}
              style="accent-color:var(--fsl-forest);width:16px;height:16px;flex-shrink:0;"
            >
            <form method="GET" class="ps-hidden-by-js">
              {if $option.id === $selected_payment_option}
                <span style="font-size:13px;color:var(--fsl-forest);">{l s='Selected' d='Shop.Theme.Checkout'}</span>
              {else}
                <button class="ps-hidden-by-js" type="submit" name="select_payment_option" value="{$option.id}"
                        style="padding:6px 14px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:13px;cursor:pointer;">
                  {l s='Choose' d='Shop.Theme.Actions'}
                </button>
              {/if}
            </form>
            <label for="{$option.id}" style="flex:1;display:flex;align-items:center;gap:12px;cursor:pointer;margin:0;">
              <span style="font-size:14px;font-weight:500;color:var(--fsl-gray-800);">{$option.call_to_action_text}</span>
              {if $option.logo}
                <img src="{$option.logo}" loading="lazy" style="max-height:24px;width:auto;">
              {/if}
            </label>
          </div>
        </div>

        {if $option.additionalInformation}
          <div id="{$option.id}-additional-information"
               class="js-additional-information definition-list additional-information{if $option.id != $selected_payment_option} ps-hidden{/if}"
               style="padding:12px 16px;background:var(--fsl-gray-50);border-radius:var(--fsl-radius);font-size:13px;color:var(--fsl-gray-600);">
            {$option.additionalInformation nofilter}
          </div>
        {/if}

        <div id="pay-with-{$option.id}-form"
             class="js-payment-option-form{if $option.id != $selected_payment_option} ps-hidden{/if}">
          {if $option.form}
            {$option.form nofilter}
          {else}
            <form id="payment-{$option.id}-form" method="POST" action="{$option.action nofilter}">
              {foreach from=$option.inputs item=input}
                <input type="{$input.type}" name="{$input.name}" value="{$input.value}">
              {/foreach}
              <button style="display:none" id="pay-with-{$option.id}" type="submit"></button>
            </form>
          {/if}
        </div>
      {/foreach}
    {foreachelse}
      <p style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:12px 16px;font-size:14px;color:#dc2626;">
        {l s='Unfortunately, there are no payment method available.' d='Shop.Theme.Checkout'}
      </p>
    {/foreach}
  </div>

  {if $conditions_to_approve|count}
    <p class="ps-hidden-by-js" style="font-size:13px;color:var(--fsl-gray-600);margin-bottom:12px;">
      {l s='By confirming the order, you certify that you have read and agree with all of the conditions below:' d='Shop.Theme.Checkout'}
    </p>

    <form id="conditions-to-approve" class="js-conditions-to-approve" method="GET">
      <ul style="list-style:none;padding:0;margin:0 0 20px;">
        {foreach from=$conditions_to_approve item="condition" key="condition_name"}
          <li style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid var(--fsl-gray-100);">
            <input id="conditions_to_approve[{$condition_name}]"
                   name="conditions_to_approve[{$condition_name}]"
                   required
                   type="checkbox"
                   value="1"
                   class="ps-shown-by-js"
                   style="accent-color:var(--fsl-forest);width:16px;height:16px;margin-top:2px;flex-shrink:0;">
            <div class="condition-label">
              <label class="js-terms" for="conditions_to_approve[{$condition_name}]"
                     style="font-size:13px;color:var(--fsl-gray-600);cursor:pointer;">
                {$condition nofilter}
              </label>
            </div>
          </li>
        {/foreach}
      </ul>
    </form>
  {/if}

  {hook h='displayCheckoutBeforeConfirmation'}

  {if $show_final_summary}
    {include file='checkout/_partials/order-final-summary.tpl'}
  {/if}

  <div id="payment-confirmation" class="js-payment-confirmation" style="margin-top:20px;">
    <div class="ps-shown-by-js" style="text-align:center;">
      <button type="submit" class="btn btn-primary{if !$selected_payment_option} disabled{/if}"
              style="padding:14px 40px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:16px;font-weight:600;cursor:pointer;width:100%;">
        {l s='Place order' d='Shop.Theme.Checkout'}
      </button>
      {if $show_final_summary}
        <div class="alert mt-2 js-alert-payment-conditions" role="alert" data-alert="danger"
             style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;color:#dc2626;margin-top:8px;">
          {l
            s="Please make sure you've chosen a [1]payment method[/1] and accepted the [2]terms and conditions[/2]."
            sprintf=[
              '[1]' => '<a href="#checkout-payment-step" style="color:#dc2626;">',
              '[/1]' => '</a>',
              '[2]' => '<a href="#conditions-to-approve" style="color:#dc2626;">',
              '[/2]' => '</a>'
            ]
            d='Shop.Theme.Checkout'
          }
        </div>
      {/if}
    </div>
    <div class="ps-hidden-by-js" style="text-align:center;">
      {if $selected_payment_option and $all_conditions_approved}
        <label for="pay-with-{$selected_payment_option}"
               style="font-size:14px;color:var(--fsl-gray-600);">
          {l s='Order with an obligation to pay' d='Shop.Theme.Checkout'}
        </label>
      {/if}
    </div>
  </div>

  {hook h='displayPaymentByBinaries'}
{/block}
