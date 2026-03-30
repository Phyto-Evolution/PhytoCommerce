---
title: "phyto_acclimation_bundler"
description: "Auto-suggest acclimation accessories when TC or deflasked plants are added to cart — with optional bundle discount."
module_name: "phyto_acclimation_bundler"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
weight: 36
---

## Overview

When a customer adds a tissue-culture or young plant to their cart, a widget pops up suggesting the acclimation accessories they'll need (humidity domes, rooting powder, speciality substrate, etc.). You configure which products make up the kit and which plant types trigger the suggestion.

## Features

- Configure trigger conditions: product category, growth stage, TC flag
- Configure the kit: any combination of existing PS products
- Optional bundle discount when all kit items are added together
- AJAX widget — appears inline without page reload
- Admin: manage kit compositions and discounts per trigger rule

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayShoppingCartFooter` | Renders acclimation kit widget in cart |
| `actionCartSave` | Checks cart for trigger products |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_acclimation_bundler.php` | Module entry + hooks |
| `classes/PhytoAcclimationRule.php` | ORM for trigger/kit rules |
| `sql/install.sql` | Creates rules table |
| `views/templates/hook/cart_widget.tpl` | AJAX kit suggestion widget |
| `views/js/acclimation.js` | Cart interaction + AJAX |
