# PhytoCommerce

A PrestaShop 8 module suite for specialty plant e-commerce — designed around the operational needs of tissue-culture and carnivorous plant businesses. Covers everything from TC batch provenance and phytosanitary compliance to wholesale portals, recurring subscriptions, and customer grow journals.

---

## Module Suite Overview

```
PhytoCommerce/
├── modules/
│   │
│   ├── [FOUNDATION]
│   ├── phytocommercefooter/          ✅ Built
│   ├── phytoquickadd/                ✅ Built
│   ├── phytoerpconnector/            ✅ Built
│   ├── phytoseobooster/              ✅ Built
│   │
│   ├── [SPECIALTY — PLANT SCIENCE]
│   ├── phyto_grex_registry/          🚧 Under Construction
│   ├── phyto_tc_batch_tracker/       ✅ Built
│   ├── phyto_growth_stage/           🚧 Under Construction
│   ├── phyto_seasonal_availability/  🚧 Under Construction
│   ├── phyto_care_card/              🚧 Under Construction
│   ├── phyto_climate_zone/           🚧 Under Construction
│   ├── phyto_acclimation_bundler/    ✅ Built
│   ├── phyto_live_arrival/           🚧 Under Construction
│   │
│   ├── [SPECIALTY — CUSTOMER & COMMUNITY]
│   ├── phyto_growers_journal/        ✅ Built
│   ├── phyto_collection_widget/      ✅ Built
│   ├── phyto_source_badge/           ✅ Built
│   │
│   ├── [SPECIALTY — OPERATIONS & COMPLIANCE]
│   ├── phyto_dispatch_logger/        ✅ Built
│   ├── phyto_phytosanitary/          ✅ Built
│   ├── phyto_tc_cost_calculator/     ✅ Built
│   │
│   └── [SPECIALTY — COMMERCE]
│       ├── phyto_wholesale_portal/   ✅ Built
│       └── phyto_subscription/       ✅ Built
│
└── taxonomy/                         ✅ Built
    ├── carnivorous/   (8 packs)
    ├── succulents/    (4 packs)
    ├── aroids/        (1 pack)
    ├── orchids/       (1 pack)
    └── bromeliads/    (1 pack)
```

> **16 built · 6 under construction · 15 taxonomy packs**

---

## Module Index

### Foundation modules

| Module | Status | Description |
|--------|--------|-------------|
| `phytocommercefooter` | ✅ Complete | Branded footer replacement |
| `phytoquickadd` | ✅ Complete | Admin quick-add for products and categories with AI descriptions and botanical taxonomy import |
| `phytoerpconnector` | ✅ Complete | Bidirectional sync with ERPNext v15 (orders, customers, products, invoices) |
| `phytoseobooster` | ✅ Complete | AI-powered SEO automation — meta generation, schema markup, bulk audit |

### Specialty plant modules

| # | Module | Status | One-line description |
|---|--------|--------|----------------------|
| 1 | `phyto_grex_registry` | ⚠️ Views pending | Structured scientific/horticultural taxonomy per product (genus, species, grex, ICPS, conservation status) |
| 2 | `phyto_tc_batch_tracker` | ✅ Complete | TC propagation batch provenance, lineage chain, contamination log, inventory auto-decrement, QR labels, low-stock alerts |
| 3 | `phyto_growth_stage` | ⚠️ Views pending | Tag products with growth stage (Deflasked / Juvenile / Semi-mature / Mature / Specimen); front badge + price block |
| 4 | `phyto_seasonal_availability` | ⚠️ Views pending | Mark products as seasonal; out-of-season message + email notify-me form |
| 5 | `phyto_care_card` | ⚠️ Views pending | Printable / downloadable PDF care card per product |
| 6 | `phyto_climate_zone` | ⚠️ Views pending | Map products to USDA / RHS hardiness zones; front compatibility checker |
| 7 | `phyto_acclimation_bundler` | ✅ Complete | Cart widget: suggest acclimation kit accessories when TC/deflasked plants are in cart |
| 8 | `phyto_live_arrival` | ⚠️ Views pending | Live Arrival Guarantee — customer opt-in, fee collection, claim form, order-detail disclosure |
| 9 | `phyto_growers_journal` | ✅ Complete | Customer grow journal with photo uploads, timeline UI, and admin moderation |
| 10 | `phyto_collection_widget` | ✅ Complete | Personal plant collection — auto-populated from orders, public share link, AJAX note-keeping |
| 11 | `phyto_dispatch_logger` | ✅ Complete | Dispatch event log per order — carrier, tracking, condition notes; admin tab + order detail hook |
| 12 | `phyto_phytosanitary` | ✅ Complete | Regulatory document management (PDF upload/download), expiry tracking, packing-slip hook |
| 13 | `phyto_source_badge` | ✅ Complete | Origin and certification badges on product listings |
| 14 | `phyto_wholesale_portal` | ✅ Complete | B2B wholesale tier — application workflow, MOQ enforcement, tiered pricing, invoice-on-delivery |
| 15 | `phyto_subscription` | ✅ Complete | Recurring mystery-box and replenishment subscriptions via Cashfree |
| 16 | `phyto_tc_cost_calculator` | ✅ Complete | Back-office TC production cost calculator (per-batch media/chemical/labour/overhead) |

