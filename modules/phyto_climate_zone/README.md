# phyto_climate_zone

Buyer enters their city/pincode; module shows which plants are suitable for their climate (outdoor vs indoor-only). Fully offline — no external API calls.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Per-product climate suitability settings tab |
| `displayProductExtraContent` | Front office Climate Suitability section with pincode input |

## FrontControllers

- `PhytoClimateCheckModuleFrontController` — AJAX endpoint for pincode lookup and zone comparison

## DB Tables

### `phyto_climate_product`

| Column | Type | Notes |
|--------|------|-------|
| `id_climate` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT UNIQUE | One row per product |
| `suitable_zones` | TEXT | JSON array of zone slugs |
| `min_temp` | INT | Minimum temperature tolerance (°C) |
| `max_temp` | INT | Maximum temperature tolerance (°C) |
| `cannot_tolerate` | TEXT | JSON array of intolerance flags |
| `outdoor_notes` | TEXT | Region-specific care tips |

## Configuration Keys

| Key | Description |
|-----|-------------|
| `PHYTO_CLIMATE_ZONE_MAP` | JSON: pincode-prefix → climate-zone mapping |

Default mapping pre-populated with ~30 common Indian city prefixes on install.

## Climate Zones

- `tropical_humid` — India coastal / South India
- `tropical_dry` — Deccan plateau
- `subtropical` — North India plains
- `highland_temperate` — Nilgiris / Himalayas
- `any_indoor` — Controlled environment

## Inter-module Dependencies

None. Standalone module.
