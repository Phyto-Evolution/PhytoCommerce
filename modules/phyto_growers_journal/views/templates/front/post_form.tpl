{**
 * Front-office customer journal entry submission form.
 *
 * Variables:
 *   $phyto_product      Product|null  The product being referenced
 *   $phyto_id_product   int
 *   $phyto_form_action  string
 *   $phyto_token        string        CSRF token
 *   $phyto_errors       array         Validation errors (optional)
 *   $phyto_old_values   array         Previously submitted values on error (optional)
 *}
{extends file='page.tpl'}

{block name='page_title'}
  {l s="Grower's Journal — Share Your Update" mod='phyto_growers_journal'}
{/block}

{block name='page_content'}
<div class="phyto-journal-post-page">

  {if isset($phyto_product) && $phyto_product}
    <p class="phyto-journal-post-product">
      {l s='Submitting for:' mod='phyto_growers_journal'}
      <strong>{$phyto_product->name|escape:'html':'UTF-8'}</strong>
    </p>
  {/if}

  {if isset($phyto_errors) && $phyto_errors|@count > 0}
    <div class="alert alert-danger phyto-journal-alert phyto-journal-alert--error" role="alert">
      <ul class="phyto-journal-error-list">
        {foreach $phyto_errors as $err}
          <li>{$err|escape:'html':'UTF-8'}</li>
        {/foreach}
      </ul>
    </div>
  {/if}

  <form
    action="{$phyto_form_action|escape:'html':'UTF-8'}"
    method="post"
    enctype="multipart/form-data"
    class="phyto-journal-post-form"
    novalidate
  >
    <input type="hidden" name="submitJournalPost" value="1">
    <input type="hidden" name="id_product" value="{$phyto_id_product|intval}">
    <input type="hidden" name="token" value="{$phyto_token|escape:'html':'UTF-8'}">

    <div class="form-group">
      <label for="phyto_entry_date" class="phyto-journal-label">
        {l s='Date' mod='phyto_growers_journal'}
      </label>
      <input
        type="date"
        id="phyto_entry_date"
        name="entry_date"
        class="form-control phyto-journal-input"
        value="{if isset($phyto_old_values.entry_date)}{$phyto_old_values.entry_date}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
        max="{$smarty.now|date_format:'%Y-%m-%d'}"
        required
      >
    </div>

    <div class="form-group">
      <label for="phyto_title" class="phyto-journal-label required-field">
        {l s='Title' mod='phyto_growers_journal'}
        <em class="required" aria-hidden="true">*</em>
      </label>
      <input
        type="text"
        id="phyto_title"
        name="title"
        class="form-control phyto-journal-input"
        value="{if isset($phyto_old_values.title)}{$phyto_old_values.title}{/if}"
        maxlength="255"
        required
        placeholder="{l s='e.g. Week 3 — first true leaves appearing' mod='phyto_growers_journal'}"
      >
    </div>

    <div class="form-group">
      <label for="phyto_body" class="phyto-journal-label required-field">
        {l s='Description' mod='phyto_growers_journal'}
        <em class="required" aria-hidden="true">*</em>
      </label>
      <textarea
        id="phyto_body"
        name="body"
        class="form-control phyto-journal-textarea"
        rows="6"
        required
        placeholder="{l s='Describe your growing progress, observations, or tips…' mod='phyto_growers_journal'}"
      >{if isset($phyto_old_values.body)}{$phyto_old_values.body}{/if}</textarea>
    </div>

    <div class="phyto-journal-photos-group">
      <p class="phyto-journal-photos-label">
        {l s='Photos (optional — max 3, up to 2 MB each, JPG/PNG/GIF/WebP)' mod='phyto_growers_journal'}
      </p>
      {foreach ['photo1', 'photo2', 'photo3'] as $photoField}
        <div class="form-group phyto-journal-photo-field">
          <label for="phyto_{$photoField}" class="phyto-journal-label">
            {l s='Photo' mod='phyto_growers_journal'} {$photoField|substr:5}
          </label>
          <input
            type="file"
            id="phyto_{$photoField}"
            name="{$photoField}"
            class="phyto-journal-file-input"
            accept="image/jpeg,image/png,image/gif,image/webp"
          >
        </div>
      {/foreach}
    </div>

    <div class="phyto-journal-post-form__footer">
      <button type="submit" class="btn btn-primary phyto-journal-submit-btn">
        {l s='Submit Entry' mod='phyto_growers_journal'}
      </button>
      {if $phyto_id_product}
        <a
          href="{$urls.base_url|escape:'html':'UTF-8'}"
          class="btn btn-link phyto-journal-cancel-btn"
        >
          {l s='Cancel' mod='phyto_growers_journal'}
        </a>
      {/if}
    </div>

  </form>
</div>

{block name='page_footer_container'}{/block}
{/block}
