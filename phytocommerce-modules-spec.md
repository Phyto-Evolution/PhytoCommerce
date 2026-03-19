# PhytoCommerce — Carnivorous Plant PrestaShop Modules
## Claude Code Build Specification

**Target repo:** github.com/Phyto-Evolution (add as `modules/` subfolder or standalone repo)
**Platform:** PrestaShop 8.1.x
**PHP:** 8.1+
**No AI backend. No external API dependencies beyond optional postcode lookup (free, cacheable). All data is manually entered via PrestaShop back office.**

---

## Repo Structure Convention

Each module lives in `modules/<module_name>/` and follows standard PrestaShop module layout:

```
modules/
  phyto_grex_registry/
  phyto_tc_batch_tracker/
  phyto_growth_stage/
  phyto_seasonal_availability/
  phyto_care_card/
  phyto_climate_zone/
  phyto_acclimation_bundler/
  phyto_live_arrival/
  phyto_growers_journal/
  phyto_collection_widget/
  phyto_source_badge/
  phyto_phytosanitary/
  phyto_dispatch_logger/
  phyto_subscription/
  phyto_wholesale_portal/
  phyto_tc_cost_calculator/
```

**Every module must follow this internal layout:**
```
phyto_<name>/
  phyto_<name>.php          ← Main module class
  config.xml                ← Module metadata
  logo.png                  ← 32x32 icon (placeholder OK)
  controllers/
    admin/                  ← AdminController subclasses
    front/                  ← FrontController subclasses (if needed)
  views/
    templates/
      admin/                ← Smarty .tpl files for back office
      front/                ← Smarty .tpl files for front office
      hook/                 ← Hook-rendered templates
  sql/
    install.sql             ← CREATE TABLE statements
    uninstall.sql           ← DROP TABLE statements
  translations/             ← Empty dir, l() calls throughout
  README.md                 ← Module purpose + hook list
```

**Coding conventions across all modules:**
- Extend `Module` class, implement `install()` and `uninstall()` that call parent + register hooks + run SQL
- All DB table names prefixed with `PREFIX_phyto_`
- Use `$this->l('...')` for all user-visible strings
- Use PrestaShop's `Db::getInstance()->execute()` / `getRow()` / `executeS()` — no raw PDO
- Admin controllers extend `ModuleAdminController`, use `HelperForm` and `HelperList`
- All front-facing output via Smarty `.tpl` assigned with `$this->context->smarty->assign()`
- CSS/JS assets registered with `$this->context->controller->addCSS()` / `addJS()` — never inline
- Respect `_PS_MODE_DEV_` for error verbosity

---

## Module 1 — `phyto_grex_registry`

**Purpose:** Attach structured scientific/horticultural taxonomy metadata to any product. Displayed as a taxonomy card on the product page front end.

**Back office:**
- New tab injected into the product edit page (hook: `displayAdminProductsExtra`)
- Form fields per product:
  - Genus (text, e.g. "Nepenthes")
  - Species (text, e.g. "rajah")
  - Subspecies / variety (text, optional)
  - Cultivar name (text, optional, e.g. 'Red Hairy')
  - Grex name (text, optional — hybrid seedling population name)
  - ICPS registered? (yes/no toggle)
  - ICPS registration number (text, conditional on above)
  - Hybrid formula (text, e.g. "N. rajah × N. lowii")
  - Primary parentage — Mother (text)
  - Primary parentage — Father (text)
  - Natural habitat (text, e.g. "Borneo highland, 1800–2600 m")
  - Endemic region (text)
  - Conservation status (dropdown: Not Assessed / LC / NT / VU / EN / CR / EW / EX)
  - Taxonomic notes (textarea, freeform)
- Save/load per `id_product` in table `phyto_grex_registry`

**Front office:**
- Hook: `displayProductExtraContent` (PS8) or `displayProductAdditionalInfo`
- Renders a collapsible card titled "Scientific Profile"
- Shows all non-empty fields in a clean two-column definition list
- If ICPS registered: show a badge "ICPS Registered" with the number
- If hybrid formula present: render it in italics with × rendered as proper ×

