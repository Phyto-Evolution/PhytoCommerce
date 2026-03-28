{extends file='page.tpl'}

{block name='page_content_container'}
<main id="content">

  {* ── Hero ── *}
  <section class="fsl-hero">
    <div class="fsl-hero__bg" style="{if $fsl_hero_bg}background-image:url('{$fsl_hero_bg|escape:'htmlall':'UTF-8'}'){else}background: linear-gradient(135deg, #e8f0e9 0%, #f4f6f2 60%, #faf5ee 100%){/if}"></div>
    <div class="container">
      <div class="fsl-hero__content">
        <span class="fsl-hero__eyebrow">{l s='Forest Studio Labs' mod='fsl'}</span>
        <h1 class="fsl-hero__headline">
          Where Nature<br><em>Comes Home</em>
        </h1>
        <p class="fsl-hero__sub">
          {l s='Rare and cultivated plants, curated for collectors and garden lovers. Every plant, hand-picked.' mod='fsl'}
        </p>
        <div class="fsl-hero__actions">
          <a href="{$urls.pages.search}?search_query=" class="btn btn-primary">
            {l s='Shop Collection' mod='fsl'}
          </a>
          <a href="{url entity='category' id=2}" class="btn btn-outline-primary">
            {l s='Browse Categories' mod='fsl'}
          </a>
        </div>
      </div>
    </div>
  </section>

  {* ── Feature strip ── *}
  <section class="fsl-feature-strip">
    <div class="container">
      <div class="fsl-feature-strip-inner">
        <div class="fsl-feature-item">
          <div class="icon"><span class="material-icons" style="font-size:18px">local_shipping</span></div>
          <h5>{l s='Live Arrival' mod='fsl'}</h5>
          <p>{l s='Every plant guaranteed to arrive alive and healthy' mod='fsl'}</p>
        </div>
        <div class="fsl-feature-item">
          <div class="icon"><span class="material-icons" style="font-size:18px">science</span></div>
          <h5>{l s='TC Certified' mod='fsl'}</h5>
          <p>{l s='Tissue culture propagated, disease-free stock' mod='fsl'}</p>
        </div>
        <div class="fsl-feature-item">
          <div class="icon"><span class="material-icons" style="font-size:18px">eco</span></div>
          <h5>{l s='Expert Care' mod='fsl'}</h5>
          <p>{l s='Detailed care guides with every purchase' mod='fsl'}</p>
        </div>
        <div class="fsl-feature-item">
          <div class="icon"><span class="material-icons" style="font-size:18px">support_agent</span></div>
          <h5>{l s='Plant Support' mod='fsl'}</h5>
          <p>{l s='Our botanists are available 7 days a week' mod='fsl'}</p>
        </div>
      </div>
    </div>
  </section>

  {* ── PS hook: featured products etc ── *}
  {hook h='displayHome'}
  {hook h='displayFslHero'}

  {* ── Promo banner ── *}
  <section class="container">
    <div class="fsl-promo-banner">
      <div class="fsl-promo-banner__image" style="background-image:url('{$shop.logo|escape:'htmlall':'UTF-8'}'); background-color:var(--fsl-light-green)"></div>
      <div class="fsl-promo-banner__content">
        <span class="fsl-promo-banner__eyebrow">{l s='New Arrivals' mod='fsl'}</span>
        <h2 class="fsl-promo-banner__headline">{l s='Fresh from the Greenhouse' mod='fsl'}</h2>
        <p class="fsl-promo-banner__body">
          {l s='Hand-picked tissue culture specimens and seasonal rarities — updated every fortnight.' mod='fsl'}
        </p>
        <a href="{$urls.pages.new_products}" class="btn btn-primary">{l s='See New Arrivals' mod='fsl'}</a>
      </div>
    </div>
  </section>

  {hook h='displayFslPromo'}
  {hook h='displayHomeTab'}
  {hook h='displayWrapperBottom'}

  {* ── Testimonials ── *}
  <section class="fsl-testimonials">
    <div class="container">
      <div class="fsl-section-header">
        <span class="fsl-section-header__eyebrow">{l s='Happy Plant Parents' mod='fsl'}</span>
        <h2>{l s='What Our Customers Say' mod='fsl'}</h2>
      </div>
      <div class="fsl-testimonial-grid">
        <div class="fsl-testimonial-card">
          <div class="stars">★★★★★</div>
          <blockquote>"The Nepenthes arrived perfectly packed and already pitchering. Absolutely stunning specimen."</blockquote>
          <span class="author">— Priya R., Bangalore</span>
        </div>
        <div class="fsl-testimonial-card">
          <div class="stars">★★★★★</div>
          <blockquote>"Best TC plants I've ever ordered. The care card made all the difference for acclimatisation."</blockquote>
          <span class="author">— Arjun M., Mumbai</span>
        </div>
        <div class="fsl-testimonial-card">
          <div class="stars">★★★★★</div>
          <blockquote>"Incredible variety, fast dispatch. My collection has grown to 40+ plants thanks to Forest Studio."</blockquote>
          <span class="author">— Sneha K., Pune</span>
        </div>
      </div>
    </div>
  </section>

</main>
{/block}
