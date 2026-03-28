{block name='step'}
  <section id="{$identifier}"
           class="{[
               'checkout-step'   => true,
               '-current'        => $step_is_current,
               '-reachable'      => $step_is_reachable,
               '-complete'       => $step_is_complete,
               'js-current-step' => $step_is_current
           ]|classnames}"
           style="background:var(--fsl-white);border:1px solid var(--fsl-gray-200);border-radius:var(--fsl-radius-lg);margin-bottom:16px;overflow:hidden;">

    <h1 class="step-title js-step-title"
        style="display:flex;align-items:center;gap:12px;padding:20px 24px;margin:0;font-family:var(--fsl-font-body);font-size:16px;font-weight:600;color:var(--fsl-gray-800);cursor:pointer;background:{if $step_is_current}var(--fsl-off-white){else}var(--fsl-white){/if};">
      <span class="step-number done" style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:{if $step_is_complete}var(--fsl-forest){else}var(--fsl-gray-200){/if};color:{if $step_is_complete}var(--fsl-white){else}var(--fsl-gray-500){/if};font-size:13px;flex-shrink:0;">
        {if $step_is_complete}
          <i class="material-icons rtl-no-flip" style="font-size:16px;">check</i>
        {else}
          {$position}
        {/if}
      </span>
      <span style="flex:1;">{$title}</span>
      {if $step_is_complete}
        <span class="step-edit" style="font-size:12px;color:var(--fsl-forest);display:flex;align-items:center;gap:4px;">
          <i class="material-icons edit" style="font-size:14px;">edit</i>
          {l s='Edit' d='Shop.Theme.Actions'}
        </span>
      {/if}
    </h1>

    <div class="content" style="{if !$step_is_current}display:none;{/if}padding:24px;">
      {block name='step_content'}DUMMY STEP CONTENT{/block}
    </div>
  </section>
{/block}
