# phytoseobooster

SEO automation for carnivorous plant listings. Injects JSON-LD Product schema markup on product pages, auto-generates meta title/description for new products, and provides a bulk SEO audit dashboard.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayHeader` | Inject JSON-LD Product schema on product pages |
| `actionObjectProductAddAfter` | Auto-generate meta for new products (if fields empty) |
| `actionObjectProductUpdateAfter` | Auto-generate meta for updated products (if fields empty) |

## Admin Pages

| Controller | Location | Purpose |
|-----------|----------|---------|
| `AdminPhytoSeoBooster` | SEO → Phyto SEO Booster | Bulk audit, manual meta editor, schema preview |

## Configuration Keys

| Key | Description |
|-----|-------------|
| `PHYTO_SEO_AUTO_META` | Enable auto meta generation on product save (0/1) |

## DB Tables

### `phyto_seo_audit`

| Column | Type | Notes |
|--------|------|-------|
| `id_audit` | INT AUTO_INCREMENT | Primary key |
| `id_product` | INT | |
| `id_lang` | INT | Language ID |
| `score` | TINYINT | 0–100 SEO score |
| `issues_json` | TEXT | JSON array of issue codes |
| `date_audited` | DATETIME | |

## Inter-module Dependencies

None. Standalone module.
