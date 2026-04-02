# Phyto SEO Booster for WooCommerce

AI-powered SEO automation for WooCommerce product pages.

## Features
- JSON-LD Product structured data (schema.org) injected on every product page
- Auto-generate Yoast/RankMath meta title & description on product save (Claude API)
- Full catalogue SEO audit with per-product scoring (0–100)
- Batch AI meta generation for underperforming products
- WooCommerce Settings > Phyto SEO tab for API key and currency config

## Requirements
- WordPress 6.0+, WooCommerce 8.0+, PHP 7.4+
- Claude API key (optional — for AI features)

## Database
Table: `{prefix}phyto_seo_audit` — one row per product, stores score + issues JSON.
