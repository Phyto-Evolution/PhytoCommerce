# Phyto Seasonal Availability for WooCommerce

Block WooCommerce product purchases during off-season months and automatically capture "notify me when in season" email subscribers.

---

## Purpose

Specialty plant retailers — particularly those selling tissue-culture flasks, bare-root orchids, or field-grown stock — operate strict shipping windows tied to ambient temperature, dormancy cycles, or harvest timing. Standard WooCommerce has no concept of seasonal purchasing. This plugin fills that gap.

When a customer views a product outside its configured shipping window, the standard Add-to-Cart button is replaced with a brief explanation and an email capture form. When the product returns to season (i.e., you re-save its available months and the current month matches), all queued subscribers receive an automatic notification email.

---

## Installation

1. Upload the `phyto-seasonal-availability` folder to `/wp-content/plugins/`.
2. Activate via **Plugins → Installed Plugins**.
3. Ensure WooCommerce 8.0 or later is active — the plugin shows an admin notice and exits gracefully if WooCommerce is missing.
4. On first activation the `phyto_seasonal_subscribers` database table is created automatically.

---

## Database Table

The plugin creates one custom table on activation:

**`{prefix}phyto_seasonal_subscribers`**

| Column | Type | Description |
|--------|------|-------------|
| `id` | `bigint(20) unsigned` AUTO_INCREMENT | Primary key |
| `product_id` | `bigint(20) unsigned` | WooCommerce product post ID |
| `email` | `varchar(200)` | Subscriber email address |
| `subscribed_at` | `datetime` | UTC timestamp of subscription |
| `notified` | `tinyint(1)` | 0 = pending, 1 = notification sent |

---

## How to Set Available Months

1. Edit any WooCommerce product.
2. In the **Seasonal Availability** meta box (below the product description editor):
   - Check each month when the product **can** be purchased and shipped.
   - Alternatively, tick **Available year-round** to allow purchase in any month (this disables the month checkboxes).
   - Optionally customise the **Unavailable message** shown to customers outside the season.
3. Save the product. Changes take effect immediately.

**Default behaviour:** if no months are checked and year-round is not ticked, the product defaults to available in all months (safe fallback for existing products).

---

## Customer Experience

### Shop / Archive Loop

The standard Add-to-Cart button is replaced with a soft amber **"Not in season"** pill. The pill is non-interactive — it signals unavailability without wasting customer clicks.

### Single Product Page

- The Add-to-Cart button is hidden entirely (and the product is marked non-purchasable to prevent cart manipulation via direct URL or REST API).
- A forest-green info box displays the unavailability message.
- Below the message, a compact **"Notify me when in season"** email form is rendered. The form submits via AJAX with no page reload.

---

## Subscriber Management

Navigate to **WooCommerce → Seasonal Subscribers**.

The table shows:
- Product name (linked to the product editor)
- Subscriber email
- Date subscribed
- Notified status (Yes / No)

Click **Export CSV** to download all subscriber records as a UTF-8 CSV file named `phyto-seasonal-subscribers-YYYY-MM-DD.csv`.

---

## Notification Emails

Notifications are triggered automatically when you **re-save** a product's available months, provided the current month is within the newly saved window. The plugin queries all unnotified subscribers for that product, sends each one a plain-text email via `wp_mail()`, and marks them as notified.

No cron job is required. Notifications are sent synchronously on product save.

---

## Developer Hooks

### Filter: `phyto_sa_unavailable_message`

Override the unavailable message shown on the single product page.

```php
add_filter( 'phyto_sa_unavailable_message', function( $message, $product_id ) {
    if ( 42 === $product_id ) {
        return 'This orchid ships October–February only. Check back soon!';
    }
    return $message;
}, 10, 2 );
```

### Filter: `phyto_sa_is_in_season`

Override the season check result entirely — useful for applying custom calendar logic, staff preview modes, or testing.

```php
add_filter( 'phyto_sa_is_in_season', function( $is_in_season, $product_id ) {
    // Always treat product 99 as in season (staff demo product).
    if ( 99 === $product_id ) {
        return true;
    }
    return $is_in_season;
}, 10, 2 );
```

---

## Source Layout

```
phyto-seasonal-availability/
├── phyto-seasonal-availability.php          # Bootstrap, constants, WC check, DB table activation
├── includes/
│   ├── class-phyto-seasonal-admin.php       # Product meta box, subscriber admin page, CSV export
│   ├── class-phyto-seasonal-frontend.php    # Season check, cart blocking, subscribe form render
│   └── class-phyto-seasonal-subscribers.php # AJAX subscribe handler, in-season notification mailer
├── assets/
│   ├── css/frontend.css                     # Pill, info box, subscribe form styles
│   └── js/subscribe.js                      # AJAX form submit + inline success/error feedback
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_seasonal_availability`](/modules/phyto_seasonal_availability/) in the `modules/` directory.
