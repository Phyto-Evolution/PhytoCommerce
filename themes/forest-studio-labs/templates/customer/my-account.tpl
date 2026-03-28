{extends file='page.tpl'}

{block name='page_content_container'}
<main id="my-account" style="background:var(--fsl-off-white);padding:40px 0 80px;">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}

    <div class="row g-4">

      {* ── Sidebar ── *}
      <div class="col-lg-3">
        <div class="fsl-account-sidebar">
          <div style="padding:20px 20px 14px;border-bottom:1px solid var(--fsl-gray-100);">
            <div style="width:44px;height:44px;background:var(--fsl-light-green);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
              <span class="material-icons" style="color:var(--fsl-forest);font-size:22px">person</span>
            </div>
            <p style="font-family:var(--fsl-font-display);font-size:1.1rem;margin:0">
              {$customer.firstname|escape:'htmlall':'UTF-8'} {$customer.lastname|escape:'htmlall':'UTF-8'}
            </p>
            <p style="font-size:12px;color:var(--fsl-gray-400);margin:0">{$customer.email|escape:'htmlall':'UTF-8'}</p>
          </div>

          {foreach $customer_navigation as $link}
            <a href="{$link.url|escape:'htmlall':'UTF-8'}" class="account-link {if $link.active}active{/if}">
              {if isset($link.icon)}<span class="material-icons">{$link.icon|escape:'htmlall':'UTF-8'}</span>{/if}
              {$link.title|escape:'htmlall':'UTF-8'}
            </a>
          {/foreach}

          {hook h='displayMyAccountBlock'}

          <a href="{$logout_url|escape:'htmlall':'UTF-8'}" class="account-link" style="color:var(--fsl-gray-400);">
            <span class="material-icons">logout</span>
            {l s='Sign Out' mod='fsl'}
          </a>
        </div>
      </div>

      {* ── Content ── *}
      <div class="col-lg-9">
        {block name='page_content'}
          <div class="fsl-account-card">
            <h2 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:24px">{l s='My Account' mod='fsl'}</h2>

            <div class="row g-3">
              {foreach $customer_navigation as $link}
                <div class="col-6 col-md-4">
                  <a href="{$link.url|escape:'htmlall':'UTF-8'}"
                     style="display:block;background:var(--fsl-off-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:20px;text-align:center;transition:all var(--fsl-transition);text-decoration:none;">
                    {if isset($link.icon)}
                      <span class="material-icons" style="font-size:28px;color:var(--fsl-sage);display:block;margin-bottom:8px">{$link.icon|escape:'htmlall':'UTF-8'}</span>
                    {/if}
                    <span style="font-size:13px;font-weight:500;color:var(--fsl-gray-700)">{$link.title|escape:'htmlall':'UTF-8'}</span>
                  </a>
                </div>
              {/foreach}
            </div>
          </div>
        {/block}
      </div>

    </div>
  </div>
</main>
{/block}
