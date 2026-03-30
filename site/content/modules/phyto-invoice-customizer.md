---
title: "phyto_invoice_customizer"
description: "Customises PS8 PDF invoices to include phytosanitary certificate refs, TC batch numbers, Live Arrival Guarantee text, and a branded header/footer."
module_name: "phyto_invoice_customizer"
category: "Invoicing & Notifications"
category_id: "invoicing-notifications"
version: "1.0"
weight: 80
---

## Overview

Customises PrestaShop's PDF invoice output to include plant-commerce-specific information: phytosanitary certificate references from `phyto_phytosanitary`, TC batch codes from `phyto_tc_batch_tracker`, Live Arrival Guarantee text where applicable, and a fully branded header/footer.

## What Gets Added to Invoices

| Section | Content Added |
|---------|--------------|
| Header | Phyto Commerce logo, shop tagline, green brand bar |
| Per line item | TC batch code and generation (if TC product) |
| Below line items | Phytosanitary certificate reference + expiry |
| Footer top | Live Arrival Guarantee terms (if LAG order) |
| Footer bottom | Regulatory disclaimer, shop contact |

## Integration Points

- **phyto_phytosanitary** — reads cert reference + expiry per product
- **phyto_tc_batch_tracker** — reads batch code + generation per line item
- **phyto_live_arrival** — checks if order has LAG coverage for footer text

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_invoice_customizer.php` | Module entry + hooks |
| `classes/PhytoInvoiceRenderer.php` | Builds custom invoice blocks |
| `views/templates/pdf/invoice.tpl` | Override of PS invoice Smarty template |
| `views/templates/pdf/invoice.style.tpl` | Custom PDF CSS styles |