**DB table:** `phyto_grex_registry`
```sql
id_grex INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
genus VARCHAR(100),
species VARCHAR(100),
subspecies VARCHAR(100),
cultivar VARCHAR(150),
grex_name VARCHAR(150),
hybrid_formula VARCHAR(255),
mother VARCHAR(150),
father VARCHAR(150),
icps_registered TINYINT(1) DEFAULT 0,
icps_number VARCHAR(50),
habitat TEXT,
endemic_region VARCHAR(200),
conservation_status VARCHAR(20),
notes TEXT,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 2 — `phyto_tc_batch_tracker`

**Purpose:** Link TC products to propagation batch records. Buyers see provenance; admin sees batch-grouped inventory.

**Back office:**
- Standalone admin menu page under Catalog → TC Batches (`AdminPhytoTcBatches`)
- Batch list view: batch ID, species, generation, date deflasked, units produced, units remaining, status
- Batch create/edit form:
  - Batch code (text, auto-suggested as YYYYMM-GENUS-SEQ, editable)
  - Species / clone name (text)
  - Generation (dropdown: G0-explant / G1 / G2 / G3+ / Acclimated / Hardened-outdoor)
  - Initiation date (date)
  - Deflask date (date)
  - Certification date — contamination-free verified (date)
  - Sterility protocol used (textarea: e.g. "MS 1/2 strength, autoclave 121°C 20min, laminar flow hood")
  - Units produced (int)
  - Units remaining (int, manually updated)
  - Batch status (dropdown: Active / Depleted / Quarantined / Archived)
  - Internal notes (textarea)
- Second tab on product edit page (hook: `displayAdminProductsExtra`)
  - Dropdown to link a product (or specific combination) to a batch record
  - One product can link to one batch at a time
  - Shows linked batch summary inline after selection

**Front office:**
- Hook: `displayProductExtraContent`
- Shows a "Batch Provenance" section if batch is linked:
  - Batch code (displayed as-is)
  - Generation label
  - Deflask date (human formatted)
  - Certification date
  - Sterility protocol (collapsible)
  - Status badge (Active = green, Quarantined = red, etc.)

**DB tables:**
```sql
-- phyto_tc_batch
id_batch INT AUTO_INCREMENT PRIMARY KEY,
batch_code VARCHAR(50) UNIQUE,
species_name VARCHAR(200),
generation ENUM('G0','G1','G2','G3+','Acclimated','Hardened'),
date_initiation DATE,
date_deflask DATE,
date_certified DATE,
sterility_protocol TEXT,
units_produced INT DEFAULT 0,
units_remaining INT DEFAULT 0,
batch_status ENUM('Active','Depleted','Quarantined','Archived') DEFAULT 'Active',
notes TEXT,
date_add DATETIME,
date_upd DATETIME

-- phyto_tc_batch_product
id_link INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
id_product_attribute INT DEFAULT 0,
id_batch INT NOT NULL
```

---

## Module 3 — `phyto_growth_stage`

**Purpose:** Replace or augment PrestaShop combination size labels with named growth stages, each carrying care-difficulty and time-to-maturity metadata.

**Back office:**
- Admin page: Catalog → Growth Stages (`AdminPhytoGrowthStages`)
- Global stage definitions (reusable across products):
  - Stage name (text: e.g. "Protocorm", "Deflasked", "Hardened", "Juvenile", "Mature Pitcher")
  - Stage code (slug, auto-generated)
  - Care difficulty (dropdown: Beginner / Intermediate / Advanced / Expert)
  - Estimated weeks to next stage (int)
  - Stage description (textarea)
  - Sort order (int)
- Per-product tab (hook: `displayAdminProductsExtra`):
  - Map each product combination (attribute set) to a global stage
  - Override weeks-to-maturity per product if needed

**Front office:**
- Hook: `displayProductExtraContent`
- Renders a "Growth Stage" card:
  - Stage name as heading
  - Difficulty badge (color-coded)
  - Weeks to maturity estimate
  - Stage description
  - A horizontal stage progression bar showing where this stage sits in the sequence (e.g. ●●○○ = stage 2 of 4)
- Hook: `displayProductPriceBlock` — inject stage badge near the Add to Cart button

**DB tables:**
```sql
-- phyto_growth_stage_def
id_stage INT AUTO_INCREMENT PRIMARY KEY,
stage_name VARCHAR(100),
stage_code VARCHAR(50) UNIQUE,
difficulty ENUM('Beginner','Intermediate','Advanced','Expert'),
weeks_to_next INT,
description TEXT,
sort_order INT DEFAULT 0

