---
title: "Phyto Restock Alert (WooCommerce)"
description: "Let buyers subscribe to out-of-stock plant products and auto-notify them the moment stock is restored."
module_name: "phyto-restock-alert"
platform: "WooCommerce"
category: "Invoicing & Notifications"
category_id: "woo-notifications"
version: "1.0.0"
weight: 39
---

## Overview

Phyto Restock Alert solves a recurring problem for specialty plant retailers: customers land on a product page, see it is out of stock, and leave — never to return. This plugin replaces that dead-end with a lightweight "Notify me when available" subscribe form that appears automatically on every out-of-stock product. Customers type their email, hit a button, and are done. When stock is restored — whether through a manual quantity update in the product editor or a bulk stock import — every subscriber for that product receives an automated notification email without any manual step from the store owner.

The plugin is designed for zero maintenance. The notification trigger hooks directly into WooCommerce's own stock-change event (`woocommerce_product_set_stock`), so it fires the moment stock goes from zero to any positive number, regardless of how the change was made. A second trigger watches for product re-publish events so that products brought back from draft status also notify their waitlists automatically.

For store admins, every product's edit page gains a subscriber meta box showing a timestamped list of waiting customers, a one-click "Notify All Now" button for manual dispatch, individual delete links, and a CSV export button that downloads the full list in Excel-friendly UTF-8 format. The Products list-table also gains a compact subscriber count badge column so you can see at a glance which products have the most demand waiting.

---

## Features

### Admin
- **Subscriber meta box** on every product edit page — lists email addresses with subscribed and notified timestamps
- **Notify All Now** button for immediate manual bulk notification from the product edit screen
- **Individual delete** — remove a single subscriber from the meta box with a confirmation prompt
- **CSV export** — download the complete subscriber list for any product with a UTF-8 BOM for direct Excel compatibility
- **Subscriber count column** on the WooCommerce Products list-table — coloured badge for any product with waiting subscribers

### Front-end
- Subscribe form automatically displayed on out-of-stock single product pages below the "Out of stock" notice
- Form is hidden when the product is in stock — no template changes required
- AJAX submission with inline success and error feedback — no page reload
- Duplicate email detection — politely informs customers already on the list
- Fully translation-ready with `gettext` strings

### Automatic Notifications
- Auto-fires on `woocommerce_product_set_stock` when stock quantity transitions from 0 to any positive value
- Auto-fires on `transition_post_status` when a product post moves to `publish` from any other status (and the product is in stock)
- Marks all subscribers as notified in a single batch DB update after each notification run

### Email
- Plain-text email sent via WordPress's built-in `wp_mail()`
- From name and From email taken from the site's configured values — no extra settings required
- Subject line and body content are both filterable for full customisation

---

## Developer Hooks

### `phyto_rs_form_label`

Filter the label text displayed above the email input field on the product page.

```php
/**
 * @param string $label      Default label string.
 * @param int    $product_id Current product ID.
 */
add_filter( 'phyto_rs_form_label', function( $label, $product_id ) {
    return __( 'Join the waitlist for this plant:', 'your-theme' );
}, 10, 2 );
```

### `phyto_rs_success_message`

Filter the inline success message shown to the subscriber after a successful form submission.

```php
/**
 * @param string $message    Default success message.
 * @param int    $product_id The product subscribed to.
 * @param string $email      The subscriber's email address.
 */
add_filter( 'phyto_rs_success_message', function( $message, $product_id, $email ) {
    return __( 'You are on the waitlist. We will be in touch!', 'your-theme' );
}, 10, 3 );
```

### `phyto_rs_email_subject`

Filter the subject line of the restock notification email.

```php
/**
 * @param string     $subject Default subject line.
 * @param WC_Product $product The restocked product.
 * @param string     $to      Recipient email address.
 */
add_filter( 'phyto_rs_email_subject', function( $subject, $product, $to ) {
    return sprintf( 'Back in stock: %s', $product->get_name() );
}, 10, 3 );
```

### `phyto_rs_email_body`

Filter the full plain-text body of the restock notification email.

```php
/**
 * @param string     $body    Default plain-text email body.
 * @param WC_Product $product The restocked product.
 * @param string     $to      Recipient email address.
 */
add_filter( 'phyto_rs_email_body', function( $body, $product, $to ) {
    return sprintf(
        "Hello,\n\n%s is back in stock. Grab yours before it sells out again:\n%s\n\nHappy growing!",
        $product->get_name(),
        get_permalink( $product->get_id() )
    );
}, 10, 3 );
```

### `phyto_rs_form_label` (reminder)

See above — the `phyto_rs_form_label` filter also receives the product ID as a second argument, making it possible to return different label text per product or per product category.
