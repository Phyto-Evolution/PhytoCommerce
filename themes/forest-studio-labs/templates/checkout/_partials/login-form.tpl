{extends file='customer/_partials/login-form.tpl'}

{block name='form_buttons'}
  <div style="text-align:right;margin-top:20px;">
    <button class="continue" name="continue" data-link-action="sign-in" type="submit" value="1"
            style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
      {l s='Continue' d='Shop.Theme.Actions'}
    </button>
  </div>
{/block}
