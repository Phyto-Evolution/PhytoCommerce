{block name="address_form"}
  <div class="js-address-form">
    {include file='_partials/form-errors.tpl' errors=$errors['']}

    {block name="address_form_url"}
      <form
        method="POST"
        action="{url entity='address' params=['id_address' => $id_address]}"
        data-id-address="{$id_address}"
        data-refresh-url="{url entity='address' params=['ajax' => 1, 'action' => 'addressForm']}"
      >
    {/block}

      {block name="address_form_fields"}
        <section class="form-fields">
          {block name='form_fields'}
            {foreach from=$formFields item="field"}
              {block name='form_field'}
                {form_field field=$field}
              {/block}
            {/foreach}
          {/block}
        </section>
      {/block}

      {block name="address_form_footer"}
        <footer style="margin-top:24px;padding-top:16px;border-top:1px solid var(--fsl-gray-100);">
          <input type="hidden" name="submitAddress" value="1">
          {block name='form_buttons'}
            <div style="text-align:right;">
              <button type="submit"
                      style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
                {l s='Save' d='Shop.Theme.Actions'}
              </button>
            </div>
          {/block}
        </footer>
      {/block}

    </form>
  </div>
{/block}
