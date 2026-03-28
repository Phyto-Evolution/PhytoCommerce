<section class="product-customization js-product-customization">
  {if !$configuration.is_catalog}
    <div style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:24px;margin-top:20px;">
      <p style="font-family:var(--fsl-font-display);font-size:20px;font-weight:500;color:var(--fsl-gray-800);margin-bottom:8px;">{l s='Product customization' d='Shop.Theme.Catalog'}</p>
      <p style="font-size:13px;color:var(--fsl-gray-500);margin-bottom:20px;">{l s="Don't forget to save your customization to be able to add to cart" d='Shop.Forms.Help'}</p>

      {block name='product_customization_form'}
        <form method="post" action="{$product.url}" enctype="multipart/form-data">
          <ul style="list-style:none;padding:0;margin:0 0 20px;">
            {foreach from=$customizations.fields item="field"}
              <li class="product-customization-item" style="margin-bottom:20px;">
                <label for="field-{$field.input_name}"
                       style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:8px;">
                  {$field.label}
                  {if $field.required}<span style="color:#e53935;margin-left:2px;">*</span>{/if}
                </label>
                {if $field.type == 'text'}
                  <textarea
                    placeholder="{l s='Your message here' d='Shop.Forms.Help'}"
                    class="product-message"
                    maxlength="250"
                    {if $field.required}required{/if}
                    name="{$field.input_name}"
                    id="field-{$field.input_name}"
                    style="width:100%;min-height:100px;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;color:var(--fsl-gray-700);resize:vertical;"
                  ></textarea>
                  <small style="display:block;text-align:right;font-size:11px;color:var(--fsl-gray-400);margin-top:4px;">{l s='250 char. max' d='Shop.Forms.Help'}</small>
                  {if $field.text !== ''}
                    <div style="margin-top:10px;padding:10px 14px;background:var(--fsl-light-green);border-radius:var(--fsl-radius);font-size:13px;">
                      <span style="font-weight:600;color:var(--fsl-forest);">{l s='Your customization:' d='Shop.Theme.Catalog'}</span>
                      <span style="color:var(--fsl-gray-700);margin-left:6px;">{$field.text}</span>
                    </div>
                  {/if}
                {elseif $field.type == 'image'}
                  {if $field.is_customized}
                    <div style="margin-bottom:10px;">
                      <img src="{$field.image.small.url}" loading="lazy" style="border-radius:var(--fsl-radius);max-height:80px;">
                      <a class="remove-image" href="{$field.remove_image_url}" rel="nofollow"
                         style="display:inline-block;margin-left:10px;font-size:12px;color:#e53935;">{l s='Remove Image' d='Shop.Theme.Actions'}</a>
                    </div>
                  {/if}
                  <div class="custom-file" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                    <label for="field-{$field.input_name}"
                           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:var(--fsl-forest);color:var(--fsl-white);border-radius:var(--fsl-radius);font-size:13px;font-weight:500;cursor:pointer;">
                      <span class="material-icons" style="font-size:16px;">upload_file</span>
                      {l s='Choose file' d='Shop.Theme.Actions'}
                    </label>
                    <span class="js-file-name" style="font-size:13px;color:var(--fsl-gray-500);">{l s='No selected file' d='Shop.Forms.Help'}</span>
                    <input class="file-input js-file-input"
                           {if $field.required}required{/if}
                           type="file"
                           name="{$field.input_name}"
                           id="field-{$field.input_name}"
                           style="position:absolute;opacity:0;width:0;height:0;">
                  </div>
                  {assign var=authExtensions value=' .'|implode:constant('ImageManager::EXTENSIONS_SUPPORTED')}
                  <small style="display:block;font-size:11px;color:var(--fsl-gray-400);margin-top:6px;">.{$authExtensions}</small>
                {/if}
              </li>
            {/foreach}
          </ul>
          <div style="text-align:right;">
            <button type="submit" name="submitCustomizedData"
                    style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
              <span class="material-icons" style="font-size:16px;">save</span>
              {l s='Save Customization' d='Shop.Theme.Actions'}
            </button>
          </div>
        </form>
      {/block}
    </div>
  {/if}
</section>
