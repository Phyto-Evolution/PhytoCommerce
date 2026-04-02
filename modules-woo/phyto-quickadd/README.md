# Phyto Quick Add for WooCommerce

Rapid product creation form with AI-generated descriptions, multi-provider AI settings, and one-click taxonomy pack importer from the PhytoCommerce taxonomy library on GitHub.

## Features

### Tab 1 — Add Product
- Name, regular price, sale price, stock quantity, SKU
- Category selector (all WC product categories)
- Tag field (comma-separated, auto-created if missing)
- Short description and long description textareas
- **Generate (AI)** button — sends product name + tags/category to the configured AI provider and fills the description field
- Image picker using the WordPress media library (multiple images → featured + gallery)
- "Add Product" AJAX button — creates a `WC_Product_Simple` and returns edit/view links without a page reload

### Tab 2 — AI Settings
- Provider selector: **Claude** (Anthropic), **OpenAI** (GPT-4o mini), **Google Gemini**, **Mistral AI**, **Cohere**
- Per-provider API key fields
- **Test Connection** button — generates a sample description to verify the key works

### Tab 3 — Taxonomy Importer
- Fetches `taxonomy/index.json` from the PhytoCommerce GitHub repo and all category sub-indexes (9 requests total)
- Displays pack cards grouped by category with genera count
- **Import as WC Categories** — creates a `Family (parent) → Genus (child)` hierarchy in WooCommerce product categories; skips existing terms

## Access

Products > Quick Add (WP Admin sidebar under Products).

## Installation

1. Upload the `phyto-quickadd` folder to `wp-content/plugins/`
2. Activate via Plugins > Installed Plugins
3. Set your AI provider API key under Products > Quick Add > AI Settings

## Requirements

- WordPress 6.0+
- WooCommerce 8.0+
- PHP 7.4+
- Outbound HTTPS to `api.anthropic.com` / OpenAI / Gemini / etc. for AI generation
- Outbound HTTPS to `raw.githubusercontent.com` for taxonomy import
