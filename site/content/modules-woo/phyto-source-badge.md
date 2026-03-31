---
title: "phyto_source_badge"
description: "Create sourcing-origin badges (Tissue Culture, Wild Collected, Conservation Propagation, etc.) and display them on WooCommerce product listings and pages."
module_name: "phyto-source-badge"
category: "Customer & Community"
category_id: "woo-customer-community"
platform: "WooCommerce"
version: "1.0.0"
weight: 40
---

## Overview

Phyto Source Badge brings transparent, standardised sourcing-origin labelling to WooCommerce product pages. Specialty plant retailers source from multiple channels — sterile tissue-culture labs, ethical wild collection, conventional nurseries, conservation propagation programmes, organic-certified growing operations — but most stores have no way to communicate this visibly on the product listing. Buyers routinely cannot tell how a plant was produced, which matters both ethically and practically (TC plants need acclimation care, wild-collected stock may have legal provenance requirements, conservation-propagated plants carry a different value story).

This plugin eliminates that gap. Store owners maintain a reusable library of badge definitions — each with a name, brand colour, emoji icon, and tooltip — then assign any combination of badges to individual products. Badges are displayed automatically on both the shop archive grid and the single product page with no template editing required.

This plugin is the direct WooCommerce port of the **phyto_source_badge** PrestaShop module. The meta key `_phyto_source_badges` is intentionally identical to the PrestaShop counterpart, making cross-platform data migration straightforward.

---

## Default Badges

Four badges are seeded automatically on plugin activation so stores have a useful starting point without any manual configuration:

| Badge | Colour | Icon | Default Tooltip |
|-------|--------|------|----------------|
| Tissue Culture | `#3a9a6a` | 🧫 | Propagated via sterile tissue culture in a laboratory setting. |
| Wild Collected | `#8B4513` | 🌿 | Ethically and legally collected from wild habitat. |
| Nursery Grown | `#4caf7d` | 🪴 | Raised from establishment to sale entirely in nursery conditions. |
| Conservation Propagation | `#1a3c2b` | ♻️ | Propagated as part of an active species conservation programme. |

Additional badges can be created at any time, and any of the defaults can be edited or deleted. Common additions include "Seed Grown", "Certified Organic", "Tissue Culture — Acclimated", and "Rescue / Rehab".

---

## Admin Workflow

### Step 1 — Create badges

Navigate to **WooCommerce → Source Badges** and click **Add New Badge**. Fill in:

- **Title** — the badge label customers will see (e.g. "Tissue Culture").
- **Badge Color** — a hex colour code. Drives the badge border, text colour, and a 15%-opacity tinted background simultaneously via the CSS custom property `--sb-color`.
- **Icon Emoji** — a single emoji shown before the label (max 2 characters). Keeps badges visually distinctive at a glance.
- **Tooltip** — a short sentence explaining the sourcing method, shown on hover.

A live preview updates in the meta box as you type, so the colour and layout are visible before saving.

### Step 2 — Assign badges to products

Open any WooCommerce product in the editor. The **Source Badges** panel in the right sidebar shows a checkbox list of all published badge definitions, with a colour swatch and icon beside each name. Check the applicable badges and save the product.

Multiple badges may be selected per product (e.g. a plant could be both "Tissue Culture" and "Conservation Propagation").

---

## Badge Display

### Shop / Archive Grid

The badge strip is hooked to `woocommerce_before_shop_loop_item_title`. It renders as a compact flex row of pills immediately below the product image. On narrow viewports the row wraps. Archive badges use a slightly smaller font size to keep listing cards clean.

### Single Product Page

The badge strip is hooked to `woocommerce_single_product_summary` at **priority 7**, placing it above the WooCommerce price block (priority 10). Full-size badges are rendered in a flex row with the icon and label clearly legible.

---

## Hooks

### `phyto_source_badge_output`

Filter the complete rendered badge strip HTML before it is echoed to the page. Return an empty string to suppress output entirely for a specific context.

```php
/**
 * @param string $output      Complete badge strip markup.
 * @param int    $post_id     WooCommerce product ID.
 * @param bool   $is_archive  True when rendering in the shop loop.
 * @param array  $badges_data Array of badge data arrays rendered in this strip.
 */
add_filter( 'phyto_source_badge_output', function( $output, $post_id, $is_archive, $badges_data ) {
    // Example: suppress badges on archive loop, show on single page only.
    if ( $is_archive ) {
        return '';
    }
    return $output;
}, 10, 4 );
```

### `phyto_source_badge_classes`

Filter the CSS classes on the badge strip wrapper `<div>`. Use this to inject theme-specific classes without editing plugin files.

```php
/**
 * @param array $classes     Array of CSS class strings.
 * @param int   $post_id     WooCommerce product ID.
 * @param bool  $is_archive  True when rendering in the shop loop.
 */
add_filter( 'phyto_source_badge_classes', function( $classes, $post_id, $is_archive ) {
    $classes[] = 'my-theme-source-badges';
    return $classes;
}, 10, 3 );
```

---

## Source Layout

```
phyto-source-badge/
├── phyto-source-badge.php                          # Bootstrap, constants, WC check, activation + seeding
├── includes/
│   ├── class-phyto-source-badge-cpt.php            # phyto_badge CPT, badge details meta box, get_all_badges()
│   ├── class-phyto-source-badge-admin.php          # Product "Source Badges" sidebar meta box + save
│   └── class-phyto-source-badge-frontend.php       # Badge strip rendering, CSS enqueue, filter hooks
├── assets/css/
│   └── frontend.css                                # Pill styles, --sb-color CSS var, archive + single layout
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_source_badge`](/modules/phyto-source-badge/) in the `modules/` directory. Meta key names and badge vocabulary are kept identical between both platforms to simplify any future data migration.
