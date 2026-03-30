---
title: "phyto_tc_cost_calculator"
description: "Back-office TC production cost calculator — substrate, electricity, labour, contamination losses, overhead, margin — outputs break-even and suggested retail price."
module_name: "phyto_tc_cost_calculator"
category: "Operations & Compliance"
category_id: "operations-compliance"
version: "1.0"
weight: 52
---

## Overview

A back-office calculator for working out the true cost of producing tissue-culture plants. Enter your input costs and desired margin — the module calculates your break-even price and suggested retail price. Admin-only, never visible to customers.

## Calculator Inputs

| Input | Unit |
|-------|------|
| Substrate cost | ₹ per litre |
| Electricity cost | ₹ per kWh |
| Labour hours per batch | Hours |
| Labour rate | ₹ per hour |
| Contamination loss rate | % |
| Overhead allocation | ₹ per batch |
| Units produced per batch | Count |
| Target margin | % |

## Outputs

- **Break-even price per unit** (₹)
- **Suggested retail price** at target margin (₹)
- **Cost breakdown chart** — pie chart of cost components

## Features

- Save named batch cost records for historical comparison
- Link a cost record to a `phyto_tc_batch_tracker` batch entry
- Export cost report as PDF

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_tc_cost_calculator.php` | Module entry + admin menu |
| `classes/PhytoCostRecord.php` | ORM for saved cost records |
| `controllers/admin/AdminPhytoTcCostController.php` | Calculator + history |
| `sql/install.sql` | Creates cost records table |
| `views/templates/admin/calculator.tpl` | Calculator form and results |
