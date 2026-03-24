# phyto_seasonal_availability

Mark products with dormancy/shipping windows; block purchase during incompatible months; show "Notify me when in season" email capture.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Per-product seasonal settings tab |
| `displayProductButtons` | Hide/disable Add to Cart outside shipping season |
| `displayProductExtraContent` | Show shipping season month grid |

## FrontControllers

- `PhytoSeasonalNotifyModuleFrontController` — handles seasonal notify email capture form POST

## DB Tables

### `phyto_seasonal_product`

| Column | Type | Notes |
|--------|------|-------|
| `id_seasonal` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT UNIQUE | One row per product |
| `ship_months` | VARCHAR(50) | Comma-separated month numbers e.g. "10,11,12,1,2,3" |
| `dormancy_months` | VARCHAR(50) | Informational dormancy months |
| `block_purchase` | TINYINT(1) | Block Add to Cart outside season |
| `out_of_season_msg` | VARCHAR(255) | e.g. "Ships October–March only" |
| `enable_notify` | TINYINT(1) | Enable notify capture form |

### `phyto_seasonal_notify`

| Column | Type | Notes |
|--------|------|-------|
| `id_notify` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT | |
| `email` | VARCHAR(150) | |
| `name` | VARCHAR(100) | |
| `notified` | TINYINT(1) | Mark as notified |
| `date_add` | DATETIME | |

## Configuration Keys

None — all data per product in DB.

## Inter-module Dependencies

None. Standalone module.
