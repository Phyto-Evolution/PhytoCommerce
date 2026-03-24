# phyto_care_card

Auto-generate a printable PDF care sheet per product using TCPDF. The PDF is attached to order confirmation emails and available for download on-demand.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Per-product care card data entry tab |
| `actionEmailAddAttachment` | Attach care PDF to order confirmation emails |

## FrontControllers

- `PhytoCareCardDownloadModuleFrontController` — serves PDF at `/module/phyto_care_card/download?id_product=X&token=Y`
  - Token = `md5(id_product . _COOKIE_KEY_)` — simple anti-scrape, no login required

## PDF Generation

- Uses **TCPDF** bundled with PrestaShop at `vendor/tecnickcom/tcpdf`
- A5 portrait layout: store logo top-right, product image top-left, product name + scientific name as heading
- Generated on-demand to `/var/tmp/`, attached to email, then deleted

## Configuration Keys

| Key | Description |
|-----|-------------|
| `PHYTO_CARE_LOGO_PATH` | Path to store logo for PDF header |
| `PHYTO_CARE_STORE_NAME` | Store name shown on PDF |
| `PHYTO_CARE_FOOTER_TEXT` | Footer text on care sheet |

## DB Tables

### `phyto_care_card`

| Column | Type | Notes |
|--------|------|-------|
| `id_care` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT UNIQUE | One row per product |
| `light` | VARCHAR(50) | Full sun / Bright indirect / Partial shade / Low light |
| `water_type` | VARCHAR(50) | Distilled only / Rainwater / Low-TDS tap / Any |
| `water_method` | VARCHAR(50) | Tray method / Top water / Mist only |
| `humidity` | VARCHAR(50) | e.g. 60–90% |
| `temperature` | VARCHAR(100) | e.g. 15–30°C day, 10–18°C night |
| `media` | TEXT | e.g. 1:1 peat:perlite |
| `feed` | TEXT | Feed protocol |
| `dormancy` | TEXT | Dormancy instructions |
| `potting` | TEXT | Potting tips |
| `problems` | TEXT | Common problems |
| `date_upd` | DATETIME | |

## Inter-module Dependencies

None. Standalone module.
