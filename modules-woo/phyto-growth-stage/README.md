# phyto-growth-stage

**Phyto Growth Stage for WooCommerce** — Tag WooCommerce products with a growth stage (Deflasked → Specimen). A colour-coded badge appears on shop/archive listing cards and on the single product page, with care-difficulty and time-to-maturity metadata displayed alongside each badge.

This is the WooCommerce port of the **phyto_growth_stage** PrestaShop module.

---

## Purpose

Specialty plant retailers — especially tissue-culture producers and rare plant nurseries — sell plants at wildly different sizes and ages. Without a clear stage label, buyers often expect a mature specimen when they purchase a freshly deflasked seedling, leading to disappointment and support overhead.

Phyto Growth Stage solves this by attaching a standardised, colour-coded stage label to every product. The badge is instantly visible on listing pages and product pages, setting accurate buyer expectations before they add to cart.

---

## Installation

1. Upload the `phyto-growth-stage/` folder to `wp-content/plugins/`.
2. In **WordPress Admin → Plugins**, activate **Phyto Growth Stage for WooCommerce**.
3. WooCommerce 8.0 or later must be active — the plugin shows an admin notice and does nothing if WooCommerce is missing.
4. Open any product in the editor. A **Growth Stage** meta box appears in the right sidebar.
5. Select a stage and optionally add stage notes. Save the product.

---

## Growth Stages

| Key | Label | Care Difficulty | Time to Maturity | Badge Colour |
|-----|-------|----------------|-----------------|--------------|
| `deflasked` | Deflasked | Beginner | 3–6 months | `#5b9bd5` (blue) |
| `juvenile` | Juvenile | Beginner | 6–12 months | `#4caf7d` (green) |
| `semi_mature` | Semi-Mature | Intermediate | 12–18 months | `#26a69a` (teal) |
| `mature` | Mature | Intermediate | 18–36 months | `#e8a135` (amber) |
| `specimen` | Specimen | Advanced | 36+ months | `#8e44ad` (purple) |

---

## Badge Placement

### Shop / Archive Listings
The badge is output via `woocommerce_before_shop_loop_item_title`. It is positioned **absolutely over the product image** (top-left corner), styled identically to the WooCommerce "Sale!" overlay badge. The difficulty and time text are hidden in the archive view to keep the card tidy.

### Single Product Page
The badge is output via `woocommerce_single_product_summary` at **priority 6**, placing it above the price line in the product summary column. On the single page the full `Difficulty · Time` metadata string is visible beside the badge pill.

### Care & Stage Info Tab
If the admin has entered Stage Notes for a product, a **Care & Stage Info** product tab is automatically added (priority 30) showing the stage badge, difficulty, time-to-maturity, and the full notes text.

---

## Meta Keys

| Meta Key | Type | Description |
|----------|------|-------------|
| `_phyto_growth_stage` | `string` | Stage slug (one of the five keys above) |
| `_phyto_growth_stage_notes` | `string` | Free-text stage notes (textarea) |

---

## Hooks

### Filters

#### `phyto_growth_stage_definitions`
Modify, extend, or replace the stage definitions array.

```php
add_filter( 'phyto_growth_stage_definitions', function( $stages ) {
    // Add a custom "Flask" stage before Deflasked.
    $flask = array(
        'flask' => array(
            'label'      => 'In Flask',
            'difficulty' => 'Advanced',
            'time'       => '0–3 months',
            'color'      => '#e53935',
        ),
    );
    return array_merge( $flask, $stages );
} );
```

#### `phyto_growth_stage_badge_html`
Filter the complete badge HTML string before it is echoed.

```php
/**
 * @param string $badge_html  Complete badge markup.
 * @param string $stage_key   Stage slug e.g. 'deflasked'.
 * @param array  $info        Stage definition (label, difficulty, time, color).
 * @param int    $post_id     WooCommerce product ID.
 * @param bool   $is_archive  True when rendering in the shop loop.
 */
add_filter( 'phyto_growth_stage_badge_html', function( $badge_html, $stage_key, $info, $post_id, $is_archive ) {
    // Wrap badge in a link to a filtered shop page, for example.
    return '<a href="' . esc_url( get_term_link( $stage_key, 'product_tag' ) ) . '">' . $badge_html . '</a>';
}, 10, 5 );
```

---

## Source Layout

```
phyto-growth-stage/
├── phyto-growth-stage.php                        # Plugin bootstrap, constants, WC check
├── includes/
│   ├── class-phyto-growth-stage-admin.php        # Meta box, admin list column, stage definitions
│   └── class-phyto-growth-stage-frontend.php     # Badge output, product tab
├── assets/
│   └── css/
│       └── frontend.css                          # Badge pill, archive overlay, single + tab styles
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module lives at [`modules/phyto_growth_stage/`](../../modules/phyto_growth_stage/).
