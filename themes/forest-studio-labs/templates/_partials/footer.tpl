<footer id="footer">
  <div class="footer-container">
    <div class="footer-top">
      <div class="container">
        <div class="row">

          {* Brand column *}
          <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
            <div class="fsl-footer-brand">
              <span class="logo-text">
                {$shop.name|escape:'htmlall':'UTF-8'}
                <span>plant studio</span>
              </span>
              <p>
                {l s='Rare and cultivated plants for collectors, gardeners, and green-space lovers across India.' mod='fsl'}
              </p>
              <div class="fsl-footer-social">
                <a href="#" aria-label="Instagram"><i class="material-icons" style="font-size:16px">photo_camera</i></a>
                <a href="#" aria-label="Facebook"><i class="material-icons" style="font-size:16px">thumb_up</i></a>
                <a href="#" aria-label="YouTube"><i class="material-icons" style="font-size:16px">play_circle</i></a>
              </div>
            </div>
          </div>

          {* Links columns from PS blocks *}
          <div class="col-lg-6 col-md-6 mb-4 mb-lg-0">
            <div class="row">
              {hook h='displayFooter'}
            </div>
          </div>

          {* Newsletter *}
          <div class="col-lg-3 col-md-6">
            <div class="fsl-newsletter">
              <h4>{l s='Stay in the Green' mod='fsl'}</h4>
              <p>{l s='Plant drops, care guides and seasonal collections — straight to your inbox.' mod='fsl'}</p>
              <form class="fsl-newsletter-form" action="{$urls.pages.index}" method="post">
                <input type="email" name="email" placeholder="{l s='your@email.com' mod='fsl'}" required>
                <button type="submit">{l s='Join' mod='fsl'}</button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>© {$smarty.now|date_format:'%Y'} {$shop.name|escape:'htmlall':'UTF-8'} — All rights reserved.</span>
        <span>
          {l s='Made with' mod='fsl'} <span style="color:var(--fsl-sage)">♥</span> {l s='for plant lovers' mod='fsl'}
        </span>
        <div class="d-flex gap-3">
          {hook h='displayFooterBefore'}
        </div>
      </div>
    </div>
  </div>
</footer>
