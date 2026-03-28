{block name='brand_miniature_item'}
  <li class="fsl-brand-item" style="display:flex;align-items:flex-start;gap:20px;padding:20px 0;border-bottom:1px solid var(--fsl-gray-200);">
    <div class="brand-img" style="flex-shrink:0;">
      <a href="{$brand.url}">
        <img src="{$brand.image}" alt="{$brand.name}" loading="lazy"
             style="width:80px;height:80px;object-fit:contain;border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);padding:8px;">
      </a>
    </div>
    <div style="flex:1;">
      <h3 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.1rem;margin:0 0 6px;">
        <a href="{$brand.url}" style="color:var(--fsl-gray-700);text-decoration:none;">{$brand.name}</a>
      </h3>
      <div style="font-size:13px;color:var(--fsl-gray-500);margin-bottom:10px;">{$brand.text nofilter}</div>
      <a href="{$brand.url}" class="btn btn-sm btn-outline-primary" style="font-size:12px;">
        {l s='View products' d='Shop.Theme.Actions'}
        <span style="font-size:11px;color:var(--fsl-gray-400);margin-left:4px;">({$brand.nb_products})</span>
      </a>
    </div>
  </li>
{/block}
