# phytoquickadd

Quickly add products and categories to PrestaShop using carnivorous plant taxonomy intelligence. Taxonomy data is fetched from the PhytoCommerce GitHub taxonomy repository and cached locally.

## Admin Pages

| Controller | Location | Purpose |
|-----------|----------|---------|
| `AdminPhytoQuickAdd` | Catalog → Phyto Quick Add | Quick product/category creation wizard |

## Hooks Registered

None — admin-only tool, no front office hooks.

## Configuration Keys

| Key | Description |
|-----|-------------|
| `phyto_<md5(url)>` | Cached taxonomy JSON payloads (TTL: 3600s) |

## DB Tables

None — no custom tables required. Taxonomy data cached via PrestaShop Configuration keys.

## Taxonomy Data Source

Fetches from `https://raw.githubusercontent.com/Phyto-Evolution/PhytoCommerce/main/taxonomy/`

- `index.json` — top-level taxonomy index
- `{category_id}/index.json` — category-level index
- Individual species pack JSON files

## Inter-module Dependencies

None. Standalone module.
