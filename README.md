# PhytoCommerce

A PrestaShop 8 module suite for specialty plant e-commerce — designed around the operational needs of tissue-culture producers, nurseries, and rare plant retailers. Covers TC batch provenance, phytosanitary compliance, wholesale portals, recurring subscriptions, scientific taxonomy, customer grow journals, image protection, and more.

> **Last updated:** 2026-03-27
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
│   ├── [COMMERCE]
│   ├── phyto_wholesale_portal/           ✅ Built
│   ├── phyto_subscription/               ✅ Built
│   │
│   └── [SECURITY]
│       └── phyto_image_sec/              ✅ Built  (v0.1 — products; v0.2 video planned)
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

## What Each Module Does (Plain English)

This section explains every module in simple terms — no technical jargon.

---

### phytocommerce_pack — The All-in-One Installer
Think of this as the "install everything" button. Instead of uploading and installing each module one by one, you upload just this single pack and it installs all 21 PhytoCommerce modules automatically in the correct order. It also gives you a dashboard showing which modules are installed and which aren't. Perfect for setting up a brand new store quickly.

---

### phytocommercefooter — Branded Footer
Replaces the default PrestaShop footer with a custom Phyto-branded one. Keeps your store looking consistent and professional without having to edit theme files. Simple to enable — just install and it takes over the footer automatically.

---

### phytoquickadd — Fast Product Entry
Adds a "Quick Add" page under your Catalog menu so you can create products much faster than the default PrestaShop product form. Type a plant name, generate a description using AI with one click, set price and stock, upload an image, and save — all from a single streamlined form. Also lets you import entire botanical family trees (genus → species → cultivar) in one go from pre-built taxonomy packs.

---

### phytoerpconnector — ERP Sync (ERPNext)
Keeps your PrestaShop store and your ERPNext accounting/inventory system in sync automatically. When a customer places an order on your store, it creates a matching Sales Order in ERPNext. New customer registrations create Customer records. Product changes push across. Invoices can be pulled back from ERPNext into PrestaShop. No more manually entering the same data in two places.

---

### phytoseobooster — SEO on Autopilot
Handles the boring but important SEO work automatically. When you add a product and forget to fill in the meta title and description, this module writes them for you using AI. It also adds structured data (JSON-LD schema) to every product page so Google understands what you're selling. Includes a bulk audit tool that shows you which products are missing SEO data so you can fix everything in one go.

---

### phyto_grex_registry — Scientific Taxonomy Per Product
Lets you attach proper scientific information to each product — genus, species, hybrid/grex name, the registration authority that named it, and its conservation status. This shows up as a neat "Scientific Profile" tab on the product page. Useful for rare plant buyers who want to know exactly what they're getting and where it sits in the botanical family tree.

---

### phyto_tc_batch_tracker — Tissue Culture Batch Provenance
Tracks every batch of tissue-culture plants from flask to sale. Each batch gets a code, a generation label (G0, G1, G2, etc.), and can reference a parent batch — so you always know the full lineage of any plant. Logs contamination events with type and severity. Automatically reduces available units as orders are fulfilled. Sends a low-stock alert email when a batch is running low. Can print QR-code labels for physical batch tubes.

---

### phyto_growth_stage — Stage Labels on Products
Lets you tag each product with its current growth stage — Deflasked, Juvenile, Semi-Mature, Mature, or Specimen. The stage shows as a coloured badge on the product listing and product page so buyers know exactly what size/age plant they're getting. Helps set realistic expectations and reduces "it's smaller than I thought" complaints.

---

### phyto_seasonal_availability — Seasonal Blocking
Some plants shouldn't be sold in certain months — too hot to ship in summer, dormant in winter. This module lets you mark which months a product is unavailable. When a customer visits during a blocked month, the "Add to Cart" button is hidden and a message explains why, with an option to enter their email for a notification when the product comes back in season.

---

### phyto_care_card — Downloadable Care Guides
Generates a printable PDF care card for each product covering everything a buyer needs to know: light requirements, watering frequency, humidity, temperature range, potting media, dormancy notes, and more. The care card is automatically attached to the order confirmation email so customers get it the moment they buy. Can also be downloaded directly from the product page.

---

### phyto_climate_zone — India Climate Suitability Checker
Lets customers type in their PIN code to instantly find out if a plant will survive in their part of India. The module maps all Indian PIN prefixes to 15 climate zones and checks the plant's tagged zones against where the customer lives. Shows a monthly temperature and humidity chart, highlights frost risk, monsoon months, and gives a clear "Suitable / Not Recommended" verdict — all without needing an internet API call (data is stored locally).

---

### phyto_acclimation_bundler — Acclimation Kit Suggestions
When a customer adds a tissue-culture or young plant to their cart, this module pops up a widget suggesting the acclimation accessories they'll need (humidity domes, rooting powder, speciality substrate, etc.). You configure which products make up the kit and which plant types trigger the suggestion. Can offer a small bundle discount if the customer adds all kit items at once.

---

### phyto_live_arrival — Live Arrival Guarantee
Offers customers an opt-in "Live Arrival Guarantee" during checkout — for a small configurable fee, they're covered if their plant arrives dead or severely damaged. The module tracks which orders have LAG coverage and provides a claim form (with photo upload) that feeds directly into your admin panel. Configurable claim window, shipping carrier rules, and blackout dates for extreme weather.

---

### phyto_growers_journal — Customer Grow Diaries
Lets customers who bought a plant write ongoing journal entries about how it's growing — with photos, notes, and milestone markers. Only buyers of that specific product can post (purchase-gated), which keeps it authentic. Entries go through admin approval before going public. Shows up as a "Growers Journal" tab on the product page so potential buyers can see real growth results from other customers.

