{block name='account_transformation_form'}
  <div style="background:var(--fsl-light-green);border-radius:var(--fsl-radius-lg);padding:24px;margin-bottom:24px;">
    <h4 style="font-family:var(--fsl-font-display);font-size:20px;font-weight:400;color:var(--fsl-gray-800);margin:0 0 12px;">
      {l s='Save time on your next order, sign up now' d='Shop.Theme.Checkout'}
    </h4>
    <ul style="list-style:none;padding:0;margin:0 0 20px;">
      <li style="display:flex;align-items:center;gap:8px;font-size:14px;color:var(--fsl-gray-600);margin-bottom:6px;">
        <i class="material-icons" style="font-size:16px;color:var(--fsl-forest);">check_circle</i>
        {l s='Personalized and secure access' d='Shop.Theme.Customeraccount'}
      </li>
      <li style="display:flex;align-items:center;gap:8px;font-size:14px;color:var(--fsl-gray-600);margin-bottom:6px;">
        <i class="material-icons" style="font-size:16px;color:var(--fsl-forest);">check_circle</i>
        {l s='Fast and easy checkout' d='Shop.Theme.Customeraccount'}
      </li>
      <li style="display:flex;align-items:center;gap:8px;font-size:14px;color:var(--fsl-gray-600);">
        <i class="material-icons" style="font-size:16px;color:var(--fsl-forest);">check_circle</i>
        {l s='Easier merchandise return' d='Shop.Theme.Customeraccount'}
      </li>
    </ul>
    <form method="post">
      <div style="margin-bottom:16px;">
        <label for="field-password" style="display:block;font-size:13px;font-weight:500;color:var(--fsl-gray-700);margin-bottom:6px;">
          {l s='Set your password:' d='Shop.Forms.Labels'} <span style="color:#e53935;">*</span>
        </label>
        <input type="password" id="field-password" class="fsl-input" data-validate="isPasswd" required name="password" value=""
               style="width:100%;max-width:320px;padding:10px 14px;border:1.5px solid var(--fsl-gray-200);border-radius:var(--fsl-radius);font-family:var(--fsl-font-body);font-size:14px;">
      </div>
      <input type="hidden" name="submitTransformGuestToCustomer" value="1">
      <button type="submit"
              style="padding:12px 28px;background:var(--fsl-forest);color:var(--fsl-white);border:none;border-radius:var(--fsl-radius-pill);font-family:var(--fsl-font-body);font-size:14px;font-weight:500;cursor:pointer;">
        {l s='Create account' d='Shop.Theme.Actions'}
      </button>
    </form>
  </div>
{/block}
