{if isset($notifications)}
  <div class="fsl-notifications" style="margin:16px 0">
    {foreach $notifications.error as $notif}
      <div class="alert alert-danger" role="alert">
        {if is_array($notif)}{$notif|implode:'<br>'}{else}{$notif|escape:'htmlall':'UTF-8'}{/if}
      </div>
    {/foreach}
    {foreach $notifications.warning as $notif}
      <div class="alert alert-warning" role="alert">
        {if is_array($notif)}{$notif|implode:'<br>'}{else}{$notif|escape:'htmlall':'UTF-8'}{/if}
      </div>
    {/foreach}
    {foreach $notifications.success as $notif}
      <div class="alert alert-success" role="alert">
        {if is_array($notif)}{$notif|implode:'<br>'}{else}{$notif|escape:'htmlall':'UTF-8'}{/if}
      </div>
    {/foreach}
    {foreach $notifications.info as $notif}
      <div class="alert alert-info" role="alert">
        {if is_array($notif)}{$notif|implode:'<br>'}{else}{$notif|escape:'htmlall':'UTF-8'}{/if}
      </div>
    {/foreach}
  </div>
{/if}
