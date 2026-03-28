<!DOCTYPE html>
<html lang="{$language.iso_code|escape:'htmlall':'UTF-8'}" dir="{if $language.is_rtl}rtl{else}ltr{/if}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  {block name='head'}{include file='_partials/head.tpl'}{/block}
</head>
<body id="{$page.page_name}" class="{$page.body_classes|implode:' '}">

  {hook h='displayAfterBodyOpeningTag'}

  {* KYC blur class injected here by phyto_kyc module *}

  {block name='header'}{include file='_partials/header.tpl'}{/block}

  {hook h='displayBanner'}
  {hook h='displayTop'}

  <main id="wrapper">
    <div class="container">
      {hook h='displayNavFullWidth'}
      {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}
    </div>

    <section id="main">
      {block name='page_content_container'}
        <div class="container">
          {block name='page_content'}{/block}
        </div>
      {/block}
    </section>
  </main>

  {block name='footer'}{include file='_partials/footer.tpl'}{/block}

  {hook h='displayBeforeBodyClosingTag'}

  {block name='javascript_bottom'}
    {$HOOK_JAVASCRIPT_BOTTOM nofilter}
  {/block}

</body>
</html>
