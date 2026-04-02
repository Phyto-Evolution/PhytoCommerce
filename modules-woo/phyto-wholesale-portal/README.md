# Phyto Wholesale Portal for WooCommerce

B2B wholesale application flow with admin approval, a dedicated `phyto_wholesale` user role, per-product minimum order quantities (MOQ) and tiered pricing, and a My Account Wholesale tab.

## Features

### Application Flow
- **Shortcode** `[phyto_wholesale_apply]` — renders an application form (business name, contact, email, phone, tax ID, website, notes)
- **Status-aware rendering** — logged-out users see a login prompt; pending applicants see a "under review" notice; rejected see a rejection message; approved see a confirmation
- **Admin notification** — email sent to the site admin on new application submission

### Admin
- **WooCommerce > Wholesale Apps** — lists all applications with status filter (All / Pending / Approved / Rejected)
- **Approve / Reject** buttons — one-click with page reload; approval automatically grants the `phyto_wholesale` role and emails the customer
- **Rejection** revokes the wholesale role if previously granted

### Pricing
- **Per-product wholesale pricing** meta box in the product editor:
  - Enable/disable wholesale pricing toggle
  - Minimum Order Quantity (MOQ) — enforced at add-to-cart with a WooCommerce notice
  - Price tiers (JSON): `[{"qty":1,"price":10},{"qty":10,"price":8}]` — highest matching tier applied
- **Cart price override** — wholesale prices applied via `woocommerce_before_calculate_totals`
- **Price display** — wholesale price shown on product pages for wholesale customers

### My Account Tab
- "Wholesale" tab in My Account showing application status and a link to the application page for unapproved customers

## Settings

WooCommerce > Settings > Phyto Wholesale:

| Option | Description |
|--------|-------------|
| Application Page | Page containing the `[phyto_wholesale_apply]` shortcode |
| Show Wholesale Prices | To wholesale customers only, or all logged-in users |

## Database

Creates `{prefix}phyto_wholesale_apps` on activation.

## Installation

1. Upload the `phyto-wholesale-portal` folder to `wp-content/plugins/`
2. Activate — DB table and `phyto_wholesale` role created automatically
3. Create a page with `[phyto_wholesale_apply]` and set it in WooCommerce > Settings > Phyto Wholesale
4. Flush rewrite rules once (Settings > Permalinks > Save) for the My Account endpoint

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
