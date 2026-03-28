{block name='product_miniature_item'}
<article class="fsl-product-card js-product-miniature" data-id-product="{$product.id_product}" data-id-product-attribute="{$product.id_product_attribute}"
         style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);overflow:hidden;border:1px solid var(--fsl-gray-200);transition:box-shadow .2s,transform .2s;height:100%;">

  {* Product image *}
  <div class="fsl-product-img" style="position:relative;overflow:hidden;">
    {include file='catalog/_partials/product-flags.tpl'}

    {block name='product_thumbnail'}
      {if $product.cover}
        <a href="{$product.url}" class="product-thumbnail">
          <picture>
            {if !empty($product.cover.bySize.home_default.sources.avif)}<source srcset="{$product.cover.bySize.home_default.sources.avif}" type="image/avif">{/if}
            {if !empty($product.cover.bySize.home_default.sources.webp)}<source srcset="{$product.cover.bySize.home_default.sources.webp}" type="image/webp">{/if}
            <img
              src="{$product.cover.bySize.home_default.url}"
              alt="{if !empty($product.cover.legend)}{$product.cover.legend}{else}{$product.name|truncate:30:'...'}{/if}"
              loading="lazy"
              data-full-size-image-url="{$product.cover.large.url}"
              width="{$product.cover.bySize.home_default.width}"
              height="{$product.cover.bySize.home_default.height}"
              style="width:100%;aspect-ratio:1/1;object-fit:cover;display:block;"
            />
          </picture>
        </a>
      {else}
        <a href="{$product.url}" class="product-thumbnail">
          <picture>
            {if !empty($urls.no_picture_image.bySize.home_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.avif}" type="image/avif">{/if}
            {if !empty($urls.no_picture_image.bySize.home_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.home_default.sources.webp}" type="image/webp">{/if}
            <img
              src="{$urls.no_picture_image.bySize.home_default.url}"
              loading="lazy"
              width="{$urls.no_picture_image.bySize.home_default.width}"
              height="{$urls.no_picture_image.bySize.home_default.height}"
              style="width:100%;aspect-ratio:1/1;object-fit:cover;display:block;"
            />
          </picture>
        </a>
      {/if}
    {/block}

    {* Quick view overlay *}
    <div class="fsl-product-overlay" style="position:absolute;bottom:0;left:0;right:0;background:rgba(74,124,89,0.9);padding:10px;text-align:center;transform:translateY(100%);transition:transform .2s;">
      {block name='quick_view'}
        <a class="quick-view js-quick-view" href="#" data-link-action="quickview"
           style="color:var(--fsl-white);font-size:12px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
          <i class="material-icons" style="font-size:14px">search</i>
          {l s='Quick view' d='Shop.Theme.Actions'}
        </a>
      {/block}
    </div>

    {* Variants *}
    {block name='product_variants'}
      {if $product.main_variants}
        <div style="position:absolute;bottom:8px;left:8px;">
          {include file='catalog/_partials/variant-links.tpl' variants=$product.main_variants}
        </div>
      {/if}
    {/block}
  </div>

  {* Product info *}
  <div class="fsl-product-info" style="padding:14px;">
    {block name='product_name'}
      {if $page.page_name == 'index'}
        <h3 class="product-title" style="font-family:var(--fsl-font-display);font-weight:400;font-size:1rem;margin:0 0 6px;line-height:1.3;">
          <a href="{$product.url}" style="color:var(--fsl-gray-700);text-decoration:none;">{$product.name|truncate:40:'...'}</a>
        </h3>
      {else}
        <h2 class="product-title" style="font-family:var(--fsl-font-display);font-weight:400;font-size:1rem;margin:0 0 6px;line-height:1.3;">
          <a href="{$product.url}" style="color:var(--fsl-gray-700);text-decoration:none;">{$product.name|truncate:40:'...'}</a>
        </h2>
      {/if}
    {/block}

    {block name='product_price_and_shipping'}
      {if $product.show_price}
        <div class="product-price-and-shipping" style="margin-top:6px;">
          {if $product.has_discount}
            {hook h='displayProductPriceBlock' product=$product type="old_price"}
            <span class="regular-price" style="font-size:12px;color:var(--fsl-gray-400);text-decoration:line-through;margin-right:6px;">{$product.regular_price}</span>
            {if $product.discount_type === 'percentage'}
              <span class="discount-percentage" style="font-size:12px;color:#e57373;font-weight:600;">{$product.discount_percentage}</span>
            {elseif $product.discount_type === 'amount'}
              <span class="discount-amount" style="font-size:12px;color:#e57373;font-weight:600;">{$product.discount_amount_to_display}</span>
            {/if}
            <br>
          {/if}
          {hook h='displayProductPriceBlock' product=$product type="before_price"}
          <span class="price" style="font-size:1rem;font-weight:600;color:var(--fsl-forest);">
            {capture name='custom_price'}{hook h='displayProductPriceBlock' product=$product type='custom_price' hook_origin='products_list'}{/capture}
            {if '' !== $smarty.capture.custom_price}
              {$smarty.capture.custom_price nofilter}
            {else}
              {$product.price}
            {/if}
          </span>
          {hook h='displayProductPriceBlock' product=$product type='unit_price'}
          {hook h='displayProductPriceBlock' product=$product type='weight'}
        </div>
      {/if}
    {/block}

    {block name='product_reviews'}
      {hook h='displayProductListReviews' product=$product}
    {/block}
  </div>

</article>
{/block}
