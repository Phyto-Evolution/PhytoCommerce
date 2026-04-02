# Phyto TC Cost Calculator for WooCommerce

Admin-only tool for estimating tissue-culture production costs. Enter substrate, overhead, and labour inputs and get instant cost-per-plant and retail pricing at configurable margin targets. Save, reload, and export named estimates.

## Features

- **Live calculation** — substrate cost + overhead + labour inputs produce cost-per-plant in real time (JavaScript, no form submission)
- **Retail pricing** — computed at 40%, 50%, and 60% margin plus a custom target % you set
- **Save estimates** — name and save any calculation to the database for later comparison
- **Load / delete** — saved estimates list in the right panel; one-click load restores all inputs
- **CSV export** — download all saved estimates as a spreadsheet via admin-post action

## Database

Creates `{prefix}phyto_tc_cost_estimate`: `batch_id`, `estimate_label`, `inputs_json`, `results_json`.

## Access

Tools > TC Cost Calculator (WP Admin sidebar).

## Installation

1. Upload the `phyto-tc-cost-calculator` folder to `wp-content/plugins/`
2. Activate — the DB table is created on activation
3. Visit Tools > TC Cost Calculator

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
