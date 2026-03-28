<div class="phyto-brand-banner" aria-label="{$phyto_brand_name|escape:'htmlall':'UTF-8'} branding">
  {if $phyto_brand_logo_url}
    <img src="{$phyto_brand_logo_url|escape:'htmlall':'UTF-8'}" alt="{$phyto_brand_name|escape:'htmlall':'UTF-8'} logo" class="phyto-brand-banner__logo" loading="lazy" />
  {/if}
  <div class="phyto-brand-banner__text">
    <strong>{$phyto_brand_name|escape:'htmlall':'UTF-8'}</strong>
    <span>{$phyto_brand_tagline|escape:'htmlall':'UTF-8'}</span>
  </div>
  <div class="phyto-brand-banner__contact">
    {if $phyto_brand_contact_phone}
      <a href="tel:{$phyto_brand_contact_phone|regex_replace:'/[^0-9+]/':''|escape:'htmlall':'UTF-8'}">
        {$phyto_brand_contact_phone|escape:'htmlall':'UTF-8'}
      </a>
    {/if}
    {if $phyto_brand_contact_email}
      <a href="mailto:{$phyto_brand_contact_email|escape:'htmlall':'UTF-8'}">
        {$phyto_brand_contact_email|escape:'htmlall':'UTF-8'}
      </a>
    {/if}
  </div>
</div>
{if $phyto_brand_contact_address}
  <div class="phyto-brand-address">
    {$phyto_brand_contact_address|escape:'htmlall':'UTF-8'}
  </div>
{/if}
