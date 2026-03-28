{if $pagination.should_be_displayed}
  <nav aria-label="{l s='Page navigation' mod='fsl'}">
    <ul class="pagination justify-content-center">

      {* Previous *}
      {if $pagination.current_page > 1}
        <li class="page-item">
          <a class="page-link" href="{$pagination.pages_count|intval > 1 ? $pagination.pages[0].url : '#'|escape:'htmlall':'UTF-8'}" aria-label="{l s='Previous' mod='fsl'}">
            <span class="material-icons" style="font-size:16px">chevron_left</span>
          </a>
        </li>
      {/if}

      {* Pages *}
      {foreach $pagination.pages as $page}
        {if $page.type == 'spacer'}
          <li class="page-item disabled"><span class="page-link">…</span></li>
        {else}
          <li class="page-item {if $page.current}active{/if}">
            <a class="page-link" href="{$page.url|escape:'htmlall':'UTF-8'}">{$page.page|intval}</a>
          </li>
        {/if}
      {/foreach}

      {* Next *}
      {if $pagination.current_page < $pagination.pages_count}
        <li class="page-item">
          <a class="page-link" href="{$pagination.pages[$pagination.pages_count - 1].url|escape:'htmlall':'UTF-8'}" aria-label="{l s='Next' mod='fsl'}">
            <span class="material-icons" style="font-size:16px">chevron_right</span>
          </a>
        </li>
      {/if}

    </ul>
    <p class="text-center mt-2" style="font-size:12px;color:var(--fsl-gray-400)">
      {l s='Showing' mod='fsl'}
      {$pagination.current_page|intval} / {$pagination.pages_count|intval}
    </p>
  </nav>
{/if}
