# Phyto Dispatch Logger for WooCommerce

Records packing conditions at the point of dispatch against WooCommerce orders. Displays a Dispatch Conditions card on the customer's Order Details page.

## Features
- Per-order dispatch log: date, temperature, humidity, packing method, gel/heat packs, transit days, staff name, notes, photo
- Admin meta box on order edit screen (classic & HPOS)
- Dispatch Logs list page under WooCommerce menu
- CSV export of all logs
- Customer-facing dispatch card on order detail page

## Requirements
- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+

## Hooks
| Hook | Type | Description |
|---|---|---|
| `woocommerce_order_details_after_order_table` | Action | Renders dispatch card on order detail page |
| `add_meta_boxes` | Action | Adds meta box to order edit screen |
| `woocommerce_process_shop_order_meta` | Action | Saves meta box (classic orders) |
| `woocommerce_after_order_object_save` | Action | Saves meta box (HPOS) |

## Database
Table: `{prefix}phyto_dispatch_log` — one row per order (UNIQUE on order_id).
