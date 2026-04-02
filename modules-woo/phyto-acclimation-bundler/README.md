# Phyto Acclimation Bundler for WooCommerce

Displays a dismissable cart widget suggesting acclimation accessories whenever a tissue-culture or deflasked plant is in the cart. Supports an optional bundle discount applied as a cart fee when all kit items are purchased together.

## Features

- **Trigger detection** — fires on product tags (e.g. `tc-plant`, `deflasked`, `tissue-culture`) or on the `_phyto_growth_stage` post meta field set by the Phyto Growth Stage plugin
- **Dismissable widget** — customers can close the widget; the dismissed state persists for the browser session via `sessionStorage`
- **Per-item add** — each suggested product has its own "Add" button with AJAX add-to-cart
- **Add All button** — adds every kit item at once; shown only when a bundle discount is configured
- **Bundle discount** — when all kit products are in the cart together, a negative cart fee is applied automatically
- **Fully configurable** — all settings live in WooCommerce > Settings > Phyto Acclimation

## Settings

| Option | Description |
|--------|-------------|
| Kit Product IDs (CSV) | Comma-separated WooCommerce product IDs to suggest |
| Trigger Tags (CSV) | Product tags that activate the widget (default: `tc-plant,deflasked,tissue-culture`) |
| Trigger Stage IDs (CSV) | Growth stage slugs from the Phyto Growth Stage plugin |
| Bundle Discount % | Percentage discount applied when all kit items are in the cart (0 = disabled) |
| Widget Headline | Heading shown in the cart widget |
| Max items to show | How many kit items to display at once (default: 3) |

## Installation

1. Upload the `phyto-acclimation-bundler` folder to `wp-content/plugins/`
2. Activate via Plugins > Installed Plugins
3. Configure under WooCommerce > Settings > Phyto Acclimation

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
