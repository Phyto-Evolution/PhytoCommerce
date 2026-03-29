{**
 * views/templates/hook/account_block.tpl
 * Points balance widget — displayMyAccountBlock
 *
 * @author PhytoCommerce
 *}

<div class="phyto-loyalty-sidebar-block">
  <a href="{$phyto_loyalty_account_url|escape:'html':'UTF-8'}" class="phyto-loyalty-sidebar-link">
    <span class="phyto-loyalty-sidebar-icon">&#9733;</span>
    <span class="phyto-loyalty-sidebar-label">
      {l s='Loyalty Points' mod='phyto_loyalty'}
    </span>
    <span class="phyto-loyalty-sidebar-balance badge badge-primary">
      {$phyto_loyalty_balance|intval} {l s='pts' mod='phyto_loyalty'}
    </span>
    <span class="phyto-loyalty-sidebar-tier phyto-tier-{$phyto_loyalty_tier|escape:'html':'UTF-8'}">
      {$phyto_loyalty_tier|escape:'html':'UTF-8'|ucfirst}
    </span>
  </a>
</div>
