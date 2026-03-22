{**
 * Front-end out-of-season widget — replaces the add-to-cart area.
 * Rendered by hookDisplayProductButtons (only when out of season + block_purchase=1).
 *}

<div class="phyto-out-of-season-msg" role="alert">

  {* ── Out-of-season alert ─────────────────────────────────────── *}
  <div class="alert alert-warning phyto-oos-alert">
    <span class="phyto-oos-icon" aria-hidden="true">&#x26A0;&#xFE0F;</span>
    {if $phyto_seasonal_out_msg}
      {$phyto_seasonal_out_msg|escape:'html':'UTF-8'}
    {else}
      {l s='This plant is currently out of its shipping season and cannot be purchased at this time. Please check back later.' mod='phyto_seasonal_availability'}
    {/if}
  </div>

  {* ── Notify-me form ─────────────────────────────────────────── *}
  {if $phyto_seasonal_enable_notify}
    <div class="phyto-notify-form-wrap">
      <p class="phyto-notify-intro">
        {l s='Want to be notified when this plant is back in season?' mod='phyto_seasonal_availability'}
      </p>

      <form method="post"
            action="{$phyto_seasonal_notify_url|escape:'html':'UTF-8'}"
            class="phyto-notify-form form-inline"
            id="phyto-notify-form"
            novalidate>

        <input type="hidden" name="id_product"   value="{$phyto_seasonal_id_product|intval}" />
        <input type="hidden" name="phyto_token"  value="{$phyto_seasonal_token|escape:'html':'UTF-8'}" />
        <input type="hidden" name="submit_notify" value="1" />

        <div class="form-group phyto-notify-email-group">
          <label for="phyto-notify-email" class="sr-only">
            {l s='Your email address' mod='phyto_seasonal_availability'}
          </label>
          <input type="email"
                 id="phyto-notify-email"
                 name="email"
                 class="form-control phyto-notify-email"
                 placeholder="{l s='Your email address' mod='phyto_seasonal_availability'}"
                 required="required"
                 autocomplete="email"
                 maxlength="255" />
        </div>

        <button type="submit"
                class="btn btn-primary phyto-notify-submit"
                id="phyto-notify-submit">
          {l s='Notify me' mod='phyto_seasonal_availability'}
        </button>

        <span class="phyto-notify-error text-danger" id="phyto-notify-error" style="display:none;margin-left:8px;">
          {l s='Please enter a valid email address.' mod='phyto_seasonal_availability'}
        </span>

      </form>
    </div>
  {/if}

</div>{* /.phyto-out-of-season-msg *}
