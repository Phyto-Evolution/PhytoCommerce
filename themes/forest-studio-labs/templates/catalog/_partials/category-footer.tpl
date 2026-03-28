<div id="js-product-list-footer">
  {if isset($category) && $category.additional_description && $listing.pagination.items_shown_from == 1}
    <div class="fsl-category-footer" style="margin-top:40px;padding:24px;background:var(--fsl-off-white);border-radius:var(--fsl-radius-lg);">
      <div class="category-additional-description" style="color:var(--fsl-gray-600);font-size:14px;line-height:1.7;">
        {$category.additional_description nofilter}
      </div>
    </div>
  {/if}
</div>
