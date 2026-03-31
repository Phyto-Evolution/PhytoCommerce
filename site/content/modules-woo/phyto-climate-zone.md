---
title: "Phyto Climate Zone (WooCommerce)"
description: "Tag products with India climate-zone suitability — buyers instantly see whether a plant will thrive in their region."
module_name: "phyto-climate-zone"
platform: "WooCommerce"
category: "Plant Science"
category_id: "woo-plant-science"
version: "1.0.0"
weight: 37
---

## Overview

Specialty plant buyers in India face a problem that almost no general-purpose e-commerce plugin solves: before purchasing a rare orchid, tropical carnivore, or highland fern, they need to know whether their local climate is actually compatible with the plant. A Rajasthan buyer and a Kerala buyer are looking at the same product page, but they live in climates that differ by 30°C and 1,500 mm of annual rainfall. Phyto Climate Zone gives store owners a direct way to answer that question before the sale, on every product.

Admins fill in the **Climate Suitability** meta box on the product edit screen — checking one or more of seven India climate zones, optionally specifying a temperature tolerance range (min and max in °C), selecting an Indoor/Outdoor/Both placement, and adding any free-form climate notes. No external API, no pincode lookup, no JavaScript dependency beyond what WooCommerce already ships: the metadata is stored as standard WordPress post meta and rendered entirely server-side.

On the storefront, two complementary surfaces communicate zone compatibility. Shop and archive listing cards receive a compact badge strip of climate-zone pills positioned above the product title, so buyers can scan a category page and filter mentally before opening a product. On the single product page a dedicated **Climate Suitability** tab lists every compatible zone with its emoji icon and the example Indian regions it covers, alongside the temperature range, placement type, and any notes the admin has added. The tab is suppressed entirely for products with no zones set — zero impact on products that do not use the feature.

---

## Features

- Seven India climate zones covering all major growing regions from the coastal tropics to the temperate Himalayan foothills
- Multi-select checkboxes in the admin meta box — a plant can suit multiple zones simultaneously
- Temperature tolerance range (min/max °C) stored per-product and displayed in the tab
- Indoor / Outdoor / Both radio selector
- Free-form climate notes textarea for edge cases, acclimation guidance, or seasonal caveats
- Archive badge strip hooked at priority 8 on `woocommerce_before_shop_loop_item_title`
- Single product tab via `woocommerce_product_tabs` filter, hidden automatically when no zones are selected
- Per-zone colour-coded CSS pill badges — each zone has a distinct accent colour for instant visual scanning
- `phyto_cz_zone_definitions` filter to add, remove, or modify zones without patching core files
- `phyto_cz_tab_title` filter to rename the product tab
- No external dependencies — pure PHP, WordPress, and WooCommerce APIs only
- All strings translation-ready with `phyto-climate-zone` text domain

---

## India Climate Zones

| Zone Key | Label | Example Regions |
|---|---|---|
| `coastal` | Coastal & Humid | Kerala, coastal Karnataka, Goa, coastal TN/AP |
| `tropical_highland` | Tropical Highland | Nilgiris, Coorg, Munnar, NE hills |
| `tropical_plains` | Tropical Plains | Most of peninsular India — Chennai, Bengaluru plains, Hyderabad |
| `arid` | Arid & Semi-Arid | Rajasthan, parts of Maharashtra/Karnataka interior |
| `temperate` | Temperate North | Himachal Pradesh, Uttarakhand foothills, J&K valleys |
| `subtropical` | Sub-tropical | Punjab, Haryana, UP, Delhi belt |
| `northeast` | North-East Humid | Assam, Meghalaya, Manipur, Sikkim |

---

## Developer Hooks

### Filter: `phyto_cz_zone_definitions`

Extend or replace the zone list. Applies everywhere zones are used — admin meta box and all front-end rendering. Each zone entry must include `label`, `emoji`, and `regions` keys.

```php
/**
 * @param array $zones Keyed array: zone_key => [ 'label', 'emoji', 'regions' ].
 * @return array
 */
add_filter( 'phyto_cz_zone_definitions', function( $zones ) {
    // Add Andaman & Nicobar as a distinct zone.
    $zones['island'] = array(
        'label'   => 'Island / Andaman',
        'emoji'   => '🏝️',
        'regions' => 'Andaman & Nicobar Islands, Lakshadweep',
    );
    return $zones;
} );
```

### Filter: `phyto_cz_tab_title`

Override the product tab label shown on single product pages.

```php
/**
 * @param string $title Default tab title.
 * @return string
 */
add_filter( 'phyto_cz_tab_title', function( $title ) {
    return __( 'Grows Best In', 'my-theme' );
} );
```

---

## Source Layout

```
phyto-climate-zone/
├── phyto-climate-zone.php               # Bootstrap, constants, WC dependency check
├── includes/
│   ├── class-phyto-cz-admin.php         # Product meta box — zones, temp, placement, notes
│   └── class-phyto-cz-frontend.php      # Archive badge strip + single product tab
├── assets/
│   └── css/frontend.css                 # Per-zone colour pills + tab layout
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_climate_zone`](/modules/phyto-climate-zone/) in `modules/`. That version uses 15 PCC-IN zones with a 797-prefix PIN-to-zone lookup table, monthly temperature and humidity bar charts, frost risk flags, and monsoon month indicators. This WooCommerce port uses a streamlined admin-selected 7-zone model optimised for product tagging workflows where pincode lookups are not required.
