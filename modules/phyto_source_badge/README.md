# phyto_source_badge

**PrestaShop 8.1 module — Phyto Source Badge**

Display sourcing-origin badges on product cards and product pages.
Badges are fully manageable from the back office and can be filtered in the catalog.

---

## Features

- Five built-in badge types with colour-coded pills (TC Lab, Division, Seed-grown, Wild Rescue, Import)
- Add, edit and delete badge definitions from **Catalog → Phyto Source Badges**
- Assign one or more badges to any product from the **product edit page** (extra tab, AJAX save)
- Wild Rescue badge: optional permit/reference number field
- Import badge: optional origin-country field
- Badges displayed in three locations:
  - Below the product price (small pills)
  - On catalog listing cards (mini pills)
  - In a dedicated "Source & Origin" tab on the product page (pills + descriptions)
- Automatic cleanup of badge assignments on product deletion

---

## Requirements

| Component        | Version      |
|------------------|--------------|
| PrestaShop       | 8.0 or later |
| PHP              | 7.4 or later |
| MySQL / MariaDB  | 5.7 / 10.3+  |

---

## Installation

1. Copy the `phyto_source_badge/` directory into `<prestashop_root>/modules/`.
2. In the back office go to **Modules → Module Manager**, search for **Phyto Source Badge** and click **Install**.
3. The module creates two database tables and seeds five default badge definitions.

### Manual installation via CLI

```bash
php bin/console prestashop:module install phyto_source_badge
```

---

## Uninstallation

Uninstalling via the Module Manager (or CLI) will:

1. Remove the two back-office menu tabs.
2. Drop `PREFIX_phyto_source_badge_def` and `PREFIX_phyto_source_badge_product`.
3. Unregister all hooks.

> **Warning:** all badge definitions and product assignments will be permanently deleted.

---

## Database schema

### `PREFIX_phyto_source_badge_def`

| Column        | Type           | Notes                          |
|---------------|----------------|--------------------------------|
| `id_badge`    | int PK AI      |                                |
| `badge_label` | varchar(100)   | Human-readable name            |
| `badge_slug`  | varchar(50)    | URL/CSS key — unique           |
| `badge_color` | varchar(10)    | green / blue / amber / red / gray |
| `description` | text           | Tooltip / tab description      |
| `sort_order`  | int            | Display order (lower = first)  |

### `PREFIX_phyto_source_badge_product`

| Column           | Type          | Notes                          |
|------------------|---------------|--------------------------------|
| `id_link`        | int PK AI     |                                |
| `id_product`     | int           | FK → ps_product                |
| `id_badge`       | int           | FK → phyto_source_badge_def    |
| `permit_ref`     | varchar(100)  | Wild Rescue permit number      |
| `origin_country` | varchar(100)  | Import origin country          |

---

## Back-office usage

### Managing badge definitions

Navigate to **Catalog → Phyto Source Badges**.

- **List view** — shows all badges with label, slug, colour swatch and sort order.
- **Add / Edit form** — fields: Label, Slug (auto-generated), Colour (select), Description, Sort order.
- **Delete** — removes the definition; existing product assignments referencing it become orphaned and will be silently skipped on front-office rendering.

### Assigning badges to a product

1. Open any product in **Catalog → Products**.
2. Scroll to the **Source & Origin Badges** panel (or find the extra tab).
3. Tick the checkboxes for the relevant badges.
4. For **Wild Rescue**: enter the permit/reference number.
5. For **Import**: enter the origin country.
6. Click **Save badge assignments** — saved via AJAX, no full page reload.

---

## Front-office display

| Hook                        | Location               | CSS class             |
|-----------------------------|------------------------|-----------------------|
| `displayProductPriceBlock`  | Below product price    | `.phyto-badge-pill`   |
| `displayProductListItem`    | Listing cards          | `.phyto-badge-mini`   |
| `displayProductExtraContent`| "Source & Origin" tab  | `.phyto-badge-pill`   |

### CSS colour classes

```css
.phyto-badge-green  { background-color: #2e7d32; }   /* TC Lab       */
.phyto-badge-blue   { background-color: #1565c0; }   /* Division     */
.phyto-badge-amber  { background-color: #e65100; }   /* Seed-grown   */
.phyto-badge-red    { background-color: #c62828; }   /* Wild Rescue  */
.phyto-badge-gray   { background-color: #546e7a; }   /* Import       */
```

To override, add rules to your theme's `custom.css` (higher specificity).

---

## File structure

```
phyto_source_badge/
├── phyto_source_badge.php                          ← Module entry point
├── config.xml                                      ← Module metadata
├── README.md
├── classes/
│   └── PhytoSourceBadgeDef.php                     ← ObjectModel
├── controllers/
│   └── admin/
│       ├── AdminPhytoSourceBadgeController.php         ← Badge CRUD
│       └── AdminPhytoSourceBadgeProductController.php  ← AJAX assignments
├── sql/
│   ├── install.sql
│   └── uninstall.sql
├── translations/                                   ← Translation files (po/mo)
└── views/
    ├── css/
    │   └── front.css
    ├── js/                                         ← (reserved for future JS)
    └── templates/
        └── hook/
            ├── admin_product_tab.tpl
            ├── product_price_block.tpl
            ├── product_list.tpl
            └── product_extra_content.tpl
```

---

## Extending / customising

### Adding a new badge colour

1. Add a row to `PREFIX_phyto_source_badge_def` with `badge_color = 'purple'`.
2. Add `.phyto-badge-purple { background-color: #6a1b9a; }` to your theme CSS (or override `front.css`).
3. Extend the `$map` array in `AdminPhytoSourceBadgeController::renderColorSwatch()` so the admin list shows the correct swatch.

### Translating strings

Run the standard PrestaShop translation tool:
**International → Translations → Modify translations → Module translations → phyto_source_badge**.

---

## Changelog

### 1.0.0 — 2024
- Initial release.

---

## License

MIT — see LICENSE file (not included; apply your own licence as required by your project).

## Author

**PhytoCommerce** — <https://phytocommerce.example>
