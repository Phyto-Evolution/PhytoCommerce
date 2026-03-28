<div class="product-add-to-cart js-product-add-to-cart">
  {if !$configuration.is_catalog}
    <p class="control-label" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-600);margin-bottom:8px;">
      {l s='Quantity' d='Shop.Theme.Catalog'}
    </p>

    {block name='product_quantity'}
      <div class="product-quantity" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
        <div class="qty-input-group" style="display:flex;align-items:center;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);overflow:hidden;">
          <button class="js-touchspin-down" type="button"
                  style="width:36px;height:44px;border:none;background:var(--fsl-off-white);font-size:18px;cursor:pointer;color:var(--fsl-gray-600);">−</button>
          <input
            type="number"
            name="qty"
            id="quantity_wanted"
            inputmode="numeric"
            pattern="[0-9]*"
            {if $product.quantity_wanted}
              value="{$product.quantity_wanted}"
              min="{$product.minimal_quantity}"
            {else}
              value="1"
              min="1"
            {/if}
            class="js-cart-product-quantity"
            aria-label="{l s='Quantity' d='Shop.Theme.Actions'}"
            style="width:50px;height:44px;text-align:center;border:none;border-left:1px solid var(--fsl-gray-200);border-right:1px solid var(--fsl-gray-200);font-family:var(--fsl-font-body);font-size:15px;outline:none;"
          >
          <button class="js-touchspin-up" type="button"
                  style="width:36px;height:44px;border:none;background:var(--fsl-off-white);font-size:18px;cursor:pointer;color:var(--fsl-gray-600);">+</button>
        </div>

        <div class="add" style="flex:1;">
          <button
            class="btn btn-primary add-to-cart"
            data-button-action="add-to-cart"
            type="submit"
            {if !$product.add_to_cart_url}disabled{/if}
            style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;"
          >
            <i class="material-icons" style="font-size:18px">shopping_bag</i>
            {l s='Add to cart' d='Shop.Theme.Actions'}
          </button>
        </div>

        {hook h='displayProductActions' product=$product}
      </div>
    {/block}

    {block name='product_availability'}
      <span id="product-availability" class="js-product-availability" style="font-size:13px;display:flex;align-items:center;gap:6px;margin-bottom:8px;">
        {if $product.show_availability && $product.availability_message}
          {if $product.availability == 'available'}
            <i class="material-icons" style="font-size:16px;color:var(--fsl-sage)">check_circle</i>
          {elseif $product.availability == 'last_remaining_items'}
            <i class="material-icons" style="font-size:16px;color:#f59e0b">info</i>
          {else}
            <i class="material-icons" style="font-size:16px;color:#e57373">cancel</i>
          {/if}
          <span style="color:var(--fsl-gray-600);">{$product.availability_message}</span>
        {/if}
      </span>
    {/block}

    {block name='product_minimal_quantity'}
      <p class="product-minimal-quantity js-product-minimal-quantity" style="font-size:12px;color:var(--fsl-gray-500);">
        {if $product.minimal_quantity > 1}
          {l
          s='The minimum purchase order quantity for the product is %quantity%.'
          d='Shop.Theme.Checkout'
          sprintf=['%quantity%' => $product.minimal_quantity]
          }
        {/if}
      </p>
    {/block}
  {/if}
</div>
