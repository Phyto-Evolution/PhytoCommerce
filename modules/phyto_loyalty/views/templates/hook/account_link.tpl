{**
 * views/templates/hook/account_link.tpl
 * "My Points" link — displayCustomerAccount
 *
 * @author PhytoCommerce
 *}

<div class="col-sm-6 col-lg-3">
  <a href="{$phyto_loyalty_account_url|escape:'html':'UTF-8'}" class="account-list-item phyto-loyalty-account-link">
    <i class="material-icons">loyalty</i>
    {l s='My Loyalty Points' mod='phyto_loyalty'}
  </a>
</div>
