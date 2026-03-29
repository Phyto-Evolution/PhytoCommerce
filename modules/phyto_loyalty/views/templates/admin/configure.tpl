{**
 * views/templates/admin/configure.tpl
 * Settings tab — admin panel
 *
 * @author PhytoCommerce
 *}

{include file='./nav.tpl'}

<div class="panel phyto-loyalty-settings">
  <div class="panel-heading">
    <i class="icon-cog"></i>
    {l s='Loyalty Programme Settings' mod='phyto_loyalty'}
  </div>
  <form method="post" action="{$phyto_loyalty_admin_link|escape:'html':'UTF-8'}&action=saveSettings">
    <div class="form-group row">
      <label class="col-md-3 control-label">{l s='Enable Loyalty Programme' mod='phyto_loyalty'}</label>
      <div class="col-md-6">
        <span class="switch prestashop-switch fixed-width-lg">
          <input type="radio" name="PHYTO_LOYALTY_ENABLED" id="enabled_on" value="1" {if $PHYTO_LOYALTY_ENABLED == 1}checked{/if}>
          <label for="enabled_on">{l s='Yes' mod='phyto_loyalty'}</label>
          <input type="radio" name="PHYTO_LOYALTY_ENABLED" id="enabled_off" value="0" {if $PHYTO_LOYALTY_ENABLED == 0}checked{/if}>
          <label for="enabled_off">{l s='No' mod='phyto_loyalty'}</label>
          <a class="slide-button btn"></a>
        </span>
      </div>
    </div>

    <div class="form-group row">
      <label class="col-md-3 control-label" for="PHYTO_LOYALTY_EARN_RATE">
        {l s='Earn Rate (points per ₹1)' mod='phyto_loyalty'}
      </label>
      <div class="col-md-4">
        <input type="text" class="form-control" id="PHYTO_LOYALTY_EARN_RATE"
               name="PHYTO_LOYALTY_EARN_RATE" value="{$PHYTO_LOYALTY_EARN_RATE|escape:'html':'UTF-8'}">
        <p class="help-block">{l s='Default: 0.1 (1 point per ₹10 spent)' mod='phyto_loyalty'}</p>
      </div>
    </div>

    <div class="form-group row">
      <label class="col-md-3 control-label" for="PHYTO_LOYALTY_REDEEM_RATE">
        {l s='Redeem Rate (₹ per point)' mod='phyto_loyalty'}
      </label>
      <div class="col-md-4">
        <input type="text" class="form-control" id="PHYTO_LOYALTY_REDEEM_RATE"
               name="PHYTO_LOYALTY_REDEEM_RATE" value="{$PHYTO_LOYALTY_REDEEM_RATE|escape:'html':'UTF-8'}">
        <p class="help-block">{l s='Default: 0.50 (₹0.50 discount per point redeemed)' mod='phyto_loyalty'}</p>
      </div>
    </div>

    <div class="form-group row">
      <label class="col-md-3 control-label" for="PHYTO_LOYALTY_MIN_REDEEM">
        {l s='Minimum Points to Redeem' mod='phyto_loyalty'}
      </label>
      <div class="col-md-4">
        <input type="text" class="form-control" id="PHYTO_LOYALTY_MIN_REDEEM"
               name="PHYTO_LOYALTY_MIN_REDEEM" value="{$PHYTO_LOYALTY_MIN_REDEEM|intval}">
        <p class="help-block">{l s='Default: 100 points' mod='phyto_loyalty'}</p>
      </div>
    </div>

    <div class="form-group row">
      <label class="col-md-3 control-label" for="PHYTO_LOYALTY_MAX_REDEEM_PCT">
        {l s='Max Redemption (% of order)' mod='phyto_loyalty'}
      </label>
      <div class="col-md-4">
        <div class="input-group">
          <input type="text" class="form-control" id="PHYTO_LOYALTY_MAX_REDEEM_PCT"
                 name="PHYTO_LOYALTY_MAX_REDEEM_PCT" value="{$PHYTO_LOYALTY_MAX_REDEEM_PCT|intval}">
          <span class="input-group-addon">%</span>
        </div>
        <p class="help-block">{l s='Default: 20% — points redemption capped at this % of cart total.' mod='phyto_loyalty'}</p>
      </div>
    </div>

    <div class="form-group row">
      <label class="col-md-3 control-label" for="PHYTO_LOYALTY_EXPIRY_DAYS">
        {l s='Points Expiry (days of inactivity)' mod='phyto_loyalty'}
      </label>
      <div class="col-md-4">
        <input type="text" class="form-control" id="PHYTO_LOYALTY_EXPIRY_DAYS"
               name="PHYTO_LOYALTY_EXPIRY_DAYS" value="{$PHYTO_LOYALTY_EXPIRY_DAYS|intval}">
        <p class="help-block">{l s='Default: 365. Set to 0 to disable expiry.' mod='phyto_loyalty'}</p>
      </div>
    </div>

    <div class="panel-footer">
      <button type="submit" class="btn btn-default pull-right">
        <i class="process-icon-save"></i>
        {l s='Save' mod='phyto_loyalty'}
      </button>
    </div>
  </form>
</div>
