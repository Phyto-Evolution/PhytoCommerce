# Phyto Source Badge for WooCommerce

Create named sourcing-origin badges and assign them to WooCommerce products. Badges appear on shop/archive listing cards and on the single product page, giving buyers clear, at-a-glance information about how and where each plant was produced.

---

## Purpose

Specialty plant retailers often source from multiple channels — tissue-culture labs, ethical wild collection, nursery propagation, conservation programmes, and certified organic operations. Without clear labelling, buyers cannot distinguish how each product was produced.

Phyto Source Badge solves this by letting store owners:

1. **Define** a library of reusable sourcing badges with a custom name, brand colour, emoji icon, and tooltip text.
2. **Assign** any combination of those badges to individual WooCommerce products.
3. **Display** the badges automatically on shop listing cards and product pages — no template editing required.

This plugin is the WooCommerce port of the [`phyto_source_badge`](../../modules/phyto_source_badge/) PrestaShop module. The meta key `_phyto_source_badges` is intentionally identical to aid cross-platform data migration.

---

## Installation

1. Upload the `phyto-source-badge` folder to `/wp-content/plugins/`.
2. Activate the plugin through **Plugins → Installed Plugins**.
3. Ensure WooCommerce is installed and active — the plugin will show an admin notice and will not load without it.

On first activation four default badges are seeded automatically:
- 🧫 **Tissue Culture** (`#3a9a6a`)
- 🌿 **Wild Collected** (`#8B4513`)
- 🪴 **Nursery Grown** (`#4caf7d`)
- ♻️ **Conservation Propagation** (`#1a3c2b`)

---

## Creating Badges

Go to **WooCommerce → Source Badges** (or **Products → Source Badges** in some themes) and click **Add New Badge**.

| Field | Description |
|-------|-------------|
| **Title** | Badge label shown on product pages (e.g. "Tissue Culture"). |
| **Badge Color** | Hex colour code. Used for the badge border, text colour, and a 15%-opacity tinted background. |
| **Icon Emoji** | A single emoji displayed before the label (e.g. `🧫`). Max 2 characters. |
| **Tooltip** | Short sentence shown when the customer hovers the badge. Describes the sourcing method. |

A live preview updates as you type so you can see exactly how the badge will look.

---

## Assigning Badges to Products

1. Open any product in the WooCommerce product editor.
2. Find the **Source Badges** panel in the right sidebar.
3. Tick the checkboxes for all applicable badges.
4. Save or update the product.

Multiple badges can be selected per product.

---

## Badge Display

### Shop / Archive Cards

The badge strip is hooked to `woocommerce_before_shop_loop_item_title` and renders immediately below the product image. Multiple badges are displayed in a compact row that wraps on narrow viewports.

### Single Product Page

The badge strip is hooked to `woocommerce_single_product_summary` at **priority 7**, placing it above the price line. Badges are rendered at full size with the icon and label clearly visible.

---

## Hooks

### `phyto_source_badge_output`

Filter the complete rendered badge strip HTML before it is echoed to the page. Return an empty string to suppress output entirely.

```php
/**
 * @param string $output      Complete badge strip markup.
 * @param int    $post_id     WooCommerce product ID.
 * @param bool   $is_archive  True when rendering in the shop loop.
 * @param array  $badges_data Array of badge data arrays in this strip.
 */
add_filter( 'phyto_source_badge_output', function( $output, $post_id, $is_archive, $badges_data ) {
    // Example: suppress badges on the archive loop entirely.
    if ( $is_archive ) {
        return '';
    }
    return $output;
}, 10, 4 );
```

### `phyto_source_badge_classes`

Filter the CSS class array applied to the badge strip wrapper `<div>`. Use this to add custom classes for your theme.

```php
/**
 * @param array $classes     Array of CSS class strings applied to the wrapper div.
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
├── phyto-source-badge.php                          # Plugin bootstrap, constants, WC check, activation hook
├── includes/
│   ├── class-phyto-source-badge-cpt.php            # phyto_badge CPT registration, badge details meta box, get_all_badges()
│   ├── class-phyto-source-badge-admin.php          # Product "Source Badges" meta box, save badges to product
│   └── class-phyto-source-badge-frontend.php       # Badge strip rendering, CSS enqueue, hooks
├── assets/css/
│   └── frontend.css                                # Badge pill styles, archive + single layout, responsive
└── README.md
```

---

## PrestaShop Equivalent

The PrestaShop version of this module is [`phyto_source_badge`](../../modules/phyto_source_badge/) in the `modules/` directory. Meta key names and badge vocabulary are kept identical between both platforms to simplify any future data migration.
