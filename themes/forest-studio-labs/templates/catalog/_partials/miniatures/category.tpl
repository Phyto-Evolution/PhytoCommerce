{block name='category_miniature_item'}
  <section class="fsl-category-miniature" style="text-align:center;">
    <a href="{$category.url}" style="display:block;border-radius:var(--fsl-radius-lg);overflow:hidden;margin-bottom:12px;">
      <picture>
        {if !empty($category.image.medium.sources.avif)}<source srcset="{$category.image.medium.sources.avif}" type="image/avif">{/if}
        {if !empty($category.image.medium.sources.webp)}<source srcset="{$category.image.medium.sources.webp}" type="image/webp">{/if}
        <img
          src="{$category.image.medium.url}"
          alt="{if !empty($category.image.legend)}{$category.image.legend}{else}{$category.name}{/if}"
          loading="lazy"
          width="250"
          height="250"
          style="width:100%;aspect-ratio:1/1;object-fit:cover;display:block;"
        >
      </picture>
    </a>
    <h2 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.1rem;margin-bottom:6px;">
      <a href="{$category.url}" style="color:var(--fsl-gray-700);text-decoration:none;">{$category.name}</a>
    </h2>
    <div class="category-description" style="font-size:13px;color:var(--fsl-gray-500);">{$category.description nofilter}</div>
  </section>
{/block}
