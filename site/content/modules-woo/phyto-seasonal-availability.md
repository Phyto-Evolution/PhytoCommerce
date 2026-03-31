---
title: "phyto_seasonal_availability"
description: "Block WooCommerce product purchases during off-season months and capture 'notify me when in season' email subscribers automatically."
module_name: "phyto-seasonal-availability"
category: "Plant Science"
category_id: "woo-plant-science"
platform: "WooCommerce"
version: "1.0.0"
weight: 32
---

## Overview

Phyto Seasonal Availability brings shipping-window awareness to WooCommerce. Specialty plant retailers — tissue-culture producers, orchid growers, bare-root nurseries, field-grown stock sellers — routinely have hard constraints on when a plant can be safely shipped: ambient temperature ranges, dormancy requirements, post-deflask acclimation windows, or phytosanitary quarantine periods. Standard WooCommerce treats every product as always purchasable, leaving stores to manage this through manual stock manipulation or custom page copy with no automation.

This plugin solves the problem structurally. Each product gets a "Seasonal Availability" admin panel where the store operator selects which calendar months the plant is available for purchase. Outside those months, the standard Add-to-Cart button is replaced — automatically, with no template editing — by a clear unavailability message and a "Notify me when in season" email capture form. When the store re-opens that product for its next season, all queued subscribers receive an automated notification email.

This plugin is the direct WooCommerce port of the **phyto_seasonal_availability** PrestaShop module.

---

## Admin Workflow

### Step 1 — Configure a product's shipping window

Open any WooCommerce product in the editor. Scroll to the **Seasonal Availability** meta box and:

- Check each month when the product can be purchased and shipped.
- Tick **Available year-round** to bypass the month picker entirely (useful for products like fertiliser or pots that have no seasonal restriction).
- Optionally edit the **Unavailable message** — the text customers see during the off-season.

Save the product. The change is live immediately.

### Step 2 — View and export subscribers

Navigate to **WooCommerce → Seasonal Subscribers** to see the full subscriber table: product name, email, subscription date, and notification status. Click **Export CSV** to download a dated CSV file for use in your CRM or email platform.

---

## Customer Experience

### Shop / Archive

The Add-to-Cart button is replaced by a soft amber **"Not in season"** pill — clearly communicating unavailability without wasting a click.

### Single Product Page

- The Add-to-Cart button and quantity field are hidden. The product is also marked non-purchasable via the `woocommerce_is_purchasable` filter, preventing cart manipulation via direct URL or WooCommerce REST API.
- A styled info box displays the unavailability message (customisable per product or via the `phyto_sa_unavailable_message` filter).
- A compact email form — forest-green, clean, AJAX-powered — lets customers subscribe without leaving the page.

---

## Notification Emails

No cron job is required. When a product's available months are saved and the current calendar month falls within the new window, the plugin automatically queries all unnotified subscribers for that product and dispatches plain-text emails via `wp_mail()`. Each subscriber is marked notified after a successful send.

---

## Database

The plugin creates one table on activation:

| Column | Type | Notes |
|--------|------|-------|
| `id` | `bigint(20) unsigned` | Primary key, auto-increment |
| `product_id` | `bigint(20) unsigned` | WooCommerce product ID |
| `email` | `varchar(200)` | Subscriber address |
| `subscribed_at` | `datetime` | UTC timestamp |
| `notified` | `tinyint(1)` | 0 = pending, 1 = sent |

---

## Hooks

### `phyto_sa_unavailable_message`

Filter the off-season message shown on the single product page.

```php
add_filter( 'phyto_sa_unavailable_message', function( $message, $product_id ) {
    return 'This orchid ships October–February only. Check back soon!';
}, 10, 2 );
```

### `phyto_sa_is_in_season`

Override the season availability check — for custom calendar logic, staff preview, or testing.

```php
add_filter( 'phyto_sa_is_in_season', function( $result, $product_id ) {
    // Staff preview: always in season for admins.
    if ( current_user_can( 'manage_woocommerce' ) ) {
        return true;
    }
    return $result;
}, 10, 2 );
```

---

## Source Layout

```
phyto-seasonal-availability/
├── phyto-seasonal-availability.php          # Bootstrap, constants, WC check, DB activation
├── includes/
│   ├── class-phyto-seasonal-admin.php       # Product meta box, subscribers page, CSV export
│   ├── class-phyto-seasonal-frontend.php    # Season check, cart blocking, subscribe form
│   └── class-phyto-seasonal-subscribers.php # AJAX handler, notification mailer
├── assets/
│   ├── css/frontend.css                     # Pill, message box, subscribe form styles
│   └── js/subscribe.js                      # AJAX form submit + inline feedback
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_seasonal_availability`](/modules/phyto-seasonal-availability/) in the `modules/` directory.
