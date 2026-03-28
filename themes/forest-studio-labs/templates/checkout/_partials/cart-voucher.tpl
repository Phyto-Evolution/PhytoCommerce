{if $cart.vouchers.allowed}
  {block name='cart_voucher'}
    <div class="block-promo">
      <div class="cart-voucher js-cart-voucher">

        {if $cart.vouchers.added}
          {block name='cart_voucher_list'}
            <ul style="list-style:none;padding:0;margin:0 0 12px;">
              {foreach from=$cart.vouchers.added item=voucher}
                <li class="cart-summary-line" style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:13px;">
                  <span class="label" style="color:var(--fsl-forest);font-weight:500;">{$voucher.name}</span>
                  <div style="display:flex;align-items:center;gap:8px;">
                    <span style="color:var(--fsl-gray-600);">{$voucher.reduction_formatted}</span>
                    {if isset($voucher.code) && $voucher.code !== ''}
                      <a href="{$voucher.delete_url}" data-link-action="remove-voucher"
                         style="color:var(--fsl-gray-400);display:flex;">
                        <i class="material-icons" style="font-size:16px;">close</i>
                      </a>
                    {/if}
                  </div>
                </li>
              {/foreach}
            </ul>
          {/block}
        {/if}

        <p class="promo-code-button display-promo{if $cart.discounts|count > 0} with-discounts{/if}" style="margin-bottom:8px;">
          <a class="collapse-button" href="#promo-code"
             style="font-size:13px;color:var(--fsl-forest);text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
            <i class="material-icons" style="font-size:16px;">local_offer</i>
            {l s='Have a promo code?' d='Shop.Theme.Checkout'}
          </a>
        </p>

        <div id="promo-code" class="collapse{if $cart.discounts|count > 0} in{/if}">
          <div class="promo-code">
            {block name='cart_voucher_form'}
              <form action="{$urls.pages.cart}" data-link-action="add-voucher" method="post"
                    style="display:flex;gap:8px;margin-bottom:8px;">
                <input type="hidden" name="token" value="{$static_token}">
                <input type="hidden" name="addDiscount" value="1">
                <input class="promo-input" type="text" name="discount_name"
                       placeholder="{l s='Promo code' d='Shop.Theme.Checkout'}"
                       style="flex:1;padding:8px 12px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:13px;">
                <button type="submit"
                        style="padding:8px 16px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:13px;font-weight:500;cursor:pointer;">
                  {l s='Add' d='Shop.Theme.Actions'}
                </button>
              </form>
            {/block}

            {block name='cart_voucher_notifications'}
              <div class="alert alert-danger js-error" role="alert"
                   style="background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--fsl-radius);padding:10px 14px;font-size:13px;display:none;">
                <span class="js-error-text" style="color:#dc2626;"></span>
              </div>
            {/block}

            <a class="collapse-button promo-code-button cancel-promo" role="button"
               data-toggle="collapse" data-target="#promo-code"
               aria-expanded="true" aria-controls="promo-code"
               style="font-size:12px;color:var(--fsl-gray-400);cursor:pointer;">
              {l s='Close' d='Shop.Theme.Checkout'}
            </a>
          </div>
        </div>

        {if $cart.discounts|count > 0}
          <p style="font-size:12px;color:var(--fsl-forest);font-weight:500;margin:12px 0 6px;">
            {l s='Take advantage of our exclusive offers:' d='Shop.Theme.Actions'}
          </p>
          <ul class="js-discount promo-discounts" style="list-style:none;padding:0;margin:0;">
            {foreach from=$cart.discounts item=discount}
              <li class="cart-summary-line" style="font-size:13px;color:var(--fsl-gray-600);padding:4px 0;">
                <span class="label">
                  <span class="code" style="font-weight:500;color:var(--fsl-forest);">{$discount.code}</span> - {$discount.name}
                </span>
              </li>
            {/foreach}
          </ul>
        {/if}
      </div>
    </div>
  {/block}
{/if}