---

## Requirements

- PrestaShop 8.0+
- PHP 8.1+
- MySQL / MariaDB
- cURL enabled (required by `phytoerpconnector`, `phytoseobooster`, `phyto_subscription`)

---

## Installation

### Single module

```bash
cp -r modules/<module_name> /path/to/prestashop/modules/
rm -rf /path/to/prestashop/var/cache/*/smarty/compile/*
# Admin → Modules → search "<module_name>" → Install
```

### All specialty modules at once

```bash
for module in modules/phyto_*; do
    cp -r "$module" /path/to/prestashop/modules/
done
rm -rf /path/to/prestashop/var/cache/*/smarty/compile/*
```

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

Connects PrestaShop to ERPNext v15 via the ERPNext REST API.

| Sync type | Direction | Trigger |
|-----------|-----------|---------|
| Customers | PS → ERPNext | On customer account creation |
| Orders | PS → ERPNext | On order status update |
| Products | PS → ERPNext | On product add/update |
| Invoices | ERPNext → PS | Manual pull (last 30 days) |

**Required ERPNext custom fields** (add via Customize Form):

| DocType | Field | Type |
|---------|-------|------|
| Sales Order | `custom_ps_order_id` | Int |
| Sales Order | `custom_ps_reference` | Data |
| Sales Invoice | `custom_ps_order_id` | Int |

**Admin location:** Advanced Parameters → ERP Connector

---

### phytoseobooster

AI SEO automation under **SEO & URLs → Phyto SEO Booster**.

- Auto-generates meta title + description when a product is saved with empty meta fields
- Bulk-fill all products missing SEO meta in one click
- SEO audit: flags missing titles, thin descriptions (<50 chars), missing images
- Injects Product JSON-LD schema on all product pages automatically

---

### phyto_tc_batch_tracker

Full TC propagation lifecycle management.

**DB tables:** `phyto_tc_batch`, `phyto_tc_batch_product`, `phyto_tc_contamination_log`

| Feature | Details |
|---------|---------|
| Batch CRUD | Auto-suggested codes (`YYYYMM-GENUS-001`), generations G0→G3+/Acclimated/Hardened |
| Mother batch lineage | `parent_id_batch` FK; `getLineageChain()` walks ancestors root→leaf; front product tab shows lineage breadcrumb |
| Contamination log | Type (Bacterial/Fungal/Viral/Pest/Unknown/Other), affected units, resolved flag; inline panel on batch edit with one-click "Mark Resolved" |
| Inventory auto-decrement | Deducts sold quantities from `units_remaining` when order reaches configurable "Shipped" status; auto-transitions to Depleted at zero |
| Low-stock alert | Single email per batch per stock-cycle; configurable threshold (default 10) and recipient |
| Printable QR label | 88 mm label card with batch code, generation, dates, lineage chain, QR code; streams from admin list row action |

**Migration:** `sql/migrate_v1_1.sql` provided for existing v1.0 installations.

---

### phyto_acclimation_bundler

Auto-suggests acclimation accessories when TC/deflasked plants are added to cart.

**Configuration (Modules → Configure):**

