{block name='product_flags'}
  <ul class="product-flags js-product-flags" style="position:absolute;top:10px;left:10px;list-style:none;padding:0;margin:0;z-index:10;display:flex;flex-wrap:wrap;gap:4px;">
    {foreach from=$product.flags item=flag}
      <li class="product-flag {$flag.type}"
          style="background:{if $flag.type == 'new'}var(--fsl-sage){elseif $flag.type == 'on-sale'}#e57373{else}var(--fsl-forest){/if};color:var(--fsl-white);font-size:11px;font-weight:600;padding:3px 8px;border-radius:4px;letter-spacing:.04em;text-transform:uppercase;">
        {$flag.label}
      </li>
    {/foreach}
  </ul>
{/block}
