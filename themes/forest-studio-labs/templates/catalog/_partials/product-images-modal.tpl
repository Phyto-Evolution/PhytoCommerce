<div class="modal fade js-product-images-modal" id="product-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border:none;border-radius:var(--fsl-radius-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.18);">
      <div class="modal-header" style="border-bottom:1px solid var(--fsl-gray-100);padding:12px 16px;display:flex;justify-content:flex-end;align-items:center;">
        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"
                style="background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;color:var(--fsl-gray-500);">
          <span class="material-icons" style="font-size:20px;">close</span>
        </button>
      </div>
      <div class="modal-body" style="padding:20px;display:flex;gap:16px;flex-direction:row-reverse;">
        {assign var=imagesCount value=$product.images|count}

        <figure style="flex:1;margin:0;">
          {if $product.default_image}
            <picture>
              {if !empty($product.default_image.bySize.large_default.sources.avif)}<source srcset="{$product.default_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
              {if !empty($product.default_image.bySize.large_default.sources.webp)}<source srcset="{$product.default_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
              <img
                class="js-modal-product-cover product-cover-modal"
                src="{$product.default_image.bySize.large_default.url}"
                width="{$product.default_image.bySize.large_default.width}"
                height="{$product.default_image.bySize.large_default.height}"
                {if !empty($product.default_image.legend)}
                  alt="{$product.default_image.legend}"
                  title="{$product.default_image.legend}"
                {else}
                  alt="{$product.name}"
                {/if}
                style="width:100%;height:auto;display:block;border-radius:var(--fsl-radius);"
              >
            </picture>
          {else}
            <picture>
              {if !empty($urls.no_picture_image.bySize.large_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.large_default.sources.avif}" type="image/avif">{/if}
              {if !empty($urls.no_picture_image.bySize.large_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.large_default.sources.webp}" type="image/webp">{/if}
              <img src="{$urls.no_picture_image.bySize.large_default.url}" loading="lazy"
                   width="{$urls.no_picture_image.bySize.large_default.width}"
                   height="{$urls.no_picture_image.bySize.large_default.height}"
                   style="width:100%;height:auto;display:block;border-radius:var(--fsl-radius);">
            </picture>
          {/if}
          <figcaption style="margin-top:12px;">
            {block name='product_description_short'}
              <div id="product-description-short" style="font-size:14px;color:var(--fsl-gray-600);line-height:1.6;">{$product.description_short nofilter}</div>
            {/block}
          </figcaption>
        </figure>

        <aside id="thumbnails" class="thumbnails js-thumbnails"
               style="width:80px;flex-shrink:0;display:flex;flex-direction:column;gap:8px;position:relative;">
          {block name='product_images'}
            <div class="js-modal-mask mask {if $imagesCount <= 5}nomargin{/if}" style="overflow:hidden;">
              <ul class="product-images js-modal-product-images" style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                {foreach from=$product.images item=image}
                  <li class="thumb-container js-thumb-container" style="cursor:pointer;">
                    <picture>
                      {if !empty($image.medium.sources.avif)}<source srcset="{$image.medium.sources.avif}" type="image/avif">{/if}
                      {if !empty($image.medium.sources.webp)}<source srcset="{$image.medium.sources.webp}" type="image/webp">{/if}
                      <img
                        class="thumb js-modal-thumb"
                        src="{$image.medium.url}"
                        data-image-large-src="{$image.large.url}"
                        {if !empty($image.large.sources)}data-image-large-sources="{$image.large.sources|@json_encode}"{/if}
                        {if !empty($image.legend)}
                          alt="{$image.legend}"
                          title="{$image.legend}"
                        {else}
                          alt="{$product.name}"
                        {/if}
                        width="{$image.medium.width}"
                        height="80"
                        style="width:72px;height:72px;object-fit:cover;border-radius:var(--fsl-radius);border:2px solid var(--fsl-gray-200);transition:border-color .2s;"
                      >
                    </picture>
                  </li>
                {/foreach}
              </ul>
            </div>
          {/block}
          {if $imagesCount > 5}
            <div class="arrows js-modal-arrows" style="display:flex;flex-direction:column;align-items:center;gap:4px;margin-top:4px;">
              <button class="js-modal-arrow-up" style="background:none;border:1px solid var(--fsl-gray-200);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <i class="material-icons" style="font-size:16px;color:var(--fsl-gray-500);">keyboard_arrow_up</i>
              </button>
              <button class="js-modal-arrow-down" style="background:none;border:1px solid var(--fsl-gray-200);border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <i class="material-icons" style="font-size:16px;color:var(--fsl-gray-500);">keyboard_arrow_down</i>
              </button>
            </div>
          {/if}
        </aside>
      </div>
    </div>
  </div>
</div>