-- phyto_growth_stage_product
id_link INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
id_product_attribute INT DEFAULT 0,
id_stage INT NOT NULL,
weeks_override INT DEFAULT NULL
```

---

## Module 4 — `phyto_seasonal_availability`

**Purpose:** Mark products with dormancy/shipping windows; block purchase during incompatible months; show "notify me" when out of season.

**Back office:**
- Per-product tab (hook: `displayAdminProductsExtra`):
  - Shipping season: checkboxes Jan–Dec (months when shipping is allowed)
  - Dormancy months: checkboxes Jan–Dec (informational display)
  - Block purchase outside season? (yes/no)
  - Out-of-season message (text, e.g. "Ships October–March only")
  - Enable "Notify me when in season" email capture? (yes/no)
- Admin page: Catalog → Seasonal Notifications (`AdminPhytoSeasonalNotify`)
  - List of captured email + product + date
  - Bulk export as CSV
  - Mark as notified checkbox

**Front office:**
- Hook: `displayProductButtons` — if current month outside allowed months AND block=yes:
  - Hide/disable Add to Cart button
  - Show out-of-season message
  - If notify capture enabled: show simple email capture form (name + email) that POSTs to a FrontController (`PhytoSeasonalNotifyModuleFrontController`)
- Hook: `displayProductExtraContent` — show shipping season info as a month grid (12 cells, highlighted = ship OK)
- FrontController saves email to DB, sends confirmation email via PrestaShop's `Mail::Send()`

**DB tables:**
```sql
-- phyto_seasonal_product
id_seasonal INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL UNIQUE,
ship_months VARCHAR(50),      -- comma-separated: "10,11,12,1,2,3"
dormancy_months VARCHAR(50),
block_purchase TINYINT(1) DEFAULT 0,
out_of_season_msg VARCHAR(255),
enable_notify TINYINT(1) DEFAULT 1

