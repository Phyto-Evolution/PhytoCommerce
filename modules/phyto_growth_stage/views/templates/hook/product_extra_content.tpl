{**
 * Phyto Growth Stage — Front-End Stage Card
 *
 * Displays a detailed growth stage card on the product page.
 * Variables supplied by hookDisplayProductExtraContent:
 *   $growth_stage   — row with: stage_name, difficulty, description, weeks_to_next, icon_slug
 *   $stage_index    — 0-based position of this stage in the full sequence
 *   $stage_total    — total number of stages defined
 *   $weeks_display  — weeks to transition (override or default)
 *   $difficulty_color — hex colour for the difficulty badge
 *}

<div class="phyto-stage-card" id="phyto-stage-card-main"
     data-stage-index="{$stage_index|intval}"
     data-stage-total="{$stage_total|intval}">

    {* ---- Header: icon + stage name ---- *}
    <div class="phyto-stage-card__header">
        {if !empty($growth_stage.icon_slug)}
            <span class="phyto-stage-card__icon" aria-hidden="true">{$growth_stage.icon_slug|escape:'htmlall':'UTF-8'}</span>
        {/if}
        <h3 class="phyto-stage-card__title">
            {$growth_stage.stage_name|escape:'htmlall':'UTF-8'}
        </h3>
    </div>

    {* ---- Difficulty badge ---- *}
    {if !empty($growth_stage.difficulty)}
        <div class="phyto-stage-card__meta">
            <span class="phyto-stage-badge phyto-stage-badge--difficulty"
                  style="color:{$difficulty_color|escape:'htmlall':'UTF-8'}; border-color:{$difficulty_color|escape:'htmlall':'UTF-8'};">
                {$growth_stage.difficulty|escape:'htmlall':'UTF-8'}
            </span>
        </div>
    {/if}

    {* ---- Progress bar: stage_index of stage_total ---- *}
    {if $stage_total > 0}
        {assign var='progress_pct' value=0}
        {if $stage_total > 1}
            {assign var='progress_pct' value=($stage_index / ($stage_total - 1)) * 100}
        {else}
            {assign var='progress_pct' value=100}
        {/if}

        <div class="phyto-stage-card__progress-wrap">
            <div class="phyto-stage-card__progress-label">
                {l s='Stage' mod='phyto_growth_stage'} {($stage_index + 1)|intval} {l s='of' mod='phyto_growth_stage'} {$stage_total|intval}
            </div>
            <div class="progress phyto-stage-progress" role="progressbar"
                 aria-valuenow="{($stage_index + 1)|intval}"
                 aria-valuemin="1"
                 aria-valuemax="{$stage_total|intval}"
                 title="{l s='Stage' mod='phyto_growth_stage'} {($stage_index + 1)|intval} {l s='of' mod='phyto_growth_stage'} {$stage_total|intval}">
                <div class="progress-bar phyto-stage-progress__bar"
                     style="width:{$progress_pct|string_format:'%.1f'}%;">
                </div>
            </div>
        </div>
    {/if}

    {* ---- Weeks to next stage ---- *}
    {if $weeks_display > 0}
        <p class="phyto-stage-card__weeks">
            <i class="phyto-stage-card__weeks-icon" aria-hidden="true">&#128337;</i>
            {l s='Weeks to next stage:' mod='phyto_growth_stage'}
            <strong>{$weeks_display|intval}</strong>
        </p>
    {/if}

    {* ---- Description ---- *}
    {if !empty($growth_stage.description)}
        <div class="phyto-stage-card__description">
            {$growth_stage.description nofilter}
        </div>
    {/if}

</div>
