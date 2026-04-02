# Phyto Collection Widget for WooCommerce

Automatically builds a per-customer plant collection as orders are fulfilled. Customers manage their collection from a dedicated My Account tab, add care notes, and optionally make it publicly viewable.

## Features

- **Auto-collection** — products are added to a customer's collection when their order status changes to `completed`
- **Product page badge** — "In your collection since [date]" badge shown on single product pages for customers who already own it
- **My Account tab** — dedicated "My Collection" endpoint listing all collected plants with acquisition date, care notes, and remove button
- **Care notes** — customers can save per-plant notes (AJAX, no page reload)
- **Public sharing** — optional toggle per item; admin can enable/disable the feature globally
- **Admin overview** — WooCommerce > Plant Collections list with search by customer

## Database

Creates `{prefix}phyto_collection_item` with a `UNIQUE KEY (customer_id, product_id)`.

## Settings

WooCommerce > Settings > Phyto Collection:

| Option | Description |
|--------|-------------|
| Allow Public Collections | Whether customers can make their collection publicly viewable |

## Installation

1. Upload the `phyto-collection-widget` folder to `wp-content/plugins/`
2. Activate — the DB table is created automatically
3. Flush rewrite rules once (Settings > Permalinks > Save) to activate the My Account endpoint

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
