---
title: "phyto_wholesale_portal"
description: "Full B2B wholesale layer — application workflow, tiered pricing, MOQ enforcement, and invoice-on-delivery for approved accounts."
module_name: "phyto_wholesale_portal"
category: "Commerce"
category_id: "commerce"
version: "1.0"
weight: 60
---

## Overview

Adds a complete wholesale layer to your store. Businesses apply for a wholesale account, you approve or reject, and approved customers get wholesale-only pricing with MOQ rules enforced in the cart. Optional invoice-on-delivery payment method for approved accounts.

## Application Workflow

1. Business fills in application form (business name, GST, website, expected order volume)
2. Admin reviews in "Wholesale Applications" panel — approve or reject
3. Approved customers see a "Wholesale" badge and unlocked pricing on their account page
4. Cart enforces MOQ per product; below-MOQ items show a warning not a block

## Pricing Tiers

| Tier | Condition | Discount |
|------|-----------|---------|
| Bronze | Approved | Configurable % off retail |
| Silver | Configurable spend threshold | Higher % |
| Gold | Configurable spend threshold | Highest % |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_wholesale_portal.php` | Module entry + hooks |
| `classes/PhytoWholesaleApplication.php` | ORM for applications |
| `classes/PhytoWholesalePricing.php` | Tier pricing logic |
| `controllers/front/apply.php` | Application form |
| `controllers/admin/AdminPhytoWholesaleController.php` | Application review + tier management |
| `sql/install.sql` | Creates application + pricing tables |
