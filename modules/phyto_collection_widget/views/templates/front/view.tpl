{**
 * Public Plant Collection View — read-only, no authentication required
 *
 * Smarty variables provided by ViewModuleFrontController:
 *   {$phyto_coll_items}         — array of public collection item rows (no personal_note)
 *   {$phyto_coll_owner_name}    — customer first name + last-name initial
 *   {$phyto_coll_customer_hash} — MD5 hash used in the URL
 *}

{extends file='page.tpl'}

{block name='page_title'}
  {if $phyto_coll_owner_name}
    {l s='%s\'s Plant Collection' sprintf=[$phyto_coll_owner_name] mod='phyto_collection_widget'}
  {else}
    {l s='Plant Collection' mod='phyto_collection_widget'}
  {/if}
{/block}

{block name='page_content'}

<section class="phyto-coll-page phyto-coll-page--public">

  {* ── Error / disabled state ─────────────────────────────────────────── *}
  {if $errors|@count > 0}
    <div class="alert alert-warning">
      {foreach $errors as $err}
        <p>{$err|escape:'html':'UTF-8'}</p>
      {/foreach}
    </div>
  {else}

    {* ── Page heading ───────────────────────────────────────────────────── *}
    <div class="phyto-coll-header">
      <h1 class="phyto-coll-title">
        <span class="phyto-coll-icon" aria-hidden="true">&#127807;</span>
        {if $phyto_coll_owner_name}
          {l s='%s\'s Plant Collection' sprintf=[$phyto_coll_owner_name] mod='phyto_collection_widget'}
        {else}
          {l s='Plant Collection' mod='phyto_collection_widget'}
        {/if}
      </h1>
      <p class="phyto-coll-subtitle text-muted">
        {l s='A curated selection of plants from this grower\'s collection.' mod='phyto_collection_widget'}
      </p>
    </div>

    {* ── Empty state ────────────────────────────────────────────────────── *}
    {if empty($phyto_coll_items)}
      <div class="phyto-coll-empty alert alert-info">
        <p>
          {if $phyto_coll_owner_name}
            {l s='%s hasn\'t shared any plants in their public collection yet.' sprintf=[$phyto_coll_owner_name] mod='phyto_collection_widget'}
          {else}
            {l s='No public plants in this collection yet.' mod='phyto_collection_widget'}
          {/if}
        </p>
        <a href="{$urls.pages.index}" class="btn btn-primary">
          {l s='Browse our plants' mod='phyto_collection_widget'}
        </a>
      </div>
    {else}

      {* ── Public collection grid ─────────────────────────────────────────── *}
      <ul class="phyto-coll-grid phyto-coll-grid--public">
        {foreach $phyto_coll_items as $item}
          <li class="phyto-coll-card phyto-coll-card--public">

            {* Plant image *}
            <a href="{$item.product_url|escape:'html':'UTF-8'}" class="phyto-coll-card__img-link">
              <img
                src="{$item.image_url|escape:'html':'UTF-8'}"
                alt="{$item.product_name|escape:'html':'UTF-8'}"
                class="phyto-coll-card__img"
                loading="lazy"
              >
            </a>

            {* Card body *}
            <div class="phyto-coll-card__body">
              <h2 class="phyto-coll-card__name">
                <a href="{$item.product_url|escape:'html':'UTF-8'}">
                  {$item.product_name|escape:'html':'UTF-8'}
                </a>
              </h2>

              {if $item.date_acquired}
                <p class="phyto-coll-card__date">
                  <small>
                    {l s='In collection since:' mod='phyto_collection_widget'}
                    <time datetime="{$item.date_acquired|escape:'html':'UTF-8'}">
                      {$item.date_acquired|date_format:'%d %B %Y'}
                    </time>
                  </small>
                </p>
              {/if}

              <a
                href="{$item.product_url|escape:'html':'UTF-8'}"
                class="btn btn-sm btn-outline-primary phyto-coll-card__shop-btn mt-2"
              >
                {l s='View plant' mod='phyto_collection_widget'}
              </a>
            </div>

          </li>
        {/foreach}
      </ul>

      {* ── Item count ─────────────────────────────────────────────────────── *}
      <p class="phyto-coll-count text-muted text-center mt-3">
        {l s='%d plant(s) in this collection' sprintf=[$phyto_coll_items|@count] mod='phyto_collection_widget'}
      </p>

    {/if}

  {/if}

</section>

{/block}
