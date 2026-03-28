{block name='header_nav'}
  <nav class="header-nav" style="background:var(--fsl-white);border-bottom:1px solid var(--fsl-gray-100);padding:16px 0;">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6 d-none d-md-block" id="_desktop_logo">
          {renderLogo}
        </div>
        <div class="col-md-6 d-none d-md-flex justify-content-end">
          {hook h='displayNav1'}
        </div>
        <div class="d-md-none text-center mobile w-100">
          {hook h='displayNav2'}
          <div style="float:left;" id="menu-icon">
            <i class="material-icons">menu</i>
          </div>
          <div style="float:right;" id="_mobile_cart"></div>
          <div style="float:right;" id="_mobile_user_info"></div>
          <div id="_mobile_logo"></div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
  </nav>
{/block}

{block name='header_top'}
  <div class="header-top d-md-none">
    <div class="container">
      <div class="row">
        <div class="col-12">
          {hook h='displayTop'}
        </div>
      </div>
      <div id="mobile_top_menu_wrapper" class="row d-md-none" style="display:none;">
        <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
        <div class="js-top-menu-bottom">
          <div id="_mobile_currency_selector"></div>
          <div id="_mobile_language_selector"></div>
          <div id="_mobile_contact_link"></div>
        </div>
      </div>
    </div>
  </div>
  {hook h='displayNavFullWidth'}
{/block}
