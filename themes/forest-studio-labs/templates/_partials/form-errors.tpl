{if $errors|count}
  <ul class="alert alert-danger" role="alert">
    {foreach $errors as $error}
      <li>{if is_array($error)}{$error|implode:' '}{else}{$error|escape:'htmlall':'UTF-8'}{/if}</li>
    {/foreach}
  </ul>
{/if}
