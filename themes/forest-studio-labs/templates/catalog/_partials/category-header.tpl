<div id="js-product-list-header">
  {if $listing.pagination.items_shown_from == 1}
    <div class="fsl-category-header" style="margin-bottom:24px;">
      {if !empty($category.image.large.url)}
        <div class="fsl-category-cover" style="position:relative;border-radius:var(--fsl-radius-lg);overflow:hidden;margin-bottom:16px;max-height:200px;">
          <picture>
            {if !empty($category.image.large.sources.avif)}<source srcset="{$category.image.large.sources.avif}" type="image/avif">{/if}
            {if !empty($category.image.large.sources.webp)}<source srcset="{$category.image.large.sources.webp}" type="image/webp">{/if}
            <img src="{$category.image.large.url}" alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}" loading="lazy" style="width:100%;height:200px;object-fit:cover;">
          </picture>
        </div>
      {/if}
      <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:8px;">{$category.name}</h1>
      {if $category.description}
        <div class="category-description" style="color:var(--fsl-gray-500);font-size:15px;">{$category.description nofilter}</div>
      {/if}
    </div>
  {/if}
</div>
