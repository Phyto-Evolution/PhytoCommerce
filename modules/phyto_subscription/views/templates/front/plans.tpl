{extends file='page.tpl'}

{block name='page_title'}
  {$phyto_page_title|escape:'html'}
{/block}

{block name='page_content'}
<section class="phyto-sub-plans">

  {if $phyto_plans}
    <div class="row">
      {foreach from=$phyto_plans item='plan'}
      <div class="col-md-4 col-sm-6 phyto-sub-plan-card-wrap">
        <div class="phyto-sub-plan-card">
          <div class="phyto-sub-plan-header">
            <h3 class="phyto-sub-plan-name">{$plan.name|escape:'html'}</h3>
            <div class="phyto-sub-plan-price">
              {$plan.price_formatted}
              <span class="phyto-sub-plan-interval">
                / {$plan.billing_interval_count} {$plan.billing_interval|escape:'html'}
              </span>
            </div>
          </div>

          {if $plan.description}
          <div class="phyto-sub-plan-desc">
            {$plan.description|escape:'html'}
          </div>
          {/if}

          <div class="phyto-sub-plan-footer">
            {if $is_logged_in}
              <a href="{$plan.subscribe_url|escape:'html'}" class="btn btn-primary btn-block phyto-sub-cta">
                {l s='Subscribe' mod='phyto_subscription'}
              </a>
            {else}
              <a href="{$login_url|escape:'html'}" class="btn btn-default btn-block phyto-sub-cta">
                {l s='Log in to Subscribe' mod='phyto_subscription'}
              </a>
            {/if}
          </div>
        </div>
      </div>
      {/foreach}
    </div>
  {else}
    <div class="alert alert-info">
      {l s='No subscription plans are available at this time. Please check back later.' mod='phyto_subscription'}
    </div>
  {/if}

</section>
{/block}
