{*
  FSL product card — used in category listings, search, featured modules
*}
<article class="product-miniature js-product-miniature"
         data-id-product="{$product.id_product}"
         data-id-product-attribute="{$product.id_product_attribute|default:0}"
         itemscope itemtype="http://schema.org/Product">

  <div class="thumbnail-container">
    {* Flags *}
    {foreach $product.flags as $flag}
      <span class="product-flag {$flag.type|escape:'htmlall':'UTF-8'}">
        {$flag.label|escape:'htmlall':'UTF-8'}
      </span>
    {/foreach}

    {* Wishlist *}
    {hook h='displayWishlistButton' product=$product}

    {* Image *}
    <a href="{$product.url}" class="thumbnail product-thumbnail" itemprop="url">
      {if $product.cover}
        <img
          src="{$product.cover.bySize.home_default.url}"
          alt="{$product.cover.legend|default:$product.name|escape:'htmlall':'UTF-8'}"
          loading="lazy"
          itemprop="image"
          width="{$product.cover.bySize.home_default.width}"
          height="{$product.cover.bySize.home_default.height}">
      {else}
        <img src="{$urls.no_picture_image.bySize.home_default.url}" alt="{$product.name|escape:'htmlall':'UTF-8'}" loading="lazy">
      {/if}
    </a>

    {* Quick add on hover *}
    <div class="highlighted-informations">
      {if $product.add_to_cart_url}
        <a href="{$product.add_to_cart_url}"
           rel="nofollow"
           data-button-action="add-to-cart"
           class="btn btn-primary btn-sm w-100 add-to-cart"
           {if !$product.add_to_cart_url}disabled{/if}>
          <span class="material-icons" style="font-size:16px">shopping_bag</span>
          {l s='Quick Add' mod='fsl'}
        </a>
      {else}
        <a href="{$product.url}" class="btn btn-light btn-sm w-100">
          {l s='View Details' mod='fsl'}
        </a>
      {/if}
    </div>
  </div>

  <div class="product-description">
    {* Category breadcrumb *}
    {if isset($product.category_name)}
      <span style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--fsl-gray-400);">
        {$product.category_name|escape:'htmlall':'UTF-8'}
      </span>
    {/if}

    {* Name *}
    <h3 class="product-title" itemprop="name">
      <a href="{$product.url}">{$product.name|escape:'htmlall':'UTF-8'}</a>
    </h3>

    {* Price *}
    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer">
      <meta itemprop="priceCurrency" content="{$currency.iso_code}">
      {if $product.has_discount}
        <span class="regular-price">{$product.regular_price}</span>
      {/if}
      <span class="price" itemprop="price" content="{$product.price_amount}">
        {$product.price}
      </span>
      {if $product.has_discount}
        <span class="discount-percentage" style="font-size:10px;font-weight:600;color:var(--fsl-warm);margin-left:4px;">
          -{$product.discount_percentage}
        </span>
      {/if}
      <meta itemprop="availability" content="{if $product.availability == 'available'}InStock{else}OutOfStock{/if}">
    </div>

    {* Rating *}
    {if $product.rating.total > 0}
      <div style="font-size:11px;color:var(--fsl-warm);margin-top:4px;">
        {for $i=1 to 5}
          {if $i <= $product.rating.grade}★{else}☆{/if}
        {/for}
        <span style="color:var(--fsl-gray-400);margin-left:4px;">({$product.rating.total})</span>
      </div>
    {/if}
  </div>

</article>
