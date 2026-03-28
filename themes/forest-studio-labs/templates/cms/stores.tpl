{extends file='page.tpl'}

{block name='page_title'}{l s='Our Stores' d='Shop.Theme.Global'}{/block}

{block name='page_content_container'}
<main style="padding:48px 0 80px;background:var(--fsl-off-white);">
  <div class="container">
    {block name='breadcrumb'}{include file='_partials/breadcrumb.tpl'}{/block}
    <h1 style="font-family:var(--fsl-font-display);font-weight:400;margin-bottom:36px">{l s='Our Stores' d='Shop.Theme.Global'}</h1>

    <div class="row g-4">
      {foreach $stores as $store}
        <div class="col-lg-6">
          <div style="background:var(--fsl-white);border-radius:var(--fsl-radius-lg);border:1px solid var(--fsl-gray-200);overflow:hidden;box-shadow:var(--fsl-shadow-sm);">

            {if !empty($store.image.bySize.stores_default.url)}
              <div style="height:200px;overflow:hidden;">
                <picture>
                  {if !empty($store.image.bySize.stores_default.sources.avif)}<source srcset="{$store.image.bySize.stores_default.sources.avif}" type="image/avif">{/if}
                  {if !empty($store.image.bySize.stores_default.sources.webp)}<source srcset="{$store.image.bySize.stores_default.sources.webp}" type="image/webp">{/if}
                  <img src="{$store.image.bySize.stores_default.url}"
                    alt="{if !empty($store.image.legend)}{$store.image.legend|escape:'htmlall':'UTF-8'}{else}{$store.name|escape:'htmlall':'UTF-8'}{/if}"
                    style="width:100%;height:100%;object-fit:cover;">
                </picture>
              </div>
            {/if}

            <div style="padding:24px;">
              <h2 style="font-family:var(--fsl-font-display);font-weight:400;font-size:1.4rem;margin-bottom:10px">
                {$store.name|escape:'htmlall':'UTF-8'}
              </h2>

              <div style="display:flex;gap:8px;margin-bottom:16px;color:var(--fsl-gray-500);font-size:13px;">
                <span class="material-icons" style="font-size:16px;color:var(--fsl-sage);margin-top:1px">location_on</span>
                <address style="font-style:normal;line-height:1.6">{$store.address.formatted nofilter}</address>
              </div>

              {if $store.phone}
                <div style="display:flex;gap:8px;margin-bottom:8px;font-size:13px;color:var(--fsl-gray-600)">
                  <span class="material-icons" style="font-size:16px;color:var(--fsl-sage)">phone</span>
                  <span>{$store.phone|escape:'htmlall':'UTF-8'}</span>
                </div>
              {/if}
              {if $store.email}
                <div style="display:flex;gap:8px;margin-bottom:8px;font-size:13px;color:var(--fsl-gray-600)">
                  <span class="material-icons" style="font-size:16px;color:var(--fsl-sage)">email</span>
                  <a href="mailto:{$store.email|escape:'htmlall':'UTF-8'}" style="color:var(--fsl-forest)">{$store.email|escape:'htmlall':'UTF-8'}</a>
                </div>
              {/if}

              {if $store.business_hours}
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--fsl-gray-100)">
                  <p style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--fsl-gray-400);margin-bottom:10px">{l s='Opening Hours' d='Shop.Theme.Global'}</p>
                  <table style="width:100%;font-size:13px;color:var(--fsl-gray-600)">
                    {foreach $store.business_hours as $day}
                      <tr>
                        <td style="padding:2px 12px 2px 0;font-weight:500;white-space:nowrap">{$day.day|truncate:4:'.'}</td>
                        <td>
                          {foreach $day.hours as $h}
                            <span>{$h}</span>{if !$h@last}, {/if}
                          {/foreach}
                        </td>
                      </tr>
                    {/foreach}
                  </table>
                </div>
              {/if}

              {if $store.note}
                <p style="margin-top:16px;font-size:13px;color:var(--fsl-gray-500);font-style:italic">{$store.note|escape:'htmlall':'UTF-8'}</p>
              {/if}
            </div>
          </div>
        </div>
      {foreachelse}
        <div class="col-12" style="text-align:center;padding:60px 20px;color:var(--fsl-gray-400)">
          <span class="material-icons" style="font-size:3rem;display:block;margin-bottom:16px">store</span>
          <p>{l s='No stores found.' d='Shop.Theme.Global'}</p>
        </div>
      {/foreach}
    </div>
  </div>
</main>
{/block}
