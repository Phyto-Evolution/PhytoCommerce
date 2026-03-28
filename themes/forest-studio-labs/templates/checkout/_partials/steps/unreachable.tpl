{block name='step'}
  <section class="checkout-step -unreachable" id="{$identifier}"
           style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);margin-bottom:16px;padding:20px 24px;opacity:.6;">
    <h1 class="step-title js-step-title"
        style="display:flex;align-items:center;gap:12px;margin:0;font-family:var(--fsl-font-body);font-size:16px;font-weight:600;color:var(--fsl-gray-400);">
      <span class="step-number"
            style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:var(--fsl-gray-100);color:var(--fsl-gray-400);font-size:13px;flex-shrink:0;">
        {$position}
      </span>
      {$title}
    </h1>
  </section>
{/block}