-- phyto_seasonal_notify
id_notify INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
email VARCHAR(150),
name VARCHAR(100),
notified TINYINT(1) DEFAULT 0,
date_add DATETIME
```

---

## Module 5 — `phyto_care_card`

**Purpose:** Auto-generate a printable PDF care sheet per product, attached to order confirmation emails.

**Back office:**
- Per-product tab (hook: `displayAdminProductsExtra`):
  - Light requirement (dropdown: Full sun / Bright indirect / Partial shade / Low light)
  - Water type (dropdown: Distilled only / Rainwater / Low-TDS tap / Any)
  - Watering method (dropdown: Tray method / Top water / Mist only)
  - Humidity range (text: e.g. "60–90%")
  - Temperature range (text: e.g. "15–30°C day, 10–18°C night")
  - Soil/media (text: e.g. "1:1 peat:perlite, no fertiliser")
  - Feed protocol (textarea: e.g. "Small live insects once/month during growing season")
  - Dormancy instructions (textarea)
  - Potting tips (textarea)
  - Common problems (textarea)
  - Module-level config: Store logo path, store name, footer text (in module Configuration page)
- "Preview PDF" button in the product tab that opens the rendered PDF in a new tab

**PDF generation:**
- Use **TCPDF** (already bundled with PrestaShop at `vendor/tecnickcom/tcpdf`) — no new dependencies
- Template layout: A5 portrait, store logo top-right, product image top-left, product name + scientific name as heading, sections as labelled blocks
- Generated on-demand via a FrontController URL: `/module/phyto_care_card/download?id_product=X&token=Y`
- Token is `md5(id_product . _COOKIE_KEY_)` — simple anti-scrape, no login required

**Order email attachment:**
- Hook: `sendMailAlterTemplateVars` or `actionEmailAddAttachment` (PS8)
- Attach the generated PDF to the order confirmation email automatically if care card data exists for any product in the order
- Generate PDF to `/var/tmp/` on the fly, attach, delete after send

**DB table:**
```sql
-- phyto_care_card
id_care INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL UNIQUE,
light VARCHAR(50),
water_type VARCHAR(50),
water_method VARCHAR(50),
humidity VARCHAR(50),
temperature VARCHAR(100),
media TEXT,
feed TEXT,
dormancy TEXT,
potting TEXT,
problems TEXT,
date_upd DATETIME
```

---

## Module 6 — `phyto_climate_zone`

**Purpose:** Buyer enters their city/pincode; module shows which plants are suitable for their climate (outdoor vs indoor-only).

**Back office:**
- Per-product settings (hook: `displayAdminProductsExtra`):
  - Suitable for outdoor in climate (multi-select checkboxes):
    - Tropical humid (India coastal / South India)
    - Tropical dry (Deccan plateau)
    - Subtropical (North India plains)
    - Highland temperate (Nilgiris / Himalayas)
    - Any indoor (controlled environment)
  - Minimum temperature tolerance (°C, int)
  - Maximum temperature tolerance (°C, int)
  - Cannot tolerate (checkboxes): Hard frost / Direct rain / Low humidity / Alkaline water
  - Outdoor care notes (textarea: region-specific tips)
- Module Configuration page:
  - A manually maintained JSON textarea: pincode-prefix → climate-zone mapping
  - Format: `{ "600": "Tropical humid", "500": "Tropical humid", "302": "Subtropical", ... }`
  - Pre-populated with ~30 common Indian city prefixes on install
  - "Download default mapping" button exports current JSON

**Front office:**
- Hook: `displayProductExtraContent`
- Shows a "Climate Suitability" section with a small form: text input for pincode
- On submit (AJAX POST to FrontController):
  - Looks up pincode prefix in the JSON config
  - Compares detected zone against product's suitable zones
  - Returns: suitable / suitable-with-care / indoor-only / not-recommended
  - Response rendered inline with appropriate message and colour badge
- No external API calls — fully offline lookup

**DB table:**
```sql
-- phyto_climate_product
id_climate INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL UNIQUE,
suitable_zones TEXT,           -- JSON array of zone slugs
min_temp INT,
max_temp INT,
cannot_tolerate TEXT,          -- JSON array
outdoor_notes TEXT
```

---

## Module 7 — `phyto_acclimation_bundler`

**Purpose:** When a TC/deflasked plant is added to cart, auto-suggest acclimation accessories as a dismissable widget.

**Back office:**
- Module Configuration page:
  - Acclimation kit products: multi-select list of existing products (humidity dome, sphagnum, perlite, distilled water, etc.)
  - Trigger condition: which growth stages (from Module 3) should trigger the suggestion? (multi-select of stage IDs)
  - If Module 3 not installed: fallback — trigger by product tag (text field: enter comma-separated tags)
  - Bundle discount % (int, 0 = no discount, applies only if all items added)
  - Widget headline text (text field, default: "Your plant needs an acclimation kit")
  - Show max N suggestions (int, default 3)

**Front office:**
- Hook: `displayShoppingCartFooter`
- JavaScript checks if any cart product is linked to a trigger stage (data embedded in page via hook `displayHeader`)
- If triggered and kit items not already in cart: render a dismissable widget below cart summary
- Widget shows kit product thumbnails, names, prices, individual "Add" buttons, and one "Add all" button
- If bundle discount > 0: show "Add all and save X%" CTA
- Dismiss is session-based (JS sessionStorage flag)
- No AJAX product fetch — all kit product data serialised into a `<script>` block in `displayHeader` hook to avoid extra requests

**DB table:**
```sql
-- phyto_acclimation_config (single-row config, use module Configuration instead of custom table — store as PS Configuration keys)
-- No custom table needed; use Configuration::get/set with keys:
-- PHYTO_ACCLIM_PRODUCTS (comma-separated id_product)
-- PHYTO_ACCLIM_STAGES (comma-separated id_stage)
-- PHYTO_ACCLIM_TAGS (comma-separated)
-- PHYTO_ACCLIM_DISCOUNT (int)
-- PHYTO_ACCLIM_HEADLINE (string)
-- PHYTO_ACCLIM_MAX_SHOW (int)
```

---

## Module 8 — `phyto_live_arrival`

**Purpose:** Live Arrival Guarantee opt-in at checkout. Controls shipping window (Mon–Wed only), adds fee or shows free LAG threshold, generates LAG claim form.

**Back office:**
- Module Configuration page:
  - LAG fee (decimal, e.g. 99.00 — set 0 to make free)
  - Free LAG above cart total (decimal, e.g. 2000.00 — set 0 to disable threshold)
  - Allowed ship days (checkboxes: Mon Tue Wed Thu Fri Sat Sun)
  - Holiday blackout dates (textarea, one YYYY-MM-DD per line)
  - LAG claim window (int days, e.g. 48 — buyer must claim within X hours of delivery)
  - LAG terms text (textarea, displayed at checkout)
  - Claim form instructions (textarea, displayed on claim page)
  - Notify email for claims (email field — store gets notified on new claim)

**Front office — Checkout:**
- Hook: `displayPaymentTop` or `displayCheckoutSummaryTop`
- Show LAG opt-in toggle with fee display or "Free LAG on this order" if threshold met
- If today is not an allowed ship day: show next valid ship date inline ("Next ship date: Wednesday 22 Jan")
- LAG selection stored as extra cart data via `updateCart` hook, persisted to order extra field

**Front office — Order page:**
- Hook: `displayOrderDetail`
- If LAG was opted in: show "File a LAG Claim" button (visible within claim window)
- Button links to FrontController claim form: name, order number (pre-filled), delivery date, issue description, photo upload (PS file upload), submit
- On submit: saves claim to DB, sends notification email to store

**DB tables:**
```sql
-- phyto_lag_order
id_lag INT AUTO_INCREMENT PRIMARY KEY,
id_order INT NOT NULL UNIQUE,
lag_opted TINYINT(1) DEFAULT 0,
fee_charged DECIMAL(10,2) DEFAULT 0.00,
date_add DATETIME

-- phyto_lag_claim
id_claim INT AUTO_INCREMENT PRIMARY KEY,
id_order INT NOT NULL,
customer_name VARCHAR(150),
delivery_date DATE,
issue_description TEXT,
photo_filename VARCHAR(255),
claim_status ENUM('Received','Under Review','Approved','Rejected') DEFAULT 'Received',
store_notes TEXT,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 9 — `phyto_growers_journal`

**Purpose:** Living grow-log attached to each product. Store posts updates with photos; buyers can also post updates on purchased products (optional). Acts as social proof.

