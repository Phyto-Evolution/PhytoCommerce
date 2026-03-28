{extends file='layouts/layout-error.tpl'}

{block name='content'}

  <section id="main" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:60vh;padding:60px 20px;text-align:center;">

    {block name='page_header_container'}
      <header style="margin-bottom:40px;">
        {block name='page_header'}
          {if $shop.logo}
            <img src="{$shop.logo}" alt="{$shop.name}" loading="lazy" style="max-height:60px;width:auto;margin-bottom:20px;display:block;margin-left:auto;margin-right:auto;">
          {/if}
          <h1 style="font-family:var(--fsl-font-display);font-size:28px;font-weight:400;color:var(--fsl-gray-800);margin:0;">
            {block name='page_title'}{$shop.name}{/block}
          </h1>
        {/block}
      </header>
    {/block}

    {block name='page_content_container'}
      <section id="content" class="page-content page-restricted"
               style="background:var(--fsl-gray-50);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);padding:48px 40px;max-width:520px;width:100%;">
        {block name='page_content'}
          <span class="material-icons" style="font-size:56px;color:var(--fsl-gray-300);display:block;margin-bottom:20px;">public_off</span>
          <h2 style="font-family:var(--fsl-font-display);font-size:24px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 16px;">
            {l s='403 Forbidden' d='Shop.Theme.Global'}
          </h2>
          <p style="font-size:15px;color:var(--fsl-gray-500);line-height:1.6;margin:0;">
            {l s='You cannot access this store from your country. We apologize for the inconvenience.' d='Shop.Theme.Global'}
          </p>
        {/block}
      </section>
    {/block}

    {block name='page_footer_container'}{/block}

  </section>

{/block}
