{extends file='page.tpl'}

{block name='notifications'}{/block}

{block name='page_content_container'}
  <div class="container" style="max-width:900px;padding:40px 16px;">
    {block name='page_content_top'}
      {block name='customer_notifications'}
        {include file='_partials/notifications.tpl'}
      {/block}
    {/block}
    {block name='page_content'}
      <!-- Page content -->
    {/block}
  </div>
{/block}

{block name='page_footer'}
  <div class="container" style="max-width:900px;padding:0 16px 40px;">
    {block name='my_account_links'}
      {include file='customer/_partials/my-account-links.tpl'}
    {/block}
  </div>
{/block}
