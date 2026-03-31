---
title: "phyto_source_badge"
description: "Create and assign sourcing-origin badges to products — Tissue Culture, Wild Collected, Nursery Grown, Conservation Propagation — displayed on listing cards and product pages."
module_name: "phyto_source_badge"
category: "Customer & Community"
category_id: "customer-community"
version: "1.0"
platform: "PrestaShop 8"
weight: 42
---

## Overview

Lets you create and assign badges to products that tell buyers where the plant came from and how it was produced. Badges appear on product listing cards and the product page, helping buyers make informed choices and building trust around your sourcing practices.

## Example Badge Types

- Tissue Culture
- Wild Collected (with CITES note)
- Nursery Grown
- Certified Organic
- Conservation Propagation
- Seed Grown

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductListFunctionalButtons` | Badge on listing card |
| `displayProductAdditionalInfo` | Badge on product page |
| `displayAdminProductsExtra` | Admin badge selector |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_source_badge.php` | Module entry + hooks |
| `classes/PhytoSourceBadge.php` | ORM for badge definitions |
| `classes/PhytoProductBadge.php` | ORM for product-to-badge mapping |
| `controllers/admin/AdminPhytoSourceBadgeController.php` | Badge management |
| `sql/install.sql` | Creates badge tables |
| `views/templates/hook/badge.tpl` | Badge display template |
