# Phyto Dispatch Logger

**Version:** 1.0.0
**Author:** PhytoCommerce
**Compatible with:** PrestaShop 8.0 – 8.1
**Module tab:** Orders

---

## Purpose

Staff log packing conditions at the point of dispatch for every live-plant shipment. Buyers can see those conditions on their order detail page. The stored record provides timestamped evidence for **Live Arrival Guarantee (LAG)** claims.

---

## Features

- One dispatch log per order (database-enforced unique key).
- Records: dispatch date, ambient temperature (°C), humidity (%), packing method, gel/heat pack inclusion, estimated transit days, staff name, free-text notes, and an optional photo.
- Photo stored under `img/phyto_dispatch/` with a directory-level `.htaccess` that blocks PHP execution.
- Front-office card displayed on the customer order history page (`displayOrderDetail`).
- Back-office tab injected into every order detail view (`displayAdminOrderTabLink` / `displayAdminOrderTabContent`).
- Full HelperList + HelperForm admin controller at **Orders → Dispatch Log**.
- Bulk delete with automatic photo file cleanup.

---

## Installation

1. Upload the `phyto_dispatch_logger/` folder to `modules/`.
2. Go to **Back Office → Modules → Module Manager** and search for *Phyto Dispatch Logger*.
3. Click **Install**.

The installer will:

- Create the database table `ps_phyto_dispatch_log`.
- Register the four hooks.
- Add a *Dispatch Log* entry under the Orders menu.
- Create the upload directory `img/phyto_dispatch/` with a protective `.htaccess`.

---

## Uninstallation

Click **Uninstall** in the Module Manager. The database table is dropped. The upload directory and any photos stored within it are **not** deleted automatically — remove `img/phyto_dispatch/` manually if you no longer need the images.

---

## Database table

| Column          | Type              | Notes                                  |
|-----------------|-------------------|----------------------------------------|
| `id_log`        | INT PK AI         | Primary key                            |
| `id_order`      | INT UNIQUE        | Foreign key to `ps_orders`             |
| `dispatch_date` | DATE              | Date handed to carrier                 |
| `temp_celsius`  | DECIMAL(4,1)      | Ambient temperature at packing         |
| `humidity_pct`  | INT               | Relative humidity %                    |
| `packing_method`| VARCHAR(100)      | See packing method options below       |
| `gel_pack`      | TINYINT(1)        | 1 = included                           |
| `heat_pack`     | TINYINT(1)        | 1 = included                           |
| `transit_days`  | INT               | Estimated business days                |
| `staff_name`    | VARCHAR(100)      | Staff member who packed                |
| `notes`         | TEXT              | Free-text notes                        |
| `photo_filename`| VARCHAR(255)      | Filename only; stored in img/phyto_dispatch/ |
| `date_add`      | DATETIME          | Auto-set by PrestaShop ObjectModel     |
| `date_upd`      | DATETIME          | Auto-set by PrestaShop ObjectModel     |

### Packing method options

- Bare-root newspaper
- Bark media bag
- Humidity box
- Insulated box
- Express pouch

---

## File structure

```
phyto_dispatch_logger/
├── phyto_dispatch_logger.php          Main module class
├── config.xml                         Module metadata
├── README.md
├── classes/
│   └── PhytoDispatchLog.php           ObjectModel
├── controllers/
│   └── admin/
│       └── AdminPhytoDispatchLogController.php
├── sql/
│   ├── install.sql
│   └── uninstall.sql
├── translations/                      (empty — ready for translation files)
└── views/
    ├── css/
    │   └── front.css
    ├── js/                            (empty — reserved)
    └── templates/
        ├── admin/
        │   ├── tab_link.tpl           Hook: displayAdminOrderTabLink
        │   └── tab_content.tpl        Hook: displayAdminOrderTabContent
        ├── front/                     (reserved)
        └── hook/
            └── order_detail.tpl       Hook: displayOrderDetail
```

---

## Hooks used

| Hook                          | Purpose                                                      |
|-------------------------------|--------------------------------------------------------------|
| `displayOrderDetail`          | Shows dispatch card to buyer on order detail / confirmation  |
| `displayAdminOrderTabLink`    | Adds "Dispatch Log" tab link in admin order detail           |
| `displayAdminOrderTabContent` | Renders log summary or create-prompt inside the tab panel    |
| `actionProductDelete`         | Reserved for future cleanup logic                            |

---

## Photo upload

- Accepted formats: JPEG, PNG, WEBP, GIF.
- Maximum file size: **2 MB**.
- Files are renamed to `<original>_<timestamp>_<random>.ext` before saving.
- Saved with `chmod 644`.
- The upload directory has a `.htaccess` that denies execution of PHP and script files.

---

## Development notes

- The module class name `Phyto_Dispatch_Logger` (with underscore) matches PrestaShop's module naming convention for multi-word module names.
- `need_instance = 0` — the module object is not instantiated on every page load.
- The controller uses `ModuleAdminController` which binds it automatically to this module's security context.
- All user-facing strings are wrapped in `$this->l()` / `{l}` for translation readiness.

---

## License

Academic Free License 3.0 (AFL-3.0)
https://opensource.org/licenses/AFL-3.0
