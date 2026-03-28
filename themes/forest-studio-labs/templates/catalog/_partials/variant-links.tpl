<div class="variant-links" style="display:flex;flex-wrap:wrap;gap:4px;">
  {foreach from=$variants item=variant}
    <a href="{$variant.url}"
       class="{$variant.type}"
       title="{$variant.name}"
       aria-label="{$variant.name}"
       style="display:inline-block;width:18px;height:18px;border-radius:50%;border:2px solid var(--fsl-white);box-shadow:0 0 0 1px var(--fsl-gray-300);"
      {if $variant.texture} style="background-image: url({$variant.texture})"
      {elseif $variant.html_color_code} style="background-color: {$variant.html_color_code}" {/if}
    ></a>
  {/foreach}
  <span class="js-count count" style="font-size:11px;color:var(--fsl-gray-500);"></span>
</div>
