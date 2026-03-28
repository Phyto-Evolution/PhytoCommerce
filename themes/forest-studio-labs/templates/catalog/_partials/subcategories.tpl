{if !empty($subcategories)}
  {if (isset($display_subcategories) && $display_subcategories eq 1) || !isset($display_subcategories)}
    <div id="subcategories" class="fsl-subcategories" style="margin-bottom:32px;">
      <h2 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.4rem;margin-bottom:16px;">
        {l s='Subcategories' d='Shop.Theme.Category'}
      </h2>
      <div class="row g-3">
        {foreach from=$subcategories item=subcategory}
          <div class="col-6 col-md-4 col-lg-3">
            <a href="{$subcategory.url}" class="fsl-subcategory-card" style="display:block;text-decoration:none;border-radius:var(--fsl-radius-lg);overflow:hidden;border:1px solid var(--fsl-gray-200);background:var(--fsl-white);transition:box-shadow .2s;"
               title="{$subcategory.name|escape:'html':'UTF-8'}">
              {if !empty($subcategory.image.large.url)}
                <picture>
                  {if !empty($subcategory.image.large.sources.avif)}<source srcset="{$subcategory.image.large.sources.avif}" type="image/avif">{/if}
                  {if !empty($subcategory.image.large.sources.webp)}<source srcset="{$subcategory.image.large.sources.webp}" type="image/webp">{/if}
                  <img class="img-fluid" src="{$subcategory.image.large.url}" alt="{$subcategory.name|escape:'html':'UTF-8'}" loading="lazy" style="width:100%;aspect-ratio:1/1;object-fit:cover;">
                </picture>
              {/if}
              <div style="padding:12px;">
                <h5 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1rem;color:var(--fsl-gray-700);margin:0 0 4px;">
                  {$subcategory.name|truncate:25:'...'|escape:'html':'UTF-8'}
                </h5>
                {if $subcategory.description}
                  <div style="font-size:12px;color:var(--fsl-gray-500);">{$subcategory.description|unescape:'html'|truncate:60:'...' nofilter}</div>
                {/if}
              </div>
            </a>
          </div>
        {/foreach}
      </div>
    </div>
  {/if}
{/if}
