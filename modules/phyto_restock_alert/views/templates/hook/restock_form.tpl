{*
 * restock_form.tpl
 *
 * "Notify me when available" subscribe widget shown on the product page.
 * Submitted via AJAX to the front controller.
 *}

<div id="phyto-restock-alert-widget" class="phyto-restock-widget card mt-3">
  <div class="card-body">
    <h3 class="phyto-restock-heading h6 mb-3">
      <i class="material-icons" aria-hidden="true">notifications_active</i>
      {l s='Notify me when available' mod='phyto_restock_alert'}
    </h3>

    <form id="phyto-restock-form" class="phyto-restock-form" novalidate>
      <input type="hidden" name="id_product"           value="{$phyto_restock_id_product|intval}">
      <input type="hidden" name="id_product_attribute" value="{$phyto_restock_id_product_attribute|intval}">
      <input type="hidden" name="action"                value="subscribe">

      <div class="form-group">
        <label for="phyto-restock-firstname" class="form-control-label">
          {l s='First Name' mod='phyto_restock_alert'}
          <span class="text-muted small">{l s='(optional)' mod='phyto_restock_alert'}</span>
        </label>
        <input
          type="text"
          id="phyto-restock-firstname"
          name="firstname"
          class="form-control form-control-sm"
          autocomplete="given-name"
          maxlength="100"
          value="{$phyto_restock_customer_firstname|escape:'html':'UTF-8'}"
        >
      </div>

      <div class="form-group">
        <label for="phyto-restock-email" class="form-control-label">
          {l s='Email Address' mod='phyto_restock_alert'}
          <span class="text-danger" aria-hidden="true">*</span>
        </label>
        <input
          type="email"
          id="phyto-restock-email"
          name="email"
          class="form-control form-control-sm"
          autocomplete="email"
          maxlength="255"
          required
          value="{$phyto_restock_customer_email|escape:'html':'UTF-8'}"
        >
      </div>

      <div id="phyto-restock-message" class="phyto-restock-message" role="alert" aria-live="polite" style="display:none;"></div>

      <button type="submit" id="phyto-restock-submit" class="btn btn-secondary btn-sm phyto-restock-submit">
        <i class="material-icons" aria-hidden="true">notifications</i>
        {l s='Notify Me' mod='phyto_restock_alert'}
      </button>
    </form>

    <p class="phyto-restock-privacy text-muted small mt-2 mb-0">
      {l s='We will send you one email when this product is back in stock. We will not share your email or spam you.' mod='phyto_restock_alert'}
    </p>
  </div>
</div>
