# phyto_tc_cost_calculator

Back-office tissue culture (TC) production cost calculator for PhytoCommerce. Lets staff calculate and record per-batch production costs for tissue-cultured plants.

## Features

- Per-batch cost entry (media, chemicals, labour, overhead)
- Calculated cost-per-unit and margin display
- Historical batch cost records with admin list view
- No front-office footprint — back-office only module

## Installation

1. Upload the `phyto_tc_cost_calculator` folder to `/modules/`.
2. Install from **Modules > Module Manager**.

## Database Tables

| Table | Description |
|-------|-------------|
| `phyto_tc_batch` | TC batch cost records |

## Admin Controller

| Controller | Tab | Description |
|------------|-----|-------------|
| `AdminPhytoTcCostCalc` | Catalog | Manage TC batch cost records |

## Calculator Fields

| Field | Description |
|-------|-------------|
| `batch_name` | Identifier for the batch |
| `id_product` | Associated PrestaShop product |
| `qty_produced` | Number of plants produced |
| `media_cost` | Growth media cost |
| `chemical_cost` | Reagents and chemical cost |
| `labour_cost` | Labour cost for the batch |
| `overhead_cost` | Overhead allocation |
| `notes` | Optional notes |

Cost per unit and margin % are calculated dynamically in the browser via `views/js/calculator.js`.

## Uninstall

Uninstalling drops the `phyto_tc_batch` table and removes the admin tab.
