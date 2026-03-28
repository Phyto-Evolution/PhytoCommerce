<div class="modal fade js-checkout-modal" id="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="border-radius:var(--fsl-radius-lg);">
      <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Global'}"
              style="position:absolute;top:12px;right:12px;background:none;border:none;cursor:pointer;font-size:20px;">
        &times;
      </button>
      <div class="js-modal-content" style="padding:24px;"></div>
    </div>
  </div>
</div>

<div style="text-align:center;padding:24px 16px;font-size:12px;color:var(--fsl-gray-400);">
  {if $tos_cms != false}
    <span class="d-block js-terms" style="margin-bottom:8px;">{$tos_cms nofilter}</span>
  {/if}
  {block name='copyright_link'}
    {l s='%copyright% %year% - Ecommerce software by %prestashop%' sprintf=['%prestashop%' => 'PrestaShop™', '%year%' => 'Y'|date, '%copyright%' => '©'] d='Shop.Theme.Global'}
  {/block}
</div>
