# Phyto Invoice Customizer for WooCommerce

Adds plant-specialist branding and compliance content to WooCommerce order confirmation emails — Live Arrival Guarantee clause, tissue-culture batch provenance reference, phytosanitary certificate number, and a custom footer note.

## Features

- **Branded email header** — shop name rendered as a styled header block in every order email
- **Live Arrival Guarantee clause** — optional legal/care text block inserted after the order table; toggled per-setting with editable body text
- **TC Batch reference** — if the Phyto TC Batch Tracker plugin is active, the batch code for each product in the order is appended (gracefully skipped if the table doesn't exist)
- **Phytosanitary certificate field** — optional phyto reference number block for international shipments (pulled from the Phyto Phytosanitary module if installed)
- **Custom footer note** — free-text footer appended to all order emails (useful for care instructions or compliance notices)
- **Order details page banner** — LAG notice shown on the customer's My Account > Orders > Order detail page

## Settings

Found under WooCommerce > Settings > Phyto Invoices:

| Option | Description |
|--------|-------------|
| Brand Name | Displayed in the email header |
| Show LAG Clause | Toggle the Live Arrival Guarantee block |
| LAG Text | Body of the LAG clause |
| Show TC Batch Reference | Toggle batch provenance in emails |
| Show Phytosanitary Reference | Toggle phyto cert block |
| Email Footer Note | Custom text appended to all order emails |

## Installation

1. Upload the `phyto-invoice-customizer` folder to `wp-content/plugins/`
2. Activate via Plugins > Installed Plugins
3. Configure under WooCommerce > Settings > Phyto Invoices

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