| Key | Description |
|-----|-------------|
| `PHYTO_ACCLIM_PRODUCTS` | Comma-separated product IDs forming the acclimation kit |
| `PHYTO_ACCLIM_STAGES` | Growth-stage IDs that trigger the widget (requires `phyto_growth_stage`) |
| `PHYTO_ACCLIM_TAGS` | Fallback product tags (e.g. `TC,deflasked`) when growth_stage is not installed |
| `PHYTO_ACCLIM_DISCOUNT` | Bundle discount % when customer adds all kit items at once |
| `PHYTO_ACCLIM_HEADLINE` | Widget headline text |
| `PHYTO_ACCLIM_MAX_SHOW` | Max number of kit products shown |

---

### phyto_phytosanitary

Regulatory document management for phytosanitary compliance.

- PDF upload with MIME validation (5 MB limit), UUID filenames, `.htaccess`-protected upload dir
- Expiry date tracking with colour-coded badges (green/orange/red) in admin list
- `hookDisplayProductExtraContent` — public documents shown with download links on product page
- `hookDisplayPDFInvoice` — appends reference numbers to packing slip
- Physical files cascade-deleted on document or product delete

---

### phyto_wholesale_portal

B2B wholesale tier.

**DB tables:** `phyto_wholesale_application`, `phyto_wholesale_product`

| Feature | Details |
|---------|---------|
| Application form | GST field, website, business name, admin email notification |
| Approval | Manual or auto-approve (`PHYTO_WHOLESALE_REQUIRE_APPROVAL`) |
| MOQ | `hookActionCartUpdateQuantityBefore` enforces minimum order quantity |
| Tiered pricing | JSON array per product; shown as a table on product page (wholesale customers only) |
| Customer group | Approved customers added to a dedicated wholesale group |

---

### phyto_subscription

Recurring subscriptions via Cashfree.

**DB tables:** `phyto_subscription_plan`, `phyto_subscription_customer`

**Configuration keys:** `PHYTO_SUB_CF_CLIENT_ID`, `PHYTO_SUB_CF_CLIENT_SECRET`, `PHYTO_SUB_CF_API_VERSION`, `PHYTO_SUB_CF_ENV` (Sandbox/Production), `PHYTO_SUB_CF_WEBHOOK_SECRET`

**Admin:** `AdminPhytoSubscription` (plan management), `AdminPhytoSubscriberList` (subscriber overview under Orders)

**Front:** `/module/phyto_subscription/plans` (public listing), `/module/phyto_subscription/subscribe` (requires login)

---

### phyto_growers_journal

Customer grow journal with admin moderation.

- Purchase gate — only customers who have bought the product can post
- Spam check — rate-limits submission frequency per customer
- Up to 3 photos per entry (JPEG/PNG, 2 MB each), UUID filenames
- Entries default to `approved=0`; admin list has one-click approval toggle
- Front: vertical timeline with type badges (Update/Milestone/Issue), photo thumbnails

---

### phyto_collection_widget

Personal plant collection.

- Auto-populated via `hookDisplayOrderConfirmation`
- Per-item public/private toggle; share URL uses `md5(id_customer)` (no PII in URL)
- AJAX note saving, toggle public, remove — CSRF token + ownership checks on every request
- `/module/phyto_collection_widget/collection` (private), `/view` (public read-only)

---

### phyto_dispatch_logger

Dispatch event log per order.

- Fields: carrier, tracking number, dispatch date, condition notes
- Admin product tab shows dispatch history across all orders for that product
- `hookDisplayOrderDetail` injects a dispatch timeline into customer order history

---

### phyto_source_badge

Origin and certification badges.

- Badge definitions: name, icon slug, colour, short description
- Per-product badge assignment (many-to-one, multiple badges per product)
- Shown on: product extra content tab, price block, product list cards, admin product tab

---

## Taxonomy Packs

Botanical taxonomy data lives in `/taxonomy/` and is fetched live by `phytoquickadd` with a 1-hour cache.

```
taxonomy/
├── index.json              ← master manifest
├── carnivorous/
│   ├── index.json
│   ├── nepenthaceae.json
│   └── ...
├── aroids/
├── orchids/
├── succulents/
└── bromeliads/
```

**Current coverage:**

| Category | Packs | Key genera |
|----------|-------|-----------|
| Carnivorous Plants | 8 | Nepenthes, Dionaea, Drosera, Sarracenia, Cephalotus, Utricularia, Pinguicula, Heliamphora |
| Succulents & Cacti | 4 | Echeveria, Haworthia, Aloe, Mammillaria, Crassula, Euphorbia |
| Aroids | 1 | Monstera, Philodendron, Anthurium, Alocasia |
| Orchids | 1 | Paphiopedilum, Dendrobium, Vanda, Coelogyne |
| Bromeliads | 1 | Tillandsia, Neoregelia, Vriesea, Aechmea |

