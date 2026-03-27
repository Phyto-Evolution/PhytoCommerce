# PhytoCommerce

A PrestaShop 8 module suite for specialty plant e-commerce — designed around the operational needs of tissue-culture producers, nurseries, and rare plant retailers. Covers TC batch provenance, phytosanitary compliance, wholesale portals, recurring subscriptions, scientific taxonomy, customer grow journals, and more.

> **Last updated:** 2026-03-24
> Session logs: [`docs/CHECKPOINT.md`](docs/CHECKPOINT.md) · [`docs/ACTIVITY_LOG.md`](docs/ACTIVITY_LOG.md)

---

## Module Suite Overview

```
PhytoCommerce/
├── modules/
│   │
│   ├── [PACK — 1-CLICK INSTALLER]
│   ├── phytocommerce_pack/               ✅ Built
│   │
│   ├── [FOUNDATION]
│   ├── phytocommercefooter/              ✅ Built
│   ├── phytoquickadd/                    ✅ Built
│   ├── phytoerpconnector/                ✅ Built
│   ├── phytoseobooster/                  ✅ Built
│   │
│   ├── [PLANT SCIENCE]
│   ├── phyto_grex_registry/              ✅ Built
│   ├── phyto_tc_batch_tracker/           ✅ Built
│   ├── phyto_growth_stage/               ✅ Built
│   ├── phyto_seasonal_availability/      ✅ Built
│   ├── phyto_care_card/                  ✅ Built
│   ├── phyto_climate_zone/               ✅ Built
│   ├── phyto_acclimation_bundler/        ✅ Built
│   ├── phyto_live_arrival/               ✅ Built
│   │
│   ├── [CUSTOMER & COMMUNITY]
│   ├── phyto_growers_journal/            ✅ Built
│   ├── phyto_collection_widget/          ✅ Built
│   ├── phyto_source_badge/               ✅ Built
│   │
│   ├── [OPERATIONS & COMPLIANCE]
│   ├── phyto_dispatch_logger/            ✅ Built
│   ├── phyto_phytosanitary/              ✅ Built
│   ├── phyto_tc_cost_calculator/         ✅ Built
│   │
│   └── [COMMERCE]
│       ├── phyto_wholesale_portal/       ✅ Built
│       └── phyto_subscription/           ✅ Built
│
└── taxonomy/                             ✅ Built
    ├── carnivorous/   (8 packs)
    ├── succulents/    (4 packs)
    ├── aroids/        (1 pack)
    ├── orchids/       (1 pack)
    └── bromeliads/    (1 pack)
```

> **22 modules built · 15 taxonomy packs**

---

## Installation

### Option A — 1-click pack (recommended)

Upload `phytocommerce_pack` to PrestaShop and click **Install**. All 21 modules are installed automatically.

```bash
# Build the standalone zip (if not deploying from full repo checkout)
cd modules
cp -r phyto_* phytocommercefooter phytocommerce_branding phytoquickadd phytoerpconnector phytoseobooster \
      phytocommerce_pack/bundled/
zip -r phytocommerce_pack.zip phytocommerce_pack/
# Upload via Admin → Modules → Upload a module
```

After install: **Admin → Advanced Parameters → PhytoCommerce Pack** — see live status of all modules, install/uninstall individually.

### Option B — individual module

```bash
cp -r modules/<module_name> /path/to/prestashop/modules/
rm -rf /path/to/prestashop/var/cache/*/smarty/compile/*
# Admin → Modules → search module name → Install
```

---

## Module Index

### Pack

| Module | Description |
|--------|-------------|
| `phytocommerce_pack` | 1-click installer — installs all 21 PhytoCommerce modules from a single back-office button. Dashboard shows live install status per module. |

### Foundation

| Module | Description |
|--------|-------------|
| `phytocommercefooter` | Branded footer replacement |
| `phytocommerce_branding` | Theme branding layer (colors, banner, and brand tokens) |
| `phytoquickadd` | Admin quick-add for products and categories with AI descriptions and botanical taxonomy import |
| `phytoerpconnector` | Bidirectional sync with ERPNext v15 (orders, customers, products, invoices) |
| `phytoseobooster` | AI-powered SEO automation — meta generation, schema markup, bulk alt-text audit |

