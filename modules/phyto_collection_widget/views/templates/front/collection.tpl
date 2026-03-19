{**
 * My Plant Collection — customer-facing page
 *
 * Smarty variables provided by CollectionModuleFrontController:
 *   {$phyto_coll_items}        — array of collection item rows
 *   {$phyto_coll_allow_public} — bool, whether public sharing is enabled
 *   {$phyto_coll_has_public}   — bool, whether the customer has any public items
 *   {$phyto_coll_public_url}   — string, shareable public URL
 *   {$phyto_coll_token}        — CSRF token
 *   {$phyto_coll_ajax_url}     — AJAX endpoint URL
 *}

{extends file='page.tpl'}

{block name='page_title'}
  {l s='My Plant Collection' mod='phyto_collection_widget'}
{/block}

{block name='page_content'}

<section class="phyto-coll-page">

  {* ── Breadcrumb supplement ──────────────────────────────────────────── *}
  <div class="phyto-coll-header">
    <h1 class="phyto-coll-title">
      <span class="phyto-coll-icon" aria-hidden="true">&#127807;</span>
      {l s='My Plant Collection' mod='phyto_collection_widget'}
    </h1>

    {if $phyto_coll_allow_public && $phyto_coll_has_public}
      <div class="phyto-coll-share-bar">
        <span class="phyto-coll-share-label">{l s='Share your collection:' mod='phyto_collection_widget'}</span>
        <input
          type="text"
          id="phyto-coll-share-url"
          class="phyto-coll-share-input form-control"
          value="{$phyto_coll_public_url|escape:'html':'UTF-8'}"
          readonly
        >
        <button
          type="button"
          class="btn btn-secondary phyto-coll-copy-btn"
          data-target="phyto-coll-share-url"
        >{l s='Copy' mod='phyto_collection_widget'}</button>
      </div>
    {/if}
  </div>

  {* ── Flash messages ─────────────────────────────────────────────────── *}
  <div id="phyto-coll-messages" class="phyto-coll-messages" role="status" aria-live="polite"></div>

  {* ── Empty state ────────────────────────────────────────────────────── *}
  {if empty($phyto_coll_items)}
    <div class="phyto-coll-empty alert alert-info">
      <p>{l s='Your collection is empty. Plants from your orders will appear here automatically after purchase.' mod='phyto_collection_widget'}</p>
      <a href="{$urls.pages.index}" class="btn btn-primary">
        {l s='Browse our plants' mod='phyto_collection_widget'}
      </a>
    </div>
  {else}

    {* ── Collection grid ────────────────────────────────────────────────── *}
    <ul class="phyto-coll-grid" id="phyto-coll-grid">
      {foreach $phyto_coll_items as $item}
        <li
          class="phyto-coll-card"
          id="phyto-coll-card-{$item.id_item|intval}"
          data-id-item="{$item.id_item|intval}"
        >

          {* Plant image *}
          <a href="{$item.product_url|escape:'html':'UTF-8'}" class="phyto-coll-card__img-link">
            <img
              src="{$item.image_url|escape:'html':'UTF-8'}"
              alt="{$item.product_name|escape:'html':'UTF-8'}"
              class="phyto-coll-card__img"
              loading="lazy"
            >
          </a>

          {* Card body *}
          <div class="phyto-coll-card__body">
            <h2 class="phyto-coll-card__name">
              <a href="{$item.product_url|escape:'html':'UTF-8'}">
                {$item.product_name|escape:'html':'UTF-8'}
              </a>
            </h2>

            {if $item.date_acquired}
              <p class="phyto-coll-card__date">
                <small>
                  {l s='Acquired:' mod='phyto_collection_widget'}
                  <time datetime="{$item.date_acquired|escape:'html':'UTF-8'}">
                    {$item.date_acquired|date_format:'%d %B %Y'}
                  </time>
                </small>
              </p>
            {/if}

            {* Personal note *}
            <div class="phyto-coll-card__note-wrap">
              <label
                for="phyto-note-{$item.id_item|intval}"
                class="phyto-coll-card__note-label"
              >
                {l s='My notes:' mod='phyto_collection_widget'}
              </label>
              <textarea
                id="phyto-note-{$item.id_item|intval}"
                class="phyto-coll-card__note form-control"
                rows="3"
                data-id-item="{$item.id_item|intval}"
                placeholder="{l s='Add a personal note about this plant…' mod='phyto_collection_widget'}"
              >{$item.personal_note|escape:'html':'UTF-8'}</textarea>
              <button
                type="button"
                class="btn btn-sm btn-outline-secondary phyto-coll-save-note mt-1"
                data-id-item="{$item.id_item|intval}"
              >
                {l s='Save note' mod='phyto_collection_widget'}
              </button>
            </div>

            {* Public / Private toggle *}
            {if $phyto_coll_allow_public}
              <div class="phyto-coll-card__visibility mt-2">
                <label class="phyto-coll-toggle-label">
                  <input
                    type="checkbox"
                    class="phyto-coll-toggle-public"
                    data-id-item="{$item.id_item|intval}"
                    {if $item.is_public}checked{/if}
                  >
                  <span class="phyto-coll-toggle-text">
                    {l s='Show in public collection' mod='phyto_collection_widget'}
                  </span>
                </label>
              </div>
            {/if}
          </div>

          {* Card footer — remove button *}
          <div class="phyto-coll-card__footer">
            <button
              type="button"
              class="btn btn-sm btn-link text-danger phyto-coll-remove"
              data-id-item="{$item.id_item|intval}"
              data-confirm="{l s='Remove this plant from your collection?' mod='phyto_collection_widget'}"
            >
              &#10005; {l s='Remove' mod='phyto_collection_widget'}
            </button>
          </div>

        </li>
      {/foreach}
    </ul>

  {/if}

