{extends file='checkout/_partials/steps/checkout-step.tpl'}

{block name='step_content'}
  {hook h='displayPersonalInformationTop' customer=$customer}

  {if $customer.is_logged && !$customer.is_guest}
    <p class="identity" style="font-size:15px;color:var(--fsl-gray-700);margin-bottom:8px;">
      {l s='Connected as [1]%firstname% %lastname%[/1].'
        d='Shop.Theme.Customeraccount'
        sprintf=[
          '[1]' => "<a href='{$urls.pages.identity}' style='color:var(--fsl-forest);font-weight:500;'>",
          '[/1]' => "</a>",
          '%firstname%' => $customer.firstname,
          '%lastname%' => $customer.lastname
        ]
      }
    </p>
    <p style="font-size:14px;color:var(--fsl-gray-500);margin-bottom:4px;">
      {l
        s='Not you? [1]Log out[/1]'
        d='Shop.Theme.Customeraccount'
        sprintf=[
          '[1]' => "<a href='{$urls.actions.logout}' style='color:var(--fsl-forest);'>",
          '[/1]' => "</a>"
        ]
      }
    </p>
    {if !isset($empty_cart_on_logout) || $empty_cart_on_logout}
      <p style="font-size:12px;color:var(--fsl-gray-400);margin-bottom:20px;">
        {l s='If you sign out now, your cart will be emptied.' d='Shop.Theme.Checkout'}
      </p>
    {/if}

    <div style="text-align:right;">
      <form method="GET" action="{$urls.pages.order}">
        <button class="continue" name="controller" type="submit" value="order"
                style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
          {l s='Continue' d='Shop.Theme.Actions'}
        </button>
      </form>
    </div>

  {else}
    <ul class="nav nav-inline" style="list-style:none;display:flex;gap:0;padding:0;margin:0 0 20px;border-bottom:2px solid var(--fsl-gray-100);" role="tablist">
      <li class="nav-item">
        <a class="nav-link {if !$show_login_form}active{/if}"
           data-toggle="tab"
           href="#checkout-guest-form"
           role="tab"
           aria-controls="checkout-guest-form"
           {if !$show_login_form}aria-selected="true"{/if}
           style="display:block;padding:10px 20px;font-size:14px;font-weight:500;color:{if !$show_login_form}var(--fsl-forest){else}var(--fsl-gray-500){/if};text-decoration:none;border-bottom:2px solid {if !$show_login_form}var(--fsl-forest){else}transparent{/if};margin-bottom:-2px;">
          {if $guest_allowed}
            {l s='Order as a guest' d='Shop.Theme.Checkout'}
          {else}
            {l s='Create an account' d='Shop.Theme.Customeraccount'}
          {/if}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {if $show_login_form}active{/if}"
           data-link-action="show-login-form"
           data-toggle="tab"
           href="#checkout-login-form"
           role="tab"
           aria-controls="checkout-login-form"
           {if $show_login_form}aria-selected="true"{/if}
           style="display:block;padding:10px 20px;font-size:14px;font-weight:500;color:{if $show_login_form}var(--fsl-forest){else}var(--fsl-gray-500){/if};text-decoration:none;border-bottom:2px solid {if $show_login_form}var(--fsl-forest){else}transparent{/if};margin-bottom:-2px;">
          {l s='Sign in' d='Shop.Theme.Actions'}
        </a>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane {if !$show_login_form}active{/if}" id="checkout-guest-form" role="tabpanel" {if $show_login_form}aria-hidden="true"{/if}>
        {render file='checkout/_partials/customer-form.tpl' ui=$register_form guest_allowed=$guest_allowed}
      </div>
      <div class="tab-pane {if $show_login_form}active{/if}" id="checkout-login-form" role="tabpanel" {if !$show_login_form}aria-hidden="true"{/if}>
        {render file='checkout/_partials/login-form.tpl' ui=$login_form}
      </div>
    </div>

  {/if}
{/block}
