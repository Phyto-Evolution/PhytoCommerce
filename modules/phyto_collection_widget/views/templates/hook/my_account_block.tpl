{**
 * My Account block link — displayed in the customer's "My Account" dashboard.
 *
 * Smarty variables:
 *   {$phyto_coll_collection_url} — link to the collection page
 *}
<div class="phyto-coll-account-link">
  <a href="{$phyto_coll_collection_url|escape:'html':'UTF-8'}" class="phyto-coll-account-link__anchor">
    <span class="phyto-coll-account-link__icon" aria-hidden="true">&#127807;</span>
    {l s='My Plant Collection' mod='phyto_collection_widget'}
  </a>
</div>