### Plant Science

| Module | Description |
|--------|-------------|
| `phyto_grex_registry` | Scientific/horticultural taxonomy per product — genus, species, grex/hybrid, registration body, conservation status |
| `phyto_tc_batch_tracker` | TC propagation batch provenance — lineage chain, contamination log, inventory auto-decrement, QR labels, low-stock alerts |
| `phyto_growth_stage` | Tag products with growth stage (Deflasked / Juvenile / Semi-mature / Mature / Specimen); front badge + price block |
| `phyto_seasonal_availability` | Mark products as seasonal; out-of-season message + email notify-me form |
| `phyto_care_card` | Printable / downloadable PDF care card per product (light, water, humidity, temperature, media, dormancy, etc.) |
| `phyto_climate_zone` | 15 PCC-IN India climate zones; customers enter pincode to check plant suitability offline; monthly temp/humidity chart; frost/rain/humidity warnings |
| `phyto_acclimation_bundler` | Cart widget — suggests acclimation accessories when TC/young plants are in cart |
| `phyto_live_arrival` | Live Arrival Guarantee — customer opt-in, configurable fee, claim form with photo upload |

### Customer & Community

| Module | Description |
|--------|-------------|
| `phyto_growers_journal` | Customer grow journal — purchase-gated, photo uploads, timeline UI, admin moderation |
| `phyto_collection_widget` | Personal plant collection auto-populated from orders; public share link; AJAX notes |
| `phyto_source_badge` | Origin and certification badges on product listings (wild-collected, nursery-grown, TC, certified organic, etc.) |

### Operations & Compliance

| Module | Description |
|--------|-------------|
| `phyto_dispatch_logger` | Per-shipment dispatch evidence log — temp, humidity, packing method, gel/heat packs, photo |
| `phyto_phytosanitary` | Regulatory document management — PDF upload/download, MIME validation, expiry tracking, packing-slip hook |
| `phyto_tc_cost_calculator` | Back-office TC production cost calculator — substrate, overhead, labour, suggested retail at configurable margin |

### Commerce

| Module | Description |
|--------|-------------|
| `phyto_wholesale_portal` | B2B wholesale tier — application workflow, MOQ enforcement, tiered pricing, invoice-on-delivery |
| `phyto_subscription` | Recurring mystery-box and replenishment subscriptions |

---

## Requirements

- PrestaShop 8.0+
- PHP 8.1+
- MySQL / MariaDB
- cURL enabled (required by `phytoerpconnector`, `phytoseobooster`, `phytoquickadd`)

---

## Module Details

### phytoquickadd

4-tab admin tool under **Catalog → Phyto Quick Add**.

| Tab | Purpose |
|-----|---------|
| Add Product | Name, description, price, stock, category, image; AI description on demand |
| Add Category | Create categories/sub-categories with live AJAX tree view |
| Taxonomy Packs | Import botanical family hierarchies from GitHub (family → genus → species → cultivar) |
| Settings | Claude AI API key for description generation |

