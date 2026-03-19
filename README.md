# PhytoCommerce

Open-source PrestaShop module suite built for **Phyto Evolution Private Limited** — a carnivorous plant and specialty plant e-commerce business based in Chennai, India. Runs on three live PrestaShop 8.1.7 stores with ERPNext v15 integration.

---

## Module Suite Overview

| Module | Status | Description |
|---|---|---|
| `phytocommercefooter` | ✅ Complete | Replaces default PS footer with Phyto Evolution branding |
| `phytoquickadd` | ✅ Complete | Admin quick-add tool for products and categories with AI descriptions and botanical taxonomy import |
| `phytoerpconnector` | ✅ Complete | Syncs orders, customers, products and invoices between PrestaShop and ERPNext v15 |
| `phytoseobooster` | ✅ Complete | AI-powered SEO automation — meta generation, schema markup, bulk audit |

---

## Module 1 — phytocommercefooter

Replaces the default PrestaShop footer with branded text:
> *Created with ❤️ from Phyto Commerce, Phyto Evolution Private Limited*

**Hooks used:** `displayFooter`

**Installation:**
```bash
cp -r modules/phytocommercefooter /path/to/prestashop/modules/
```
Activate from Admin → Modules.

---

## Module 2 — phytoquickadd

4-tab admin interface under **Catalog → Phyto Quick Add** for rapid product and category management.

### Features
- **Add Product tab** — Name, short/full description, price (Rs.), stock, category selector, image upload
- **Add Category tab** — Create categories/sub-categories with live tree view; AJAX-based (no page reload)
- **Taxonomy Packs tab** — Import botanical family hierarchies (family → genus → species → cultivar) from GitHub
- **Settings tab** — Claude AI API key (`PHYTO_AI_KEY`)
- **AI description generation** — Uses Claude Haiku to write product descriptions on demand

