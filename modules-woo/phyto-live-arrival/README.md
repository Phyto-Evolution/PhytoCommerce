# Phyto Live Arrival — WooCommerce Plugin

**Version:** 1.0.0
**Requires WooCommerce:** 8.0+
**Requires WordPress:** 6.0+
**Text Domain:** `phyto-live-arrival`

---

## Overview

Adds a Live Arrival Guarantee (LAG) system for live plant orders. Per-product enable/disable, configurable guarantee window (hours after delivery), replacement vs refund vs store-credit policy choice. Buyers opt in at checkout. If a plant doesn't arrive alive, a claim can be logged against the order.

---

## Features

### Admin
- Product meta box: enable LAG toggle, guarantee window (hours), policy type, custom policy note
- Orders list column: "LAG" / "Claimed" badge on LAG-enrolled orders
- Order detail meta box: log a claim, add claim notes, set resolution status
- WooCommerce → Settings → LAG tab: global defaults for window, policy, checkout label, disclaimer

### Front-end
- Product page badge: "Live Arrival Guaranteed" with expandable policy details
- Checkout opt-in checkbox (required when cart has LAG products)
- Order confirmation email: LAG policy reminder paragraph

---

## Settings

| Option | Default | Description |
|--------|---------|-------------|
| Default Guarantee Window | 24 hours | How long after delivery buyers can report a claim |
| Default Policy Type | Replacement | Resolution offered (Replacement / Refund / Store Credit) |
| Checkout Opt-in Label | (see settings) | Text beside the checkout checkbox |
| Policy Disclaimer | (see settings) | Shown on product page and in confirmation emails |

---

## Developer Hooks

### Filters

```php
// Override LAG eligibility per product.
add_filter( 'phyto_lag_is_eligible', function( $enabled, $product_id ) {
    // return true/false
    return $enabled;
}, 10, 2 );

// Override guarantee window per product.
add_filter( 'phyto_lag_window_hours', function( $hours, $product_id ) {
    return 48; // Always 48 hours.
}, 10, 2 );

// Override checkout opt-in label text.
add_filter( 'phyto_lag_checkout_label', function( $label ) {
    return 'I agree to the LAG policy.';
} );
```

---

## Order Meta Keys

| Key | Values | Description |
|-----|--------|-------------|
| `_phyto_lag_accepted` | `1` / `0` | Buyer accepted LAG at checkout |
| `_phyto_lag_claimed` | `1` / `0` | Claim has been logged |
| `_phyto_lag_claim_notes` | string | Admin claim notes |
| `_phyto_lag_resolution` | `pending` / `replacement-sent` / `refunded` / `rejected` | Claim resolution status |

## Product Meta Keys

| Key | Values | Description |
|-----|--------|-------------|
| `_phyto_lag_enabled` | `1` / `0` | LAG enabled for this product |
| `_phyto_lag_window_hours` | integer | Per-product guarantee window |
| `_phyto_lag_policy_type` | `replacement` / `refund` / `store-credit` | Per-product policy |
| `_phyto_lag_policy_note` | string | Custom policy note |
