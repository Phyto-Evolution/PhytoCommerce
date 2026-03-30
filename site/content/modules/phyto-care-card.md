---
title: "phyto_care_card"
description: "Auto-generate a printable PDF care guide per product using TCPDF — attached to order confirmation emails and downloadable on-demand from the product page."
module_name: "phyto_care_card"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
weight: 34
---

## Overview

Generates a printable PDF care card for each product covering everything a buyer needs to know: light requirements, watering frequency, humidity, temperature range, potting media, dormancy notes, and more. Automatically attached to the order confirmation email.

## Care Card Fields

- Light requirements (Full sun / Partial shade / Low light)
- Watering frequency and method
- Humidity range (%)
- Temperature min/max (°C)
- Potting media recipe
- Fertilisation schedule
- Dormancy / seasonal notes
- Special care tips (free text)

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `actionOrderStatusUpdate` | Attaches PDF to order confirmation email |
| `displayProductAdditionalInfo` | "Download Care Card" button on product page |
| `displayAdminProductsExtra` | Admin care card data entry form |
| `displayPDFInvoice` | Optional inclusion on invoice |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_care_card.php` | Module entry + hooks |
| `classes/PhytoCareCardGenerator.php` | TCPDF wrapper — builds the PDF |
| `classes/PhytoCareCardData.php` | ORM model for care fields |
| `controllers/front/download.php` | Public PDF download endpoint |
| `controllers/admin/AdminPhytoCareCardController.php` | Admin data entry |
| `sql/install.sql` | Creates `phyto_care_card_data` table |
