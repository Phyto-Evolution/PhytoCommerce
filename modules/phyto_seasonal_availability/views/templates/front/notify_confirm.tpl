{**
 * Seasonal availability — notify-me confirmation page.
 *
 * Extends the store's full-width layout.
 * Rendered when the front controller renders a confirmation rather than
 * redirecting (fallback path).
 *}
{extends file='layouts/layout-full-width.tpl'}

{block name='content'}
<section class="page-content phyto-notify-confirm">
  <div class="container">
    <div class="row">
      <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">

        <div class="alert alert-success phyto-notify-confirm__alert" role="alert">
          <span class="phyto-notify-confirm__icon" aria-hidden="true">&#x2713;</span>
          {l s="Thank you! We'll notify you when this plant is back in season." mod='phyto_seasonal_availability'}
        </div>

        <p class="phyto-notify-confirm__body">
          {l s="You have been added to the notification list. We will send you an email as soon as this product is available for shipping again." mod='phyto_seasonal_availability'}
        </p>

        <a href="javascript:history.back()"
           class="btn btn-default phyto-notify-confirm__back">
          &larr; {l s='Back' mod='phyto_seasonal_availability'}
        </a>

      </div>
    </div>
  </div>
</section>
{/block}
