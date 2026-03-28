<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

{block name='head_seo'}
  <title>{$page.meta.title|escape:'htmlall':'UTF-8'}</title>
  <meta name="description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
  {if $page.meta.robots}<meta name="robots" content="{$page.meta.robots|escape:'htmlall':'UTF-8'}">{/if}
  {if $page.canonical_url}<link rel="canonical" href="{$page.canonical_url|escape:'htmlall':'UTF-8'}">{/if}
{/block}

{block name='head_og'}
  <meta property="og:title" content="{$page.meta.title|escape:'htmlall':'UTF-8'}">
  <meta property="og:description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
  <meta property="og:url" content="{$urls.current_url|escape:'htmlall':'UTF-8'}">
  <meta property="og:site_name" content="{$shop.name|escape:'htmlall':'UTF-8'}">
  {if isset($product.cover.bySize.large_default.url)}
    <meta property="og:image" content="{$product.cover.bySize.large_default.url|escape:'htmlall':'UTF-8'}">
  {/if}
{/block}

{block name='head_favicon'}
  {if $shop.favicon}
    <link rel="icon" type="image/x-icon" href="{$shop.favicon|escape:'htmlall':'UTF-8'}">
    <link rel="shortcut icon" type="image/x-icon" href="{$shop.favicon|escape:'htmlall':'UTF-8'}">
  {/if}
{/block}

{* Google Fonts *}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

{block name='head_stylesheet'}
  {$HOOK_HEADER nofilter}
  <link rel="stylesheet" href="{$urls.theme_assets}css/theme.css">
{/block}

{block name='head_javascript'}
  {$HOOK_JAVASCRIPT_HEAD nofilter}
{/block}

{hook h='displayHead'}
