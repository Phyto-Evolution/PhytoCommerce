---
title: "phyto_dispatch_logger"
description: "Log shipping conditions per order at dispatch — temperature, humidity, packing method, gel/heat packs, photos — creating a tamper-proof dispute-resolution record."
module_name: "phyto_dispatch_logger"
category: "Operations & Compliance"
category_id: "operations-compliance"
version: "1.0"
weight: 50
---

## Overview

Every time you dispatch an order, this module lets you log the shipping conditions. Creates a timestamped record per shipment for dispute resolution. If a customer claims damage, you have documented proof of the condition the plants left in.

## Log Fields

| Field | Notes |
|-------|-------|
| Temperature at packing | °C |
| Humidity at packing | % RH |
| Packing method | e.g. "Individual wrap + sphagnum" |
| Gel packs / heat packs | Yes/No + quantity |
| Shipping carrier | Selected from configured list |
| Tracking number | Auto-linked to order |
| Photos | Up to 5 images of packed box |
| Notes | Free text for unusual conditions |

## Features

- Triggered from order detail page in admin
- All entries timestamped (server time) and immutable after 24 h
- Admin can view all dispatch logs in a searchable table
- Photos stored in a private directory (not web-accessible directly)

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_dispatch_logger.php` | Module entry + admin menu |
| `classes/PhytoDispatchLog.php` | ORM for log entries |
| `controllers/admin/AdminPhytoDispatchLogController.php` | Log entry form + history |
| `sql/install.sql` | Creates `phyto_dispatch_log` table |
