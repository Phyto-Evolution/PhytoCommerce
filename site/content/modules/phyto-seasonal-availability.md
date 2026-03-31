---
title: "phyto_seasonal_availability"
description: "Mark products with dormancy or shipping windows, block purchase during incompatible months, and capture email leads with 'Notify me when in season'."
module_name: "phyto_seasonal_availability"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
platform: "PrestaShop 8"
weight: 33
---

## Overview

Some plants shouldn't be sold in certain months — too hot to ship in summer, dormant in winter. This module lets you mark which months a product is unavailable. When a customer visits during a blocked month, the "Add to Cart" button is hidden and an explanation appears, with an email capture for in-season notification.

## Features

- Per-product availability month selector (multi-select, e.g. March–October)
- Automatic Add-to-Cart block when current month is outside the window
- Custom "unavailable" message per product
- "Notify me when in season" email subscriber capture
- Admin subscriber list with bulk email capability

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductButtons` | Swaps Add-to-Cart for seasonal block message |
| `displayAdminProductsExtra` | Month range picker in product admin |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_seasonal_availability.php` | Module entry + hooks |
| `classes/PhytoSeasonalSub.php` | ORM for email subscribers |
| `sql/install.sql` | Creates availability + subscriber tables |
| `views/templates/hook/block.tpl` | Front-end seasonal block widget |
