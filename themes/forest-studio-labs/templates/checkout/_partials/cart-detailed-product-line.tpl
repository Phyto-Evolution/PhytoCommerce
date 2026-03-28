<div class="product-line-grid" style="display:flex;gap:16px;align-items:flex-start;">

  {* Image *}
  <div class="product-line-grid-left" style="flex-shrink:0;width:72px;">
    <span class="product-image">
      {if $product.default_image}
        <picture>
          {if !empty($product.default_image.bySize.cart_default.sources.avif)}<source srcset="{$product.default_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
          {if !empty($product.default_image.bySize.cart_default.sources.webp)}<source srcset="{$product.default_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
          <img src="{$product.default_image.bySize.cart_default.url}" alt="{$product.name|escape:'quotes'}" loading="lazy"
               style="width:72px;height:72px;object-fit:cover;border-radius:var(--fsl-radius);">
        </picture>
      {else}
        <picture>
          {if !empty($urls.no_picture_image.bySize.cart_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.cart_default.sources.avif}" type="image/avif">{/if}
          {if !empty($urls.no_picture_image.bySize.cart_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.cart_default.sources.webp}" type="image/webp">{/if}
          <img src="{$urls.no_picture_image.bySize.cart_default.url}" loading="lazy"
               style="width:72px;height:72px;object-fit:cover;border-radius:var(--fsl-radius);">
        </picture>
      {/if}
    </span>
  </div>

  {* Product info *}
  <div class="product-line-grid-body" style="flex:1;min-width:0;">
    <div class="product-line-info" style="margin-bottom:6px;">
      <a href="{$product.url}" data-id_customization="{$product.id_customization|intval}"
         style="font-size:14px;font-weight:500;color:var(--fsl-gray-800);text-decoration:none;">{$product.name}</a>
    </div>

    <div class="product-line-info product-price {if $product.has_discount}has-discount{/if}" style="margin-bottom:6px;">
      {if $product.has_discount}
        <div class="product-discount" style="display:flex;gap:8px;align-items:center;">
          <span class="regular-price" style="font-size:12px;color:var(--fsl-gray-400);text-decoration:line-through;">{$product.regular_price}</span>
          {if $product.discount_type === 'percentage'}
            <span class="discount discount-percentage" style="font-size:11px;background:var(--fsl-light-green);color:var(--fsl-forest);padding:2px 6px;border-radius:var(--fsl-radius-pill);">
              -{$product.discount_percentage_absolute}
            </span>
          {else}
            <span class="discount discount-amount" style="font-size:11px;background:var(--fsl-light-green);color:var(--fsl-forest);padding:2px 6px;border-radius:var(--fsl-radius-pill);">
              -{$product.discount_to_display}
            </span>
          {/if}
        </div>
      {/if}
      <div class="current-price">
        <span class="price" style="font-size:14px;font-weight:500;color:var(--fsl-forest);">{$product.price}</span>
        {if $product.unit_price_full}
          <div class="unit-price-cart" style="font-size:11px;color:var(--fsl-gray-400);">{$product.unit_price_full}</div>
        {/if}
      </div>
      {hook h='displayProductPriceBlock' product=$product type="unit_price"}
    </div>

    {foreach from=$product.attributes key="attribute" item="value"}
      <div class="product-line-info {$attribute|lower}" style="font-size:12px;color:var(--fsl-gray-500);margin-bottom:2px;">
        <span class="label" style="font-weight:500;">{$attribute}:</span>
        <span class="value">{$value}</span>
      </div>
    {/foreach}

    {if is_array($product.customizations) && $product.customizations|count}
      {block name='cart_detailed_product_line_customization'}
        {foreach from=$product.customizations item="customization"}
          <a href="#" data-toggle="modal" data-target="#product-customizations-modal-{$customization.id_customization}"
             style="font-size:12px;color:var(--fsl-forest);text-decoration:underline;">
            {l s='Product customization' d='Shop.Theme.Catalog'}
          </a>
          <div class="modal fade customization-modal js-customization-modal"
               id="product-customizations-modal-{$customization.id_customization}"
               tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
              <div class="modal-content" style="border-radius:var(--fsl-radius-lg);">
                <div class="modal-header" style="border-bottom:1px solid var(--fsl-gray-100);padding:16px 20px;">
                  <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"
                          style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
                  <h4 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;margin:0;">{l s='Product customization' d='Shop.Theme.Catalog'}</h4>
                </div>
                <div class="modal-body" style="padding:20px;">
                  {foreach from=$customization.fields item="field"}
                    <div class="product-customization-line row" style="margin-bottom:12px;">
                      <div class="col-sm-3 col-4" style="font-size:13px;font-weight:500;color:var(--fsl-gray-600);">
                        {$field.label}
                      </div>
                      <div class="col-sm-9 col-8" style="font-size:13px;color:var(--fsl-gray-700);">
                        {if $field.type == 'text'}
                          {if (int)$field.id_module}{$field.text nofilter}{else}{$field.text}{/if}
                        {elseif $field.type == 'image'}
                          <img src="{$field.image.small.url}" loading="lazy" style="border-radius:var(--fsl-radius);max-height:80px;">
                        {/if}
                      </div>
                    </div>
                  {/foreach}
                </div>
              </div>
            </div>
          </div>
        {/foreach}
      {/block}
    {/if}
  </div>

  {* Actions: qty + price + delete *}
  <div class="product-line-grid-right product-line-actions" style="display:flex;flex-direction:column;align-items:flex-end;gap:8px;flex-shrink:0;">
    <div style="display:flex;align-items:center;gap:10px;">
      {if !empty($product.is_gift)}
        <span class="gift-quantity" style="font-size:14px;color:var(--fsl-gray-600);">{$product.quantity}</span>
      {else}
        <input
          class="js-cart-line-product-quantity"
          data-down-url="{$product.down_quantity_url}"
          data-up-url="{$product.up_quantity_url}"
          data-update-url="{$product.update_quantity_url}"
          data-product-id="{$product.id_product}"
          type="number"
          inputmode="numeric"
          pattern="[0-9]*"
          value="{$product.quantity}"
          name="product-quantity-spin"
          aria-label="{l s='%productName% product quantity field' sprintf=['%productName%' => $product.name] d='Shop.Theme.Checkout'}"
          style="width:60px;padding:6px 8px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;text-align:center;"
        >
      {/if}
      <span class="product-price" style="font-size:15px;font-weight:600;color:var(--fsl-gray-800);min-width:60px;text-align:right;">
        {if !empty($product.is_gift)}
          <span class="gift" style="font-size:12px;background:var(--fsl-light-green);color:var(--fsl-forest);padding:3px 8px;border-radius:var(--fsl-radius-pill);">{l s='Gift' d='Shop.Theme.Checkout'}</span>
        {else}
          {$product.total}
        {/if}
      </span>
    </div>
    <div class="cart-line-product-actions">
      {if empty($product.is_gift)}
        <a class="remove-from-cart" rel="nofollow" href="{$product.remove_from_cart_url}"
           data-link-action="delete-from-cart"
           data-id-product="{$product.id_product|escape:'javascript'}"
           data-id-product-attribute="{$product.id_product_attribute|escape:'javascript'}"
           data-id-customization="{$product.id_customization|default|escape:'javascript'}"
           style="display:flex;align-items:center;color:var(--fsl-gray-400);">
          <i class="material-icons" style="font-size:18px;">delete_outline</i>
        </a>
      {/if}
      {block name='hook_cart_extra_product_actions'}
        {hook h='displayCartExtraProductActions' product=$product}
      {/block}
    </div>
  </div>

</div>
