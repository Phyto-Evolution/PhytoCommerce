{**
 * views/templates/admin/customers.tpl
 * Customers tab — searchable list.
 *
 * @author PhytoCommerce
 *}

{include file='./nav.tpl'}

<div class="panel">
  <div class="panel-heading">{l s='Loyalty Members' mod='phyto_loyalty'}</div>
  <div class="panel-body">

    <form method="get" action="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}" class="form-inline mb-3">
      <input type="hidden" name="action" value="customers">
      <input type="text" name="search" class="form-control mr-2"
             placeholder="{l s='Search by name or email…' mod='phyto_loyalty'}"
             value="{$phyto_loyalty_search|escape:'html':'UTF-8'}">
      <button type="submit" class="btn btn-default">
        <i class="icon-search"></i> {l s='Search' mod='phyto_loyalty'}
      </button>
    </form>

    {if $phyto_loyalty_customers}
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>{l s='Customer' mod='phyto_loyalty'}</th>
              <th>{l s='Email' mod='phyto_loyalty'}</th>
              <th>{l s='Tier' mod='phyto_loyalty'}</th>
              <th>{l s='Balance' mod='phyto_loyalty'}</th>
              <th>{l s='Lifetime' mod='phyto_loyalty'}</th>
              <th>{l s='Redeemed' mod='phyto_loyalty'}</th>
              <th>{l s='Last Activity' mod='phyto_loyalty'}</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            {foreach $phyto_loyalty_customers as $c}
              <tr>
                <td>{$c.customer_name|escape:'html':'UTF-8'}</td>
                <td>{$c.email|escape:'html':'UTF-8'}</td>
                <td><span class="phyto-tier-badge phyto-tier-{$c.tier}">{$c.tier|ucfirst}</span></td>
                <td><strong>{$c.points_balance|intval}</strong></td>
                <td>{$c.points_lifetime|intval}</td>
                <td>{$c.points_redeemed|intval}</td>
                <td>{$c.date_upd|escape:'html':'UTF-8'}</td>
                <td>
                  <a href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=customerDetail&id_customer={$c.id_customer|intval}"
                     class="btn btn-xs btn-primary">
                    {l s='Detail' mod='phyto_loyalty'}
                  </a>
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>

      {* Pagination *}
      {if $phyto_loyalty_pages > 1}
        <nav class="text-center mt-3">
          <ul class="pagination pagination-sm d-inline-flex mb-0">
            {for $p = 1 to $phyto_loyalty_pages}
              <li class="page-item{if $p == $phyto_loyalty_page} active{/if}">
                <a class="page-link"
                   href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=customers&search={$phyto_loyalty_search|escape:'url'}&page={$p}">
                  {$p}
                </a>
              </li>
            {/for}
          </ul>
        </nav>
      {/if}
    {else}
      <p class="text-muted">{l s='No customers found.' mod='phyto_loyalty'}</p>
    {/if}
  </div>
</div>