**Back office:**
- Admin page: Catalog → Grower's Journal (`AdminPhytoGrowersJournal`)
- List of all journal entries filterable by product
- Create/edit entry form:
  - Product (dropdown, searchable)
  - Entry date (date, default today)
  - Title (text, e.g. "First pitcher opened — July 2025")
  - Body (textarea with basic formatting support — allow `<b>`, `<i>`, `<br>`, `<ul>` only via PS's `purifyHTML`)
  - Photo upload (up to 3 images, stored in `/img/phyto_journal/`)
  - Entry type (dropdown: Store Update / Customer Post / Milestone)
  - Approved? (yes/no — customer posts start unapproved)
- Module Configuration: allow customer posts? (yes/no)

**Front office:**
- Hook: `displayProductExtraContent`
- Renders journal entries for the product as a chronological timeline
- Each entry: date pill, title, body, thumbnails (click to lightbox using native PS fancybox)
- If customer posts enabled: show "Share your grow story" link (requires login)
- FrontController for customer post submission: authenticated, basic spam check (max 1 post per customer per product per 7 days), saved as unapproved

**DB table:**
```sql
-- phyto_journal_entry
id_entry INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
id_customer INT DEFAULT 0,    -- 0 = store post
entry_date DATE,
title VARCHAR(255),
body TEXT,
photo1 VARCHAR(255),
photo2 VARCHAR(255),
photo3 VARCHAR(255),
entry_type ENUM('Store','Customer','Milestone') DEFAULT 'Store',
approved TINYINT(1) DEFAULT 1,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 10 — `phyto_collection_widget`

**Purpose:** Logged-in buyers can mark purchased plants in their collection, add notes, optionally make collection public.

**Back office:**
- Module Configuration: allow public collections? (yes/no)
- Admin page: Customers → Collections (`AdminPhytoCollections`)
  - View all collections by customer
  - No editing — read-only admin overview

**Front office:**
- Hook: `displayMyAccountBlock` — add "My Plant Collection" link in customer account sidebar
- Collection page (FrontController): lists customer's collection items
  - Product thumbnail, name, date acquired (from order), personal note (editable inline), toggle public/private per item
  - "Remove from collection" option
- Hook: `displayOrderConfirmation` — auto-add ordered products to customer's collection after order complete
- Hook: `displayProductExtraContent` — if customer owns this plant: show "In your collection" badge with link to their note
- Public collection page: `/module/phyto_collection_widget/view?customer=HASH` — shows approved public items only, no personal notes visible

**DB table:**
```sql
-- phyto_collection_item
id_item INT AUTO_INCREMENT PRIMARY KEY,
id_customer INT NOT NULL,
id_product INT NOT NULL,
id_order INT DEFAULT 0,
personal_note TEXT,
is_public TINYINT(1) DEFAULT 0,
date_acquired DATE,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 11 — `phyto_source_badge`

**Purpose:** Display sourcing origin badges on product cards and product pages. Filterable in catalog.

**Back office:**
- Admin page: Catalog → Source Badges (`AdminPhytoSourceBadge`)
  - Global badge definitions:
    - Badge label (text: "TC Lab", "Division", "Seed-grown", "Wild Rescue", "Import")
    - Badge slug (auto)
    - Badge colour (hex colour picker or dropdown: green/blue/amber/red/gray)
    - Description (text, shown on product page tooltip)
  - Pre-install 5 default badge types
- Per-product tab (hook: `displayAdminProductsExtra`):
  - Assign one or multiple source badges
  - If "Wild Rescue" assigned: show additional field "Permit/reference number" (text)
  - If "Import" assigned: show "Origin country" (text)

**Front office:**
- Hook: `displayProductPriceBlock` — show badge pills on product page below price
- Hook: `displayProductListItem` — show badge pills on category listing cards (small)
- Hook: `displayProductExtraContent` — expanded badge section with descriptions
- Category filter: hook `displayLeftColumn` — render checkbox filter by source badge; filter applied via JS `history.pushState` + AJAX product list reload using PS's native search/filter mechanism (append `source_badge[]=slug` to URL)

**DB tables:**
```sql
-- phyto_source_badge_def
id_badge INT AUTO_INCREMENT PRIMARY KEY,
badge_label VARCHAR(100),
badge_slug VARCHAR(50) UNIQUE,
badge_color VARCHAR(10),
description TEXT,
sort_order INT DEFAULT 0

-- phyto_source_badge_product
id_link INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL,
id_badge INT NOT NULL,
permit_ref VARCHAR(100),
origin_country VARCHAR(100)
```

---

## Module 12 — `phyto_phytosanitary`

**Purpose:** Attach inspection certificates and import permits to products. Auto-include in packing slip PDF.

**Back office:**
- Admin page: Catalog → Phytosanitary Docs (`AdminPhytoPhytosanitary`)
  - Document list: doc type, product, issue date, expiry, file download
  - Upload form:
    - Document type (dropdown: Phytosanitary Certificate / Import Permit / Quarantine Clearance / CITES Permit / State Movement Permit / Other)
    - Linked product (dropdown, optional — can be store-level)
    - Issuing authority (text)
    - Issue date (date)
    - Expiry date (date, optional)
    - Reference number (text)
    - File upload (PDF, max 5MB, stored in `/upload/phyto_phytosanitary/`)
  - Expiry alert: entries within 30 days of expiry shown in orange in the list

**Front office:**
- Hook: `displayProductExtraContent` — if documents linked to product: show "Regulatory Documents" section listing doc type, authority, reference number, issue date
  - Download link only if document is public (toggle in back office per document)
- Hook: `displayPDFInvoice` or packing slip override:
  - Append a section to packing slip: "Regulatory Compliance" — list all valid docs linked to any product in the order
  - Reference numbers listed as text (not the full PDF — just the reference for customs/transit)

**DB table:**
```sql
-- phyto_phytosanitary_doc
id_doc INT AUTO_INCREMENT PRIMARY KEY,
id_product INT DEFAULT 0,      -- 0 = store-level
doc_type VARCHAR(50),
issuing_authority VARCHAR(200),
reference_number VARCHAR(100),
issue_date DATE,
expiry_date DATE,
filename VARCHAR(255),
is_public TINYINT(1) DEFAULT 0,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 13 — `phyto_dispatch_logger`

**Purpose:** Staff log packing conditions per shipment. Buyers see dispatch conditions; store has evidence for LAG claims.

**Back office:**
- Admin page: Orders → Dispatch Log (`AdminPhytoDispatchLog`)
  - List view: order ID, customer name, dispatch date, temp, humidity, packing method, linked order link
  - Create log entry form (also accessible from Order detail page via hook `displayAdminOrderTabContent`):
    - Order ID (int, linked to PS order — validated)
    - Dispatch date (date, default today)
    - Ambient temperature at packing (°C, decimal)
    - Ambient humidity at packing (%, int)
    - Packaging method (dropdown: Bare-root newspaper / Bark media bag / Humidity box / Insulated box / Express pouch)
    - Gel pack / heat pack used? (checkboxes)
    - Estimated transit days (int)
    - Packing staff name (text)
    - Additional notes (textarea)
    - Photo upload (1 image, optional — photo of packed box, stored in `/img/phyto_dispatch/`)

**Front office:**
- Hook: `displayOrderDetail` — show "Dispatch Conditions" section if log entry exists for this order:
  - Date, temperature, humidity, packing method, transit estimate
  - Photo thumbnail if uploaded
  - "This information is provided to support Live Arrival Guarantee claims"

**DB table:**
```sql
-- phyto_dispatch_log
id_log INT AUTO_INCREMENT PRIMARY KEY,
id_order INT NOT NULL UNIQUE,
dispatch_date DATE,
temp_celsius DECIMAL(4,1),
humidity_pct INT,
packing_method VARCHAR(100),
gel_pack TINYINT(1) DEFAULT 0,
heat_pack TINYINT(1) DEFAULT 0,
transit_days INT,
staff_name VARCHAR(100),
notes TEXT,
photo_filename VARCHAR(255),
date_add DATETIME,
date_upd DATETIME
```

---

## Module 14 — `phyto_subscription`

**Purpose:** Recurring mystery box or scheduled supply replenishment. Integrates with Cashfree Subscriptions API for recurring billing.

**Back office:**
- Admin page: Catalog → Subscription Plans (`AdminPhytoSubscription`)
  - Plan list: name, type, frequency, price, active subscribers count, status
  - Plan create/edit form:
    - Plan name (text: e.g. "Monthly CP Mystery Box")
    - Plan type (dropdown: Mystery Box / Media Replenishment / Custom Selection)
    - Billing frequency (dropdown: Weekly / Monthly / Quarterly — maps to Cashfree `plan_interval_type`: WEEK / MONTH / MONTH with `plan_intervals: 3`)
    - Price per cycle (decimal — maps to Cashfree `plan_recurring_amount`)
    - Max cycles (int, 0 = unlimited — maps to Cashfree `plan_max_cycles`)
    - Description (textarea)
    - Included products hint (textarea — informational only for mystery box)
    - Cashfree Plan ID (text — enter manually after creating plan in Cashfree dashboard, or auto-create via API on save)
    - Active? (yes/no)
- Module Configuration page:
  - Cashfree Client ID (text)
  - Cashfree Client Secret (text, masked)
  - API version (text, default: `2023-08-01`)
  - Environment (dropdown: Sandbox / Production — switches base URL between `https://sandbox.cashfree.com` and `https://api.cashfree.com`)
  - Webhook secret (text, for signature validation)
- Admin page: Orders → Subscriptions (`AdminPhytoSubscriberList`)
  - Subscriber list: customer, plan, start date, next billing date, status
  - Manual cancel button — calls `POST /pg/subscriptions/{subscription_id}/manage` with `action: CANCEL`
  - Pause/resume — calls same endpoint with `action: PAUSE` / `action: ACTIVATE`

**Front office:**
- Subscription plan listing page (FrontController): shows active plans with subscribe CTA
- Subscribe flow:
  1. Customer selects plan, clicks Subscribe
  2. FrontController calls Cashfree Subscriptions API (`POST /pg/subscriptions`) via `cashfree/cashfree-pg-sdk-php` (installed via Composer in module)
     - Sets `subscription_id` as `PHYTO-{id_customer}-{id_plan}-{timestamp}`
     - Sets `plan_id`, `customer_details` (name, email, phone from PS customer)
     - Sets `return_url` to module FrontController callback URL
  3. Redirect customer to Cashfree-hosted mandate/payment page (`authorisation_details.payment_url` from API response)
  4. On return callback: verify subscription status via `GET /pg/subscriptions/{subscription_id}`, save to DB, send confirmation email
- Customer account section (hook `displayMyAccountBlock`): "My Subscriptions" page — list active subscriptions with cancel button
- Webhook endpoint FrontController (`/module/phyto_subscription/webhook`):
  - Validate `x-webhook-signature` header using HMAC-SHA256 with webhook secret
  - Handle event `SUBSCRIPTION_CHARGED` — create a PS order automatically for the charged cycle
  - Handle event `SUBSCRIPTION_CANCELLED` / `SUBSCRIPTION_STATUS_CHANGED` — update local status in DB

**Cashfree integration:**
- SDK: `cashfree/cashfree-pg-sdk-php` installed via Composer inside module directory
- Credentials stored in PS Configuration via `Configuration::get/set`, secret encrypted with `Tools::encrypt`
- All API calls use headers: `x-client-id`, `x-client-secret`, `x-api-version`
- Webhook signature validated as: `hash_hmac('sha256', $timestamp . $raw_body, $webhook_secret)` compared against `x-webhook-signature`
- No card or mandate data stored anywhere on your server

**DB tables:**
```sql
-- phyto_subscription_plan
id_plan INT AUTO_INCREMENT PRIMARY KEY,
plan_name VARCHAR(200),
plan_type ENUM('Mystery','Replenishment','Custom'),
frequency ENUM('weekly','monthly','quarterly'),
price DECIMAL(10,2),
max_cycles INT DEFAULT 0,
description TEXT,
cashfree_plan_id VARCHAR(100),
active TINYINT(1) DEFAULT 1,
date_add DATETIME

-- phyto_subscription_customer
id_sub INT AUTO_INCREMENT PRIMARY KEY,
id_customer INT NOT NULL,
id_plan INT NOT NULL,
cashfree_subscription_id VARCHAR(150),
status ENUM('created','active','paused','cancelled','completed') DEFAULT 'created',
start_date DATE,
next_billing_date DATE,
date_add DATETIME,
date_upd DATETIME
```

---

## Module 15 — `phyto_wholesale_portal`

**Purpose:** B2B tier with MOQ, tiered pricing, invoice-on-delivery, and order approval workflows.

**Back office:**
- Admin page: Customers → Wholesale Applications (`AdminPhytoWholesale`)
  - Application list: business name, applicant, date, status
  - View application detail, approve/reject with notes
  - Approved → customer automatically added to "Wholesale" customer group in PS
- Module Configuration:
  - Wholesale customer group ID (link to existing PS group or auto-create)
  - Require approval for new wholesale accounts? (yes/no)
  - Default payment term: Invoice on Delivery (yes/no toggle)
  - Invoice due days (int, default 30)
- Per-product tab (hook: `displayAdminProductsExtra`):
  - MOQ (minimum order quantity) for wholesale group (int, 0 = no MOQ)
  - Wholesale price tiers (up to 5 tiers): min_qty, price_per_unit — stored as JSON
  - Wholesale-only? (yes/no — hide from retail)

**Front office:**
- Wholesale application page (FrontController): application form — business name, GST number, address, phone, website, message
  - Saves to DB, sends notification email to store
  - If auto-approve off: shows "Application received, we'll contact you within 48 hours"
- Wholesale catalog (only visible to wholesale customer group):
  - Hook `displayProductPriceBlock`: show tiered pricing table instead of retail price
  - MOQ enforced via hook `actionCartUpdateQuantityBefore` — reject quantity below MOQ with message
  - Hook `displayPaymentTop`: show "Invoice on Delivery" as an available payment method (custom simple payment module integrated here or as a separate PS payment module)
- "Invoice on Delivery" payment:
  - Subclass `PaymentModule` within this module (or a sibling payment module file)
  - Places order with status "Awaiting Invoice Payment"
  - Generates a PS invoice and emails it to the wholesale customer

**DB tables:**
```sql
-- phyto_wholesale_application
id_app INT AUTO_INCREMENT PRIMARY KEY,
id_customer INT DEFAULT 0,
business_name VARCHAR(200),
gst_number VARCHAR(30),
address TEXT,
phone VARCHAR(30),
website VARCHAR(200),
message TEXT,
status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
admin_notes TEXT,
date_add DATETIME,
date_upd DATETIME

-- phyto_wholesale_product
id_ws INT AUTO_INCREMENT PRIMARY KEY,
id_product INT NOT NULL UNIQUE,
moq INT DEFAULT 0,
price_tiers TEXT,    -- JSON: [{"min_qty":10,"price":150},{"min_qty":50,"price":120}]
wholesale_only TINYINT(1) DEFAULT 0
```

---

## Module 16 — `phyto_tc_cost_calculator`

**Purpose:** Internal admin tool for pricing TC batches. Input substrate, autoclave, agar, labor, rejection rate → output suggested retail price with margin targets.

**Back office only — no front office output.**

- Admin page: Catalog → TC Cost Calculator (`AdminPhytoTcCostCalc`)
- Calculator form (single page, no DB persistence needed — all in session/JS):
  - **Substrate costs:**
    - MS salts (g used, price per g)
    - Agar (g used, price per g)
    - Sucrose (g used, price per kg)
    - Additives (up to 3 rows: name, qty, unit cost)
  - **Overhead per batch:**
    - Autoclave cycles (count, cost per cycle)
    - Electricity estimate (₹ flat per batch)
    - Laminar flow hood hours (hours, ₹/hr)
    - Glassware/disposables (₹ flat)
  - **Labor:**
    - Person-hours (decimal)
    - Labor rate (₹/hr)
  - **Batch outputs:**
    - Total explants initiated (int)
    - Expected rejection/contamination rate (% slider)
    - Expected sellable units (auto-calculated: initiated × (1 - rejection%))
  - **Pricing targets:**
    - Target gross margin % (slider 20–80%)
    - Packaging cost per unit (₹)
    - Shipping material cost per unit (₹)
  - **Results panel (live JS calculation, no server round-trip):**
    - Total batch cost (₹)
    - Cost per sellable unit (₹)
    - Suggested retail price at target margin (₹)
    - Break-even price (₹)
    - Profit per batch at suggested price (₹)
    - Batch profitability chart (simple bar: cost vs revenue) — rendered as inline HTML/CSS bar, no chart library

- "Save as Estimate" button: saves the current inputs + results to DB linked to a TC batch (if batch ID entered)
- Saved estimates list below the calculator

**DB table:**
```sql
-- phyto_tc_cost_estimate
id_estimate INT AUTO_INCREMENT PRIMARY KEY,
id_batch INT DEFAULT 0,
estimate_label VARCHAR(200),
inputs_json TEXT,     -- full form inputs as JSON blob
results_json TEXT,    -- calculated results as JSON blob
date_add DATETIME
```

---

## General Build Instructions for Claude Code

1. **Build modules in the listed order** — Modules 3 and 5 are dependencies for Modules 7 and 8 respectively; build them first.

2. **Install/uninstall safety:** Every `install()` must:
   - Call `parent::install()`
   - Call `$this->registerHook(...)` for every hook used
   - Run `install.sql` via `Db::getInstance()->execute()`
   - Return `false` and clean up on any failure
   - Every `uninstall()` must drop all module tables and deregister hooks

3. **No hard-coded shop ID** — always use `(int)$this->context->shop->id` for multi-shop compatibility

4. **Token/CSRF on all FrontController POST actions** — use `Tools::getToken(false)` and validate

5. **File uploads** — validate mime type (use `$_FILES` + `getimagesize()` for images; check extension for PDFs), set `chmod 644` after move

6. **CSS scoping** — all module CSS classes prefixed with `.phyto-<module-slug>-` to avoid conflicts

7. **No jQuery conflicts** — use `$(document).ready()` wrapped in `(function($){ ... }(jQuery))` for all module JS

8. **Test install/uninstall cycle** on a fresh PS 8.1.7 install before committing — the `install.sql` must be idempotent (`CREATE TABLE IF NOT EXISTS`)

9. **Each module README.md must list:**
   - Hooks registered
   - Configuration keys used
   - DB tables created
   - Any inter-module dependencies

10. **Commit structure per module:**
    ```
    feat(phyto_grex_registry): initial scaffold
    feat(phyto_grex_registry): back office form and DB
    feat(phyto_grex_registry): front office taxonomy card
    fix(phyto_grex_registry): uninstall SQL cleanup
    ```
