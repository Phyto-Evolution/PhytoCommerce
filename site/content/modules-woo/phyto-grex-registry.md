---
title: "phyto_grex_registry"
description: "Attach scientific taxonomy metadata (genus, species, grex, authority, conservation status) to WooCommerce products — shown as a 'Scientific Profile' tab on the product page."
module_name: "phyto-grex-registry"
category: "Plant Science"
category_id: "woo-plant-science"
platform: "WooCommerce"
version: "1.0.0"
weight: 30
---

## Overview

Phyto Grex Registry brings rigorous botanical taxonomy to WooCommerce product pages. Store owners enter genus, species, hybrid or grex name, registration authority, conservation status, and common name via a dedicated **Scientific Profile** meta box on the product edit screen. When at least Genus or Species is filled in, a collapsible **Scientific Profile** tab appears automatically on the single product page, presenting the data in a clean definition-list layout with colour-coded conservation status badges.

The plugin is a direct WooCommerce port of the **phyto_grex_registry** PrestaShop module. The meta key structure and field vocabulary are intentionally identical, making cross-platform data migration straightforward.

---

## Fields

| Field | Meta key | Description |
|-------|----------|-------------|
| Genus | `_phyto_grex_genus` | Botanical genus — e.g. *Nepenthes*, *Heliamphora* |
| Species | `_phyto_grex_species` | Specific epithet — e.g. *rajah*, *heterodoxa* |
| Hybrid / Grex Name | `_phyto_grex_grex_name` | Registered hybrid or grex, e.g. *Trusham* (RHS) |
| Registration Authority | `_phyto_grex_authority` | Naming body — e.g. RHS, ICPS, IUCN |
| Conservation Status | `_phyto_grex_conservation_status` | IUCN / CITES category (select) |
| Common Name | `_phyto_grex_common_name` | Vernacular or trade name |
| Notes | `_phyto_grex_notes` | Free-text internal notes |

### Conservation Status Options

| Stored value | Label | Badge colour |
|-------------|-------|-------------|
| `not_evaluated` | Not Evaluated | Grey |
| `least_concern` | Least Concern | Green |
| `vulnerable` | Vulnerable | Amber |
| `endangered` | Endangered | Orange-red |
| `critically_endangered` | Critically Endangered | Red |
| `cites_appendix_i` | CITES Appendix I | Deep red |
| `cites_appendix_ii` | CITES Appendix II | Orange |

---

## WooCommerce Hooks Used

| Hook | Type | Purpose |
|------|------|---------|
| `add_meta_boxes` | Action | Registers the Scientific Profile meta box on the product edit screen |
| `save_post_product` | Action | Saves all taxonomy fields as `_phyto_grex_*` post meta |
| `woocommerce_product_tabs` | Filter | Injects the Scientific Profile tab (priority 25) |
| `wp_enqueue_style` | Function | Loads `frontend.css` only when the tab is rendered |

### Plugin-level filters

| Filter | Arguments | Purpose |
|--------|-----------|---------|
| `phyto_grex_tab_title` | `string $title` | Override the tab label |
| `phyto_grex_fields` | `array $fields, int $product_id` | Add, remove, or reorder displayed fields |

---

## Source Layout

| File | Purpose |
|------|---------|
| `phyto-grex-registry.php` | Plugin header, constants (`PHYTO_GREX_VERSION`, `PHYTO_GREX_PATH`, `PHYTO_GREX_URL`), WooCommerce dependency check, bootstrap |
| `includes/class-phyto-grex-admin.php` | `Phyto_Grex_Admin` — meta box registration, render, and save |
| `includes/class-phyto-grex-frontend.php` | `Phyto_Grex_Frontend` — product tab injection and Scientific Profile card output |
| `assets/css/frontend.css` | Definition-list card layout + conservation status badge styles |

---

## PrestaShop Equivalent

This plugin is a WooCommerce port of [`phyto_grex_registry`](/modules/phyto-grex-registry/) in the PhytoCommerce PrestaShop module suite. Both implementations store data under the same field names and expose the same "Scientific Profile" UI pattern, so shops migrating between platforms can transfer product metadata without data transformation.
