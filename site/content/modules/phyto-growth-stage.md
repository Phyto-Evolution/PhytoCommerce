---
title: "phyto_growth_stage"
description: "Replace or augment PrestaShop combination size labels with named growth stages, each with care-difficulty and time-to-maturity metadata."
module_name: "phyto_growth_stage"
category: "Plant Science"
category_id: "plant-science"
version: "1.0"
platform: "PrestaShop 8"
weight: 32
---

## Overview

Lets you tag each product with its current growth stage — Deflasked, Juvenile, Semi-Mature, Mature, or Specimen. The stage shows as a coloured badge on product listing cards and product pages so buyers know exactly what size/age plant they're getting.

## Growth Stages

| Stage | Badge Colour | Typical Use |
|-------|-------------|-------------|
| Deflasked | Blue | Just out of TC flask |
| Juvenile | Green | Growing on, 1–6 months |
| Semi-Mature | Teal | Established, 6–18 months |
| Mature | Amber | Full size, near blooming |
| Specimen | Gold | Oversized, exhibition grade |

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductListFunctionalButtons` | Badge on listing card |
| `displayProductAdditionalInfo` | Badge on product page |
| `displayAdminProductsExtra` | Admin stage selector |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_growth_stage.php` | Module entry + hook handlers |
| `classes/PhytoGrowthStage.php` | ORM model |
| `sql/install.sql` | Creates `phyto_growth_stage` table |
| `views/templates/hook/badge.tpl` | Stage badge template |
