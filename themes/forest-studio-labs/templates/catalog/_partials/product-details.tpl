<div class="js-product-details tab-pane fade{if !$product.description} in active{/if}"
     id="product-details"
     data-product="{$product.embedded_attributes|json_encode}"
     role="tabpanel"
     style="padding:24px 0;">

  {block name='product_reference'}
    {if isset($product_manufacturer->id) || (isset($product.reference_to_display) && $product.reference_to_display neq '')}
      <div style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--fsl-gray-100);">
        {if isset($product_manufacturer->id)}
          <div class="product-manufacturer" style="display:flex;align-items:center;gap:10px;">
            {if isset($manufacturer_image_url)}
              <a href="{$product_brand_url}">
                <img src="{$manufacturer_image_url}" alt="{$product_manufacturer->name}" loading="lazy"
                     style="max-height:40px;width:auto;border-radius:var(--fsl-radius);">
              </a>
            {else}
              <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);">{l s='Brand' d='Shop.Theme.Catalog'}</span>
              <a href="{$product_brand_url}" style="color:var(--fsl-forest);font-size:14px;font-weight:500;">{$product_manufacturer->name}</a>
            {/if}
          </div>
        {/if}
        {if isset($product.reference_to_display) && $product.reference_to_display neq ''}
          <div class="product-reference" style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);">{l s='Reference' d='Shop.Theme.Catalog'}</span>
            <span style="font-size:13px;color:var(--fsl-gray-700);font-family:monospace;">{$product.reference_to_display}</span>
          </div>
        {/if}
      </div>
    {/if}
  {/block}

  {block name='product_quantities'}
    {if $product.show_quantities}
      <div class="product-quantities" style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);">{l s='In stock' d='Shop.Theme.Catalog'}</span>
        <span data-stock="{$product.quantity}" data-allow-oosp="{$product.allow_oosp}"
              style="font-size:13px;color:var(--fsl-forest);font-weight:500;">{$product.quantity} {$product.quantity_label}</span>
      </div>
    {/if}
  {/block}

  {block name='product_availability_date'}
    {if $product.availability_date}
      <div class="product-availability-date" style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
        <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);">{l s='Availability date:' d='Shop.Theme.Catalog'}</span>
        <span style="font-size:13px;color:var(--fsl-gray-700);">{$product.availability_date}</span>
      </div>
    {/if}
  {/block}

  {block name='product_out_of_stock'}
    <div class="product-out-of-stock">
      {hook h='actionProductOutOfStock' product=$product}
    </div>
  {/block}

  {block name='product_features'}
    {if $product.grouped_features}
      <section class="product-features" style="margin-top:16px;">
        <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;color:var(--fsl-gray-500);margin-bottom:12px;">{l s='Data sheet' d='Shop.Theme.Catalog'}</p>
        <dl class="data-sheet" style="display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);overflow:hidden;">
          {foreach from=$product.grouped_features item=feature}
            <dt style="padding:10px 14px;background:var(--fsl-gray-50);font-size:13px;font-weight:500;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{$feature.name}</dt>
            <dd style="padding:10px 14px;font-size:13px;color:var(--fsl-gray-700);border-bottom:1px solid var(--fsl-gray-200);margin:0;">{$feature.value|escape:'htmlall'|nl2br nofilter}</dd>
          {/foreach}
        </dl>
      </section>
    {/if}
  {/block}

  {block name='product_specific_references'}
    {if !empty($product.specific_references)}
      <section class="product-features" style="margin-top:20px;">
        <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.12em;color:var(--fsl-gray-500);margin-bottom:12px;">{l s='Specific References' d='Shop.Theme.Catalog'}</p>
        <dl class="data-sheet" style="display:grid;grid-template-columns:1fr 1fr;gap:0;border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);overflow:hidden;">
          {foreach from=$product.specific_references item=reference key=key}
            <dt style="padding:10px 14px;background:var(--fsl-gray-50);font-size:13px;font-weight:500;color:var(--fsl-gray-600);border-bottom:1px solid var(--fsl-gray-200);">{$key}</dt>
            <dd style="padding:10px 14px;font-size:13px;color:var(--fsl-gray-700);border-bottom:1px solid var(--fsl-gray-200);margin:0;">{$reference}</dd>
          {/foreach}
        </dl>
      </section>
    {/if}
  {/block}

  {block name='product_condition'}
    {if $product.condition}
      <div class="product-condition" style="display:flex;align-items:center;gap:8px;margin-top:16px;">
        <link href="{$product.condition.schema_url}"/>
        <span style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-500);">{l s='Condition' d='Shop.Theme.Catalog'}</span>
        <span style="font-size:13px;color:var(--fsl-gray-700);">{$product.condition.label}</span>
      </div>
    {/if}
  {/block}
</div>
