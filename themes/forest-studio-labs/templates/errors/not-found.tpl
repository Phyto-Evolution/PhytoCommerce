<section id="content" class="page-content page-not-found" style="padding:80px 20px;text-align:center;">

  {block name='page_content'}

    {block name='error_content'}
      {if isset($errorContent)}
        <div style="margin-bottom:24px;">
          {$errorContent nofilter}
        </div>
      {else}
        <span class="material-icons" style="font-size:72px;color:var(--fsl-gray-200);display:block;margin-bottom:20px;">search_off</span>
        <h1 style="font-family:var(--fsl-font-display);font-size:32px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 12px;">
          {l s='This page could not be found' d='Shop.Theme.Global'}
        </h1>
        <p style="font-size:15px;color:var(--fsl-gray-500);margin-bottom:32px;max-width:480px;margin-left:auto;margin-right:auto;">
          {l s='Try to search our catalog, you may find what you are looking for!' d='Shop.Theme.Global'}
        </p>
      {/if}
    {/block}

    {block name='search'}
      <div style="max-width:480px;margin:0 auto 32px;">
        {hook h='displaySearch'}
      </div>
    {/block}

    {block name='hook_not_found'}
      {hook h='displayNotFound'}
    {/block}

    <a href="{$urls.pages.index}"
       style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;text-decoration:none;margin-top:16px;">
      <i class="material-icons" style="font-size:18px;">home</i>
      {l s='Back to home' d='Shop.Theme.Actions'}
    </a>

  {/block}

</section>
