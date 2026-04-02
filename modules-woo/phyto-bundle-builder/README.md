# Phyto Bundle Builder for WooCommerce

Create named bundle templates with configurable product slots. Customers build custom bundles on a front-end page with real-time search and pricing. A percentage discount is automatically applied as a cart fee when all slots are filled.

## Features

### Admin — Bundle Templates
- **Template CRUD** — Bundle Builder admin page with list and edit views
- **Slot configuration** — define any number of slots per template, each with a label and optional restrictions (specific product IDs or category IDs)
- **Discount** — set a bundle discount % applied at checkout when all slots are filled
- **Status** — Active / Draft; only Active bundles are visible on the front end

### Front End — Customer Builder
- **Shortcode** `[phyto_bundle id="N"]` — renders the interactive builder for template N
- **Live product search** — debounced search per slot, results filtered to allowed products/categories
- **Slot selection** — click a result to select it; click ✕ to clear
- **Real-time pricing** — running total updates as slots are filled; discount applied to total when all slots complete
- **Add to Cart** — adds all selected products at once via AJAX; disabled until all slots are filled

### Cart & Checkout
- **Bundle label** — each cart line item shows the bundle name it belongs to
- **Automatic discount** — a negative fee `"{Bundle Name} Bundle Discount (N%)"` is added when all slots for a bundle are present in the cart

## Database

Creates two tables on activation:
- `{prefix}phyto_bundle_templates` — template metadata
- `{prefix}phyto_bundle_slots` — slot definitions per template

## Installation

1. Upload the `phyto-bundle-builder` folder to `wp-content/plugins/`
2. Activate — DB tables created automatically
3. Go to Bundle Builder > New Template to create your first template
4. Create a page with `[phyto_bundle id="1"]` (use the template ID shown in the list)

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
