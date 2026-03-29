# phyto_restock_alert

**"Notify me when available"** — PrestaShop 8 module for out-of-stock product subscriptions.

Customers enter their email on a product page when it is out of stock. When the product is restocked (stock goes above 0), they receive an automatic email notification.

---

## Features

- Subscribe widget on the product page (only shown when stock <= 0 by default)
- AJAX form submission with CSRF protection
- Support for product combinations (`id_product_attribute`)
- Guest and logged-in customer subscriptions
- Automatic email notifications triggered by `actionUpdateQuantity` and `actionOrderStatusPostUpdate`
- Back-office management list with filters, bulk actions, and per-row "Send Now"
- Subscriber overview inside the product admin tab (`displayAdminProductsExtra`)
- Configurable sender name, max emails per cron run, and OOS-only form display
- PrestaShop `Mail::Send()` HTML + plain-text email templates

---

## Installation

1. Upload the `phyto_restock_alert` folder to `/modules/`.
2. Go to **Back-office > Modules > Module Manager** and install.
3. Configure under **Modules > Configure > Phyto Restock Alert**.

---

## Configuration

| Key | Default | Description |
|-----|---------|-------------|
| `PHYTO_RESTOCK_FROM_NAME` | Shop name | Sender name in notification emails |
| `PHYTO_RESTOCK_MAX_PER_RUN` | 50 | Max emails sent per stock trigger (prevents flooding) |
| `PHYTO_RESTOCK_SHOW_FORM_OOS` | 1 (on) | Only display the widget when the product is out of stock |

---

## Database

**Table:** `PREFIX_phyto_restock_alert`

| Column | Type | Notes |
|--------|------|-------|
| `id_alert` | INT PK AUTO_INCREMENT | |
| `id_product` | INT NOT NULL | |
| `id_product_attribute` | INT DEFAULT 0 | 0 = base product |
| `id_customer` | INT DEFAULT 0 | 0 = guest |
| `email` | VARCHAR(255) NOT NULL | |
| `firstname` | VARCHAR(100) | Optional |
| `date_add` | DATETIME | |
| `notified` | TINYINT(1) DEFAULT 0 | |
| `date_notified` | DATETIME NULL | |

Indexes: composite on `(id_product, id_product_attribute, notified)`, unique on `(id_product, id_product_attribute, email)`.

---

## Hooks

| Hook | Purpose |
|------|---------|
| `displayProductAdditionalInfo` | Renders the subscribe widget |
| `actionUpdateQuantity` | Triggers emails when stock > 0 |
| `actionOrderStatusPostUpdate` | Secondary trigger on order cancellation/refund |
| `displayAdminProductsExtra` | Shows subscriber list in product admin tab |
| `displayHeader` | Enqueues CSS and JS on product pages |

---

## File Structure

```
phyto_restock_alert/
├── phyto_restock_alert.php
├── config.xml
├── README.md
├── controllers/
│   ├── front/
│   │   └── subscribe.php
│   └── admin/
│       └── AdminPhytoRestockAlertController.php
├── sql/
│   ├── install.sql
│   └── uninstall.sql
├── views/
│   ├── css/
│   │   └── front.css
│   ├── js/
│   │   └── front.js
│   └── templates/
│       ├── hook/
│       │   ├── restock_form.tpl
│       │   └── email/
│       │       ├── restock_alert.html
│       │       └── restock_alert.txt
│       └── admin/
│           └── configure.tpl
```

---

## Author

PhytoCommerce
