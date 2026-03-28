{extends file='customer/my-account.tpl'}

{block name='page_content'}
<div class="fsl-account-card">
  <h3>{l s='Personal Information' mod='fsl'}</h3>
  {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

  <form method="post" action="{$urls.pages.identity}">
    <input type="hidden" name="submitIdentity" value="1">

    <div class="row g-3">
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='First name' mod='fsl'}</label>
          <input type="text" class="form-control" name="firstname"
                 value="{$customer.firstname|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='Last name' mod='fsl'}</label>
          <input type="text" class="form-control" name="lastname"
                 value="{$customer.lastname|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-12">
        <div class="form-group">
          <label>{l s='Email' mod='fsl'}</label>
          <input type="email" class="form-control" name="email"
                 value="{$customer.email|escape:'htmlall':'UTF-8'}" required>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='Current password' mod='fsl'}</label>
          <input type="password" class="form-control" name="old_passwd" autocomplete="current-password">
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label>{l s='New password (optional)' mod='fsl'}</label>
          <input type="password" class="form-control" name="passwd" autocomplete="new-password" minlength="8">
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary mt-4">{l s='Save Changes' mod='fsl'}</button>
  </form>
</div>
{/block}
