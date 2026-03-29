{**
 * views/templates/front/account.tpl
 * My Loyalty Points — customer-facing account page
 *
 * @author PhytoCommerce
 *}

{extends file='customer/page.tpl'}

{block name='page_title'}
  {l s='My Loyalty Points' mod='phyto_loyalty'}
{/block}

{block name='page_content'}
<div class="phyto-loyalty-account">

  {* ── Tier + Balance Card ─────────────────────────────────────────────── *}
  <div class="card phyto-loyalty-summary mb-4">
    <div class="card-body row align-items-center">
      <div class="col-md-4 text-center">
        <div class="phyto-loyalty-tier-badge phyto-tier-{$phyto_loyalty_tier}">
          {$phyto_loyalty_tier_label|escape:'html':'UTF-8'}
        </div>
        <p class="mt-2 text-muted small">{l s='Your Tier' mod='phyto_loyalty'}</p>
      </div>
      <div class="col-md-4 text-center">
        <div class="phyto-loyalty-balance-number">{$phyto_loyalty_balance|intval}</div>
        <p class="text-muted small">{l s='Points Available' mod='phyto_loyalty'}</p>
      </div>
      <div class="col-md-4 text-center">
        <div class="phyto-loyalty-lifetime">{$phyto_loyalty_lifetime|intval}</div>
        <p class="text-muted small">{l s='Lifetime Points Earned' mod='phyto_loyalty'}</p>
      </div>
    </div>
  </div>

  {* ── Tier Progress ───────────────────────────────────────────────────── *}
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">{l s='Tier Progress' mod='phyto_loyalty'}</h5>
    </div>
    <div class="card-body">
      {if $phyto_loyalty_next_tier}
        <p>
          {l s='You need' mod='phyto_loyalty'}
          <strong>{$phyto_loyalty_points_to_next|intval} {l s='more lifetime points' mod='phyto_loyalty'}</strong>
          {l s='to reach' mod='phyto_loyalty'}
          <strong class="phyto-tier-name phyto-tier-{$phyto_loyalty_next_tier}">{$phyto_loyalty_tier_labels[$phyto_loyalty_next_tier]|escape:'html':'UTF-8'}</strong>
          {l s='tier.' mod='phyto_loyalty'}
        </p>
        <div class="progress phyto-tier-progress">
          <div class="progress-bar phyto-tier-bar phyto-tier-{$phyto_loyalty_tier}"
               role="progressbar"
               style="width: {$phyto_loyalty_progress_pct|intval}%"
               aria-valuenow="{$phyto_loyalty_progress_pct|intval}"
               aria-valuemin="0" aria-valuemax="100">
            {$phyto_loyalty_progress_pct|intval}%
          </div>
        </div>
      {else}
        <p class="text-success mb-0">
          <strong>{l s='Congratulations! You have reached the highest tier — Rare.' mod='phyto_loyalty'}</strong>
        </p>
      {/if}

      {* Tier ladder *}
      <div class="phyto-tier-ladder mt-3 row text-center">
        {foreach ['seed' => 0, 'sprout' => 500, 'bloom' => 2000, 'rare' => 5000] as $t => $pts}
          <div class="col-3">
            <div class="phyto-tier-step{if $phyto_loyalty_tier == $t} active{/if} phyto-tier-{$t}">
              {$phyto_loyalty_tier_labels[$t]|escape:'html':'UTF-8'}
            </div>
            <small class="text-muted">{$pts|intval}+ pts</small>
          </div>
        {/foreach}
      </div>
    </div>
  </div>

  {* ── How It Works ────────────────────────────────────────────────────── *}
  <div class="card mb-4">
    <div class="card-header">
      <h5 class="mb-0">{l s='How It Works' mod='phyto_loyalty'}</h5>
    </div>
    <div class="card-body">
      <ul class="list-unstyled">
        <li>&#9733; {l s='Earn' mod='phyto_loyalty'} <strong>{$phyto_loyalty_earn_rate|string_format:'%.2f'}</strong> {l s='points per ₹1 spent.' mod='phyto_loyalty'}</li>
        <li>&#9733; {l s='Redeem points for ₹' mod='phyto_loyalty'}<strong>{$phyto_loyalty_redeem_rate|string_format:'%.2f'}</strong> {l s='discount per point.' mod='phyto_loyalty'}</li>
        <li>&#9733; {l s='Use the cart widget to redeem during checkout.' mod='phyto_loyalty'}</li>
      </ul>
    </div>
  </div>

  {* ── Transaction History ─────────────────────────────────────────────── *}
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{l s='Transaction History' mod='phyto_loyalty'}</h5>
      <span class="badge badge-secondary">{$phyto_loyalty_total_tx|intval} {l s='records' mod='phyto_loyalty'}</span>
    </div>
    <div class="card-body p-0">
      {if $phyto_loyalty_transactions}
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead class="thead-light">
              <tr>
                <th>{l s='Date' mod='phyto_loyalty'}</th>
                <th>{l s='Type' mod='phyto_loyalty'}</th>
                <th>{l s='Points' mod='phyto_loyalty'}</th>
                <th>{l s='Balance After' mod='phyto_loyalty'}</th>
                <th>{l s='Note' mod='phyto_loyalty'}</th>
              </tr>
            </thead>
            <tbody>
              {foreach $phyto_loyalty_transactions as $tx}
                <tr>
                  <td>{$tx.date_add|escape:'html':'UTF-8'}</td>
                  <td>
                    <span class="badge phyto-tx-type phyto-tx-{$tx.type|escape:'html':'UTF-8'}">
                      {$tx.type|escape:'html':'UTF-8'|ucfirst}
                    </span>
                  </td>
                  <td class="{if $tx.points > 0}text-success{else}text-danger{/if}">
                    {if $tx.points > 0}+{/if}{$tx.points|intval}
                  </td>
                  <td>{$tx.balance_after|intval}</td>
                  <td class="text-muted small">{$tx.note|escape:'html':'UTF-8'}</td>
                </tr>
              {/foreach}
            </tbody>
          </table>
        </div>

        {* Pagination *}
        {if $phyto_loyalty_pages > 1}
          <div class="phyto-loyalty-pagination p-3 d-flex justify-content-center">
            <nav>
              <ul class="pagination pagination-sm mb-0">
                {if $phyto_loyalty_page > 1}
                  <li class="page-item">
                    <a class="page-link" href="?page={$phyto_loyalty_page - 1}">&laquo;</a>
                  </li>
                {/if}
                {for $p = 1 to $phyto_loyalty_pages}
                  <li class="page-item{if $p == $phyto_loyalty_page} active{/if}">
                    <a class="page-link" href="?page={$p}">{$p}</a>
                  </li>
                {/for}
                {if $phyto_loyalty_page < $phyto_loyalty_pages}
                  <li class="page-item">
                    <a class="page-link" href="?page={$phyto_loyalty_page + 1}">&raquo;</a>
                  </li>
                {/if}
              </ul>
            </nav>
          </div>
        {/if}
      {else}
        <p class="p-3 text-muted">{l s='No transactions yet. Start shopping to earn points!' mod='phyto_loyalty'}</p>
      {/if}
    </div>
  </div>

</div>
{/block}
