{if $product.show_price}
  <div class="product-prices js-product-prices" style="margin-bottom:16px;">
    {block name='product_discount'}
      {if $product.has_discount}
        <div class="product-discount" style="margin-bottom:4px;">
          {hook h='displayProductPriceBlock' product=$product type="old_price"}
          <span class="regular-price" style="font-size:14px;color:var(--fsl-gray-400);text-decoration:line-through;">{$product.regular_price}</span>
        </div>
      {/if}
    {/block}

    {block name='product_price'}
      <div class="product-price {if $product.has_discount}has-discount{/if}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <div class="current-price">
          <span class="current-price-value" style="font-family:var(--fsl-font-display);font-size:1.6rem;font-weight:400;color:var(--fsl-forest);" content="{$product.rounded_display_price}">
            {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='product_sheet'}{/capture}
            {if '' !== $smarty.capture.custom_price}
              {$smarty.capture.custom_price nofilter}
            {else}
              {$product.price}
            {/if}
          </span>

          {if $product.has_discount}
            {if $product.discount_type === 'percentage'}
              <span class="discount discount-percentage" style="background:#e57373;color:#fff;font-size:12px;font-weight:600;padding:2px 8px;border-radius:4px;margin-left:6px;">
                {l s='Save %percentage%' d='Shop.Theme.Catalog' sprintf=['%percentage%' => $product.discount_percentage_absolute]}
              </span>
            {else}
              <span class="discount discount-amount" style="background:#e57373;color:#fff;font-size:12px;font-weight:600;padding:2px 8px;border-radius:4px;margin-left:6px;">
                {l s='Save %amount%' d='Shop.Theme.Catalog' sprintf=['%amount%' => $product.discount_to_display]}
              </span>
            {/if}
          {/if}
        </div>

        {block name='product_unit_price'}
          {if $displayUnitPrice}
            <p class="product-unit-price" style="font-size:12px;color:var(--fsl-gray-500);margin:0;">{$product.unit_price_full}</p>
          {/if}
        {/block}
      </div>
    {/block}

    {block name='product_without_taxes'}
      {if $priceDisplay == 2}
        <p class="product-without-taxes" style="font-size:12px;color:var(--fsl-gray-500);margin-top:4px;">
          {l s='%price% tax excl.' d='Shop.Theme.Catalog' sprintf=['%price%' => $product.price_tax_exc]}
        </p>
      {/if}
    {/block}

    {block name='product_pack_price'}
      {if $displayPackPrice}
        <p class="product-pack-price" style="font-size:12px;color:var(--fsl-gray-500);margin-top:4px;">
          {l s='Instead of %price%' d='Shop.Theme.Catalog' sprintf=['%price%' => $noPackPrice]}
        </p>
      {/if}
    {/block}

    {block name='product_ecotax'}
      {if !$product.is_virtual && $product.ecotax.amount > 0}
        <p class="price-ecotax" style="font-size:12px;color:var(--fsl-gray-500);margin-top:4px;">
          {l s='Including %amount% for ecotax' d='Shop.Theme.Catalog' sprintf=['%amount%' => $product.ecotax.value]}
          {if $product.has_discount}
            {l s='(not impacted by the discount)' d='Shop.Theme.Catalog'}
          {/if}
        </p>
      {/if}
    {/block}

    {hook h='displayProductPriceBlock' product=$product type="weight" hook_origin='product_sheet'}

    <div class="tax-shipping-delivery-label" style="font-size:12px;color:var(--fsl-gray-500);margin-top:6px;">
      {if !$configuration.taxes_enabled}
        {l s='No tax' d='Shop.Theme.Catalog'}
      {elseif $configuration.display_taxes_label}
        {$product.labels.tax_long}
      {/if}
      {hook h='displayProductPriceBlock' product=$product type="price"}
      {hook h='displayProductPriceBlock' product=$product type="after_price"}
      {if $product.is_virtual == 0}
        {if $product.additional_delivery_times == 1}
          {if $product.delivery_information}
            <span class="delivery-information">{$product.delivery_information}</span>
          {/if}
        {elseif $product.additional_delivery_times == 2}
          {if $product.quantity >= $product.quantity_wanted}
            <span class="delivery-information">{$product.delivery_in_stock}</span>
          {elseif $product.add_to_cart_url}
            <span class="delivery-information">{$product.delivery_out_stock}</span>
          {/if}
        {/if}
      {/if}
    </div>
  </div>
{/if}
