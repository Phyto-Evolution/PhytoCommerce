# phyto_invoice_customizer

**Phyto Invoice Customizer** — PrestaShop 8 module by PhytoCommerce.

Customises PDF invoices to include phytosanitary certificate details, TC batch numbers, Live Arrival Guarantee text, and a branded header/footer.

## Features

| Feature | Hook | Toggle |
|---|---|---|
| Branded header | `displayPDFInvoiceHeader` | always on |
| TC batch numbers per product | `displayPDFInvoice` | `PHYTO_INV_SHOW_BATCH` |
| Phytosanitary certificate refs | `displayPDFInvoice` | `PHYTO_INV_SHOW_PHYTO` |
| Live Arrival Guarantee statement | `displayPDFInvoice` | `PHYTO_INV_SHOW_LAG` |
| Branded footer + disclaimer | `displayPDFInvoiceFooter` | always on |

## Dependencies

This module **reads** data from other PhytoCommerce modules but does **not** require them to be installed. Each cross-module query is guarded by a `SHOW TABLES LIKE` check and silently skipped when the table is absent.

| Data | Source module |
|---|---|
| TC batch codes | `phyto_tc_batch_tracker` |
| Phytosanitary certificate refs | `phyto_phytosanitary` |

## Configuration keys

| Key | Type | Default | Description |
|---|---|---|---|
| `PHYTO_INV_SHOW_LAG` | bool | `1` | Show Live Arrival Guarantee text |
| `PHYTO_INV_LAG_TEXT` | text | *(see below)* | LAG statement |
| `PHYTO_INV_SHOW_BATCH` | bool | `1` | Show TC batch numbers |
| `PHYTO_INV_SHOW_PHYTO` | bool | `1` | Show phytosanitary certificate references |
| `PHYTO_INV_FOOTER_NOTE` | text | *(empty)* | Custom footer note |
| `PHYTO_INV_BRAND_NAME` | text | shop name | Brand name in header/footer |

Default LAG text:
> "This order is covered by our Live Arrival Guarantee. If your plants arrive dead or severely damaged, contact us within 2 hours with photos."

## Installation

1. Upload the `phyto_invoice_customizer` directory to `modules/`.
2. In the PrestaShop back office go to **Modules > Module Manager** and install **Phyto Invoice Customizer**.
3. Configure via **Modules > Configure** or the **Invoice Customizer** tab in the Phyto Suite menu.

## File structure

```
phyto_invoice_customizer/
├── phyto_invoice_customizer.php          Main module class
├── config.xml
├── README.md
├── controllers/
│   └── admin/
│       └── AdminPhytoInvoiceCustomizerController.php
├── sql/
│   ├── install.sql     (no new tables)
│   └── uninstall.sql   (no tables to drop)
└── views/
    └── templates/
        ├── hook/
        │   ├── invoice_header.tpl   displayPDFInvoiceHeader
        │   ├── invoice_extra.tpl    displayPDFInvoice
        │   └── invoice_footer.tpl   displayPDFInvoiceFooter
        └── admin/
            └── configure.tpl
```

## Compatibility

- PrestaShop 8.0.0 – 8.x
- PHP 8.1+

## Author

PhytoCommerce — Specialist Plant E-Commerce Solutions
