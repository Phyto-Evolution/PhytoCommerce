---
title: "phytoerpconnector"
description: "Bi-directional sync between PrestaShop 8 and ERPNext v15 via REST API — orders, customers, products, and invoices."
module_name: "phytoerpconnector"
category: "Foundation"
category_id: "foundation"
version: "1.0"
weight: 23
---

## Overview

Keeps your PrestaShop store and ERPNext accounting/inventory system in sync automatically. No manual double-entry. When a customer places an order, a Sales Order appears in ERPNext. New customer registrations create Customer records. Product changes push across. Invoices pull back.

## Sync Direction

| Event | PrestaShop → ERPNext |
|-------|---------------------|
| New order | Creates Sales Order |
| New customer | Creates Customer record |
| Product update | Pushes product data |
| Invoice created (ERPNext) | Pulls back to PS order history |

## Features

- Full sync log for audit: every API call logged with status and response
- Configurable ERPNext base URL, API key, and secret from PS admin
- Retry queue for failed sync attempts
- Manual "Sync Now" button per order from the order detail page

## Source Layout

| Path | Purpose |
|------|---------|
| `phytoerpconnector.php` | Module entry + hook registration |
| `classes/ErpNextClient.php` | REST API wrapper (auth, requests, retry) |
| `classes/SyncLog.php` | DB model for the audit log |
| `controllers/admin/` | Sync log viewer + settings page |
| `sql/install.sql` | Creates `phyto_erp_sync_log` table |
