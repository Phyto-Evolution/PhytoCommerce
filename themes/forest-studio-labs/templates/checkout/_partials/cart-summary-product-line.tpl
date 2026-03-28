{block name='cart_summary_product_line'}
  <div style="display:flex;gap:12px;align-items:flex-start;">
    <div style="flex-shrink:0;">
      <a href="{$product.url}" title="{$product.name}">
        {if $product.default_image}
          <picture>
            {if !empty($product.default_image.small.sources.avif)}<source srcset="{$product.default_image.small.sources.avif}" type="image/avif">{/if}
            {if !empty($product.default_image.small.sources.webp)}<source srcset="{$product.default_image.small.sources.webp}" type="image/webp">{/if}
            <img src="{$product.default_image.small.url}" alt="{$product.name}" loading="lazy"
                 style="width:48px;height:48px;object-fit:cover;border-radius:var(--fsl-radius);">
          </picture>
        {else}
          <picture>
            {if !empty($urls.no_picture_image.bySize.small_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.small_default.sources.avif}" type="image/avif">{/if}
            {if !empty($urls.no_picture_image.bySize.small_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.small_default.sources.webp}" type="image/webp">{/if}
            <img src="{$urls.no_picture_image.bySize.small_default.url}" loading="lazy"
                 style="width:48px;height:48px;object-fit:cover;border-radius:var(--fsl-radius);">
          </picture>
        {/if}
      </a>
    </div>
    <div style="flex:1;min-width:0;">
      <a href="{$product.url}" target="_blank" rel="noopener noreferrer nofollow"
         style="font-size:13px;font-weight:500;color:var(--fsl-gray-800);text-decoration:none;">{$product.name}</a>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
        <span style="font-size:12px;color:var(--fsl-gray-400);">x{$product.quantity}</span>
        <span style="font-size:13px;font-weight:600;color:var(--fsl-gray-700);">{$product.price}</span>
      </div>
      {hook h='displayProductPriceBlock' product=$product type="unit_price"}
      {foreach from=$product.attributes key="attribute" item="value"}
        <div style="font-size:11px;color:var(--fsl-gray-400);margin-top:2px;">
          <span style="font-weight:500;">{$attribute}:</span> {$value}
        </div>
      {/foreach}
    </div>
  </div>
{/block}
