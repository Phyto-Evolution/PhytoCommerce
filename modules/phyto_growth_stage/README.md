# phyto_growth_stage

Replace or augment PrestaShop combination size labels with named growth stages, each carrying care-difficulty and time-to-maturity metadata.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Per-product tab: map combinations to global stages |
| `displayProductExtraContent` | Renders Growth Stage card on product page |
| `displayProductPriceBlock` | Injects stage difficulty badge near Add to Cart |

## DB Tables

### `phyto_growth_stage_def`

| Column | Type | Notes |
|--------|------|-------|
| `id_stage` | INT AUTO_INCREMENT | Primary key |
| `stage_name` | VARCHAR(100) | e.g. Protocorm, Deflasked, Hardened |
| `stage_code` | VARCHAR(50) UNIQUE | Auto-generated slug |
| `difficulty` | ENUM | Beginner / Intermediate / Advanced / Expert |
| `weeks_to_next` | INT | Estimated weeks to advance to next stage |
| `description` | TEXT | Stage description |
| `sort_order` | INT | Display order |

### `phyto_growth_stage_product`

| Column | Type | Notes |
|--------|------|-------|
| `id_link` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT | |
| `id_product_attribute` | INT | 0 = product-level |
| `id_stage` | INT | FK → phyto_growth_stage_def |
| `weeks_override` | INT | Per-product weeks override |

## Configuration Keys

None — all data stored in DB tables.

## Inter-module Dependencies

Used by `phyto_acclimation_bundler` (Module 7) to determine acclimation trigger stages.
