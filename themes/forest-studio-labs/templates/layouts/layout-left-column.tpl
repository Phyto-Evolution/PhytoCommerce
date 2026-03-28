<!DOCTYPE html>
<html lang="{$language.iso_code|escape:'htmlall':'UTF-8'}" dir="{if $language.is_rtl}rtl{else}ltr{/if}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  {block name='head'}{include file='_partials/head.tpl'}{/block}
</head>
<body id="{$page.page_name}" class="{$page.body_classes|classnames}">

  {hook h='displayAfterBodyOpeningTag'}

  {block name='header'}{include file='_partials/header.tpl'}{/block}

  {hook h='displayBanner'}
  {hook h='displayTop'}

  <main id="wrapper">
    <section id="main">
      <div class="container">
        {hook h='displayNavFullWidth'}
        {block name='notifications'}{include file='_partials/notifications.tpl'}{/block}
        <div class="row">
          {* Left sidebar *}
          <div class="col-lg-3 col-md-4" id="left-column">
            {block name='left_column'}{/block}
          </div>
          {* Main content *}
          <div class="col-lg-9 col-md-8" id="content-wrapper">
            {block name='page_content_container'}
              {block name='page_content'}{/block}
            {/block}
          </div>
        </div>
      </div>
    </section>
  </main>

  {block name='footer'}{include file='_partials/footer.tpl'}{/block}

  {hook h='displayBeforeBodyClosingTag'}
  {block name='javascript_bottom'}{$HOOK_JAVASCRIPT_BOTTOM nofilter}{/block}

</body>
</html>
