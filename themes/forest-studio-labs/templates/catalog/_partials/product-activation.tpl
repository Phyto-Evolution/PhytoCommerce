{if $page.admin_notifications}
  <div class="alert alert-warning" role="alert"
       style="background:#fffbeb;border:1px solid #f59e0b;border-radius:var(--fsl-radius);padding:12px 16px;margin-bottom:16px;">
    <div class="container">
      {foreach $page.admin_notifications as $notif}
        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:8px;">
          <i class="material-icons" style="font-size:20px;color:#f59e0b;flex-shrink:0;">warning</i>
          <p class="alert-text" style="margin:0;font-size:13px;color:#92400e;">{$notif.message}</p>
        </div>
      {/foreach}
    </div>
  </div>
{/if}
