{**
 * Phyto Growth Stage — Product Price Block Badge
 *
 * Injects a compact inline pill near the product price area.
 * Variables supplied by hookDisplayProductPriceBlock:
 *   $badge_stage      — row with: stage_name, difficulty
 *   $badge_diff_color — hex colour string for the difficulty text
 *}

{if isset($badge_stage) && !empty($badge_stage.stage_name)}
<span class="phyto-stage-badge" title="{l s='Growth Stage' mod='phyto_growth_stage'}: {$badge_stage.stage_name|escape:'htmlall':'UTF-8'}">
    {if !empty($badge_stage.icon_slug)}
        <span class="phyto-stage-badge__icon" aria-hidden="true">{$badge_stage.icon_slug|escape:'htmlall':'UTF-8'}</span>
    {/if}
    <span class="phyto-stage-badge__name">{$badge_stage.stage_name|escape:'htmlall':'UTF-8'}</span>
    {if !empty($badge_stage.difficulty)}
        <span class="phyto-stage-badge__difficulty"
              style="color:{$badge_diff_color|escape:'htmlall':'UTF-8'};">
            {$badge_stage.difficulty|escape:'htmlall':'UTF-8'}
        </span>
    {/if}
</span>
{/if}