### AI Setup
1. Get a Claude API key from [console.anthropic.com](https://console.anthropic.com/settings/keys)
2. Go to Admin → Catalog → Phyto Quick Add → Settings
3. Paste the key (`sk-ant-...`) and save

### Known behaviour
- Category dropdowns and tree view refresh live via AJAX after adding a category
- After taxonomy pack import, page reloads and returns to the Taxonomy tab automatically
- `ob_start()` / `ob_clean()` in `init()` guards all AJAX responses from PHP warnings

---

## Module 3 — phytoerpconnector

Connects PrestaShop stores to [ERPNext v15](https://erp.phytolabs.in) via the ERPNext REST API.

### Sync directions
| Type | Direction | Trigger |
|---|---|---|
| Customers | PS → ERPNext | On customer account creation |
| Orders | PS → ERPNext | On order status update |
| Products/Items | PS → ERPNext | On product add/update |
| Invoices | ERPNext → PS | Manual pull (last 30 days) |

### Admin location
Admin → Advanced Parameters → ERP Connector *(installed under AdminTools)*

### Tabs
- **Dashboard** — Sync stats, manual sync buttons, test connection
- **Sync Log** — Last 50 sync events with status (success / error / skipped)
- **Settings** — ERPNext URL, API Key, API Secret, per-type enable/disable toggles

### ERPNext setup
1. In ERPNext, go to **Settings → Users & Permissions → User**, select your API user, click **Generate Keys**
2. In PrestaShop admin, go to ERP Connector → Settings
3. Enter: `your ERPNext portal`, API Key, API Secret
4. Click **Test Connection** on the Dashboard tab

### Required custom ERPNext fields
Add these via ERPNext **Customize Form** → Sales Order / Sales Invoice:

| DocType | Field Name | Field Type |
|---|---|---|
| Sales Order | `custom_ps_order_id` | Int |
| Sales Order | `custom_ps_reference` | Data |
| Sales Invoice | `custom_ps_order_id` | Int |

### Database
Creates `ps_phyto_erp_sync_log` on install. Automatically pruned to 200 rows.

---

## Module 4 — phytoseobooster

SEO automation for plant product listings using Claude AI.

### Features
- **Auto meta** — Generates meta title + description via Claude Haiku when a product is saved with empty meta fields
- **Bulk generate** — Fill SEO meta for all products missing it in one click
- **SEO audit** — Scans all active products and flags: missing meta title, missing meta description, thin description (<50 chars), no images
- **Schema markup** — Automatically injects Product JSON-LD schema on all product pages (front-end)

### Admin location
Admin → SEO & URLs → Phyto SEO Booster *(installed under AdminParentMeta)*

### Tabs
- **SEO Audit** — Run scan, view issues per product, generate meta individually
- **Bulk Generate** — One-click AI meta generation for all products missing it
- **Settings** — Claude AI key (`PHYTO_AI_KEY`), auto-meta toggle

### Schema markup example (injected automatically in `<head>`)
```json
{
  "@context": "https://schema.org",
  "@type": "Product",
  "name": "Nepenthes rajah",
  "sku": "NEP-001",
  "brand": { "@type": "Brand", "name": "Phyto Evolution" },
  "offers": {
    "@type": "Offer",
    "price": "2999.00",
    "priceCurrency": "INR",
    "availability": "https://schema.org/InStock"
  }
}
```

---

## Taxonomy Packs

Botanical taxonomy data lives in `/taxonomy/` and is hosted on GitHub for live fetching by phytoquickadd.

### Directory structure
```
taxonomy/
├── index.json              ← master manifest (version, categories, pack list)
├── carnivorous/
│   ├── index.json          ← carnivorous category index (8 packs)
│   ├── nepenthaceae.json
│   ├── droseraceae.json
│   └── ...
├── aroids/
├── orchids/
├── succulents/
└── bromeliads/
```

### index.json format
```json
{
  "version": "1.1.0",
  "categories": [
    { "id": "carnivorous", "name": "Carnivorous Plants", "index": "carnivorous/index.json", "pack_count": 8 }
  ],
  "packs": [
    {
      "file": "carnivorous/nepenthaceae.json",
      "category": "carnivorous",
      "display_name": "Nepenthaceae",
      "description": "Tropical pitcher plants",
      "genera": ["Nepenthes"],
      "difficulty_range": "Intermediate to Expert"
    }
  ]
}
```

### Family pack format
```json
{
  "family": "Nepenthaceae",
  "common_name": "Tropical Pitcher Plants",
  "genera": [
    {
      "genus": "Nepenthes",
      "common_name": "Tropical Pitcher Plants",
      "species": [
        {
          "full_name": "Nepenthes rajah",
          "cultivars": [
            { "cultivar": "Giant Form", "pcr_code": "PCR-2024-NEP-001" }
          ]
        }
      ]
    }
  ]
}
```

### Adding a new taxonomy pack
1. Create `taxonomy/{category}/{family_slug}.json` following the family pack format above
2. Add an entry to `taxonomy/{category}/index.json`
3. Update `taxonomy/index.json` (bump `pack_count`, add to `packs[]`, update `summary`)
4. Commit and push to `main` — the module fetches from `raw.githubusercontent.com` with 1-hour cache

### Current pack coverage

| Category | Packs | Genera |
|---|---|---|
| Carnivorous Plants | 8 | Nepenthes, Dionaea, Drosera, Sarracenia, Cephalotus, Utricularia, Pinguicula, Heliamphora, Byblis, Roridula, Triphyophyllum |
| Succulents & Cacti | 4 | Opuntia, Mammillaria, Echeveria, Crassula, Haworthia, Aloe, Euphorbia |
| Aroids | 1 | Monstera, Philodendron, Anthurium, Alocasia |
| Orchids | 1 | Paphiopedilum, Dendrobium, Vanda, Coelogyne, Bulbophyllum |
| Bromeliads | 1 | Tillandsia, Neoregelia, Vriesea, Aechmea |

---

## Installation

### Requirements
- PrestaShop 8.1.7
- PHP 8.1+
- cURL enabled
- MariaDB / MySQL

### Per module
```bash
# Clone the repo
git clone https://github.com/Phyto-Evolution/PhytoCommerce.git

# Copy desired module into PrestaShop
cp -r PhytoCommerce/modules/phytoquickadd /path/to/prestashop/modules/

# Activate in PrestaShop admin
# Admin → Modules → search "Phyto" → Install
```

---

## Deployment to All 3 Shops (OVH VPS)

```bash
for shop_path in \
  "/home/nymph/web/nymphofnature.in/public_html" \
  "/home/cpindia_user/web/carnivorousplants.in/public_html" \
  "/home/debjit/web/thesapliing.in/public_html"
do
  cp -r /root/PhytoCommerce/modules/[modulename] "$shop_path/modules/"
  rm -rf "$shop_path/var/cache/dev/smarty/compile/"*
done

chown -R nymph:nymph /home/nymph/web/nymphofnature.in/public_html/modules/[modulename]
chown -R cpindia_user:cpindia_user /home/cpindia_user/web/carnivorousplants.in/public_html/modules/[modulename]
chown -R debjit:debjit /home/debjit/web/thesapliing.in/public_html/modules/[modulename]
```

---

## Coding Standards

- PHP 8.1, PrestaShop 8.1.7 conventions
- All admin controllers extend `ModuleAdminController` with `$this->display = 'view'`
- All AJAX responses: `ob_start()` at top of `init()`, `ob_clean()` before `echo json_encode()`
- Use `protected` (not `private`) for methods that may conflict with PS core; use unique method names
- Smarty templates: `{extends file='helpers/view/view.tpl'}` + `{block name="override_tpl"}`
- Bootstrap 3 for UI (PS 8.1.7 admin uses Bootstrap 3)
- All DB queries: `Db::getInstance()->executeS()` with `pSQL()` sanitization
- Module tabs registered via `installTab()` in `install()`

---

## Contributing — Adding Taxonomy Packs

1. Fork this repo
2. Copy an existing family JSON (e.g. `taxonomy/carnivorous/nepenthaceae.json`) as a template
3. Fill in your family, genera, species and cultivars
4. Update `taxonomy/{category}/index.json` to include your pack
5. Update `taxonomy/index.json` — increment `pack_count` and add to `packs[]`
6. Open a pull request with title: `taxonomy: add {FamilyName} pack`

Cultivar `pcr_code` follows the format `PCR-{YEAR}-{GENUS_ABBR}-{SEQ}` (e.g. `PCR-2024-NEP-001`).

---

## License

MIT — see [LICENSE](LICENSE)

## About

Built with ❤️ by [Phyto Evolution Private Limited](https://phytolabs.in), Chennai, India.