---

### phyto_collection_widget — Personal Plant Collections
Automatically builds each customer a personal "My Collection" page populated from their order history. Each plant in the collection can be toggled public or private, and customers can add personal care notes to each one. A shareable link lets them show off their collection to friends without exposing their account details. Think of it as a mini plant journal/portfolio tied to their purchases.

---

### phyto_source_badge — Origin & Certification Badges
Lets you create and assign badges to products that tell buyers where the plant came from and how it was produced — examples: "Tissue Culture", "Wild Collected", "Nursery Grown", "Certified Organic", "Conservation Propagation". Badges appear on product listing cards and the product page. Helps buyers make informed choices and builds trust around your sourcing practices.

---

### phyto_dispatch_logger — Shipment Evidence Log
Every time you dispatch an order, this module lets you log the shipping conditions: temperature at packing, humidity, packing method used, whether gel packs or heat packs were included, and optional photos of the packed box. This creates a tamper-proof timestamped record per shipment. Useful for resolving disputes — if a customer claims damage, you have documented proof of the condition the plants left in.

---

### phyto_phytosanitary — Regulatory Document Management
Manages phytosanitary certificates, import permits, and other regulatory PDFs per product. You upload the document, set an expiry date, and the module tracks validity with colour-coded badges (green = valid, orange = expiring soon, red = expired). Documents can be made public so buyers can download them from the product page. References are also automatically appended to packing slips.

---

### phyto_tc_cost_calculator — Production Cost Tool
A back-office calculator for working out the true cost of producing tissue-culture plants. Enter substrate cost, electricity, labour hours, contamination losses, overhead allocation, and desired margin — the module calculates your break-even price and suggested retail price. Helps you price TC products accurately instead of guessing. Admin-only, never visible to customers.

---

### phyto_wholesale_portal — B2B Wholesale Tier
Adds a full wholesale layer to your store. Businesses can apply for a wholesale account by filling in a form with their business name, GST number, and website. You approve or reject applications. Approved customers get access to wholesale-only pricing, minimum order quantity (MOQ) rules are enforced in the cart, and tiered pricing tables show how the price drops with volume. Approved customers can also pay by invoice on delivery.

---

### phyto_subscription — Recurring Subscriptions
Lets customers subscribe to regular deliveries — mystery plant boxes, monthly replenishment orders, or curated collections. You create subscription plans with name, price, frequency, and description. Customers browse the plans page, subscribe (requires login), and recurring payments are handled via Cashfree. Manage all subscribers and their status from a dedicated admin panel.

---

### phyto_image_sec — Image Protection (v0.1)
Protects your product photography from being copied or stolen. When installed, it automatically stamps your shop logo as a watermark onto every product image — on upload and when thumbnails are regenerated. You control the watermark position (corner, centre, or tiled), opacity, and size. On the front end, right-clicking images is disabled, dragging images to the desktop is blocked, and Ctrl+S is intercepted — all without breaking the image zoom or lightbox. A batch processor lets you watermark your entire existing catalogue in one click. v0.2 will extend this protection to video assets.

---

## Installation

### Option A — 1-click pack (recommended)

Upload `phytocommerce_pack` to PrestaShop and click **Install**. All 21 modules are installed automatically.

```bash
# Build the standalone zip (if not deploying from full repo checkout)
cd modules
cp -r phyto_* phytocommercefooter phytoquickadd phytoerpconnector phytoseobooster \
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

### Security

| Module | Description |
|--------|-------------|
| `phyto_image_sec` | Watermarks all product images with your shop logo; JS blocks right-click, drag-to-save, and Ctrl+S on product/category pages. Batch processor for existing catalogue. v0.1 — product images. |

---

## Requirements

- PrestaShop 8.0+
- PHP 8.1+ with GD extension (required by `phyto_image_sec`)
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

### phyto_image_sec (v0.1)

**Config keys:**

| Key | Default | Description |
|-----|---------|-------------|
| `PHYTO_IMGSEC_WATERMARK_ENABLED` | 1 | Enable/disable watermarking on image upload |
| `PHYTO_IMGSEC_POSITION` | `bottom-right` | `center` / `bottom-right` / `bottom-left` / `tiled` |
| `PHYTO_IMGSEC_OPACITY` | 60 | Watermark opacity 0–100 |
| `PHYTO_IMGSEC_SIZE_PCT` | 25 | Watermark width as % of image width (5–75) |
| `PHYTO_IMGSEC_PROTECT_ENABLED` | 1 | Enable/disable JS front-office protection |

**Watermark engine:** `classes/PhytoImageWatermarker.php` — pure GD, no external dependencies. Handles JPEG, PNG, GIF, WebP. Correct per-pixel alpha compositing for PNG logos.

**Batch processor:** Admin → Module Config → "Start Batch Watermark" — processes all product images in chunks of 20 via AJAX with a live progress bar.

**Hooks used:** `actionWatermark` (fires after any product image is generated/regenerated, including via `phytoquickadd`), `displayHeader` (JS protection), `displayBackOfficeHeader` (admin assets).

**Note:** Disable PrestaShop's built-in Watermark module before using this one to avoid double watermarks.

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
| `phyto_image_sec` | Upload a product image → check it gets watermarked; visit product page → right-click on image blocked |

---

## Changelog

See [`docs/ACTIVITY_LOG.md`](docs/ACTIVITY_LOG.md) for the full timestamped session history.

---

## License

MIT — see [LICENSE](LICENSE)
