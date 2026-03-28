{extends file='customer/_partials/customer-form.tpl'}

{block name='form_field'}
  {if $field.name === 'password' and $guest_allowed}
    <div style="background:var(--fsl-light-green);border-radius:var(--fsl-radius);padding:12px 16px;margin-bottom:16px;">
      <p style="margin:0 0 4px;">
        <span style="font-size:14px;font-weight:600;color:var(--fsl-gray-800);">{l s='Create an account' d='Shop.Theme.Checkout'}</span>
        <span style="font-size:13px;font-style:italic;color:var(--fsl-gray-500);margin-left:6px;">{l s='(optional)' d='Shop.Theme.Checkout'}</span>
      </p>
      <p style="font-size:13px;color:var(--fsl-gray-600);margin:0;">{l s='And save time on your next order!' d='Shop.Theme.Checkout'}</p>
    </div>
    {$smarty.block.parent}
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

{block name='form_buttons'}
  <div style="text-align:right;margin-top:20px;">
    <button class="continue" name="continue" data-link-action="register-new-customer" type="submit" value="1"
            style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
      {l s='Continue' d='Shop.Theme.Actions'}
    </button>
  </div>
{/block}
