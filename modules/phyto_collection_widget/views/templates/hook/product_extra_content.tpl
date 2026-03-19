{**
 * "In your collection" badge — shown on the product page extra-content tab
 * when the logged-in customer already owns this plant.
 *
 * Smarty variables:
 *   {$phyto_coll_date_acquired}   — acquisition date string (Y-m-d)
 *   {$phyto_coll_collection_url}  — link to the customer's collection page
 *}
<div class="phyto-coll-badge">
  <span class="phyto-coll-badge__icon" aria-hidden="true">&#127807;</span>
  <div class="phyto-coll-badge__text">
    <strong class="phyto-coll-badge__headline">
      {l s='In your collection' mod='phyto_collection_widget'}
    </strong>
    {if $phyto_coll_date_acquired}
      <span class="phyto-coll-badge__date">
        {l s='Since' mod='phyto_collection_widget'}
        <time datetime="{$phyto_coll_date_acquired|escape:'html':'UTF-8'}">
          {$phyto_coll_date_acquired|date_format:'%d %B %Y'}
        </time>
      </span>
    {/if}
  </div>
  <a
    href="{$phyto_coll_collection_url|escape:'html':'UTF-8'}"
    class="phyto-coll-badge__link btn btn-sm btn-outline-success"
  >
    {l s='View my collection' mod='phyto_collection_widget'}
  </a>
</div>
