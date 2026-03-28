{block name='pack_miniature_item'}
  <article class="fsl-pack-product" style="border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;background:var(--fsl-white);">
    <div class="pack-product-container" style="display:flex;align-items:center;gap:12px;padding:12px;">
      <div class="thumb-mask" style="flex-shrink:0;width:60px;height:60px;">
        <a href="{$product.url}" title="{$product.name}" style="display:block;border-radius:8px;overflow:hidden;">
          {if !empty($product.default_image.medium)}
            <picture>
              {if !empty($product.default_image.medium.sources.avif)}<source srcset="{$product.default_image.medium.sources.avif}" type="image/avif">{/if}
              {if !empty($product.default_image.medium.sources.webp)}<source srcset="{$product.default_image.medium.sources.webp}" type="image/webp">{/if}
              <img
                src="{$product.default_image.medium.url}"
                {if !empty($product.default_image.legend)}
                  alt="{$product.default_image.legend}"
                {else}
                  alt="{$product.name}"
                {/if}
                loading="lazy"
                style="width:60px;height:60px;object-fit:cover;"
              >
            </picture>
          {else}
            <picture>
              {if !empty($urls.no_picture_image.bySize.medium_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.medium_default.sources.avif}" type="image/avif">{/if}
              {if !empty($urls.no_picture_image.bySize.medium_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.medium_default.sources.webp}" type="image/webp">{/if}
              <img src="{$urls.no_picture_image.bySize.medium_default.url}" loading="lazy" style="width:60px;height:60px;object-fit:cover;" />
            </picture>
          {/if}
        </a>
      </div>

      <div style="flex:1;min-width:0;">
        <div class="pack-product-name" style="font-size:13px;color:var(--fsl-gray-700);margin-bottom:4px;">
          <a href="{$product.url}" title="{$product.name}" style="color:inherit;text-decoration:none;">
            {$product.name}
          </a>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
          {if $showPackProductsPrice}
            <span style="font-size:13px;font-weight:600;color:var(--fsl-forest);">{$product.price}</span>
          {/if}
          <span style="font-size:12px;color:var(--fsl-gray-500);">x {$product.pack_quantity}</span>
        </div>
      </div>
    </div>
  </article>
{/block}
