# Phyto Bundle Builder

**Author:** PhytoCommerce
**Version:** 1.0.0
**Compatible:** PrestaShop 8.0+

## Overview

Customer-facing bundle creator for plant e-commerce. Admin defines bundle templates (e.g. "Starter Kit" = 1 plant + 1 pot + 1 substrate). Customers pick specific products from each slot, receive a combined discount, and the selection is added to the cart.

## Features

- Admin-defined bundle templates with named slots
- Per-slot category restriction (limit product choices to a category)
- Discount types: percentage or fixed amount
- AJAX product search/filter within each slot
- Live running total with "You save X" display
- Cart rules created automatically at checkout
- Sidebar and homepage widgets via hooks
- Translatable via PrestaShop translation system

## Installation

1. Upload the `phyto_bundle_builder` folder to `/modules/`
2. Install via Back Office > Modules
3. Configure via **Modules > Configure** or the **Bundle Builder** admin tab

## Database Tables

| Table | Purpose |
|---|---|
| `phyto_bundle` | Bundle definitions (discount, active flag) |
| `phyto_bundle_lang` | Multilingual bundle names and descriptions |
| `phyto_bundle_slot` | Slots within a bundle (name, type, category, required) |

## Admin Tab

**Catalog > Bundle Builder** — full CRUD for bundles and slot management.

## Configuration Keys

| Key | Default | Description |
|---|---|---|
| `PHYTO_BUNDLE_MAX_SLOTS` | `5` | Maximum slots per bundle template |
| `PHYTO_BUNDLE_SHOW_SAVINGS` | `1` | Show "You save X" on the builder page |
| `PHYTO_BUNDLE_CTA_TEXT` | `Add Bundle to Cart` | CTA button label |

## Front-End URLs

| URL | Description |
|---|---|
| `/module/phyto_bundle_builder/builder` | Bundle listing page |
| `/module/phyto_bundle_builder/builder?id_bundle=X` | Builder for bundle X |
| `/module/phyto_bundle_builder/products?id_slot=X&q=search` | AJAX product search |

## Hooks

| Hook | Usage |
|---|---|
| `displayHeader` | Enqueue CSS / JS on builder pages |
| `displayHome` | Featured bundles widget on homepage |
| `displayLeftColumn` | Sidebar widget |
| `displayRightColumn` | Sidebar widget |
| `actionCartSave` | Validate bundle selection integrity on cart save |

## File Structure

```
phyto_bundle_builder/
├── phyto_bundle_builder.php
├── config.xml
├── classes/
│   ├── PhytoBundle.php
│   └── PhytoBundleSlot.php
├── controllers/
│   ├── admin/
│   │   └── AdminPhytoBundleBuilderController.php
│   └── front/
│       ├── builder.php
│       └── products.php
├── views/
│   ├── css/front.css
│   ├── js/front.js
│   └── templates/
│       ├── admin/configure.tpl
│       ├── front/builder.tpl
│       └── hook/bundle_widget.tpl
├── sql/
│   ├── install.sql
│   └── uninstall.sql
└── translations/
```