**AI setup:** Obtain a Claude API key from [console.anthropic.com](https://console.anthropic.com/settings/keys) and paste into the Settings tab.

---

### phytoerpconnector

| Sync type | Direction | Trigger |
|-----------|-----------|---------|
| Customers | PS → ERPNext | On customer account creation |
| Orders | PS → ERPNext | On order status update |
| Products | PS → ERPNext | On product add/update |
| Invoices | ERPNext → PS | Manual pull (last 30 days) |

Required ERPNext custom fields (add via Customize Form):

| DocType | Field | Type |
|---------|-------|------|
| Sales Order | `custom_ps_order_id` | Int |
| Sales Order | `custom_ps_reference` | Data |
| Sales Invoice | `custom_ps_order_id` | Int |

**Admin location:** Advanced Parameters → ERP Connector

---

### phytoseobooster

- Auto-generates meta title + description when a product is saved with empty meta fields
- Bulk-fill all products missing SEO meta in one click
- SEO audit: flags missing titles, thin descriptions (<50 chars), missing images
- Injects Product JSON-LD schema on all product pages automatically

---

### phyto_tc_batch_tracker

**DB tables:** `phyto_tc_batch`, `phyto_tc_batch_product`, `phyto_tc_contamination_log`

| Feature | Details |
|---------|---------|
| Batch CRUD | Auto-suggested codes (`YYYYMM-GENUS-001`), generations G0→G3+/Acclimated/Hardened |
| Mother batch lineage | `parent_id_batch` FK; `getLineageChain()` walks ancestors root→leaf; front product tab shows lineage breadcrumb |
| Contamination log | Type (Bacterial/Fungal/Viral/Pest/Unknown/Other), affected units, resolved flag; one-click "Mark Resolved" |
| Inventory auto-decrement | Deducts sold quantities from `units_remaining` on order ship; auto-transitions to Depleted at zero |
| Low-stock alert | Single email per batch per stock-cycle; configurable threshold (default 10) |
| Printable QR label | 88 mm label card with batch code, generation, dates, lineage chain, QR code |

**Migration:** `sql/migrate_v1_1.sql` provided for existing v1.0 installations.

---

### phyto_acclimation_bundler

| Config key | Description |
|------------|-------------|
| `PHYTO_ACCLIM_PRODUCTS` | Comma-separated product IDs forming the acclimation kit |
| `PHYTO_ACCLIM_STAGES` | Growth-stage IDs that trigger the widget (requires `phyto_growth_stage`) |
| `PHYTO_ACCLIM_TAGS` | Fallback product tags (e.g. `TC,deflasked`) when growth_stage is not installed |
| `PHYTO_ACCLIM_DISCOUNT` | Bundle discount % when customer adds all kit items at once |
| `PHYTO_ACCLIM_HEADLINE` | Widget headline text |
| `PHYTO_ACCLIM_MAX_SHOW` | Max number of kit products shown |

---

### phyto_phytosanitary

- PDF upload with MIME validation (5 MB limit), UUID filenames, `.htaccess`-protected upload dir
- Expiry date tracking with colour-coded badges (green/orange/red)
- `hookDisplayProductExtraContent` — public documents shown with download links on product page
- `hookDisplayPDFInvoice` — appends reference numbers to packing slip
- Physical files cascade-deleted on document or product delete

---

### phyto_wholesale_portal

**DB tables:** `phyto_wholesale_application`, `phyto_wholesale_product`, `phyto_wholesale_tier_pricing`

| Feature | Details |
|---------|---------|
| Application form | Business name, GST field, website, admin email notification |
| Approval | Manual or auto-approve (`PHYTO_WHOLESALE_REQUIRE_APPROVAL`) |
| MOQ | `hookActionCartUpdateQuantityBefore` enforces minimum order quantity |
| Tiered pricing | JSON array per product; shown as a table on product page (wholesale customers only) |
| Customer group | Approved customers added to a dedicated wholesale group |

---

### phyto_subscription

**DB tables:** `phyto_subscription_plan`, `phyto_subscription_customer`

**Admin:** `AdminPhytoSubscription` (plan management), `AdminPhytoSubscriberList` (subscriber overview)

**Front:** `/module/phyto_subscription/plans` (public listing), `/module/phyto_subscription/subscribe` (requires login)

---

### phyto_growers_journal

- Purchase gate — only customers who bought the product can post
- Rate-limit: spam check per customer
- Up to 3 photos per entry (JPEG/PNG, 2 MB each), UUID filenames
- Entries default to `approved=0`; admin list has one-click approval toggle
- Front: vertical timeline with type badges (Update / Milestone / Issue), photo thumbnails

---

### phyto_collection_widget

- Auto-populated via `hookDisplayOrderConfirmation`
- Per-item public/private toggle; share URL uses `md5(id_customer)` (no PII in URL)
- AJAX note saving, toggle public, remove — CSRF token + ownership checks on every request

---

### phyto_dispatch_logger

- Fields: carrier, tracking number, dispatch date, condition notes
- Admin product tab shows dispatch history across all orders for that product
- `hookDisplayOrderDetail` injects a dispatch timeline into customer order history

---

### phyto_source_badge

- Badge definitions: name, icon slug, colour, short description
- Per-product badge assignment (multiple badges per product)
- Shown on: product extra content tab, price block, product list cards, admin product tab

---

### phyto_climate_zone (v2)

Offline India climate zone checker — customers enter a 6-digit pincode to check plant suitability.

| Layer | File | Role |
|-------|------|------|
| Data generator | `data/generate_climate_data.py` | Python — produces `india_climate_zones.json` + `india_pin_prefix_zone_map.json` |
| Zone data | `data/india_climate_zones.json` | 15 PCC-IN zones with monthly avg temp, humidity, frost risk, monsoon months, example cities |
| PIN map | `data/india_pin_prefix_zone_map.json` | 3-digit prefix → PCC-IN code for 797 PIN ranges |
| Front controller | `controllers/front/check.php` | POST pincode → zone code, monthly data, suitability verdict, intolerance warnings |
| Front widget | `views/templates/hook/product_extra_content.tpl` | Pincode input; verdict banner + monthly bar chart |

**15 PCC-IN Zones:**

| Code | Label | Key Areas |
|------|-------|-----------|
| PCC-IN-01 | Humid Tropical Coast — South | Chennai, Vizag, Thiruvananthapuram |
| PCC-IN-02 | Humid Tropical — Kerala & Konkan | Kochi, Mangalore, Goa, Mumbai coast |
| PCC-IN-03 | Tropical Wet-Dry — Deccan Plateau North | Pune, Nashik, Bangalore |
| PCC-IN-04 | Tropical Dry — Telangana & Rayalaseema | Hyderabad, Vijayawada, Kurnool |
| PCC-IN-05 | Subtropical — Indo-Gangetic Plains West | Delhi, Agra, Jaipur, Chandigarh |
| PCC-IN-06 | Subtropical — Indo-Gangetic Plains East | Varanasi, Patna, Lucknow |
| PCC-IN-07 | Hot Arid — Rajasthan Desert | Jodhpur, Jaisalmer, Bikaner |
| PCC-IN-08 | Tropical Monsoon — Central India | Bhopal, Nagpur, Raipur |
| PCC-IN-09 | Humid Subtropical — West Bengal & Odisha | Kolkata, Bhubaneswar |
| PCC-IN-10 | Humid Subtropical — Northeast India | Guwahati, Shillong, Agartala |
| PCC-IN-11 | Highland Subtropical — Western Ghats | Ooty, Munnar, Coorg, Kodaikanal |
| PCC-IN-12 | Highland Temperate — Lower Himalayas | Shimla, Dehradun, Darjeeling |
| PCC-IN-13 | Alpine — Upper Himalayas | Srinagar, Leh, Manali |
| PCC-IN-14 | Island Tropical — Andaman & Nicobar | Port Blair |
| PCC-IN-15 | Island Tropical — Lakshadweep | Kavaratti |

```bash
# Regenerate data files after editing zone definitions:
cd modules/phyto_climate_zone/data
python3 generate_climate_data.py
```

---

## Taxonomy Packs

Botanical taxonomy data lives in `/taxonomy/` and is fetched live by `phytoquickadd` (1-hour cache).

```
taxonomy/
├── index.json              ← master manifest (categories)
├── carnivorous/            ← each category has its own index.json + pack files
├── aroids/
├── orchids/
├── succulents/
└── bromeliads/
```

| Category | Packs | Key genera |
|----------|-------|-----------|
| Carnivorous Plants | 8 | Nepenthes, Dionaea, Drosera, Sarracenia, Cephalotus, Utricularia, Pinguicula, Heliamphora |
| Succulents & Cacti | 4 | Echeveria, Haworthia, Aloe, Mammillaria, Crassula, Euphorbia |
| Aroids | 1 | Monstera, Philodendron, Anthurium, Alocasia |
| Orchids | 1 | Paphiopedilum, Dendrobium, Vanda, Coelogyne |
| Bromeliads | 1 | Tillandsia, Neoregelia, Vriesea, Aechmea |

**Adding a taxonomy pack:**
1. Create `taxonomy/{category}/{family_slug}.json` following the existing family format
2. Update `taxonomy/{category}/index.json` (add to `packs[]`)
3. Update `taxonomy/index.json` (bump `pack_count` for that category)
4. Open a PR

Cultivar codes: `PCR-{YEAR}-{GENUS_ABBR}-{SEQ}` (e.g. `PCR-2024-NEP-001`)

---

## Coding Conventions

- PHP 8.1, PrestaShop 8 conventions throughout
- All DB tables prefixed `phyto_`; `sql/install.sql` + `sql/uninstall.sql` per module
- User-visible strings: `$this->l('...')` / `{l s='...' mod='...'}`
- Admin controllers extend `ModuleAdminController`, use `HelperForm` + `HelperList`
- AJAX handlers: `ob_clean()` before every `echo json_encode()` to prevent PHP warning bleed
- Front output via Smarty templates — `$this->context->smarty->assign()`
- Assets: `registerStylesheet()` / `registerJavascript()`
- DB queries: `Db::getInstance()` with `pSQL()` — no raw PDO
- Bootstrap 3 for back-office UI

---

## Local Testing with Docker

```bash
# MySQL container
docker run -d --name mysql-ps \
  -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=prestashop \
  -e MYSQL_USER=ps -e MYSQL_PASSWORD=ps \
  mysql:8.0

# PrestaShop 8
docker run -d --name ps-test \
  -e PS_INSTALL_AUTO=1 -e PS_DOMAIN=localhost:8080 \
  -e DB_SERVER=mysql -e DB_NAME=prestashop \
  -e DB_USER=ps -e DB_PASSWD=ps \
  -e PS_FOLDER_ADMIN=admin-test \
  -p 8080:80 --link mysql-ps:mysql \
  prestashop/prestashop:8

# Copy a module into the container
docker cp modules/phyto_grex_registry ps-test:/var/www/html/modules/
# Then Admin → Modules → search → Install
```

### Per-module install checklist

| Module | Verify after install |
|--------|---------------------|
| `phyto_grex_registry` | Edit product → "Scientific Profile" tab; fill fields; save; front product page shows taxonomy card |
| `phyto_growth_stage` | Catalog → Growth Stages → add stage; assign to product; front shows stage badge |
| `phyto_seasonal_availability` | Edit product → "Seasonal" tab; block month; visit front — Add to Cart hidden, notify-me shown |
| `phyto_care_card` | Edit product → "Care Card" tab; fill fields; Preview PDF |
| `phyto_climate_zone` | Edit product → "Climate" tab; select zones; front: enter a pincode prefix → verdict shown |
| `phyto_live_arrival` | Configure LAG; add product to cart; checkout shows LAG toggle; order detail shows LAG panel |
| `phyto_tc_batch_tracker` | Catalog → TC Batches → create batch; link to product; front product shows batch provenance tab |
| `phyto_phytosanitary` | Edit product → upload PDF; set expiry; product page shows download link |
| `phyto_wholesale_portal` | Customers → Wholesale → approve application; log in as wholesale customer → tiered pricing shown |
| `phyto_subscription` | Catalog → Subscription Plans → create plan; visit `/module/phyto_subscription/plans` |
| `phytoquickadd` | Catalog → Phyto Quick Add → Taxonomy Packs tab → loads pack list from GitHub |

---

## Changelog

See [`docs/ACTIVITY_LOG.md`](docs/ACTIVITY_LOG.md) for the full timestamped session history.

---

## License

MIT — see [LICENSE](LICENSE)
