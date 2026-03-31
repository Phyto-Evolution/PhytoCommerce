# Phyto Grex Registry — OpenCart 3.x Module

**Platform:** OpenCart 3.x
**Version:** 1.0.0
**Author:** K. Shivaramakrishnan / Forest Studio Labs

## Description

Attaches plant grex/hybrid registry metadata to OpenCart products. Provides:

- An **admin module settings page** (Extensions > Modules > Phyto Grex Registry) to add, edit, and delete grex records linked to any product by its product ID.
- A **"Grex Registry" tab** on the storefront product page, displayed automatically when grex data exists for that product.

### Fields stored per product

| Field | Type | Notes |
|---|---|---|
| `grex_id` | VARCHAR(100) | Registry identifier |
| `parent_a` | VARCHAR(200) | First parent taxon |
| `parent_b` | VARCHAR(200) | Second parent taxon |
| `grex_year` | INT | Year of registration |
| `registrant` | VARCHAR(200) | Registering authority/person |
| `species_status` | VARCHAR(50) | `hybrid`, `species`, `cultivar`, or `variety` |
| `taxonomy_pack` | VARCHAR(100) | Linked taxonomy pack identifier |
| `notes` | TEXT | Free-form notes |

## Install Instructions

1. Copy the contents of `upload/` into the root of your OpenCart installation, merging with the existing directory structure.
2. In the OpenCart admin, go to **Extensions > Extensions**, select type **Modules**.
3. Find **Phyto Grex Registry** in the list and click **Install**.
4. Click the **Edit** button to open the module management page.
5. Register the frontend event so the product tab appears:
   - Go to **System > Events** and add:
     - **Trigger:** `catalog/view/product/product/before`
     - **Action:** `extension/module/phyto_grex_registry/injectTab`

## Database Table

The install step creates:

```sql
CREATE TABLE `oc_phyto_grex_registry` (
    `grex_registry_id` INT(11)      NOT NULL AUTO_INCREMENT,
    `product_id`       INT(11)      NOT NULL,
    `grex_id`          VARCHAR(100) NOT NULL DEFAULT '',
    `parent_a`         VARCHAR(200) NOT NULL DEFAULT '',
    `parent_b`         VARCHAR(200) NOT NULL DEFAULT '',
    `grex_year`        INT(4)       DEFAULT NULL,
    `registrant`       VARCHAR(200) NOT NULL DEFAULT '',
    `species_status`   VARCHAR(50)  NOT NULL DEFAULT 'hybrid',
    `taxonomy_pack`    VARCHAR(100) NOT NULL DEFAULT '',
    `notes`            TEXT,
    `date_added`       DATETIME     NOT NULL,
    `date_modified`    DATETIME     NOT NULL,
    PRIMARY KEY (`grex_registry_id`),
    KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

The table is dropped when the module is uninstalled.

## File Structure

```
upload/
├── admin/
│   ├── controller/extension/module/phyto_grex_registry.php
│   ├── model/extension/module/phyto_grex_registry.php
│   ├── view/template/extension/module/phyto_grex_registry.twig
│   └── language/en-gb/extension/module/phyto_grex_registry.php
└── catalog/
    ├── controller/extension/module/phyto_grex_registry.php
    ├── model/extension/module/phyto_grex_registry.php
    ├── view/theme/default/template/extension/module/phyto_grex_registry.twig
    └── language/en-gb/extension/module/phyto_grex_registry.php
```
