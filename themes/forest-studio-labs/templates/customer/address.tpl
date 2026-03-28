{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <h3>{if $address.id}{l s='Edit Address' mod='fsl'}{else}{l s='New Address' mod='fsl'}{/if}</h3>
  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

  <form method="post" action="{$urls.pages.address}">
    <input type="hidden" name="submitAddress" value="1">
    {if $address.id}<input type="hidden" name="id_address" value="{$address.id|intval}">{/if}

    <div class="row g-3">
      <div class="col-12">
        <div class="form-group">
          <label>{l s='Address Alias' mod='fsl'}</label>
          <input type="text" class="form-control" name="alias"
                 value="{$address.alias|default:'Home'|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='First name' mod='fsl'}</label>
          <input type="text" class="form-control" name="firstname"
                 value="{$address.firstname|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='Last name' mod='fsl'}</label>
          <input type="text" class="form-control" name="lastname"
                 value="{$address.lastname|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-12">
        <div class="form-group">
          <label>{l s='Address' mod='fsl'}</label>
          <input type="text" class="form-control" name="address1"
                 value="{$address.address1|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-12">
        <div class="form-group">
          <label>{l s='Address line 2 (optional)' mod='fsl'}</label>
          <input type="text" class="form-control" name="address2"
                 value="{$address.address2|escape:'htmlall':'UTF-8'}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='City' mod='fsl'}</label>
          <input type="text" class="form-control" name="city"
                 value="{$address.city|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='Postcode' mod='fsl'}</label>
          <input type="text" class="form-control" name="postcode"
                 value="{$address.postcode|escape:'htmlall':'UTF-8'}">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='Phone' mod='fsl'}</label>
          <input type="tel" class="form-control" name="phone"
                 value="{$address.phone|escape:'htmlall':'UTF-8'}">
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button type="submit" class="btn btn-primary">{l s='Save Address' mod='fsl'}</button>
      <a href="{$urls.pages.addresses}" class="btn btn-light">{l s='Cancel' mod='fsl'}</a>
    </div>
  </form>
</div>
{/block}
