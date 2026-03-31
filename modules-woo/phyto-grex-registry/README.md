# Phyto Grex Registry for WooCommerce

A WooCommerce plugin that attaches structured scientific and horticultural taxonomy metadata to any product. The data is entered by the shop admin via a custom meta box on the product edit screen and displayed to customers as a collapsible **"Scientific Profile"** tab on the single product page.

---

## Requirements

| Requirement | Minimum version |
|-------------|----------------|
| WordPress   | 6.0             |
| WooCommerce | 8.0             |
| PHP         | 7.4             |

---

## Installation

1. Upload the `phyto-grex-registry/` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through **Plugins → Installed Plugins** in the WordPress admin.
3. WooCommerce must be installed and active — the plugin will display an admin notice if it is not.

---

## Usage

### Entering data (admin)

1. Open any product in **Products → Edit Product**.
2. Scroll to the **Scientific Profile** meta box (below the main editor).
3. Fill in whichever fields apply and click **Update**.

### Front-end display

The **Scientific Profile** tab appears automatically on the single product page whenever at least **Genus** or **Species** is populated. All other fields are shown only when they contain a value.

---

## Fields

| Field | Meta key stored | Notes |
|-------|----------------|-------|
| Genus | `_phyto_grex_genus` | Botanical genus, e.g. *Nepenthes* |
| Species | `_phyto_grex_species` | Specific epithet, e.g. *rajah* |
| Hybrid / Grex Name | `_phyto_grex_grex_name` | Registered hybrid or grex name |
| Registration Authority | `_phyto_grex_authority` | e.g. RHS, ICPS, IUCN |
| Conservation Status | `_phyto_grex_conservation_status` | See status options below |
| Common Name | `_phyto_grex_common_name` | Vernacular/trade name |
| Notes | `_phyto_grex_notes` | Free-text internal notes |

### Conservation status options

| Value stored | Label displayed | Badge colour |
|-------------|----------------|-------------|
| `not_evaluated` | Not Evaluated | Grey |
| `least_concern` | Least Concern | Green |
| `vulnerable` | Vulnerable | Amber |
| `endangered` | Endangered | Orange-red |
| `critically_endangered` | Critically Endangered | Red |
| `cites_appendix_i` | CITES Appendix I | Deep red |
| `cites_appendix_ii` | CITES Appendix II | Orange |

---

## Hooks & Filters

### `phyto_grex_tab_title`

Filters the tab title shown in the WooCommerce product tab bar.

```php
add_filter( 'phyto_grex_tab_title', function( $title ) {
    return __( 'Taxonomy', 'my-theme' );
} );
```

### `phyto_grex_fields`

Filters the associative array of field values just before they are rendered. Use this to add, remove, or override fields programmatically.

```php
add_filter( 'phyto_grex_fields', function( $fields, $product_id ) {
    // Remove notes from display.
    unset( $fields['notes'] );
    return $fields;
}, 10, 2 );
```

The `$fields` array uses the same slugs as the meta key suffixes (without the `_phyto_grex_` prefix).

---

## Source Layout

| File | Purpose |
|------|---------|
| `phyto-grex-registry.php` | Plugin header, constants, WooCommerce check, bootstrap |
| `includes/class-phyto-grex-admin.php` | Admin meta box — render + save |
| `includes/class-phyto-grex-frontend.php` | Product tab + Scientific Profile card |
| `assets/css/frontend.css` | Tab and badge styles |

---

## PrestaShop equivalent

This plugin is a WooCommerce port of the **phyto_grex_registry** PrestaShop module in the [PhytoCommerce](https://github.com/kshivaramakrishnan/PhytoCommerce) suite. Field names and data model are intentionally identical to allow data migration between platforms.

---

## License

GPL v2 or later — see [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)
