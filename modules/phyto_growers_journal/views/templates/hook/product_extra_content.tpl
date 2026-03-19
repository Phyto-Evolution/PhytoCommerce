{**
 * Front-office product extra content — Grower's Journal timeline.
 *
 * Variables assigned by hookDisplayProductExtraContent:
 *   $phyto_entries       array   Approved journal entries for this product
 *   $phyto_img_base_url  string  Base URL for photo thumbnails
 *   $phyto_can_post      bool    Whether the current customer may submit an entry
 *   $phyto_post_url      string  URL to the post submission form
 *   $phyto_id_product    int     Current product ID
 *}

<section class="phyto-journal-timeline" aria-label="{l s="Grower's Journal" mod='phyto_growers_journal'}">

  {if isset($smarty.get.phyto_success) && $smarty.get.phyto_success == 1}
    <div class="phyto-journal-alert phyto-journal-alert--success" role="alert">
      <strong>{l s='Thank you!' mod='phyto_growers_journal'}</strong>
      {l s='Your journal entry has been submitted and is pending approval.' mod='phyto_growers_journal'}
    </div>
  {/if}

  {if $phyto_entries|@count > 0}
    <ol class="phyto-journal-entries">
      {foreach $phyto_entries as $entry}
        <li class="phyto-journal-entry phyto-journal-entry--{$entry.entry_type|lower|escape:'html':'UTF-8'}
          {if $entry.entry_type == 'Milestone'} phyto-journal-entry--milestone{/if}">

          <div class="phyto-journal-entry__date-pill">
            {$entry.entry_date|date_format:"%b %d, %Y"|escape:'html':'UTF-8'}
          </div>

          <div class="phyto-journal-entry__card">

            <div class="phyto-journal-entry__header">
              <h3 class="phyto-journal-entry__title">
                {if $entry.entry_type == 'Milestone'}
                  <span class="phyto-journal-badge phyto-journal-badge--milestone" aria-label="{l s='Milestone' mod='phyto_growers_journal'}">
                    &#127942;
                  </span>
                {/if}
                {$entry.title|escape:'html':'UTF-8'}
              </h3>

              <div class="phyto-journal-entry__meta">
                <span class="phyto-journal-badge phyto-journal-badge--{$entry.entry_type|lower|escape:'html':'UTF-8'}">
                  {if $entry.entry_type == 'Store'}
                    {l s='Store Update' mod='phyto_growers_journal'}
                  {elseif $entry.entry_type == 'Customer'}
                    {l s='Customer Update' mod='phyto_growers_journal'}
                  {else}
                    {l s='Milestone' mod='phyto_growers_journal'}
                  {/if}
                </span>
                {if $entry.id_customer > 0 && $entry.firstname}
                  <span class="phyto-journal-entry__author">
                    {l s='by' mod='phyto_growers_journal'}
                    {$entry.firstname|escape:'html':'UTF-8'}
                    {$entry.lastname|substr:0:1|escape:'html':'UTF-8'}.
                  </span>
                {/if}
              </div>
            </div>

            {if $entry.body}
              <div class="phyto-journal-entry__body">
                {$entry.body|@htmlspecialchars_decode nofilter}
              </div>
            {/if}

            {if $entry.photo1 || $entry.photo2 || $entry.photo3}
              <div class="phyto-journal-entry__photos">
                {foreach ['photo1', 'photo2', 'photo3'] as $photoField}
                  {if $entry[$photoField]}
                    <a href="{$phyto_img_base_url|escape:'html':'UTF-8'}{$entry[$photoField]|escape:'html':'UTF-8'}"
                       class="phyto-journal-photo-link"
                       target="_blank"
                       rel="noopener"
                       aria-label="{l s='View photo' mod='phyto_growers_journal'}">
                      <img
                        src="{$phyto_img_base_url|escape:'html':'UTF-8'}{$entry[$photoField]|escape:'html':'UTF-8'}"
                        alt="{$entry.title|escape:'html':'UTF-8'}"
                        class="phyto-journal-photo-thumb"
                        loading="lazy"
                        width="160"
                        height="120"
                      >
                    </a>
                  {/if}
                {/foreach}
              </div>
            {/if}

          </div>{* /.phyto-journal-entry__card *}
        </li>
      {/foreach}
    </ol>

  {else}
    <p class="phyto-journal-empty">
      {l s='No grow log entries yet. Check back soon!' mod='phyto_growers_journal'}
    </p>
  {/if}

  {if $phyto_can_post}
    <div class="phyto-journal-cta">
      <a href="{$phyto_post_url|escape:'html':'UTF-8'}" class="btn btn-secondary phyto-journal-submit-btn">
        {l s='Share your grow update' mod='phyto_growers_journal'}
      </a>
    </div>
  {/if}

</section>
