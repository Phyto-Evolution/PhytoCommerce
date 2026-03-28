<!DOCTYPE html>
<html lang="{$language.iso_code|escape:'htmlall':'UTF-8'}" dir="{$language.is_rtl ? 'rtl' : 'ltr'}">
<head>
  {block name='head'}{include file='_partials/head.tpl'}{/block}
</head>
<body id="{$page.page_name}" class="{$page.body_classes|implode:' '}">

  {hook h='displayAfterBodyOpeningTag'}

  {block name='header'}{include file='_partials/header.tpl'}{/block}

  <main id="wrapper" style="min-height:60vh;display:flex;align-items:center;">
    <section id="main" style="width:100%;">
      {block name='page_content_container'}
        {block name='page_content'}{/block}
      {/block}
    </section>
  </main>

  {block name='footer'}{include file='_partials/footer.tpl'}{/block}

  {hook h='displayBeforeBodyClosingTag'}
  {block name='javascript_bottom'}{$HOOK_JAVASCRIPT_BOTTOM nofilter}{/block}

</body>
</html>
