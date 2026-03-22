# phyto_phytosanitary

**Author:** PhytoCommerce
**Version:** 1.0.0
**Compatibility:** PrestaShop 8.0 – 8.1
**License:** AFL-3.0

---

## Overview

`phyto_phytosanitary` is a PrestaShop 8.1 module that lets store administrators attach phytosanitary regulatory documents (inspection certificates, import permits, quarantine clearances, CITES permits, and state movement permits) to individual products or to the store as a whole.

Key capabilities:

- Upload PDF documents (≤ 5 MB) through a dedicated back-office controller.
- Associate documents with a single product or make them store-level (applies to all products).
- Control customer visibility per document (`is_public` flag).
- Display a "Regulatory Documents" tab on the product page for public documents with optional download links.
- Automatically append all non-expired reference numbers to packing slip PDFs.
- Clean up orphaned documents when a product is deleted.
- Visual expiry warnings (orange = within 30 days, red = expired) in both the back-office list and the front-office product tab.

---

## File Structure

```
phyto_phytosanitary/
├── phyto_phytosanitary.php                          Main module class
├── config.xml                                        Module metadata
├── README.md                                         This file
├── classes/
│   └── PhytoPhytosanitaryDoc.php                    ObjectModel + static queries
├── controllers/
│   ├── admin/
│   │   └── AdminPhytoPhytosanitaryController.php    Back-office CRUD + file upload
│   └── front/                                        (reserved for future front controllers)
├── sql/
│   ├── install.sql                                   CREATE TABLE
│   └── uninstall.sql                                 DROP TABLE
├── translations/                                     PO/MO translation files
└── views/
    ├── css/
    │   ├── front.css                                 Front-office styles
    │   └── admin.css                                 Back-office styles
    ├── js/                                           (reserved for future JS)
    └── templates/
        ├── admin/                                    (reserved for custom admin templates)
        ├── front/                                    (reserved for future front templates)
        └── hook/
            └── product_extra_content.tpl             Product-page regulatory docs tab
```

---

## Installation

1. Copy the `phyto_phytosanitary/` directory to `<prestashop_root>/modules/`.
2. In the PrestaShop back office go to **Modules > Module Manager**.
3. Search for "Phyto Phytosanitary" and click **Install**.

During installation the module:

- Creates the `ps_phyto_phytosanitary_doc` database table.
- Registers the hooks `displayProductExtraContent`, `displayPDFInvoice`, and `actionProductDelete`.
- Adds a "Phytosanitary Docs" entry under the **Catalog** menu.
- Creates the upload directory `<prestashop_root>/upload/phyto_phytosanitary/` with permissions `0755` and a protective `.htaccess`.

---

## Usage

### Adding a Document

1. Navigate to **Catalog > Phytosanitary Docs** in the back office.
2. Click **Add new**.
3. Fill in the form:
   - **Document Type** – select from the predefined list.
   - **Product** – choose a specific product or "Store-level".
   - **Issuing Authority** – the body that issued the document.
   - **Reference Number** – the certificate/permit number printed on the document.
   - **Issue Date / Expiry Date** – leave expiry blank if the document does not expire.
   - **Document File** – upload a PDF (max 5 MB).
   - **Visible to Customers** – enable to show a download link on the product page.
4. Click **Save**.

### Front-Office Display

When a product with at least one public document is viewed, an extra "Regulatory Documents" tab appears in the product description tabs section. The tab lists all relevant documents with optional PDF download links.

Documents expiring within 30 days are highlighted in orange; expired documents are shown in red.

### Packing Slip Integration

The hook `displayPDFInvoice` fires when an invoice/delivery slip PDF is generated. All non-expired reference numbers for the products in that order are appended as a "Regulatory Compliance" line at the bottom of the PDF.

---

## Upload Directory

Uploaded PDFs are stored in:

```
<prestashop_root>/upload/phyto_phytosanitary/
```

The module writes an `.htaccess` that disables directory listing. Direct file access via HTTP is possible if your web server honours `.htaccess` files; for stricter protection consider moving the upload directory outside the web root and serving files via a PHP controller.

---

## Hooks

| Hook | Purpose |
|---|---|
| `displayProductExtraContent` | Renders the "Regulatory Documents" tab on the product page |
| `displayPDFInvoice` | Appends reference numbers to packing slip / invoice PDFs |
| `actionProductDelete` | Removes documents and files when a product is deleted |

---

## Database

Single table `ps_phyto_phytosanitary_doc` (prefix may differ):

| Column | Type | Notes |
|---|---|---|
| `id_doc` | INT PK | Auto-increment |
| `id_product` | INT | 0 = store-level |
| `doc_type` | VARCHAR(50) | Slug, e.g. `phytosanitary_certificate` |
| `issuing_authority` | VARCHAR(200) | |
| `reference_number` | VARCHAR(100) | |
| `issue_date` | DATE | |
| `expiry_date` | DATE | NULL = no expiry |
| `filename` | VARCHAR(255) | UUID-prefixed filename in upload dir |
| `is_public` | TINYINT(1) | 1 = visible to customers |
| `date_add` | DATETIME | |
| `date_upd` | DATETIME | |

---

## Uninstallation

Go to **Modules > Module Manager**, find the module and click **Uninstall**.

The database table is dropped. Physical PDF files in the upload directory are **not** automatically deleted to avoid accidental data loss; remove the directory manually if desired.

---

## Changelog

### 1.0.0 (2026-03-19)
- Initial release.
