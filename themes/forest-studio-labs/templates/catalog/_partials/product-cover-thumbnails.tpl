<div class="images-container js-images-container">
  {block name='product_cover'}
    <div class="product-cover" style="border-radius:var(--fsl-radius-lg);overflow:hidden;margin-bottom:12px;position:relative;">
      {if $product.default_image}
        <picture>
          {if !empty($product.default_image.bySize.large_default.sources.avif)}<source srcset="{$product.default_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
          {if !empty($product.default_image.bySize.large_default.sources.webp)}<source srcset="{$product.default_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
          <img
            class="js-qv-product-cover img-fluid"
            src="{$product.default_image.bySize.large_default.url}"
            {if !empty($product.default_image.legend)}
              alt="{$product.default_image.legend}"
              title="{$product.default_image.legend}"
            {else}
              alt="{$product.name}"
            {/if}
            loading="lazy"
            width="{$product.default_image.bySize.large_default.width}"
            height="{$product.default_image.bySize.large_default.height}"
            style="width:100%;height:auto;display:block;"
          >
        </picture>
        <div class="layer" data-toggle="modal" data-target="#product-modal"
             style="position:absolute;bottom:12px;right:12px;background:rgba(255,255,255,.85);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
          <i class="material-icons" style="font-size:18px;color:var(--fsl-forest)">search</i>
        </div>
      {else}
        <picture>
          {if !empty($urls.no_picture_image.bySize.large_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
          {if !empty($urls.no_picture_image.bySize.large_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
          <img
            class="img-fluid"
            src="{$urls.no_picture_image.bySize.large_default.url}"
            loading="lazy"
            width="{$urls.no_picture_image.bySize.large_default.width}"
            height="{$urls.no_picture_image.bySize.large_default.height}"
            style="width:100%;height:auto;display:block;"
          >
        </picture>
      {/if}
    </div>
  {/block}

  {block name='product_images'}
    <div class="js-qv-mask mask" style="overflow:hidden;">
      <ul class="product-images js-qv-product-images" style="list-style:none;padding:0;margin:0;display:flex;gap:8px;flex-wrap:wrap;">
        {foreach from=$product.images item=image}
          <li class="thumb-container js-thumb-container" style="cursor:pointer;">
            <picture>
              {if !empty($image.bySize.small_default.sources.avif)}<source srcset="{$image.bySize.small_default.sources.avif}" type="image/avif">{/if}
              {if !empty($image.bySize.small_default.sources.webp)}<source srcset="{$image.bySize.small_default.sources.webp}" type="image/webp">{/if}
              <img
                class="thumb js-thumb {if $image.id_image == $product.default_image.id_image}selected js-thumb-selected{/if}"
                data-image-medium-src="{$image.bySize.medium_default.url}"
                {if !empty($image.bySize.medium_default.sources)}data-image-medium-sources="{$image.bySize.medium_default.sources|@json_encode}"{/if}
                data-image-large-src="{$image.bySize.large_default.url}"
                {if !empty($image.bySize.large_default.sources)}data-image-large-sources="{$image.bySize.large_default.sources|@json_encode}"{/if}
                src="{$image.bySize.small_default.url}"
                {if !empty($image.legend)}
                  alt="{$image.legend}"
                  title="{$image.legend}"
                {else}
                  alt="{$product.name}"
                {/if}
                loading="lazy"
                width="{$product.default_image.bySize.small_default.width}"
                height="{$product.default_image.bySize.small_default.height}"
                style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:2px solid var(--fsl-gray-200);transition:border-color .2s;"
              >
            </picture>
          </li>
        {/foreach}
      </ul>
    </div>
  {/block}
  {hook h='displayAfterProductThumbs' product=$product}
</div>
