---
title: "phyto_grex_registry"
description: "Attach scientific and horticultural taxonomy metadata to any product — displayed as a collapsible 'Scientific Profile' card on the product page."
module_name: "phyto_grex_registry"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
platform: "PrestaShop 8"
weight: 30
---

## Overview

Lets you attach proper scientific information to each product: genus, species, hybrid/grex name, the registration authority that named it, and its conservation status. Shows up as a neat "Scientific Profile" tab on the product page. Ideal for rare plant buyers who need botanical provenance.

## Data Fields

| Field | Example |
|-------|---------|
| Genus | *Nepenthes* |
| Species | *rajah* |
| Hybrid / Grex name | *N. × veitchii* |
| Registration authority | RHS, ICPS |
| Conservation status | CITES Appendix I |
| Common name | Giant Pitcher Plant |

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductTabContent` | Renders "Scientific Profile" tab |
| `displayAdminProductsExtra` | Admin tab for entering taxonomy data |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_grex_registry.php` | Module entry + hook handlers |
| `classes/PhytoGrexEntry.php` | ORM model for taxonomy records |
| `sql/install.sql` | Creates `phyto_grex_registry` table |
| `views/templates/hook/product_tab.tpl` | Front-end "Scientific Profile" card |
| `views/templates/hook/admin_product_tab.tpl` | Admin entry form |
