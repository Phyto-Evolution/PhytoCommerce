---
title: "Phyto Live Arrival (WooCommerce)"
description: "Live Arrival Guarantee system for plant orders — per-product policy, checkout opt-in, claim logging, and email reminders."
module_name: "phyto-live-arrival"
platform: "WooCommerce"
category: "Plant Science"
category_id: "woo-plant-science"
version: "1.0.0"
weight: 38
---

Phyto Live Arrival adds a complete Live Arrival Guarantee (LAG) system to WooCommerce plant stores. Shop owners enable the guarantee per product, set the claim window in hours, and choose whether an approved claim resolves as a replacement plant, a full refund, or store credit. All policy details are stored per product and can also be overridden at the global level through a dedicated WooCommerce settings tab.

At checkout, buyers are presented with a required opt-in checkbox whenever their cart contains at least one LAG-enrolled product. Their acceptance is stored against the order so the store can always demonstrate informed consent. Once an order is placed, a policy reminder paragraph is automatically appended to the customer's order confirmation email, ensuring they know exactly how and when to file a claim.

When a claim arises, the shop manager opens the order in WooCommerce, ticks "Mark as Claimed," enters claim notes (photo references, communication history, etc.), and sets the resolution status — Pending, Replacement Sent, Refunded, or Rejected. The orders list also displays a colour-coded LAG badge so staff can spot LAG orders and open claims at a glance without needing to open each order individually.

## Features

### Admin

- **Product meta box** — enable/disable LAG per product; set guarantee window (hours, default 24); choose policy type (Replacement, Refund, or Store Credit); add a custom policy note that overrides the global disclaimer on the product page
- **Orders list column** — "LAG" badge on all orders where the buyer opted in; badge changes to "Claimed" once a claim is logged
- **Order detail claim meta box** — mark order as claimed, add detailed claim notes, set resolution status (Pending / Replacement Sent / Refunded / Rejected)
- **WooCommerce → Settings → LAG tab** — global default guarantee window; global default policy type; customisable checkout opt-in label text; customisable policy disclaimer shown on product pages and in emails

### Front-end

- **Product page badge** — "Live Arrival Guaranteed" pill badge displayed at priority 15 on `woocommerce_single_product_summary`; clicking expands a panel showing the claim window, policy type, and disclaimer text; hidden when LAG is not enabled on the product
- **Checkout opt-in** — checkbox injected via `woocommerce_review_order_before_submit`; marked as required when any cart item has LAG enabled; checkout is blocked with a clear error message if the buyer does not check the box
- **Order confirmation email** — LAG policy reminder block automatically appended to customer-facing processing and completed-order emails for orders where the buyer accepted the guarantee

## Settings

| Setting | Default | Description |
|---------|---------|-------------|
| Default Guarantee Window | 24 hours | Global fallback claim window used when no per-product value is set |
| Default Policy Type | Replacement | Global fallback resolution type (Replacement / Refund / Store Credit) |
| Checkout Opt-in Label | *(see settings)* | Label text displayed beside the checkout acceptance checkbox |
| Policy Disclaimer | *(see settings)* | Paragraph shown on product pages and appended to confirmation emails |

Individual products override the global window and policy type via the product meta box. A custom policy note on the product also replaces the global disclaimer on that specific product page.

## Developer Hooks

Three filters are available for programmatic overrides:

```php
// Override whether LAG is active for a given product.
add_filter( 'phyto_lag_is_eligible', function( $enabled, $product_id ) {
    return $enabled;
}, 10, 2 );

// Override the guarantee window (in hours) for a given product.
add_filter( 'phyto_lag_window_hours', function( $hours, $product_id ) {
    return 48;
}, 10, 2 );

// Override the checkout opt-in label text globally.
add_filter( 'phyto_lag_checkout_label', function( $label ) {
    return 'I agree to the Live Arrival Guarantee policy.';
} );
```

The `phyto_lag_is_eligible` filter runs before the product badge, the checkout checkbox visibility check, and the email reminder logic, so a single filter callback controls all three surfaces.
