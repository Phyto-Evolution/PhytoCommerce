# Phyto Restock Alert for WooCommerce

Version: 1.0.0
Requires WooCommerce: 8.0+
Requires WordPress: 6.0+
Requires PHP: 7.4+

## Overview

"Notify me when available" subscriber system for out-of-stock plant products. Customers enter their email on the product page. When stock is restored or the product is re-published, all subscribers receive an automated notification email.

## Features

### Front-end
- Email subscribe form displayed below "Out of stock" notice on out-of-stock products
- Hidden automatically when product is in stock
- AJAX submission — inline success/error confirmation without page reload
- Duplicate subscription detection

### Admin
- Subscriber list meta box on each product's edit page
- "Notify All Now" button to trigger manual bulk notification
- Individual subscriber delete
- CSV export with BOM for Excel compatibility
- Subscriber count badge column on the Products list-table

### Automatic notifications
- Fires on `woocommerce_product_set_stock` when stock transitions from 0 → positive
- Fires on `transition_post_status` when product is published

### Email
- Plain-text email via `wp_mail()`
- Uses site name and admin email as From header
- Subject and body fully filterable

## Developer Hooks

| Hook | Type | Description |
|------|------|-------------|
| `phyto_rs_form_label` | filter | Label text above the subscribe email input |
| `phyto_rs_success_message` | filter | Inline success message after subscription |
| `phyto_rs_email_subject` | filter | Notification email subject line |
| `phyto_rs_email_body` | filter | Notification email body (plain text) |

## DB Table

`{prefix}phyto_restock_subscribers`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint unsigned | PK auto-increment |
| product_id | bigint unsigned | WC product ID, indexed |
| email | varchar(200) | unique per product_id |
| subscribed_at | datetime | set on insert |
| notified_at | datetime | NULL until first notification |

## AJAX Actions

| Action | Access | Description |
|--------|--------|-------------|
| `phyto_restock_subscribe` | public | Subscribe an email to a product |
| `phyto_rs_notify_now` | admin | Trigger notification for all subscribers |
| `phyto_rs_delete_subscriber` | admin | Delete a single subscriber row |
| `phyto_rs_export_csv` | admin | Download subscriber list as CSV |
