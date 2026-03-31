---
title: "phyto_tc_batch_tracker"
description: "Links tissue-culture products to propagation batch records — full provenance (batch code, generation, protocol, deflask/cert dates) on the product page."
module_name: "phyto_tc_batch_tracker"
category: "Plant Science"
category_id: "plant-science"
version: "1.1"
platform: "PrestaShop 8"
weight: 31
---

## Overview

Tracks batches of tissue-culture plants from flask to sale. Each batch gets an auto-generated code, generation label (G0, G1, G2…), and dates for initiation, deflasking, and certification. A provenance card shows on the product page so buyers see the batch's full history.

## Version History

### v1.0 — First Build
Batch codes, generation labels, initiation/deflask/cert dates. Provenance card on product page. Admin batch list.

### v1.1 — Five Robustness Features (current)

1. **Inventory auto-decrement** — when an order ships, the batch's unit count drops automatically; transitions to "Depleted" at zero.
2. **Contamination incident log** — tracks contamination events by type (bacterial, fungal, viral, pest) with severity and "Mark Resolved".
3. **Mother batch lineage** — each batch can point to a parent; product page shows the full ancestral chain from original mother stock.
4. **Printable QR label** — 88 mm label with batch code, generation, dates, and lineage breadcrumb for tube stickers.
5. **Low-stock alert email** — configurable threshold triggers a single warning email per batch when units are running low.

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_tc_batch_tracker.php` | Module entry + hooks |
| `classes/PhytoTcBatch.php` | ORM model for batch records |
| `classes/PhytoContaminationLog.php` | Contamination incident model |
| `controllers/admin/AdminPhytoTcBatchController.php` | Batch management dashboard |
| `sql/install.sql` | Creates batch + contamination tables |
| `views/templates/hook/product_tab.tpl` | Front-end provenance card |
| `views/templates/admin/qr_label.tpl` | Printable QR label layout |
