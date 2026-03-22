{**
 * Front-end shipping season calendar — 3-col × 4-row Bootstrap 3 grid.
 * Rendered by hookDisplayProductExtraContent.
 *
 * Smarty vars:
 *   $phyto_seasonal_months      array  1-12 => label
 *   $phyto_seasonal_ship_months array  of int month numbers
 *   $phyto_seasonal_dorm_months array  of int month numbers
 *   $phyto_seasonal_current     int    current month (1-12)
 *}

<div class="phyto-seasonal-calendar" aria-label="{l s='Shipping Season Calendar' mod='phyto_seasonal_availability'}">

  <h4 class="phyto-seasonal-calendar__title">
    {l s='Shipping Season Calendar' mod='phyto_seasonal_availability'}
  </h4>

  {* Legend ──────────────────────────────────────────────────────── *}
  <ul class="phyto-seasonal-calendar__legend list-inline">
    <li>
      <span class="phyto-seasonal-month phyto-seasonal-month--ship phyto-legend-swatch" aria-hidden="true"></span>
      {l s='Shipping' mod='phyto_seasonal_availability'}
    </li>
    <li>
      <span class="phyto-seasonal-month phyto-seasonal-month--dorm phyto-legend-swatch" aria-hidden="true"></span>
      {l s='Dormancy' mod='phyto_seasonal_availability'}
    </li>
    <li>
      <span class="phyto-seasonal-month phyto-legend-swatch" aria-hidden="true"></span>
      {l s='Unavailable' mod='phyto_seasonal_availability'}
    </li>
  </ul>

  {* 12-month grid — 4 rows × 3 columns ─────────────────────────── *}
  <div class="row phyto-seasonal-calendar__grid">

    {foreach $phyto_seasonal_months as $num => $label}

      {assign var='is_ship'    value=in_array($num, $phyto_seasonal_ship_months)}
      {assign var='is_dorm'    value=in_array($num, $phyto_seasonal_dorm_months)}
      {assign var='is_current' value=($num == $phyto_seasonal_current)}

      {* Build modifier class string *}
      {assign var='chip_class' value='phyto-seasonal-month'}
      {if $is_ship}
        {assign var='chip_class' value="`$chip_class` phyto-seasonal-month--ship"}
      {elseif $is_dorm}
        {assign var='chip_class' value="`$chip_class` phyto-seasonal-month--dorm"}
      {/if}
      {if $is_current}
        {assign var='chip_class' value="`$chip_class` phyto-seasonal-month--is-current"}
      {/if}

      {* Determine accessible state label *}
      {if $is_ship && $is_current}
        {assign var='state_label' value="{l s='Shipping — current month' mod='phyto_seasonal_availability'}"}
      {elseif $is_ship}
        {assign var='state_label' value="{l s='Shipping' mod='phyto_seasonal_availability'}"}
      {elseif $is_dorm && $is_current}
        {assign var='state_label' value="{l s='Dormancy — current month' mod='phyto_seasonal_availability'}"}
      {elseif $is_dorm}
        {assign var='state_label' value="{l s='Dormancy' mod='phyto_seasonal_availability'}"}
      {elseif $is_current}
        {assign var='state_label' value="{l s='Unavailable — current month' mod='phyto_seasonal_availability'}"}
      {else}
        {assign var='state_label' value="{l s='Unavailable' mod='phyto_seasonal_availability'}"}
      {/if}

      <div class="col-xs-4">
        <div class="{$chip_class}"
             role="img"
             aria-label="{$label|escape:'html':'UTF-8'}: {$state_label|escape:'html':'UTF-8'}">
          <span class="phyto-seasonal-month__label">{$label|escape:'html':'UTF-8'}</span>
          {if $is_current}
            <span class="phyto-seasonal-month__now-badge"
                  aria-hidden="true"
                  title="{l s='Current month' mod='phyto_seasonal_availability'}">&#x25CF;</span>
          {/if}
        </div>
      </div>

    {/foreach}

  </div>{* /.row *}

</div>{* /.phyto-seasonal-calendar *}
