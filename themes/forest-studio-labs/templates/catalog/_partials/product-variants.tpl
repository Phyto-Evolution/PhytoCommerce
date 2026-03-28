<div class="product-variants js-product-variants" style="margin-bottom:16px;">
  {foreach from=$groups key=id_attribute_group item=group}
    {if !empty($group.attributes)}
    <div class="product-variants-item" style="margin-bottom:12px;">
      <span class="control-label" style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--fsl-gray-600);display:block;margin-bottom:8px;">
        {$group.name}{l s=': ' d='Shop.Theme.Catalog'}
        {foreach from=$group.attributes key=id_attribute item=group_attribute}
          {if $group_attribute.selected}<span style="font-weight:400;color:var(--fsl-gray-700);">{$group_attribute.name}</span>{/if}
        {/foreach}
      </span>
      {if $group.group_type == 'select'}
        <select
          class="form-control fsl-select"
          id="group_{$id_attribute_group}"
          aria-label="{$group.name}"
          data-product-attribute="{$id_attribute_group}"
          name="group[{$id_attribute_group}]"
          style="border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:8px 12px;font-family:var(--fsl-font-body);font-size:13px;max-width:200px;">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            <option value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} selected="selected"{/if}>{$group_attribute.name}</option>
          {/foreach}
        </select>
      {elseif $group.group_type == 'color'}
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:6px;" id="group_{$id_attribute_group}">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            <li>
              <label aria-label="{$group_attribute.name}" title="{$group_attribute.name}"
                     style="display:block;cursor:pointer;">
                <input class="input-color" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} checked="checked"{/if}
                       style="position:absolute;opacity:0;width:0;height:0;">
                <span
                  style="display:block;width:24px;height:24px;border-radius:50%;{if $group_attribute.selected}box-shadow:0 0 0 2px var(--fsl-forest),0 0 0 4px var(--fsl-white);{else}border:2px solid var(--fsl-gray-300);{/if}"
                  {if $group_attribute.texture}
                    style="background-image: url({$group_attribute.texture})"
                  {elseif $group_attribute.html_color_code}
                    style="background-color: {$group_attribute.html_color_code}"
                  {/if}
                ><span class="sr-only">{$group_attribute.name}</span></span>
              </label>
            </li>
          {/foreach}
        </ul>
      {elseif $group.group_type == 'radio'}
        <ul style="list-style:none;padding:0;margin:0;display:flex;flex-wrap:wrap;gap:6px;" id="group_{$id_attribute_group}">
          {foreach from=$group.attributes key=id_attribute item=group_attribute}
            <li>
              <label style="cursor:pointer;">
                <input class="input-radio" type="radio" data-product-attribute="{$id_attribute_group}" name="group[{$id_attribute_group}]" value="{$id_attribute}" title="{$group_attribute.name}"{if $group_attribute.selected} checked="checked"{/if}
                       style="position:absolute;opacity:0;width:0;height:0;">
                <span class="radio-label"
                      style="display:inline-block;padding:5px 12px;border-radius:var(--fsl-radius-lg);border:1.5px solid {if $group_attribute.selected}var(--fsl-forest){else}var(--fsl-gray-200){/if};font-size:13px;color:{if $group_attribute.selected}var(--fsl-forest){else}var(--fsl-gray-700){/if};background:{if $group_attribute.selected}var(--fsl-light-green){else}var(--fsl-white){/if};">
                  {$group_attribute.name}
                </span>
              </label>
            </li>
          {/foreach}
        </ul>
      {/if}
    </div>
    {/if}
  {/foreach}
</div>
