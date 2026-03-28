{capture assign="productClasses"}{if !empty($productClass)}{$productClass}{else}col-6 col-md-4{/if}{/capture}

<div class="products row{if !empty($cssClass)} {$cssClass}{/if}">
  {foreach from=$products item="product" key="position"}
    <div class="{$productClasses} mb-4">
      {include file="catalog/_partials/miniatures/product.tpl" product=$product position=$position}
    </div>
  {/foreach}
</div>
