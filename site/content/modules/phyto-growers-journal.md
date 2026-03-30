---
title: "phyto_growers_journal"
description: "Purchase-gated customer grow diaries with photos, milestone markers, and admin approval — shown as a 'Growers Journal' tab on the product page."
module_name: "phyto_growers_journal"
category: "Customer & Community"
category_id: "customer-community"
version: "1.0"
weight: 40
---

## Overview

Lets customers who bought a plant write ongoing journal entries about how it's growing — with photos, notes, and milestone markers. Only verified buyers of that specific product can post, which keeps content authentic. Entries go through admin approval before going public.

## Features

- Purchase-gated posting (buyers only, verified via order history)
- Rich entries: date, text note, photo upload, milestone type (First Leaf, First Flower, Repotted, etc.)
- Admin approval queue — entries hidden until approved
- "Growers Journal" tab on product page with chronological entries and photos
- Shows number of active growers for social proof

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductTabContent` | "Growers Journal" tab on product page |
| `displayCustomerAccount` | "My Journal Entries" in account area |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_growers_journal.php` | Module entry + hooks |
| `classes/PhytoJournalEntry.php` | ORM model for journal entries |
| `controllers/front/` | Submit and view journal endpoints |
| `controllers/admin/AdminPhytoGrowersJournalController.php` | Approval queue |
| `sql/install.sql` | Creates journal entries table |
| `views/templates/hook/journal_tab.tpl` | Product page journal tab |
