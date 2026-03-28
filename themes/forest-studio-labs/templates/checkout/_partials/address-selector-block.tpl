{block name='address_selector_blocks'}
  <div style="display:flex;flex-direction:column;gap:12px;">
    {foreach $addresses as $address}
      <article class="js-address-item address-item{if $address.id == $selected} selected{/if}"
               id="{$name|classname}-address-{$address.id}"
               style="border:1.5px solid {if $address.id == $selected}var(--fsl-forest){else}var(--fsl-gray-200){/if};border-radius:var(--fsl-radius-lg);padding:16px;transition:border-color .2s;">
        <label class="radio-block" style="display:flex;gap:12px;cursor:pointer;margin:0;">
          <input type="radio" name="{$name}" value="{$address.id}"
                 {if $address.id == $selected}checked{/if}
                 style="accent-color:var(--fsl-forest);width:16px;height:16px;flex-shrink:0;margin-top:3px;">
          <div>
            <span class="address-alias" style="font-size:14px;font-weight:600;color:var(--fsl-gray-800);display:block;margin-bottom:6px;">{$address.alias}</span>
            <div class="address" style="font-size:13px;color:var(--fsl-gray-600);line-height:1.6;">{$address.formatted nofilter}</div>
          </div>
        </label>
        {if $interactive}
          <div class="address-footer" style="display:flex;gap:16px;margin-top:12px;padding-top:12px;border-top:1px solid var(--fsl-gray-100);">
            <a class="edit-address" data-link-action="edit-address"
               href="{url entity='order' params=['id_address' => $address.id, 'editAddress' => $type, 'token' => $token]}"
               style="font-size:12px;color:var(--fsl-forest);text-decoration:none;display:flex;align-items:center;gap:4px;">
              <i class="material-icons" style="font-size:14px;">edit</i>{l s='Edit' d='Shop.Theme.Actions'}
            </a>
            <a class="delete-address" data-link-action="delete-address"
               href="{url entity='order' params=['id_address' => $address.id, 'deleteAddress' => true, 'token' => $token]}"
               style="font-size:12px;color:var(--fsl-gray-400);text-decoration:none;display:flex;align-items:center;gap:4px;">
              <i class="material-icons" style="font-size:14px;">delete_outline</i>{l s='Delete' d='Shop.Theme.Actions'}
            </a>
          </div>
        {/if}
      </article>
    {/foreach}
    {if $interactive}
      <button class="ps-hidden-by-js" type="submit"
              style="padding:10px 24px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;cursor:pointer;">
        {l s='Save' d='Shop.Theme.Actions'}
      </button>
    {/if}
  </div>
{/block}
