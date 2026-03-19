{**
 * My Account block — link to the customer's submitted journal posts.
 *
 * Variable assigned by hookDisplayMyAccountBlock:
 *   $phyto_journal_post_url  string  URL to the post submission page
 *}

<li>
  <a href="{$phyto_journal_post_url|escape:'html':'UTF-8'}" class="phyto-journal-my-account-link">
    <span class="phyto-journal-my-account-icon" aria-hidden="true">&#127807;</span>
    {l s="Grower's Journal" mod='phyto_growers_journal'}
  </a>
</li>
