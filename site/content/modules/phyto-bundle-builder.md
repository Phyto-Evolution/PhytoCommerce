---
title: "phyto_bundle_builder"
description: "Customer-facing bundle creator — slot-based configuration, AJAX product picker, combined bundle discount, and admin bundle management."
module_name: "phyto_bundle_builder"
category: "Commerce"
category_id: "commerce"
version: "1.0"
platform: "PrestaShop 8"
weight: 62
---

## Overview

Lets customers build their own plant bundles from slot-based templates you define. They pick products to fill each slot, get a combined bundle discount, and add the whole bundle to cart in one click. You manage bundle templates and discounts from admin.

## How It Works

1. Admin creates a bundle template: name, number of slots, eligible product categories per slot, and discount %
2. Customer visits the bundle builder page, selects a template
3. AJAX product picker lets them search and choose a product for each slot
4. Bundle summary shows total price with discount applied
5. "Add Bundle to Cart" adds all selected products with the combined discount as a cart rule

## Features

- Slot-based templates (e.g. "3-plant starter bundle: 1 Nepenthes + 1 Heliamphora + 1 Sarracenia")
- AJAX product search per slot (filters by eligible categories)
- Combined bundle discount applied as a cart rule (not a fake price reduction)
- Bundle compositions saved to DB — admin can see what bundles customers built
- Mobile-responsive bundle builder UI

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_bundle_builder.php` | Module entry + menu registration |
| `classes/PhytoBundle.php` | ORM for bundle templates |
| `classes/PhytoBundleSlot.php` | ORM for slot definitions |
| `controllers/front/builder.php` | Bundle builder page |
| `controllers/front/products.php` | AJAX product search endpoint |
| `controllers/admin/AdminPhytoBundleBuilderController.php` | Bundle template management |
| `views/templates/front/builder.tpl` | Bundle builder UI |
| `views/js/front.js` | AJAX slot interaction + cart add |
