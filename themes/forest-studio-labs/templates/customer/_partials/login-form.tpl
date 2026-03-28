{block name='login_form'}
  {block name='login_form_errors'}
    {include file='_partials/form-errors.tpl' errors=$errors['']}
  {/block}

  <form id="login-form" action="{block name='login_form_actionurl'}{$action}{/block}" method="post">
    <div>
      {block name='login_form_fields'}
        {foreach from=$formFields item="field"}
          {block name='form_field'}
            {form_field field=$field}
          {/block}
        {/foreach}
      {/block}
      <div style="margin-top:12px;">
        <a href="{$urls.pages.password}" rel="nofollow"
           style="font-size:13px;color:var(--fsl-forest);">
          {l s='Forgot your password?' d='Shop.Theme.Customeraccount'}
        </a>
      </div>
    </div>

    {block name='login_form_footer'}
      <footer style="margin-top:24px;padding-top:16px;border-top:1px solid var(--fsl-gray-100);">
        <input type="hidden" name="submitLogin" value="1">
        {block name='form_buttons'}
          <div style="text-align:center;">
            <button id="submit-login" class="btn btn-primary" data-link-action="sign-in" type="submit"
                    style="padding:12px 40px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;width:100%;">
              {l s='Sign in' d='Shop.Theme.Actions'}
            </button>
          </div>
        {/block}
      </footer>
    {/block}
  </form>
{/block}