</section>

{* ── Inline JS for AJAX interactions ───────────────────────────────────── *}
<script>
(function () {
  'use strict';

  var ajaxUrl = {$phyto_coll_ajax_url|json_encode};
  var token   = {$phyto_coll_token|json_encode};

  function showMsg(msg, ok) {
    var el = document.getElementById('phyto-coll-messages');
    el.innerHTML = '<div class="alert ' + (ok ? 'alert-success' : 'alert-danger') + '">' +
      escHtml(msg) + '</div>';
    setTimeout(function () { el.innerHTML = ''; }, 4000);
  }

  function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
  }

  function postAjax(data, cb) {
    data.token = token;
    data.ajax  = 1;
    var body = Object.keys(data).map(function (k) {
      return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
    }).join('&');

    fetch(ajaxUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body
    })
    .then(function (r) { return r.json(); })
    .then(cb)
    .catch(function () { showMsg('Request failed.', false); });
  }

  // Save note
  document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('phyto-coll-save-note')) return;
    var idItem  = e.target.getAttribute('data-id-item');
    var textarea = document.getElementById('phyto-note-' + idItem);
    postAjax({
      phyto_coll_action: 'update_note',
      id_item:           idItem,
      personal_note:     textarea ? textarea.value : ''
    }, function (resp) {
      showMsg(resp.message, resp.success);
    });
  });

  // Toggle public
  document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('phyto-coll-toggle-public')) return;
    var idItem = e.target.getAttribute('data-id-item');
    postAjax({
      phyto_coll_action: 'toggle_public',
      id_item:           idItem
    }, function (resp) {
      showMsg(resp.message, resp.success);
      if (!resp.success) {
        // Revert checkbox state on failure
        e.target.checked = !e.target.checked;
      }
    });
  });

  // Remove item
  document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('phyto-coll-remove')) return;
    var msg = e.target.getAttribute('data-confirm') || 'Remove this item?';
    if (!window.confirm(msg)) return;
    var idItem = e.target.getAttribute('data-id-item');
    postAjax({
      phyto_coll_action: 'remove_item',
      id_item:           idItem
    }, function (resp) {
      if (resp.success) {
        var card = document.getElementById('phyto-coll-card-' + idItem);
        if (card) { card.remove(); }
        showMsg(resp.message, true);
      } else {
        showMsg(resp.message, false);
      }
    });
  });

  // Copy share URL
  document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('phyto-coll-copy-btn')) return;
    var targetId = e.target.getAttribute('data-target');
    var input    = document.getElementById(targetId);
    if (!input) return;
    input.select();
    try {
      document.execCommand('copy');
      e.target.textContent = '{l s='Copied!' mod='phyto_collection_widget' js=1}';
      setTimeout(function () {
        e.target.textContent = '{l s='Copy' mod='phyto_collection_widget' js=1}';
      }, 2000);
    } catch (err) {
      // Fallback: do nothing, user can manually copy
    }
  });
}());
</script>

{/block}
