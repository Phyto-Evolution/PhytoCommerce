# phyto-climate-zone

**WooCommerce plugin — PhytoCommerce suite · v1.0.0**

Tags WooCommerce products with climate suitability for Indian growing regions. Admins select which climate zones a plant suits via a product meta box. On the storefront a "Climate Suitability" badge strip appears on product cards and a dedicated tab on single product pages lists compatible zones with emoji icons, indoor/outdoor placement, temperature range, and optional notes.

---

## File Structure

```
phyto-climate-zone/
├── phyto-climate-zone.php               # Bootstrap, constants, WC dependency check
├── includes/
│   ├── class-phyto-cz-admin.php         # Product meta box — zone checkboxes, temp, placement, notes
│   └── class-phyto-cz-frontend.php      # Archive badge strip + single product tab
├── assets/
│   └── css/frontend.css                 # Pill badge + tab styles, per-zone colour scheme
└── README.md
```

---

## India Climate Zones

| Key | Label | Example Regions |
|---|---|---|
| `coastal` | Coastal & Humid | Kerala, coastal Karnataka, Goa, coastal TN/AP |
| `tropical_highland` | Tropical Highland | Nilgiris, Coorg, Munnar, NE hills |
| `tropical_plains` | Tropical Plains | Chennai, Bengaluru plains, Hyderabad |
| `arid` | Arid & Semi-Arid | Rajasthan, parts of Maharashtra/Karnataka interior |
| `temperate` | Temperate North | Himachal, Uttarakhand foothills, J&K valleys |
| `subtropical` | Sub-tropical | Punjab, Haryana, UP, Delhi belt |
| `northeast` | North-East Humid | Assam, Meghalaya, Manipur, Sikkim |

---

## Admin Meta Box Fields

| Field | Meta Key | Type |
|---|---|---|
| Suitable Climate Zones | `_phyto_cz_zones` | array of zone keys |
| Min Temperature (°C) | `_phyto_cz_temp_min` | float |
| Max Temperature (°C) | `_phyto_cz_temp_max` | float |
| Indoor / Outdoor | `_phyto_cz_placement` | `indoor` / `outdoor` / `both` |
| Climate Notes | `_phyto_cz_notes` | text |

---

## Developer Hooks

### Filter: `phyto_cz_zone_definitions`

Modify, extend, or replace the zone list. Applies to both admin meta box and front-end rendering.

```php
add_filter( 'phyto_cz_zone_definitions', function( $zones ) {
    // Add a custom zone.
    $zones['island'] = array(
        'label'   => 'Island / Andaman',
        'emoji'   => '🏝️',
        'regions' => 'Andaman & Nicobar Islands, Lakshadweep',
    );
    return $zones;
} );
```

### Filter: `phyto_cz_tab_title`

Override the product tab label.

```php
add_filter( 'phyto_cz_tab_title', function( $title ) {
    return 'Grows Best In';
} );
```

---

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+

---

## PrestaShop Equivalent

[`phyto_climate_zone`](../../modules/phyto_climate_zone/) — the PrestaShop version uses 15 PCC-IN zones with PIN-prefix lookup and monthly climate charts. This WooCommerce port uses a simplified 7-zone admin-selected model suited to product tagging workflows.
