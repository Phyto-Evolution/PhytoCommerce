<template id="password-feedback">
  <div
    class="password-strength-feedback mt-1"
    style="display: none;"
  >
    <div class="progress-container">
      <div class="progress mb-1" style="height:4px;background:var(--fsl-gray-200);border-radius:2px;">
        <div class="progress-bar" role="progressbar" value="50" aria-valuemin="0" aria-valuemax="100" style="background:var(--fsl-forest);height:4px;border-radius:2px;transition:width .3s;"></div>
      </div>
    </div>
    <script type="text/javascript" class="js-hint-password">
      {if !empty($page['password-policy']['feedbacks'])}
        {$page['password-policy']['feedbacks']|@json_encode nofilter}
      {/if}
    </script>

    <div class="password-strength-text" style="font-size:12px;color:var(--fsl-gray-500);margin-top:4px;"></div>
    <div class="password-requirements" style="font-size:12px;color:var(--fsl-gray-500);margin-top:6px;">
      <p class="password-requirements-length" data-translation="{l s='Enter a password between %s and %s characters' d='Shop.Theme.Customeraccount'}">
        <i class="material-icons" style="font-size:14px;vertical-align:middle;color:var(--fsl-sage)">check_circle</i>
        <span></span>
      </p>
      <p class="password-requirements-score" data-translation="{l s='The minimum score must be: %s' d='Shop.Theme.Customeraccount'}">
        <i class="material-icons" style="font-size:14px;vertical-align:middle;color:var(--fsl-sage)">check_circle</i>
        <span></span>
      </p>
    </div>
  </div>
</template>
