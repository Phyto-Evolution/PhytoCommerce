{extends file='page.tpl'}

{block name='head_seo'}
  <title>{$page.meta.title|escape:'htmlall':'UTF-8'}</title>
  <meta name="description" content="{$page.meta.description|escape:'htmlall':'UTF-8'}">
  {hook h='displayMetaTags'}
{/block}

{block name='page_content_container'}
<main id="product">
  <div class="container">

    {* Breadcrumb *}
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}

    {* ── Product hero ── *}
    <div class="fsl-product-hero">

      {* ── Left: images ── *}
      <div class="product-images-col">
        {block name='product_cover_thumbnails'}
          <div class="product-cover js-product-cover">
            {if $product.cover}
              <img id="product-cover-img"
                   src="{$product.cover.bySize.large_default.url}"
                   alt="{$product.cover.legend|default:$product.name|escape:'htmlall':'UTF-8'}"
                   itemprop="image"
                   style="width:100%;height:auto;display:block;">
            {else}
              <img src="{$urls.no_picture_image.bySize.large_default.url}"
                   alt="{$product.name|escape:'htmlall':'UTF-8'}">
            {/if}
          </div>

          {if $product.images|count > 1}
            <div class="product-images">
              {foreach $product.images as $image}
                <div class="thumb-container js-thumb {if $image.cover}selected{/if}"
                     data-image-large-src="{$image.bySize.large_default.url}">
                  <img src="{$image.bySize.small_default.url}"
                       alt="{$image.legend|default:$product.name|escape:'htmlall':'UTF-8'}"
                       loading="lazy">
                </div>
              {/foreach}
            </div>
          {/if}
        {/block}
      </div>

      {* ── Right: info ── *}
      <div class="product-information">
        {block name='product_name'}
          <p class="product-reference text-muted">
            {if $product.reference}{l s='Ref.' mod='fsl'} {$product.reference|escape:'htmlall':'UTF-8'}{/if}
          </p>
          <h1 itemprop="name">{$product.name|escape:'htmlall':'UTF-8'}</h1>
        {/block}

        {* Rating *}
        {hook h='displayProductListReviews' product=$product}

        {* Price *}
        {block name='product_prices'}
          <div class="product-price-and-shipping" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
            <meta itemprop="priceCurrency" content="{$currency.iso_code}">
            <meta itemprop="availability" content="{if $product.availability_message}InStock{else}OutOfStock{/if}">
            {hook h='displayProductPriceBlock' product=$product type='old_price'}
            {hook h='displayProductPriceBlock' product=$product type='before_price'}
            <span class="current-price" itemprop="price" content="{$product.price_amount}">
              {$product.price}
            </span>
            {hook h='displayProductPriceBlock' product=$product type='price'}
            {if $product.has_discount}
              <span class="regular-price">{$product.regular_price}</span>
              <span class="discount-percentage">{$product.discount_percentage_absolute}% {l s='off' mod='fsl'}</span>
            {/if}
            {hook h='displayProductPriceBlock' product=$product type='unit_price'}
            {hook h='displayProductPriceBlock' product=$product type='after_price'}
          </div>
        {/block}

        {* Short description *}
        {block name='product_description_short'}
          {if $product.description_short}
            <div class="product-description-short" itemprop="description">
              {$product.description_short nofilter}
            </div>
          {/if}
        {/block}

        {* Combinations (variants) *}
        {block name='product_variants'}
          {hook h='displayProductAdditionalInfo' product=$product}
        {/block}

        {* Add to cart *}
        {block name='product_add_to_cart'}
          {if $product.add_to_cart_url}
            <div class="product-add-to-cart">
              <div class="qty-input-group">
                <button class="js-touchspin-down" type="button">−</button>
                <input id="quantity_wanted"
                       type="number"
                       name="qty"
                       value="{$product.quantity_wanted}"
                       min="1"
                       max="{if $product.stock.quantity > 0}{$product.stock.quantity}{else}1{/if}"
                       class="js-cart-product-quantity">
                <button class="js-touchspin-up" type="button">+</button>
              </div>
              <button class="btn btn-primary add-to-cart"
                      data-button-action="add-to-cart"
                      type="submit">
                <span class="material-icons" style="font-size:16px">shopping_bag</span>
                {l s='Add to Cart' mod='fsl'}
              </button>
            </div>
            {hook h='displayProductButtons' product=$product}
          {else}
            <p class="product-unavailable alert alert-warning mt-3">
              {$product.availability_message|escape:'htmlall':'UTF-8'}
            </p>
          {/if}
        {/block}

        {* Trust badges *}
        <div class="fsl-trust-row">
          <div class="fsl-trust-item">
            <span class="material-icons" style="font-size:18px;color:var(--fsl-sage)">verified</span>
            <span>{l s='Live arrival guaranteed' mod='fsl'}</span>
          </div>
          <div class="fsl-trust-item">
            <span class="material-icons" style="font-size:18px;color:var(--fsl-sage)">local_shipping</span>
            <span>{l s='Ships in 24–48 hrs' mod='fsl'}</span>
          </div>
          <div class="fsl-trust-item">
            <span class="material-icons" style="font-size:18px;color:var(--fsl-sage)">eco</span>
            <span>{l s='Sustainably propagated' mod='fsl'}</span>
          </div>
        </div>

      </div>
    </div>

    {* ── Tabs: description, care, reviews ── *}
    {block name='product_tabs'}
      <section class="tabs mt-4">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <a class="nav-link active" id="tab-description" data-toggle="tab" href="#description" role="tab">
              {l s='Description' mod='fsl'}
            </a>
          </li>
          {foreach $product.extraContent as $extra}
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="tab-{$extra.attr.id}" data-toggle="tab" href="#{$extra.attr.id}" role="tab">
                {$extra.title|escape:'htmlall':'UTF-8'}
              </a>
            </li>
          {/foreach}
          <li class="nav-item" role="presentation">
            <a class="nav-link" id="tab-reviews" data-toggle="tab" href="#product-reviews" role="tab">
              {l s='Reviews' mod='fsl'}
            </a>
          </li>
        </ul>
        <div class="tab-content" style="padding:28px 0">
          <div class="tab-pane active" id="description" role="tabpanel">
            {if $product.description}
              <div class="product-description">{$product.description nofilter}</div>
            {else}
              <p class="text-muted">{l s='No description available.' mod='fsl'}</p>
            {/if}
          </div>
          {foreach $product.extraContent as $extra}
            <div class="tab-pane" id="{$extra.attr.id}" role="tabpanel">
              {$extra.content nofilter}
            </div>
          {/foreach}
          <div class="tab-pane" id="product-reviews" role="tabpanel">
            {hook h='displayProductAdditionalInfo' product=$product}
            {hook h='displayProductListReviewsBlock' product=$product}
          </div>
        </div>
      </section>
    {/block}

    {* ── Related products ── *}
    {block name='product_accessories'}
      {if $accessories}
        <section class="product-accessories py-5">
          <div class="fsl-section-header">
            <span class="fsl-section-header__eyebrow">{l s='Pairs Well With' mod='fsl'}</span>
            <h2>{l s='You Might Also Like' mod='fsl'}</h2>
          </div>
          <div class="row">
            {foreach $accessories as $accessory}
              <div class="col-6 col-md-3 mb-4">
                {include file='catalog/listing/product-miniature.tpl' product=$accessory}
              </div>
            {/foreach}
          </div>
        </section>
      {/if}
    {/block}

    {hook h='displayProductFooter'}

  </div>
</main>
{/block}

{* Image gallery JS *}
{block name='javascript_bottom'}
  {parent}
  <script>
  document.querySelectorAll('.js-thumb').forEach(function(thumb) {
    thumb.addEventListener('click', function() {
      var img = document.getElementById('product-cover-img');
      if (img) img.src = this.dataset.imageLargeSrc;
      document.querySelectorAll('.js-thumb').forEach(function(t) { t.classList.remove('selected'); });
      this.classList.add('selected');
    });
  });
  // Qty spinner
  document.querySelector('.js-touchspin-up')?.addEventListener('click', function() {
    var inp = document.getElementById('quantity_wanted');
    if (inp) inp.value = Math.min(parseInt(inp.value||1) + 1, parseInt(inp.max||999));
  });
  document.querySelector('.js-touchspin-down')?.addEventListener('click', function() {
    var inp = document.getElementById('quantity_wanted');
    if (inp) inp.value = Math.max(parseInt(inp.value||1) - 1, parseInt(inp.min||1));
  });
  </script>
{/block}
