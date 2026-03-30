---
title: "phyto_collection_widget"
description: "Auto-builds each customer a 'My Collection' page from their order history — public/private toggles, personal care notes, and a shareable link."
module_name: "phyto_collection_widget"
category: "Customer & Community"
category_id: "customer-community"
version: "1.0"
weight: 41
---

## Overview

Automatically builds each customer a personal "My Collection" page populated from their order history. Each plant in the collection can be toggled public or private, and customers can add personal care notes to each one. A shareable link lets them show off their collection without exposing account details.

## Features

- Auto-populated from order history — no manual entry needed
- Per-plant public/private toggle
- Personal care notes field per plant
- Shareable public collection URL (token-based, account details hidden)
- Beautiful plant card grid layout with product image and name

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayCustomerAccount` | "My Plant Collection" link in account |
| `actionOrderStatusUpdate` | Adds purchased plants to collection on order confirmation |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_collection_widget.php` | Module entry + hooks |
| `classes/PhytoCollectionItem.php` | ORM for collection entries |
| `controllers/front/collection.php` | Customer collection page |
| `controllers/front/share.php` | Public shareable collection view |
| `sql/install.sql` | Creates collection items table |