**Adding a taxonomy pack:**
1. Create `taxonomy/{category}/{family_slug}.json` following the existing family format
2. Update `taxonomy/{category}/index.json`
3. Update `taxonomy/index.json` (bump `pack_count`, add to `packs[]`)
4. Open a PR

Cultivar PCR codes follow: `PCR-{YEAR}-{GENUS_ABBR}-{SEQ}` (e.g. `PCR-2024-NEP-001`)

---

## Coding Conventions

- PHP 8.1, PrestaShop 8 conventions throughout
- All DB tables prefixed `phyto_`; install/uninstall SQL provided per module
- User-visible strings: `$this->l('...')` / `{l s='...' mod='...'}`
- Admin controllers extend `ModuleAdminController`, use `HelperForm` + `HelperList`
- Front output via Smarty templates with `$this->context->smarty->assign()`
- Assets: `registerStylesheet()` / `registerJavascript()` or `addCSS()` / `addJS()`
- DB queries: `Db::getInstance()` with `pSQL()` sanitisation — no raw PDO
- Bootstrap 3 for back-office UI
- AJAX endpoints: hidden admin tabs with `ajaxProcess*()` methods returning JSON

---

## Contributing

Pull requests welcome for:
- New taxonomy packs (follow the format in `/taxonomy/`)
- Translations (add to `translations/` per PrestaShop convention)
- Bug fixes and compatibility improvements

Please keep module scope focused — each module should do one thing well.

---

## Changelog

All times in IST (UTC +5:30).

---

### 22 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 15:57 | `11847af` | **phytoquickadd — Notes field + multi-category selection.** New Notes textarea (between Short Description and Full Description) with live `#hashtag` badge preview; hashtags saved as PS product tags on submit. Category selector upgraded to multi-select — Ctrl/⌘+click to pick multiple categories; first selected becomes the primary; selected categories shown as colour-coded badges below the list. |

---

### 20 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 06:51 | `61ee636` | **README — module suite tree diagram.** Added ASCII tree under new *Module Suite Overview* section grouping all 20 modules by functional area with ✅ Built / 🚧 Under Construction status. Summary line: 16 built · 6 under construction · 15 taxonomy packs. |
| 06:01 | `933b6ab` | **Merge** — feature branch `claude/phytocommerce-module-dev-HGpZM` merged into master. |
| 06:01 | `be4e381` | **README — full rewrite.** Complete module index (4 foundation + 16 specialty), detailed sections per built module (DB tables, config keys, hook list, feature tables). Removed all server paths, usernames, domain names, ERP instance URLs, and host-specific deployment scripts. |
| 05:53 | `9aab886` | **phyto_tc_batch_tracker v1.1 — 5 robustness features.** Mother batch lineage chain (`getLineageChain()`, root→leaf breadcrumb on front product tab); contamination incident log (type, affected units, resolved flag, inline panel with one-click resolve); inventory auto-decrement on order ship (configurable status, auto-transition to Depleted at zero); low-stock alert email (single alert per batch per cycle, configurable threshold); printable 88 mm QR label card streaming from admin list row action. Migration SQL provided for v1.0 installs. |
| 05:38 | `eead0e0` | **phyto_tc_batch_tracker v1.0 — complete.** Full TC propagation batch CRUD with auto-suggested batch codes, generation tracking G0→Hardened, admin JS, CSS, Smarty views, and per-module README. |
| 05:31 | `f5358cf` | **3 modules complete** — `phyto_wholesale_portal` (B2B application workflow, MOQ enforcement, tiered pricing JSON, invoice-on-delivery); `phyto_subscription` (Cashfree recurring — plan CRUD, subscribe flow, webhook handler); `phyto_tc_cost_calculator` (per-batch media/chemical/labour/overhead cost estimator, admin-only). |
| 05:28 | `eb1febb` | **4 modules complete** — `phyto_growers_journal` (grow log with photo upload, purchase gate, admin moderation, timeline UI); `phyto_collection_widget` (order-auto-populated collection, AJAX notes, public share URL); `phyto_dispatch_logger` (per-order dispatch log, admin product tab, buyer order detail hook); `phyto_source_badge` (badge definitions, per-product assignment, product list + price block hooks). |

