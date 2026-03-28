<div id="quickview-modal-{$product.id}-{$product.id_product_attribute}"
     class="modal fade quickview"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content" style="border:none;border-radius:var(--fsl-radius-lg);overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.15);">

      <div class="modal-header" style="border-bottom:1px solid var(--fsl-gray-100);padding:12px 20px;display:flex;justify-content:flex-end;">
        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"
                style="background:none;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;color:var(--fsl-gray-500);">
          <span class="material-icons" style="font-size:20px;">close</span>
        </button>
      </div>

      <div class="modal-body" style="padding:24px;">
        <div class="row">
          <div class="col-md-6 col-sm-6 d-none d-sm-block">
            {block name='product_cover_thumbnails'}
              {include file='catalog/_partials/product-cover-thumbnails.tpl'}
            {/block}
            <div class="arrows js-arrows" style="display:flex;gap:8px;margin-top:8px;">
              <button class="js-arrow-up" style="background:none;border:1px solid var(--fsl-gray-200);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <i class="material-icons" style="font-size:18px;color:var(--fsl-gray-500);">keyboard_arrow_up</i>
              </button>
              <button class="js-arrow-down" style="background:none;border:1px solid var(--fsl-gray-200);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                <i class="material-icons" style="font-size:18px;color:var(--fsl-gray-500);">keyboard_arrow_down</i>
              </button>
            </div>
          </div>

          <div class="col-md-6 col-sm-6">
            <h1 style="font-family:var(--fsl-font-display);font-size:26px;font-weight:400;color:var(--fsl-gray-800);margin-bottom:12px;">{$product.name}</h1>
            {block name='product_prices'}
              {include file='catalog/_partials/product-prices.tpl'}
            {/block}
            {block name='product_description_short'}
              <div id="product-description-short" style="font-size:14px;color:var(--fsl-gray-600);line-height:1.7;margin:16px 0;">{$product.description_short nofilter}</div>
            {/block}
            {block name='product_buy'}
              <div class="product-actions js-product-actions">
                <form action="{$urls.pages.cart}" method="post" id="add-to-cart-or-refresh">
                  <input type="hidden" name="token" value="{$static_token}">
                  <input type="hidden" name="id_product" value="{$product.id}" id="product_page_product_id">
                  <input type="hidden" name="id_customization" value="{$product.id_customization}" id="product_customization_id" class="js-product-customization-id">
                  {block name='product_variants'}
                    {include file='catalog/_partials/product-variants.tpl'}
                  {/block}
                  {block name='product_add_to_cart'}
                    {include file='catalog/_partials/product-add-to-cart.tpl'}
                  {/block}
                  {block name='product_refresh'}{/block}
                </form>
              </div>
            {/block}
          </div>
        </div>
      </div>

      <div class="modal-footer" style="border-top:1px solid var(--fsl-gray-100);padding:12px 24px;background:var(--fsl-gray-50);">
        <div class="product-additional-info js-product-additional-info">
          {hook h='displayProductAdditionalInfo' product=$product}
        </div>
      </div>

    </div>
  </div>
</div>
