---
title: "phyto_growth_stage"
description: "Tag WooCommerce products with a growth stage (Deflasked → Specimen) — colour-coded badge on shop listings and product pages, with difficulty and time-to-maturity metadata."
module_name: "phyto-growth-stage"
category: "Plant Science"
category_id: "woo-plant-science"
platform: "WooCommerce"
version: "1.0.0"
weight: 31
---

## Overview

Phyto Growth Stage brings standardised plant-age labelling to WooCommerce product pages. Tissue-culture producers and rare plant retailers often sell specimens at very different developmental stages — from a freshly deflasked seedling to a decades-old specimen plant — but without a clear stage label buyers routinely underestimate care requirements or are surprised by plant size. This plugin eliminates that ambiguity.

Store owners select one of five predefined stages from a sidebar meta box on the product edit screen. A colour-coded pill badge is rendered automatically:

- **On shop/archive listing cards** — positioned absolutely over the product image (top-left corner), matching the visual weight of WooCommerce's built-in "Sale!" badge.
- **On the single product page** — injected into the product summary above the price, with full difficulty and time-to-maturity metadata displayed beside the badge.
- **In a "Care & Stage Info" product tab** — automatically added when stage notes are present, showing the badge, care difficulty, time-to-maturity, and the full notes text.

This plugin is the direct WooCommerce port of the **phyto_growth_stage** PrestaShop module. The meta key structure is intentionally identical, making cross-platform data migration straightforward.

---

## Growth Stages

| Key | Label | Care Difficulty | Time to Maturity | Badge Colour |
|-----|-------|----------------|-----------------|--------------|
| `deflasked` | Deflasked | Beginner | 3–6 months | `#5b9bd5` (blue) |
| `juvenile` | Juvenile | Beginner | 6–12 months | `#4caf7d` (green) |
| `semi_mature` | Semi-Mature | Intermediate | 12–18 months | `#26a69a` (teal) |
| `mature` | Mature | Intermediate | 18–36 months | `#e8a135` (amber) |
| `specimen` | Specimen | Advanced | 36+ months | `#8e44ad` (purple) |

All five stages are filterable via `phyto_growth_stage_definitions`, so custom stages (e.g. "In Flask") can be added without modifying plugin code.

---

## Badge Placement

### Shop / Archive Cards

The badge is hooked to `woocommerce_before_shop_loop_item_title`. CSS positions it **absolutely over the product image** at `top: 8px; left: 8px` with `z-index: 10` — the same visual treatment WooCommerce uses for the Sale badge. The difficulty and time metadata are hidden at this breakpoint to keep archive cards clean. On narrow viewports (≤ 480 px) the badge shrinks to 10 px font and shifts inward to `4px` from the image edges.

### Single Product Page

The badge is hooked to `woocommerce_single_product_summary` at **priority 6**, placing it above the product title's price line. Here the full `Difficulty · Time` string is visible beside the badge pill in a flex row.

### Care & Stage Info Tab

When a "Stage Notes" textarea has been populated in the product editor, the plugin adds a **Care & Stage Info** tab at priority 30 via `woocommerce_product_tabs`. The tab displays the stage badge, difficulty, time-to-maturity as a definition list, and the stage notes paragraph.

---

## Admin Features

- **Growth Stage meta box** — placed in the product editor right sidebar. A `<select>` lists all five stages; selecting one triggers an inline JS preview showing the stage's difficulty and time-to-maturity inside a colour-accented callout box, without requiring a page save.
- **Stage Notes textarea** — free-text field beneath the select. Saved to `_phyto_growth_stage_notes`. Shown in the Care & Stage Info product tab.
- **Products list-table column** — a "Growth Stage" column is injected after the product Name column, showing the colour-coded pill badge and difficulty label for each product at a glance.

---

## Hooks

### Filters

#### `phyto_growth_stage_definitions`

Modify, extend, or replace the five built-in stage definitions.

```php
add_filter( 'phyto_growth_stage_definitions', function( $stages ) {
    $stages['in_flask'] = array(
        'label'      => 'In Flask',
        'difficulty' => 'Advanced',
        'time'       => '0–3 months',
        'color'      => '#e53935',
    );
    return $stages;
} );
```

#### `phyto_growth_stage_badge_html`

Filter the complete rendered badge HTML string before it is echoed to the page.

```php
/**
 * @param string $badge_html  Complete badge markup string.
 * @param string $stage_key   Stage slug, e.g. 'deflasked'.
 * @param array  $info        Stage definition: label, difficulty, time, color.
 * @param int    $post_id     WooCommerce product ID.
 * @param bool   $is_archive  True when rendering inside the shop loop.
 */
add_filter( 'phyto_growth_stage_badge_html', function( $badge_html, $stage_key, $info, $post_id, $is_archive ) {
    // Example: wrap the badge in a filtered-shop link.
    return '<a href="/?filter_stage=' . esc_attr( $stage_key ) . '">' . $badge_html . '</a>';
}, 10, 5 );
```

---

## Source Layout

```
phyto-growth-stage/
├── phyto-growth-stage.php                        # Plugin bootstrap, constants, WC check
├── includes/
│   ├── class-phyto-growth-stage-admin.php        # Meta box, admin list column, stage definitions
│   └── class-phyto-growth-stage-frontend.php     # Badge rendering, product tab
├── assets/
│   └── css/
│       └── frontend.css                          # Badge pill, archive overlay, single + tab styles
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_growth_stage`](/modules/phyto-growth-stage/) in the `modules/` directory. Meta key names and stage slug vocabulary are kept identical between both platforms to simplify any future data migration.
