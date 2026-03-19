# Phyto Growers Journal

**Version:** 1.0.0
**Author:** PhytoCommerce
**Compatible with:** PrestaShop 8.1+
**License:** MIT

---

## Overview

Phyto Growers Journal is a living grow-log module for PrestaShop 8.1. It attaches a chronological journal to each product, allowing store staff to post progress updates, milestone announcements, and growing tips. Optionally, verified buyers can also submit their own grow updates, creating a community social-proof feed directly on the product page.

---

## Features

- **Per-product timeline** — journal entries are displayed as a chronological timeline on the product page via the `displayProductExtraContent` hook.
- **Three entry types** — Store (staff), Customer (buyer), Milestone (highlighted events).
- **Photo support** — up to 3 photos per entry, stored in `/img/phyto_journal/`, validated by MIME type and file size (max 2 MB each).
- **Moderation** — customer submissions are created as unapproved and must be approved by a back-office admin.
- **Spam protection** — customers may submit at most one entry per product every 7 days (configurable via `PhytoJournalEntry::hasRecentPost`).
- **Purchase gating** — customer posting is restricted to verified buyers (`PhytoJournalEntry::customerHasPurchased`).
- **Back-office management** — dedicated `AdminPhytoGrowersJournal` controller with a filterable HelperList and HelperForm (including photo uploads).
- **Admin product tab** — quick-view of recent entries on the product edit page.
- **My Account link** — link in the customer account block when customer posting is enabled.

---

## Installation

1. Upload the `phyto_growers_journal/` folder to `/modules/`.
2. In the PrestaShop Back Office, navigate to **Modules > Module Manager**.
3. Search for **Grower's Journal** and click **Install**.
4. PrestaShop will execute `sql/install.sql`, register hooks, and create the hidden admin tab.

---

## Configuration

Go to **Modules > Module Manager > Grower's Journal > Configure**.

| Setting | Description |
|---|---|
| Allow customer posts | When enabled, logged-in customers who have purchased the product can submit entries (pending approval). |

---

## Back-Office Usage

### Managing Entries

Navigate to the hidden admin controller directly or via the link on any product edit page:

```
/admin{SUFFIX}/index.php?controller=AdminPhytoGrowersJournal
```

- **List view** — all entries, filterable by product ID, type, and approval status.
- **Create/edit form** — fields: Product, Entry Date, Title, Body (HTML), Photo 1–3, Entry Type, Approved.
- **Bulk delete** — removes selected entries and their associated photo files.
- **Toggle approved** — click the Approved column in the list to toggle without leaving the page.

### Per-Product Tab

On any product edit page, the **Grower's Journal** tab (injected via `displayAdminProductsExtra`) shows recent entries for that product with quick links to add a new entry or open the full admin list filtered to that product.

---

## Front-Office

### Product Page Timeline

Approved entries appear under the **Grower's Journal** tab on the product page. If a customer is logged in, has purchased the product, and has not posted in the last 7 days, a **Share your grow update** button is displayed.

### Customer Submission Form

URL: `/module/phyto_growers_journal/post?id_product={ID}`

- Requires login (`$this->auth = true`; unauthenticated visitors are redirected to the login page).
- On success, redirects back to the product page with a confirmation message.
- On failure, re-renders the form with inline validation errors.

---

## File Structure

```
phyto_growers_journal/
├── phyto_growers_journal.php               Main module class
├── config.xml                              Module metadata (do not edit)
├── README.md                               This file
├── classes/
│   └── PhytoJournalEntry.php               ObjectModel (do not modify)
├── controllers/
│   ├── admin/
│   │   └── AdminPhytoGrowersJournalController.php
│   └── front/
│       └── post.php                        Customer submission FrontController
├── sql/
│   ├── install.sql
│   └── uninstall.sql
├── views/
│   ├── css/
│   │   └── front.css                       Timeline & form styles
│   └── templates/
│       ├── front/
│       │   └── post_form.tpl               Customer submission form
│       └── hook/
│           ├── admin_product_tab.tpl       Admin product tab entries list
│           ├── my_account_block.tpl        My Account link
│           └── product_extra_content.tpl  Product page timeline
```

---

## Photo Storage

Photos are stored at: `{PS_IMG_DIR}/phyto_journal/`
URL path: `/img/phyto_journal/{filename}`

The directory is created automatically on first upload with `0755` permissions. Each uploaded file is assigned `0644` permissions. Filenames are randomised to prevent enumeration.

When an entry is deleted via the back office, associated photo files are removed from disk.

---

## Hooks Used

| Hook | Purpose |
|---|---|
| `displayAdminProductsExtra` | Injects journal tab into the product edit page |
| `displayProductExtraContent` | Renders the front-office timeline |
| `actionProductDelete` | Removes journal entries when a product is deleted |
| `displayMyAccountBlock` | Adds a link to customer's journal posts |

---

## Database Table

`ps_phyto_journal_entry`

| Column | Type | Notes |
|---|---|---|
| id_entry | INT(11) AUTO_INCREMENT | Primary key |
| id_product | INT(11) | Indexed |
| id_customer | INT(11) DEFAULT 0 | 0 = store-authored entry |
| entry_date | DATE | |
| title | VARCHAR(255) | |
| body | TEXT | Purified HTML |
| photo1–photo3 | VARCHAR(255) | Filename only |
| entry_type | ENUM('Store','Customer','Milestone') | |
| approved | TINYINT(1) DEFAULT 1 | 0 = pending moderation |
| date_add | DATETIME | |
| date_upd | DATETIME | |

---

## Uninstallation

In **Modules > Module Manager**, click **Uninstall** on Grower's Journal. This will:

- Execute `sql/uninstall.sql` (drops the table).
- Remove the hidden admin tab.
- Delete the module configuration key.

Photo files in `/img/phyto_journal/` are **not** removed automatically; delete the directory manually if required.

---

## Development Notes

- All user-visible strings use `$this->l('...')` / `{l s='...' mod='phyto_growers_journal'}` for translation compatibility.
- CSS classes are uniformly prefixed with `.phyto-journal-` to avoid theme conflicts.
- The `PhytoJournalEntry` ObjectModel follows standard PrestaShop conventions; extend it in your own module overrides if needed.
- The admin controller inherits from `ModuleAdminController` and therefore benefits from standard PS CRUD scaffolding (`postProcess`, `renderList`, `renderForm`).
