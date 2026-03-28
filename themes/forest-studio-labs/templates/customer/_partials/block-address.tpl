{block name='address_block_item'}
  <article id="address-{$address.id}" class="address" data-id-address="{$address.id}"
           style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px;display:flex;flex-direction:column;gap:12px;">
    <div class="address-body">
      <h4 style="font-family:var(--fsl-font-display);font-size:16px;font-weight:500;color:var(--fsl-gray-800);margin:0 0 8px;">{$address.alias}</h4>
      <address style="font-style:normal;font-size:14px;color:var(--fsl-gray-600);line-height:1.7;">{$address.formatted nofilter}</address>
      {hook h='displayAdditionalCustomerAddressFields' address=$address}
    </div>

    {block name='address_block_item_actions'}
      <div class="address-footer" style="display:flex;gap:16px;padding-top:12px;border-top:1px solid var(--fsl-gray-100);">
        <a href="{url entity=address id=$address.id}" data-link-action="edit-address"
           style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:var(--fsl-forest);text-decoration:none;">
          <i class="material-icons" style="font-size:14px;">edit</i>
          <span>{l s='Update' d='Shop.Theme.Actions'}</span>
        </a>
        <a href="{url entity=address id=$address.id params=['delete' => 1, 'token' => $token]}" data-link-action="delete-address"
           style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:var(--fsl-gray-400);text-decoration:none;">
          <i class="material-icons" style="font-size:14px;">delete_outline</i>
          <span>{l s='Delete' d='Shop.Theme.Actions'}</span>
        </a>
      </div>
    {/block}
  </article>
{/block}
