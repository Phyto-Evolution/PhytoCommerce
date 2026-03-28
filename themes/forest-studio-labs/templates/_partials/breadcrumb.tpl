{if $breadcrumb.links|count > 1}
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      {foreach $breadcrumb.links as $link}
        {if !$link@last}
          <li class="breadcrumb-item">
            <a href="{$link.url|escape:'htmlall':'UTF-8'}" rel="nofollow">
              {$link.title|escape:'htmlall':'UTF-8'}
            </a>
          </li>
        {else}
          <li class="breadcrumb-item active" aria-current="page">
            {$link.title|escape:'htmlall':'UTF-8'}
          </li>
        {/if}
      {/foreach}
    </ol>
  </nav>
{/if}
