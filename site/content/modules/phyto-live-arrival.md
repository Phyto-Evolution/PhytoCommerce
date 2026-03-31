---
title: "phyto_live_arrival"
description: "Live Arrival Guarantee opt-in at checkout — configurable fee or free threshold, photo-upload claim form, admin claim panel."
module_name: "phyto_live_arrival"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
platform: "PrestaShop 8"
weight: 37
---

## Overview

Offers customers an opt-in "Live Arrival Guarantee" during checkout. For a small configurable fee (or free above a spend threshold), they're covered if their plant arrives dead or severely damaged. The module tracks LAG coverage per order and provides a photo-upload claim form.

## Features

- Opt-in checkbox at checkout with configurable fee or free threshold
- LAG fee added as a cart rule automatically
- Per-order LAG coverage flag visible in admin order view
- Claim form (with photo upload) on customer order history page
- Admin claims panel: view, approve, reject, and track claim status
- Configurable: claim window (days), allowed shipping carriers, weather blackout dates

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayPaymentTop` | LAG opt-in widget at checkout |
| `actionOrderStatusUpdate` | Tags order with LAG coverage on confirmation |
| `displayCustomerAccount` | "Submit LAG Claim" link in account area |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_live_arrival.php` | Module entry + hooks |
| `classes/PhytoLagClaim.php` | ORM for LAG claim records |
| `controllers/front/claim.php` | Customer claim submission form |
| `controllers/admin/AdminPhytoLagClaimsController.php` | Admin claim management |
| `sql/install.sql` | Creates LAG coverage + claims tables |
