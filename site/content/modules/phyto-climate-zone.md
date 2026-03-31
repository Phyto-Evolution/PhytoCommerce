---
title: "phyto_climate_zone"
description: "Customer enters their PIN code and sees which plants suit their Indian climate zone — 15 PCC-IN zones, 797 PIN prefixes, monthly climate chart, fully offline."
module_name: "phyto_climate_zone"
category: "Plant Science"
category_id: "plant-science"
version: "2.0"
platform: "PrestaShop 8"
weight: 35
---

## Overview

Buyer enters their city or PIN code; the module shows which plants are suitable for their climate (outdoor vs indoor-only). Fully offline — no external API calls. Covers all major Indian climate types from humid tropical Kerala to Rajasthan desert to Himalayan alpine.

## Version History

### v1.0 — First Build
5 hardcoded climate zones, 26 PIN prefixes, basic "Suitable / Not Recommended" result. Proof-of-concept only.

### v2.0 — 15 PCC-IN Zones, 797 PIN Prefixes, Monthly Climate Chart (current)
Complete data-driven rewrite:

- **797 Indian 3-digit PIN prefixes** mapped to 15 climate zones
- **15 PCC-IN climate zones** — from `IN-KL-TH` (Kerala humid tropical) to `IN-RJ-HD` (Rajasthan hot desert) to `IN-HP-AL` (Himalayan alpine)
- **Monthly climate chart** — temperature + humidity bar chart, frost risk flags, monsoon month markers
- **Zone detail panel** — example cities, outdoor/indoor suitability verdict, specific plant warnings
- Entire dataset ships as two JSON files — zero HTTP calls at runtime

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_climate_zone.php` | Module entry + hooks |
| `data/pin_to_zone.json` | 797 PIN prefix → zone mappings |
| `data/zone_climate.json` | Monthly climate data per zone |
| `views/js/climate_widget.js` | PIN lookup + chart rendering (Chart.js) |
| `views/templates/hook/widget.tpl` | Product page climate widget |
