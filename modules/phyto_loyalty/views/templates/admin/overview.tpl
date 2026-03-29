{**
 * views/templates/admin/overview.tpl
 * Overview tab — summary stats and top earners.
 *
 * @author PhytoCommerce
 *}

{include file='./nav.tpl'}

<div class="row">
  <div class="col-md-4">
    <div class="panel text-center phyto-loyalty-stat-card">
      <div class="panel-body">
        <div class="phyto-loyalty-stat-number">{$phyto_loyalty_total_members|intval}</div>
        <div class="phyto-loyalty-stat-label">{l s='Total Members' mod='phyto_loyalty'}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel text-center phyto-loyalty-stat-card">
      <div class="panel-body">
        <div class="phyto-loyalty-stat-number text-warning">{$phyto_loyalty_points_outstanding|intval}</div>
        <div class="phyto-loyalty-stat-label">{l s='Points Outstanding' mod='phyto_loyalty'}</div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel text-center phyto-loyalty-stat-card">
      <div class="panel-body">
        <div class="phyto-loyalty-stat-number text-success">{$phyto_loyalty_points_redeemed_life|intval}</div>
        <div class="phyto-loyalty-stat-label">{l s='Points Redeemed (Lifetime)' mod='phyto_loyalty'}</div>
      </div>
    </div>
  </div>
</div>

{* Tier breakdown *}
<div class="panel">
  <div class="panel-heading">{l s='Members by Tier' mod='phyto_loyalty'}</div>
  <div class="panel-body row text-center">
    {foreach ['seed', 'sprout', 'bloom', 'rare'] as $tier}
      <div class="col-md-3">
        <span class="phyto-tier-badge phyto-tier-{$tier}">{$tier|ucfirst}</span>
        <div class="mt-1">{if isset($phyto_loyalty_tier_counts[$tier])}{$phyto_loyalty_tier_counts[$tier]|intval}{else}0{/if}</div>
      </div>
    {/foreach}
  </div>
</div>

{* Top earners *}
<div class="panel">
  <div class="panel-heading">{l s='Top 10 Earners (Lifetime Points)' mod='phyto_loyalty'}</div>
  <div class="panel-body p-0">
    {if $phyto_loyalty_top_earners}
      <table class="table table-striped mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>{l s='Customer' mod='phyto_loyalty'}</th>
            <th>{l s='Email' mod='phyto_loyalty'}</th>
            <th>{l s='Tier' mod='phyto_loyalty'}</th>
            <th>{l s='Balance' mod='phyto_loyalty'}</th>
            <th>{l s='Lifetime' mod='phyto_loyalty'}</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          {foreach $phyto_loyalty_top_earners as $k => $earner}
            <tr>
              <td>{$k+1}</td>
              <td>{$earner.customer_name|escape:'html':'UTF-8'}</td>
              <td>{$earner.email|escape:'html':'UTF-8'}</td>
              <td><span class="phyto-tier-badge phyto-tier-{$earner.tier}">{$earner.tier|ucfirst}</span></td>
              <td>{$earner.points_balance|intval}</td>
              <td>{$earner.points_lifetime|intval}</td>
              <td>
                <a href="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=customerDetail&id_customer={$earner.id_customer|intval}"
                   class="btn btn-xs btn-default">
                  {l s='View' mod='phyto_loyalty'}
                </a>
              </td>
            </tr>
          {/foreach}
        </tbody>
      </table>
    {else}
      <p class="p-3 text-muted">{l s='No members yet.' mod='phyto_loyalty'}</p>
    {/if}
  </div>
</div>
