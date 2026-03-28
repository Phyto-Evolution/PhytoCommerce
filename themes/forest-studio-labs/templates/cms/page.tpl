{extends file='page.tpl'}

{block name='page_content_container'}
<main id="cms" style="padding:48px 0 80px;">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
    {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:32px">
          {$cms.meta_title|escape:'htmlall':'UTF-8'}
        </h1>
        <div class="cms-content" style="font-size:15px;line-height:1.8;color:var(--fsl-gray-600);">
          {$cms.content nofilter}
        </div>
      </div>
    </div>
  </div>
</main>
{/block}
