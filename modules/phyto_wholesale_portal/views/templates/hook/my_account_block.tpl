<li>
  {if $phyto_ws_is_wholesale}
    <a href="{$phyto_ws_dashboard_url|escape:'html'}" title="{l s='Wholesale Dashboard' mod='phyto_wholesale_portal'}">
      <i class="icon-briefcase"></i>
      {l s='Wholesale Dashboard' mod='phyto_wholesale_portal'}
    </a>
  {else}
    <a href="{$phyto_ws_apply_url|escape:'html'}" title="{l s='Apply for Wholesale Account' mod='phyto_wholesale_portal'}">
      <i class="icon-briefcase"></i>
      {l s='Apply for Wholesale Account' mod='phyto_wholesale_portal'}
    </a>
  {/if}
</li>
