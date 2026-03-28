{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 style="margin:0">{l s='My Addresses' mod='fsl'}</h3>
    <a href="{$urls.pages.address}" class="btn btn-outline-primary btn-sm">
      <span class="material-icons" style="font-size:16px">add</span>
      {l s='New Address' mod='fsl'}
    </a>
  </div>

  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

  {if $addresses|count}
    <div class="row g-3">
      {foreach $addresses as $address}
        <div class="col-md-6">
          <div style="border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px;">
            <p style="font-weight:600;margin-bottom:8px">{$address.alias|escape:'htmlall':'UTF-8'}</p>
            <p style="font-size:13px;color:var(--fsl-gray-600);line-height:1.6;margin:0">
              {$address.firstname|escape:'htmlall':'UTF-8'} {$address.lastname|escape:'htmlall':'UTF-8'}<br>
              {$address.address1|escape:'htmlall':'UTF-8'}<br>
              {if $address.address2}{$address.address2|escape:'htmlall':'UTF-8'}<br>{/if}
              {$address.city|escape:'htmlall':'UTF-8'}, {$address.postcode|escape:'htmlall':'UTF-8'}<br>
              {$address.country|escape:'htmlall':'UTF-8'}
            </p>
            <div class="d-flex gap-3 mt-3">
              <a href="{$address.edit_url|escape:'htmlall':'UTF-8'}" style="font-size:12px;color:var(--fsl-forest);">
                {l s='Edit' mod='fsl'}
              </a>
              <a href="{$address.delete_url|escape:'htmlall':'UTF-8'}" style="font-size:12px;color:var(--fsl-gray-400);"
                 onclick="return confirm('{l s='Delete this address?' mod='fsl'}')">
                {l s='Delete' mod='fsl'}
              </a>
            </div>
          </div>
        </div>
      {/foreach}
    </div>
  {else}
    <p style="color:var(--fsl-gray-400)">{l s='No addresses saved yet.' mod='fsl'}</p>
  {/if}
</div>
{/block}
