---
title: "phyto_phytosanitary"
description: "Manage phytosanitary certificates, import permits, and regulatory PDFs per product — colour-coded validity tracking, public download, and automatic packing-slip references."
module_name: "phyto_phytosanitary"
category: "Operations & Compliance"
category_id: "operations-compliance"
version: "1.0"
weight: 51
---

## Overview

Manages phytosanitary certificates, import permits, and other regulatory PDFs per product. Upload the document, set an expiry date, and the module tracks validity with colour-coded badges. Documents can be made public for buyer download. References auto-append to packing slips.

## Validity Badges

| Status | Colour | Condition |
|--------|--------|-----------|
| Valid | Green | Expiry > 30 days away |
| Expiring Soon | Amber | Expiry ≤ 30 days |
| Expired | Red | Past expiry date |
| No Document | Grey | No PDF uploaded |

## Features

- Upload PDF per product with expiry date
- Public/private toggle (public = downloadable from product page)
- Admin dashboard of all documents sorted by expiry
- Certificate reference auto-appended to packing slip PDF
- Email alert when a certificate is expiring within 14 days

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_phytosanitary.php` | Module entry + hooks |
| `classes/PhytoSanitaryDoc.php` | ORM for document records |
| `controllers/admin/AdminPhytoPhytosanitaryController.php` | Document management |
| `controllers/front/download.php` | Public PDF download |
| `sql/install.sql` | Creates document table |
| `views/templates/hook/product_badge.tpl` | Validity badge on product page |
