{**
 * Bundle Builder — front-end template
 *
 * Used for both the bundle listing page and the single-bundle builder page.
 * Variables set by the front controller:
 *
 * Listing mode:
 *   $phyto_bundles       — array of active bundle rows
 *   $phyto_builder_base  — base URL to the builder controller
 *
 * Builder mode (id_bundle set):
 *   $phyto_bundle        — current bundle row (with lang fields)
 *   $phyto_slots         — array of slots, each with a 'products' sub-array
 *   $phyto_products_url  — AJAX endpoint URL for product search
 *   $phyto_builder_url   — builder base URL (for back link)
 *   $phyto_form_token    — CSRF token
 *   $phyto_cta_text      — CTA button label
 *   $phyto_show_savings  — bool: display savings line
 *   $phyto_currency      — current currency object
 **}

{extends file='page.tpl'}

{block name='page_title'}
  {if isset($phyto_bundle)}
    {$phyto_bundle.name|escape:'html':'UTF-8'}
  {else}
    {l s='Build a Bundle' mod='phyto_bundle_builder'}
  {/if}
{/block}

{block name='page_content'}
<div class="phyto-bundle-builder">

  {* ---- Bundle listing (no id_bundle in URL) ---- *}
  {if !isset($phyto_bundle)}

    <h1 class="phyto-bb-heading">{l s='Choose a Bundle' mod='phyto_bundle_builder'}</h1>
    <p class="phyto-bb-subheading">{l s='Select a bundle template to get started.' mod='phyto_bundle_builder'}</p>

    {if $phyto_bundles|@count > 0}
      <div class="phyto-bb-listing row">
        {foreach from=$phyto_bundles item=bundle}
          <div class="col-md-4 col-sm-6 phyto-bb-listing-item">
            <div class="card phyto-bb-card">
              <div class="card-body">
                <h3 class="card-title">{$bundle.name|escape:'html':'UTF-8'}</h3>
                {if $bundle.description}
                  <p class="card-text">{$bundle.description|strip_tags|truncate:120:'...'}</p>
                {/if}
                {if $bundle.discount_value > 0}
                  <p class="phyto-bb-discount-badge">
                    {if $bundle.discount_type == 'percent'}
                      {l s='Save %value%%' sprintf=['%value%' => $bundle.discount_value|string_format:'%g'] mod='phyto_bundle_builder'}
                    {else}
                      {l s='Save %value%' sprintf=['%value%' => $bundle.discount_value|displayPrice] mod='phyto_bundle_builder'}
                    {/if}
                  </p>
                {/if}
                <a href="{$phyto_builder_base|escape:'html':'UTF-8'}?id_bundle={$bundle.id_bundle|intval}"
                   class="btn btn-primary phyto-bb-start-btn">
                  {l s='Build This Bundle' mod='phyto_bundle_builder'}
                </a>
              </div>
            </div>
          </div>
        {/foreach}
      </div>
    {else}
      <p class="alert alert-info">{l s='No bundle templates are available at the moment.' mod='phyto_bundle_builder'}</p>
    {/if}

  {* ---- Single bundle builder ---- *}
  {else}

    <a href="{$phyto_builder_url|escape:'html':'UTF-8'}" class="phyto-bb-back-link">
      &laquo; {l s='All Bundles' mod='phyto_bundle_builder'}
    </a>

    <h1 class="phyto-bb-heading">{$phyto_bundle.name|escape:'html':'UTF-8'}</h1>

    {if $phyto_bundle.description}
      <p class="phyto-bb-description">{$phyto_bundle.description|strip_tags}</p>
    {/if}

    {if $phyto_bundle.discount_value > 0}
      <div class="phyto-bb-discount-info alert alert-success">
        {if $phyto_bundle.discount_type == 'percent'}
          {l s='Complete this bundle and save %value%%!' sprintf=['%value%' => $phyto_bundle.discount_value|string_format:'%g'] mod='phyto_bundle_builder'}
        {else}
          {l s='Complete this bundle and save %value%!' sprintf=['%value%' => $phyto_bundle.discount_value|displayPrice] mod='phyto_bundle_builder'}
        {/if}
      </div>
    {/if}

    <form method="post"
          action="{$phyto_builder_url|escape:'html':'UTF-8'}"
          id="phyto-bundle-form"
          data-bundle-id="{$phyto_bundle.id_bundle|intval}"
          data-discount-type="{$phyto_bundle.discount_type|escape:'html':'UTF-8'}"
          data-discount-value="{$phyto_bundle.discount_value|floatval}"
          data-products-url="{$phyto_products_url|escape:'html':'UTF-8'}"
          data-show-savings="{if $phyto_show_savings}1{else}0{/if}">

      <input type="hidden" name="submitBundleToCart" value="1">
      <input type="hidden" name="id_bundle" value="{$phyto_bundle.id_bundle|intval}">
      <input type="hidden" name="token" value="{$phyto_form_token|escape:'html':'UTF-8'}">

      {* ---- Slots ---- *}
      <div class="phyto-bb-slots">

        {foreach from=$phyto_slots item=slot name=slotLoop}
          <div class="phyto-bb-slot card"
               id="phyto-slot-{$slot.id_slot|intval}"
               data-slot-id="{$slot.id_slot|intval}"
               data-required="{$slot.required|intval}">

            <div class="card-header phyto-bb-slot-header">
              <span class="phyto-bb-slot-number">{$smarty.foreach.slotLoop.iteration}</span>
              <span class="phyto-bb-slot-name">
                {$slot.slot_name|escape:'html':'UTF-8'}
                {if $slot.required}
                  <span class="phyto-bb-required" title="{l s='Required' mod='phyto_bundle_builder'}">*</span>
                {/if}
              </span>
              <span class="phyto-bb-slot-status phyto-bb-slot-empty">
                {l s='No product selected' mod='phyto_bundle_builder'}
              </span>
            </div>

            <div class="card-body phyto-bb-slot-body">

              {* Search box *}
              <div class="phyto-bb-search-box">
                <input type="text"
                       class="form-control phyto-bb-search"
                       placeholder="{l s='Search products...' mod='phyto_bundle_builder'}"
                       data-slot-id="{$slot.id_slot|intval}"
                       autocomplete="off">
              </div>

              {* Product grid *}
              <div class="phyto-bb-product-grid" id="phyto-grid-{$slot.id_slot|intval}">
                {foreach from=$slot.products item=product}
                  <div class="phyto-bb-product-card"
                       data-id="{$product.id_product|intval}"
                       data-name="{$product.name|escape:'html':'UTF-8'}"
                       data-price="{$product.price|floatval}"
                       data-slot="{$slot.id_slot|intval}">
                    <div class="phyto-bb-product-image">
                      {if $product.image_url}
                        <img src="{$product.image_url|escape:'html':'UTF-8'}"
                             alt="{$product.name|escape:'html':'UTF-8'}"
                             loading="lazy">
                      {else}
                        <div class="phyto-bb-no-image">
                          <i class="material-icons">image_not_supported</i>
                        </div>
                      {/if}
                      <span class="phyto-bb-checkmark"><i class="material-icons">check_circle</i></span>
                    </div>
                    <div class="phyto-bb-product-info">
                      <p class="phyto-bb-product-name">{$product.name|escape:'html':'UTF-8'}</p>
                      {if $product.reference}
                        <p class="phyto-bb-product-ref">{$product.reference|escape:'html':'UTF-8'}</p>
                      {/if}
                      <p class="phyto-bb-product-price">{$product.price|displayPrice}</p>
                    </div>
                  </div>
                {/foreach}

                {if $slot.products|@count == 0}
                  <p class="phyto-bb-no-products alert alert-warning">
                    {l s='No products available for this slot.' mod='phyto_bundle_builder'}
                  </p>
                {/if}
              </div>

              {* Hidden input that holds the chosen product ID *}
              <input type="hidden"
                     name="slot_{$slot.id_slot|intval}"
                     id="phyto-selection-{$slot.id_slot|intval}"
                     value="">

            </div>{* /card-body *}
          </div>{* /phyto-bb-slot *}
        {/foreach}

      </div>{* /phyto-bb-slots *}

      {* ---- Running total ---- *}
      <div class="phyto-bb-totals card" id="phyto-bb-totals">
        <div class="card-body">
          <div class="phyto-bb-total-line">
            <span class="phyto-bb-label">{l s='Products total:' mod='phyto_bundle_builder'}</span>
            <span class="phyto-bb-subtotal" id="phyto-subtotal">—</span>
          </div>
          {if $phyto_show_savings}
          <div class="phyto-bb-total-line phyto-bb-savings-line" id="phyto-savings-row" style="display:none;">
            <span class="phyto-bb-label phyto-bb-savings-label">{l s='You save:' mod='phyto_bundle_builder'}</span>
            <span class="phyto-bb-savings" id="phyto-savings">—</span>
          </div>
          {/if}
          <div class="phyto-bb-total-line phyto-bb-grand-total-line">
            <span class="phyto-bb-label">{l s='Bundle total:' mod='phyto_bundle_builder'}</span>
            <span class="phyto-bb-grand-total" id="phyto-grand-total">—</span>
          </div>
        </div>
      </div>

      {* ---- Submit button ---- *}
      <div class="phyto-bb-submit-row">
        <button type="submit"
                id="phyto-add-to-cart-btn"
                class="btn btn-primary btn-lg phyto-bb-submit-btn"
                disabled>
          {$phyto_cta_text|escape:'html':'UTF-8'}
        </button>
        <p class="phyto-bb-submit-hint" id="phyto-submit-hint">
          {l s='Please select a product for each required slot.' mod='phyto_bundle_builder'}
        </p>
      </div>

    </form>

  {/if}{* /isset $phyto_bundle *}

</div>{* /phyto-bundle-builder *}
{/block}
