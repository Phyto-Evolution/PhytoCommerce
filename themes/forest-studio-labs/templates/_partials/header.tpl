<header id="header">
  {* ── Top bar ── *}
  <div class="header-top">
    <div class="container">
      {l s='Free shipping on orders above ₹999 · Grown with care · 10,000+ happy plant parents' mod='fsl'}
    </div>
  </div>

  {* ── Main nav ── *}
  <div class="header-nav">
    <div class="container d-flex align-items-center justify-content-between py-2">

      {* Logo *}
      <div id="_desktop_logo">
        <a href="{$urls.base_url}" class="d-flex align-items-center gap-2">
          {if $shop.logo}
            <img src="{$shop.logo}" alt="{$shop.name|escape:'htmlall':'UTF-8'}" class="logo" />
          {else}
            <span class="logo-text">
              {$shop.name|escape:'htmlall':'UTF-8'}
              <span>plant studio</span>
            </span>
          {/if}
        </a>
      </div>

      {* Navigation *}
      <nav class="d-none d-lg-flex">
        {block name='nav'}
          {hook h='displayTop'}
        {/block}
      </nav>

      {* Search *}
      <div id="search_widget" class="d-none d-md-block">
        {block name='search_widget'}
          {hook h='displaySearch'}
        {/block}
      </div>

      {* Icons *}
      <div class="d-flex align-items-center gap-3">
        <div id="_desktop_user_info">
          {block name='user_info'}
            {hook h='displayNav2'}
          {/block}
        </div>
        <div class="cart-preview">
          {block name='cart_top'}
            {hook h='displayNav1'}
          {/block}
        </div>
        {* Mobile menu toggle *}
        <button class="d-lg-none btn btn-light btn-sm" id="mobile-menu-btn" aria-label="Menu">
          <span class="material-icons" style="font-size:20px">menu</span>
        </button>
      </div>

    </div>
  </div>

  {* ── Mobile menu ── *}
  <div id="mobile_menu" class="mobile-nav d-lg-none" style="display:none!important">
    <div class="container py-3">
      {hook h='displayMobileHeader'}
    </div>
  </div>

</header>

<script>
document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
  const m = document.getElementById('mobile_menu');
  m.style.display = m.style.display === 'none' || !m.style.display ? 'block' : 'none';
});
</script>
