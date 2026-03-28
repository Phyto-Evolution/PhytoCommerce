<!DOCTYPE html>
<html lang="{$language.iso_code|escape:'htmlall':'UTF-8'}" dir="{if $language.is_rtl}rtl{else}ltr{/if}">
<head>
  {block name='head'}{include file='_partials/head.tpl'}{/block}
</head>
<body id="{$page.page_name}" class="{$page.body_classes|implode:' '}">

  {hook h='displayAfterBodyOpeningTag'}

  {block name='header'}
    {include file='_partials/header.tpl'}
  {/block}

  {hook h='displayBanner'}
  {hook h='displayTop'}

  <main id="wrapper">
    <div id="main">
      {block name='page_content_container'}
        <div class="container">
          {block name='notifications'}
            {include file='_partials/notifications.tpl'}
          {/block}
          {block name='page_content'}{/block}
        </div>
      {/block}
    </div>
  </main>

  {block name='footer'}
    {include file='_partials/footer.tpl'}
  {/block}

  {hook h='displayBeforeBodyClosingTag'}

  {block name='javascript_bottom'}
    {$HOOK_JAVASCRIPT_BOTTOM nofilter}
  {/block}

</body>
</html>
