{**
 * views/templates/admin/nav.tpl
 * Shared tab navigation for admin views.
 *
 * @author PhytoCommerce
 *}

<ul class="nav nav-tabs phyto-loyalty-tabs mb-4">
  <li class="nav-item{if $phyto_loyalty_tab == 'overview'} active{/if}">
    <a class="nav-link" href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=overview">
      {l s='Overview' mod='phyto_loyalty'}
    </a>
  </li>
  <li class="nav-item{if $phyto_loyalty_tab == 'customers'} active{/if}">
    <a class="nav-link" href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=customers">
      {l s='Customers' mod='phyto_loyalty'}
    </a>
  </li>
  <li class="nav-item{if $phyto_loyalty_tab == 'transactions'} active{/if}">
    <a class="nav-link" href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=transactions">
      {l s='Transactions' mod='phyto_loyalty'}
    </a>
  </li>
  <li class="nav-item{if $phyto_loyalty_tab == 'settings'} active{/if}">
    <a class="nav-link" href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=settings">
      {l s='Settings' mod='phyto_loyalty'}
    </a>
  </li>
</ul>
