{extends file='page.tpl'}

{block name='page_title'}
  {l s='Wholesale Application Submitted' mod='phyto_wholesale_portal'}
{/block}

{block name='page_content'}
<section class="phyto-ws-success">
  {if $phyto_ws_require_approval}
    <div class="alert alert-success">
      <h4>{l s='Application Received!' mod='phyto_wholesale_portal'}</h4>
      <p>
        {l s='Thank you for applying for a wholesale account. Our team will review your application and contact you within 2 business days.' mod='phyto_wholesale_portal'}
      </p>
    </div>
  {else}
    <div class="alert alert-success">
      <h4>{l s='Welcome, Wholesale Partner!' mod='phyto_wholesale_portal'}</h4>
      <p>
        {l s='Your wholesale account has been automatically approved. You now have access to wholesale pricing and ordering.' mod='phyto_wholesale_portal'}
      </p>
    </div>
  {/if}

  <p>
    <a href="{$urls.pages.my_account}" class="btn btn-default">
      <i class="icon-user"></i>
      {l s='Return to My Account' mod='phyto_wholesale_portal'}
    </a>
  </p>
</section>
{/block}
