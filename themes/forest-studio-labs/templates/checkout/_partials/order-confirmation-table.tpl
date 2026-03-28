<div id="order-items" style="margin-top:24px;">

  {block name='order_items_table_head'}
    <div style="display:grid;grid-template-columns:3fr 1fr 1fr 1fr;gap:8px;padding:10px 0;border-bottom:2px solid var(--fsl-gray-200);margin-bottom:16px;">
      <h3 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0;">{l s='Order items' d='Shop.Theme.Checkout'}</h3>
      <h3 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0;text-align:center;">{l s='Unit price' d='Shop.Theme.Checkout'}</h3>
      <h3 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0;text-align:center;">{l s='Quantity' d='Shop.Theme.Checkout'}</h3>
      <h3 style="font-size:13px;font-weight:600;color:var(--fsl-gray-600);margin:0;text-align:right;">{l s='Total products' d='Shop.Theme.Checkout'}</h3>
    </div>
  {/block}

  <div class="order-confirmation-table">
    {block name='order_confirmation_table'}
      {foreach from=$products item=product}
        <div class="order-line" style="display:grid;grid-template-columns:auto 1fr 1fr 1fr 1fr;gap:12px;align-items:center;padding:12px 0;border-bottom:1px solid var(--fsl-gray-100);">
          <div style="width:60px;">
            {if !empty($product.default_image)}
              <picture>
                {if !empty($product.default_image.medium.sources.avif)}<source srcset="{$product.default_image.medium.sources.avif}" type="image/avif">{/if}
                {if !empty($product.default_image.medium.sources.webp)}<source srcset="{$product.default_image.medium.sources.webp}" type="image/webp">{/if}
                <img src="{$product.default_image.medium.url}" loading="lazy"
                     style="width:60px;height:60px;object-fit:cover;border-radius:var(--fsl-radius);">
              </picture>
            {else}
              <picture>
                {if !empty($urls.no_picture_image.bySize.medium_default.sources.avif)}<source srcset="{$urls.no_picture_image.bySize.medium_default.sources.avif}" type="image/avif">{/if}
                {if !empty($urls.no_picture_image.bySize.medium_default.sources.webp)}<source srcset="{$urls.no_picture_image.bySize.medium_default.sources.webp}" type="image/webp">{/if}
                <img src="{$urls.no_picture_image.bySize.medium_default.url}" loading="lazy"
                     style="width:60px;height:60px;object-fit:cover;border-radius:var(--fsl-radius);">
              </picture>
            {/if}
          </div>
          <div style="font-size:14px;">
            {if $add_product_link}
              <a href="{$product.url}" target="_blank" style="color:var(--fsl-gray-800);font-weight:500;text-decoration:none;">{$product.name}</a>
            {else}
              <span style="font-weight:500;color:var(--fsl-gray-800);">{$product.name}</span>
            {/if}
            {if is_array($product.customizations) && $product.customizations|count}
              {foreach from=$product.customizations item="customization"}
                <a href="#" data-toggle="modal" data-target="#product-customizations-modal-{$customization.id_customization}"
                   style="display:block;font-size:12px;color:var(--fsl-forest);">
                  {l s='Product customization' d='Shop.Theme.Catalog'}
                </a>
                <div class="modal fade customization-modal" id="product-customizations-modal-{$customization.id_customization}" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content" style="border-radius:var(--fsl-radius-lg);">
                      <div class="modal-header" style="border-bottom:1px solid var(--fsl-gray-100);padding:16px 20px;">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
                        <h4 style="font-family:var(--fsl-font-display);font-size:18px;font-weight:400;margin:0;">{l s='Product customization' d='Shop.Theme.Catalog'}</h4>
                      </div>
                      <div class="modal-body" style="padding:20px;">
                        {foreach from=$customization.fields item="field"}
                          <div style="display:flex;gap:16px;padding:8px 0;border-bottom:1px solid var(--fsl-gray-100);">
                            <div style="width:120px;font-size:13px;font-weight:500;color:var(--fsl-gray-600);">{$field.label}</div>
                            <div style="font-size:13px;color:var(--fsl-gray-700);">
                              {if $field.type == 'text'}
                                {if (int)$field.id_module}{$field.text nofilter}{else}{$field.text}{/if}
                              {elseif $field.type == 'image'}
                                <img src="{$field.image.small.url}" loading="lazy" style="max-height:80px;border-radius:var(--fsl-radius);">
                              {/if}
                            </div>
                          </div>
                        {/foreach}
                      </div>
                    </div>
                  </div>
                </div>
              {/foreach}
            {/if}
            {hook h='displayProductPriceBlock' product=$product type="unit_price"}
          </div>
          <div style="font-size:14px;color:var(--fsl-gray-700);text-align:center;">{$product.price}</div>
          <div style="font-size:14px;color:var(--fsl-gray-700);text-align:center;">{$product.quantity}</div>
          <div style="font-size:14px;font-weight:600;color:var(--fsl-gray-800);text-align:right;">{$product.total}</div>
        </div>
      {/foreach}

      <div style="margin-top:16px;border-top:2px solid var(--fsl-gray-200);padding-top:16px;">
        <table style="width:100%;max-width:360px;margin-left:auto;font-size:14px;">
          {foreach $subtotals as $subtotal}
            {if $subtotal !== null && $subtotal.type !== 'tax' && $subtotal.label !== null}
              <tr>
                <td style="padding:5px 0;color:var(--fsl-gray-600);">{$subtotal.label}</td>
                <td style="padding:5px 0;text-align:right;color:var(--fsl-gray-700);">
                  {if 'discount' == $subtotal.type}-&nbsp;{/if}{$subtotal.value}
                </td>
              </tr>
            {/if}
          {/foreach}

          {if !$configuration.display_prices_tax_incl && $configuration.taxes_enabled}
            <tr>
              <td style="padding:5px 0;color:var(--fsl-gray-600);">{$totals.total.label}&nbsp;{$labels.tax_short}</td>
              <td style="padding:5px 0;text-align:right;color:var(--fsl-gray-700);">{$totals.total.value}</td>
            </tr>
            <tr style="border-top:1px solid var(--fsl-gray-200);">
              <td style="padding:10px 0 0;font-weight:700;font-size:16px;color:var(--fsl-gray-800);">{$totals.total_including_tax.label}</td>
              <td style="padding:10px 0 0;text-align:right;font-weight:700;font-size:16px;color:var(--fsl-forest);">{$totals.total_including_tax.value}</td>
            </tr>
          {else}
            <tr style="border-top:1px solid var(--fsl-gray-200);">
              <td style="padding:10px 0 0;font-weight:700;font-size:16px;color:var(--fsl-gray-800);">{$totals.total.label}&nbsp;{if $configuration.taxes_enabled && $configuration.display_taxes_label}{$labels.tax_short}{/if}</td>
              <td style="padding:10px 0 0;text-align:right;font-weight:700;font-size:16px;color:var(--fsl-forest);">{$totals.total.value}</td>
            </tr>
          {/if}
          {if $subtotals.tax !== null && $subtotals.tax.label !== null}
            <tr>
              <td colspan="2" style="padding:5px 0;font-size:12px;color:var(--fsl-gray-400);">
                <span>{l s='%label%:' sprintf=['%label%' => $subtotals.tax.label] d='Shop.Theme.Global'}</span>&nbsp;<span>{$subtotals.tax.value}</span>
              </td>
            </tr>
          {/if}
        </table>
      </div>
    {/block}
  </div>
</div>
