---
title: "phyto_loyalty"
description: "Points-based loyalty programme — earn on purchase, redeem in cart, 4 tiers (Seedling → Specimen), full transaction ledger, and admin adjustments."
module_name: "phyto_loyalty"
category: "Commerce"
category_id: "commerce"
version: "1.0"
platform: "PrestaShop 8"
weight: 63
---

## Overview

A full-featured loyalty points programme. Customers earn points on every purchase, redeem them in the cart as a discount, and progress through four tiers that unlock better earn rates and perks. Admins can view the full transaction ledger and manually adjust points per customer.

## Tiers

| Tier | Name | Points Threshold | Earn Rate |
|------|------|-----------------|-----------|
| 1 | Seedling | 0 | 1× |
| 2 | Cutting | 500 | 1.25× |
| 3 | Grower | 2,000 | 1.5× |
| 4 | Specimen | 5,000 | 2× |

## Customer Experience

- Points balance shown in account dashboard and cart
- "Redeem X points for ₹Y off" widget in cart (configurable conversion rate)
- Tier badge displayed on account page
- Transaction history: earned, redeemed, expired, admin-adjusted

## Admin Features

- Per-customer ledger view
- Manual point adjustment (add/deduct) with reason note
- Configurable: points per ₹1 spent, redemption rate, tier thresholds
- Bulk export of loyalty balances

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_loyalty.php` | Module entry + hooks |
| `classes/PhytoLoyaltyAccount.php` | ORM for customer point balances |
| `classes/PhytoLoyaltyLedger.php` | ORM for individual transactions |
| `classes/PhytoTierCalculator.php` | Tier and earn-rate logic |
| `controllers/admin/AdminPhytoLoyaltyController.php` | Admin ledger + adjustments |
| `sql/install.sql` | Creates accounts + ledger tables |
| `views/templates/hook/cart_redeem.tpl` | Cart redemption widget |
| `views/templates/hook/account_balance.tpl` | Account dashboard balance card |
