# phyto_grex_registry

Attach structured scientific and horticultural taxonomy metadata to any product. Displayed as a collapsible "Scientific Profile" card on the product page.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Injects taxonomy form tab in the product edit page |
| `displayProductExtraContent` | Renders the Scientific Profile card on the front office product page |

## DB Tables

### `phyto_grex_registry`

| Column | Type | Notes |
|--------|------|-------|
| `id_grex` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT | Linked PS product |
| `genus` | VARCHAR(100) | e.g. Nepenthes |
| `species` | VARCHAR(100) | e.g. rajah |
| `subspecies` | VARCHAR(100) | Optional |
| `cultivar` | VARCHAR(150) | e.g. 'Red Hairy' |
| `grex_name` | VARCHAR(150) | Hybrid seedling population name |
| `hybrid_formula` | VARCHAR(255) | e.g. N. rajah × N. lowii |
| `mother` | VARCHAR(150) | Primary parentage — mother |
| `father` | VARCHAR(150) | Primary parentage — father |
| `icps_registered` | TINYINT(1) | 0/1 toggle |
| `icps_number` | VARCHAR(50) | Conditional on icps_registered |
| `habitat` | TEXT | e.g. Borneo highland, 1800–2600 m |
| `endemic_region` | VARCHAR(200) | |
| `conservation_status` | VARCHAR(20) | Not Assessed / LC / NT / VU / EN / CR / EW / EX |
| `notes` | TEXT | Freeform taxonomic notes |
| `date_add` | DATETIME | |
| `date_upd` | DATETIME | |

## Configuration Keys

None — all data stored per product in the DB table.

## Inter-module Dependencies

None. Standalone module.
