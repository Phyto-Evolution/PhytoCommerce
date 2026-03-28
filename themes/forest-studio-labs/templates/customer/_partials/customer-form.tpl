{block name='customer_form'}
  {block name='customer_form_errors'}
    {include file='_partials/form-errors.tpl' errors=$errors['']}
  {/block}

  <form action="{block name='customer_form_actionurl'}{$action}{/block}" id="customer-form" class="js-customer-form" method="post">
    <div>
      {block name='form_fields'}
        {foreach from=$formFields item="field"}
          {block name='form_field'}
            {if $field.type === "password"}
              <div class="field-password-policy">
                {form_field field=$field}
              </div>
            {else}
              {form_field field=$field}
            {/if}
          {/block}
        {/foreach}
        {$hook_create_account_form nofilter}
      {/block}
    </div>

    {block name='customer_form_footer'}
      <footer style="margin-top:24px;padding-top:16px;border-top:1px solid var(--fsl-gray-100);">
        <input type="hidden" name="submitCreate" value="1">
        {block name='form_buttons'}
          <div style="text-align:right;">
            <button class="fsl-btn-primary" data-link-action="save-customer" type="submit"
                    style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
              {l s='Save' d='Shop.Theme.Actions'}
            </button>
          </div>
        {/block}
      </footer>
    {/block}
  </form>
{/block}
