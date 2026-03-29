{*
 * configure.tpl
 *
 * Displayed inside the "Extra" tab on the Product admin page.
 * Shows current restock alert subscribers for this product.
 *}

<div class="panel phyto-restock-admin-panel">
  <div class="panel-heading">
    <i class="icon-bell"></i>
    {l s='Restock Alert Subscribers' mod='phyto_restock_alert'}
    <span class="badge badge-secondary" style="margin-left:6px;">
      {$phyto_restock_alerts|count}
    </span>
  </div>

  <div class="panel-body">
    {if $phyto_restock_alerts|count > 0}
      <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" id="phyto-restock-admin-table">
          <thead>
            <tr>
              <th>{l s='Email' mod='phyto_restock_alert'}</th>
              <th>{l s='First Name' mod='phyto_restock_alert'}</th>
              <th>{l s='Combination' mod='phyto_restock_alert'}</th>
              <th>{l s='Subscribed On' mod='phyto_restock_alert'}</th>
              <th class="text-center">{l s='Notified' mod='phyto_restock_alert'}</th>
              <th>{l s='Notified On' mod='phyto_restock_alert'}</th>
              <th>{l s='Actions' mod='phyto_restock_alert'}</th>
            </tr>
          </thead>
          <tbody>
            {foreach from=$phyto_restock_alerts item=alert}
              <tr class="{if $alert.notified}text-muted{/if}">
                <td>{$alert.email|escape:'html':'UTF-8'}</td>
                <td>{$alert.firstname|escape:'html':'UTF-8'|default:'-'}</td>
                <td class="text-center">
                  {if $alert.id_product_attribute > 0}
                    #{$alert.id_product_attribute|intval}
                  {else}
                    <span class="text-muted">—</span>
                  {/if}
                </td>
                <td>{$alert.date_add|escape:'html':'UTF-8'}</td>
                <td class="text-center">
                  {if $alert.notified}
                    <span class="label label-success">
                      <i class="icon-check"></i>
                      {l s='Yes' mod='phyto_restock_alert'}
                    </span>
                  {else}
                    <span class="label label-warning">
                      <i class="icon-clock-o"></i>
                      {l s='Pending' mod='phyto_restock_alert'}
                    </span>
                  {/if}
                </td>
                <td>
                  {if $alert.date_notified}
                    {$alert.date_notified|escape:'html':'UTF-8'}
                  {else}
                    <span class="text-muted">—</span>
                  {/if}
                </td>
                <td>
                  <a
                    href="{$phyto_restock_admin_url|escape:'html':'UTF-8'}&action=sendnow&id_alert={$alert.id_alert|intval}&token={Tools::getAdminTokenLite('AdminPhytoRestockAlert')}"
                    class="btn btn-default btn-xs"
                    title="{l s='Send notification now' mod='phyto_restock_alert'}"
                  >
                    <i class="icon-envelope"></i>
                    {l s='Send Now' mod='phyto_restock_alert'}
                  </a>
                </td>
              </tr>
            {/foreach}
          </tbody>
        </table>
      </div>

      <div class="mt-2">
        <a
          href="{$phyto_restock_admin_url|escape:'html':'UTF-8'}&id_product_filter={$phyto_restock_id_product|intval}"
          class="btn btn-default btn-sm"
          target="_blank"
        >
          <i class="icon-list"></i>
          {l s='View all in Restock Alerts manager' mod='phyto_restock_alert'}
        </a>
      </div>

    {else}
      <p class="text-muted">
        <i class="icon-info-circle"></i>
        {l s='No subscribers yet for this product.' mod='phyto_restock_alert'}
      </p>
    {/if}
  </div>
</div>
