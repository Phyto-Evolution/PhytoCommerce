{block name='sitemap_item'}
  <ul style="list-style:none;padding:0;margin:0{if !empty($is_nested)};padding-left:12px;margin-top:4px{/if}">
    {foreach $links as $link}
      <li style="margin-bottom:6px;">
        <a id="{$link.id}" href="{$link.url|escape:'htmlall':'UTF-8'}" title="{$link.label|escape:'htmlall':'UTF-8'}"
           style="font-size:13px;color:var(--fsl-gray-600);text-decoration:none;">
          {$link.label|escape:'htmlall':'UTF-8'}
        </a>
        {if !empty($link.children)}
          {include file='cms/_partials/sitemap-nested-list.tpl' links=$link.children is_nested=true}
        {/if}
      </li>
    {/foreach}
  </ul>
{/block}
