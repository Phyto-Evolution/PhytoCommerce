# Phyto Plant Collection Widget

**Module name:** `phyto_collection_widget`
**Version:** 1.0.0
**Author:** PhytoCommerce
**Compatible with:** PrestaShop 8.0+
**Tab:** Front Office Features

---

## Overview

Allows logged-in customers to maintain a personal plant collection built automatically from their order history. Customers can annotate each plant with private notes, optionally make individual items public to share their collection with other visitors, and the shop owner can disable public sharing at any time from the module configuration page.

---

## Features

- **Automatic collection building** — when an order is confirmed, every product in that order is added to the customer's collection automatically.
- **Personal notes** — customers can add and edit private notes for each plant (AJAX, no page reload).
- **Public / private toggle** — each item can be individually marked public or private. Public items appear on a shareable URL.
- **Public collection page** — a read-only public view accessible via `/module/phyto_collection_widget/view?customer=<hash>`. Only publicly-marked plants are shown; personal notes are never exposed.
- **"In your collection" badge** — a badge appears on the product page extra-content tab when the logged-in customer already owns the plant.
- **My Account link** — a "My Plant Collection" link is injected into the customer's My Account dashboard block.
- **Admin overview** — a read-only HelperList in the back office (under Customers → Plant Collections) shows all collection items with customer name, product name, visibility status, and acquisition date.
- **Admin configuration** — a toggle to enable or disable public collection sharing site-wide (`PHYTO_COLL_ALLOW_PUBLIC`).

---

## Database

Single table created on install:

```
PREFIX_phyto_collection_item
  id_item        INT AUTO_INCREMENT PK
  id_customer    INT
  id_product     INT
  id_order       INT
  personal_note  TEXT
  is_public      TINYINT(1) DEFAULT 0
  date_acquired  DATE
  date_add       DATETIME
  date_upd       DATETIME
  UNIQUE KEY (id_customer, id_product)
```

---

## Hooks

| Hook | Purpose |
|---|---|
| `displayOrderConfirmation` | Auto-add ordered products to the customer's collection |
| `displayProductExtraContent` | Show "In your collection" badge on product pages |
| `displayMyAccountBlock` | Inject "My Plant Collection" link in My Account |
| `actionProductDelete` | Remove collection rows when a product is deleted |

---

## Front-end Routes

| URL | Controller | Auth |
|---|---|---|
| `/module/phyto_collection_widget/collection` | `collection.php` | Required |
| `/module/phyto_collection_widget/view?customer=<md5>` | `view.php` | None |

### AJAX endpoints (collection page)

All AJAX calls POST to the collection URL with `ajax=1` and a valid CSRF token.

| `phyto_coll_action` | Parameters | Description |
|---|---|---|
| `update_note` | `id_item`, `personal_note` | Save personal note |
| `toggle_public` | `id_item` | Flip is_public flag |
| `remove_item` | `id_item` | Delete item from collection |

---

## Admin

- **Back-office tab:** Customers → Plant Collections (`AdminPhytoCollections`)
- **Configuration:** Modules → Configure (`getContent()`)
  - `PHYTO_COLL_ALLOW_PUBLIC` — yes/no toggle; default yes

---

## Installation

1. Upload the `phyto_collection_widget` directory to `/modules/`.
2. In the PrestaShop back office go to **Modules → Module Manager** and install the module.
3. The SQL table is created automatically. The admin tab is registered under the Customers menu.
4. Configure the module via **Modules → Configure** to set public-collection preferences.

---

## File Structure

```
phyto_collection_widget/
├── phyto_collection_widget.php          Main module class
├── config.xml                           Module metadata
├── controllers/
│   ├── admin/
│   │   └── AdminPhytoCollectionsController.php
│   └── front/
│       ├── collection.php               My collection (auth required)
│       └── view.php                     Public collection view
├── sql/
│   ├── install.sql
│   └── uninstall.sql
├── views/
│   ├── css/
│   │   └── front.css
│   └── templates/
│       ├── front/
│       │   ├── collection.tpl
│       │   └── view.tpl
│       └── hook/
│           ├── product_extra_content.tpl
│           └── my_account_block.tpl
└── README.md
```

---

## Security Notes

- All POST actions require a valid PrestaShop CSRF token (`Tools::getToken(false)`).
- Ownership is verified on every update/delete by matching both `id_item` and `id_customer`.
- The public view controller never exposes personal notes.
- Public collection lookup uses MD5 of `id_customer` — no private data is embedded in the URL.
- All database inputs are cast to int or passed through `pSQL()`.