---

### 19 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 25:33 | `6ec332b` | **Scrubbed private content from public push.** Removed ERP instance URL and server-specific references that Claude Code had included in the previous commit. |
| 25:30 | `dd1b432` | **Replaced ERP URL with placeholder** in phytoerpconnector docs/config. |
| 21:51 | `b429a70` | **phyto_phytosanitary — complete.** PDF upload with MIME validation, UUID filenames, `.htaccess`-protected upload dir, expiry colour-coded badges, `hookDisplayProductExtraContent` download links, `hookDisplayPDFInvoice` packing-slip append, cascade file delete. |
| 21:49 | `bfc9b4a` | **PhytoCommerce 16-module suite scaffold (WIP).** Initial commit of all 16 specialty module directories with main PHP class, config.xml, SQL install/uninstall, admin/front controller stubs, and Smarty template stubs. |
| 23:07 | `5854cf4` | **Module spec doc added** — `phytocommerce-modules-spec.md` added to repo as build reference for Claude Code. |
| 16:22 | `66da793` | **phytoerpconnector + phytoseobooster — complete.** ERP connector: bidirectional sync (customers, orders, products, invoices) with ERPNext v15 REST API, sync log table, dashboard + log + settings tabs. SEO booster: Claude Haiku meta generation, bulk fill, SEO audit scanner, Product JSON-LD schema injection. |

---

### 16 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 25:23 | `04874ad` | **phytoquickadd — live category reload fixed.** After adding a category via AJAX, both the product category select and parent select now reload without page refresh; tree view also updates live. |
| 24:49 | `799979a` | Auto-refresh dropdowns after category add and taxonomy import. |
| 24:33 | `2cf4733` | Fix AJAX JSON output buffering — `ob_start()` / `ob_clean()` guard prevents PHP warnings from corrupting JSON responses. |
| 24:29 | `9587e92` | Add missing `classes/` folder with `PhytoTaxonomy` class. |
| 24:17 | `85254ea` | **phytoquickadd v3** — taxonomy packs tab added; live GitHub fetch, category import, per-pack sync, import log panel. |
| 15:11 | `ea66d7a` | Fix category tree array handling. |
| 15:09 | `f29d4e0` | Rename `uploadImage` → `uploadProductImage` to avoid PrestaShop core method conflict. |
| 15:05 | `b846000` | Fix `uploadImage` visibility to `protected`. |
| 15:02 | `8fd3f89` | Add `getContent()` to trigger Configure button in module list. |
| 14:54 | `bd025e4` | **phytoquickadd v2** — full tab rewrite: Add Product, Add Category, Settings; AJAX category add with live tree; AI description toggle. |
| 13:56 | `ef5351d` | Add files via upload. |
| 13:45 | `61a9f22` | Remove duplicate/broken `phytoquickadd.php` from root. |
| 11:18 | `3e89d78` | Move AJAX handler to `init()` to intercept before page render. |
| 11:08 | `b090050` | Fix template variable checks with `isset`. |
| 11:02 | `fd84f91` | Fix controller variable assignment for quickadd module. |
| 11:00 | `4474197` | Fix Smarty `{literal}` tags wrapping inline JS in quickadd template. |

---

### 15 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 28:14 | `fe12487` | **phytoquickadd v1** — initial module: quick product add with Claude AI description generation (Haiku), basic category select, image upload. |
| 28:12 | `ac83070` | Add PhytoTaxonomy helper methods. |
| 28:12 | `4c98c85` | Restructure taxonomy data into clean folder hierarchy: `carnivorous/`, `aroids/`, `orchids/`, `succulents/`, `bromeliads/` with per-category index files. |
| 28:09 | `883b9f5` | Remove loose index files from repo root — moved to taxonomy folders. |

---

### 7 Mar 2026

| Time (IST) | Commit | Change |
|------------|--------|--------|
| 19:43 | `5c00072` | **phytocommercefooter** — first module: replaces default PS footer with Phyto Evolution branding. |
| 19:27 | `8a2a953` | **Initial commit** — repository created. |

---

## License

MIT — see [LICENSE](LICENSE)
