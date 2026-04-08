# Differential Features Matrix — Stripped from 17 Open Source Ecommerce Platforms

> **Purpose:** Every feature below is something that MOST ecommerce platforms do NOT have, or do differently/worse. These are the differentiators we absorb into PhytoCommerce Stack to make it the most feature-complete open source ecommerce platform.

---

## How to Read This Document

- **Common features** (basic CRUD, simple cart, basic checkout, basic auth, basic orders) are excluded — every platform has those
- Each feature is tagged with its **source platform** and **our adoption priority**
- Priority: **P0** = must have (architectural foundation), **P1** = high value, **P2** = strong differentiator, **P3** = nice to have

---

## 1. ARCHITECTURAL PATTERNS

### 1.1 Workflow Engine with Saga Compensation (P0)
**Source:** Medusa v2
**What:** Multi-step business operations defined as composable workflows. Each step has a compensation (rollback) function. If step 3 of 5 fails, steps 2 and 1 are automatically compensated in reverse order. Supports long-running workflows with persistent state, pause/resume via `waitForEvent`, conditional steps via `when()`, and parallel execution via `parallelize()`.
**Why we need it:** Every complex operation (checkout → payment → inventory → order → email) needs atomic guarantees. Without this, we get partial orders, double charges, and phantom inventory.
**Adoption:** Build a lightweight workflow engine in `lib/workflows/` using BullMQ for persistence and async steps. Not as heavy as Temporal, but gives us compensation and idempotency.

### 1.2 Event Bus with Typed Events (P0)
**Source:** Medusa, Spree 5.3, EverShop
**What:** Every meaningful action fires a typed domain event. Built-in handlers process core logic. External systems connect via webhooks. Events are the foundation for all side effects (email, analytics, inventory, loyalty).
**Adoption:** Already in our spec (`lib/events/`). Strengthen with typed event payloads and handler registration.

### 1.3 Stateful Server-Side Checkout (P0)
**Source:** Saleor
**What:** Checkout is a persistent database entity, not client-side state. Survives across sessions and devices. Server recalculates prices/taxes/discounts on every mutation (prevents client-side price manipulation). Enables abandoned checkout tracking and stock reservation.
**Data model:** `Checkout` table with `token`, `userId`, `sessionId`, `shippingAddress`, `billingAddress`, `shippingMethodId`, `voucherCode`, `metadata`, `lastChange`.
**Adoption:** Replace our simple Cart model with a `Checkout` entity that evolves through states. Cart becomes the early phase of checkout.

### 1.4 Idempotency Keys with Recovery Points (P1)
**Source:** Medusa
**What:** Critical mutations accept an `Idempotency-Key` header. Server stores the key + response + recovery point (which step was reached in a multi-step operation). Retry with same key = cached response. Recovery point means if payment succeeded but order creation failed, retry skips payment and retries only order creation.
**Adoption:** Implement on checkout completion, payment capture, and refund endpoints.

### 1.5 Adjustments System (P1)
**Source:** Spree, Solidus
**What:** Every price modification (promotion, tax, shipping surcharge) is modeled as an `Adjustment` record attached to an adjustable entity (Order, LineItem, Shipment). Adjustments have an `open`/`closed` state — open adjustments recalculate on any order change, closed are locked.
**Data model:** `Adjustment` with `adjustableType`, `adjustableId`, `sourceType`, `sourceId`, `amount`, `label`, `mandatory`, `state` (open/closed), `eligible`.
**Why we need it:** Without this, calculating "why does this order total $X?" requires reverse-engineering. Adjustments give a transparent audit trail of every price modification.
**Adoption:** Add `OrderAdjustment` model linked to orders/line items. All discounts, taxes, and shipping surcharges create adjustment records.

---

## 2. CATALOG & PRODUCT SYSTEM

### 2.1 Dynamic Product Types with Configurable Attributes (P0)
**Source:** Saleor, Sylius
**What:** Products don't have fixed fields. `ProductType` defines which attributes a product has. Attribute types include: DROPDOWN, MULTISELECT, FILE, REFERENCE (to other entities), NUMERIC (with units like kg/cm), RICH_TEXT, PLAIN_TEXT, BOOLEAN, DATE, DATETIME, SWATCH (color with hex code or image).
**Why we need it:** Plants have wildly different attributes — orchids need light requirements, cacti need soil mix, tissue culture needs generation info. A fixed schema can't serve all plant types.
**Adoption:** Add `ProductType` and `Attribute` models. Products inherit attribute schema from their type. Store values in `ProductAttributeValue` junction table.

### 2.2 Options vs Attributes Separation (P1)
**Source:** Sylius
**What:** Clear separation — **Options** create variants (Size, Pot Size, Age), **Attributes** describe products (Brand, Origin, Light Requirement). Options affect price/stock/SKU. Attributes are for filtering/display only.
**Adoption:** Split our current flat model. `ProductOption` + `ProductOptionValue` for variant-creating dimensions. `ProductAttribute` + `ProductAttributeValue` (EAV) for descriptive data.

### 2.3 Collections via Pluggable Filter Rules (P1)
**Source:** Vendure
**What:** Collections are NOT static product lists. They're defined by filter rules evaluated against the catalog. Built-in filters: facet-value match, variant name pattern, manual product/variant selection. Custom filters can use any SQL-expressible condition ("all products under ₹500", "all products added this month").
**Adoption:** Add `Collection` model with `filters` JSON column. Build a `CollectionFilter` interface. Auto-populate collections on product create/update.

### 2.4 Config-Time Custom Fields on Any Entity (P2)
**Source:** Vendure
**What:** Any of 30+ entities can be extended with additional fields via config (not migration). Vendure auto-generates: DB columns, GraphQL schema (input + output types), Admin UI form controls. Supports types: string, int, float, boolean, datetime, text, relation, struct (typed JSON sub-object).
**Why we need it:** Tenants will want custom fields on products, orders, customers without touching code.
**Adoption:** Implement via JSON `customFields` column on major entities + a `TenantCustomFieldDefinition` table that defines per-tenant field schemas. Admin UI dynamically renders form controls from definitions.

### 2.5 Facets as First-Class Entities (P2)
**Source:** Vendure
**What:** Facets are decoupled from product attributes. A `Facet` is a named grouping, `FacetValue` is a value within it. FacetValues attach to variants via many-to-many. Facets drive search filters, collection rules, AND promotion conditions simultaneously. Can be public or private (admin-only).
**Adoption:** Add `Facet` and `FacetValue` models. Index in Meilisearch for faceted search. Use in collection filters and promotion conditions.

---

## 3. PRICING ENGINE

### 3.1 Rule-Based Price Lists (P1)
**Source:** Medusa, Spree 5.3
**What:** Named collections of price overrides with conditions: customer group, region, currency, quantity ranges, date ranges. Price calculation evaluates all applicable price lists and selects the best price. Override-type replaces default; sale-type shows both original and sale price.
**Data model:** `PriceList` → `PriceListRule[]` (conditions) → `Price[]` (actual prices with rules like region, currency, min quantity).
**Adoption:** Add `PriceList`, `PriceListRule`, and `PriceOverride` models. Price resolution: default price → check applicable price lists → select best match.

### 3.2 Channel-Specific Pricing (P2)
**Source:** Saleor, Vendure
**What:** Per-channel (per-tenant-storefront) pricing with different currencies. A product can cost $10 on channel-US and €9 on channel-EU. Visibility, publication status, and pricing are all per-channel.
**Adoption:** For multi-tenant, this maps to per-tenant pricing naturally. For tenants running B2B+B2C, the B2B "channel" can have wholesale prices while B2C has retail.

### 3.3 Exchange Rate Management (P2)
**Source:** Sylius
**What:** Built-in exchange rate records between currency pairs. `CurrencyConverter` service handles display conversion. Orders settle in base currency; other currencies are display-only approximations.
**Adoption:** Add `ExchangeRate` model. Tenants set base currency + display currencies. Product pages show converted prices.

---

## 4. PROMOTIONS & DISCOUNTS

### 4.1 Dual Promotion System: Catalog + Cart (P1)
**Source:** Sylius
**What:** Two separate engines. **Catalog promotions** modify product prices BEFORE the cart (async, processed by background workers, physically update price records). **Cart promotions** apply at checkout time with rule evaluation. A toggle controls interaction: cart promo can apply from original price OR from already-discounted catalog price.
**Why we need it:** "20% off all Orchids" should show on the product listing page (catalog promo), while "₹200 off orders over ₹2000" applies at cart (cart promo). Different timing, different UX.
**Adoption:** Implement both. Catalog promos run as BullMQ jobs that update `salePrice` on products. Cart promos evaluate at checkout via promotion engine.

### 4.2 Promotion Conditions + Actions with Typed State Passing (P1)
**Source:** Vendure, Solidus
**What:** Promotions composed of `Condition` predicates and `Action` discount calculators. Conditions can pass typed state to actions (e.g., condition "customer spent > ₹5000" passes exact spend amount, action uses it for tiered discount). Actions include: order percentage, order fixed, line item discount, free shipping, buy-X-get-Y.
**Adoption:** Build `PromotionCondition` and `PromotionAction` as pluggable interfaces. Register built-in conditions (min spend, min quantity, customer group, product/category match, nth order, first order).

### 4.3 Promotion Lanes for Stacking Control (P2)
**Source:** Solidus
**What:** Promotions assigned to lanes (pre, default, post, or custom). Within a lane, only the best promotion applies. Across lanes, promotions stack sequentially. Solves "which discount wins?" elegantly.
**Adoption:** Add `lane` field to `Coupon`/`AutoDiscount`. Evaluate lane-by-lane, best-per-lane, stack across lanes.

### 4.4 Bulk Voucher Code Generation (P2)
**Source:** Saleor
**What:** One voucher definition can have thousands of unique codes (for marketing campaigns). Single `Voucher` with many `VoucherCode` records. Staff-only vouchers for internal discounts.
**Adoption:** Add `CouponCode` model (many-to-one with `Coupon`). Bulk generation endpoint for campaigns.

---

## 5. INVENTORY & WAREHOUSING

### 5.1 Multi-Warehouse with Allocation Strategies (P1)
**Source:** Saleor
**What:** Multiple warehouses per tenant. Configurable allocation strategy per channel:
- `PRIORITIZE_SORTING_ORDER`: deplete warehouses in admin-defined priority
- `PRIORITIZE_HIGH_STOCK`: allocate from warehouse with most stock (load balancing)
Split fulfillment: single order fulfilled from multiple warehouses.
**Adoption:** Add `Warehouse`, `StockLevel` (per warehouse×variant), `Allocation` (per order line). Allocation strategy runs on order confirmation.

### 5.2 Inventory Reservation During Checkout (P1)
**Source:** Medusa, Saleor
**What:** When checkout begins, stock is temporarily reserved (`ReservationItem` with `expiresAt`). Available = stocked - reserved. If checkout abandoned, reservation expires and stock releases.
**Adoption:** Add `StockReservation` model. Create on checkout initiation, release on expiry or order cancellation, convert to allocation on payment.

### 5.3 Four-Operation Inventory Lifecycle (P1)
**Source:** Sylius
**What:** Four explicit operations: **hold** (checkout: onHold += qty), **sell** (payment: onHold -= qty, onHand -= qty), **release** (cancel before payment: onHold -= qty), **giveBack** (cancel after payment: onHand += qty).
**Adoption:** Implement as `InventoryOperator` service with these four methods. Each operation creates a `StockMovement` audit record.

### 5.4 Stock Transfers Between Locations (P2)
**Source:** Spree
**What:** Move inventory between warehouses. Also supports "Receive Stock" mode (no source — receiving from external supplier). `StockMovement` records for audit.
**Adoption:** Add `StockTransfer` model. Admin dashboard UI for warehouse-to-warehouse transfers.

### 5.5 Click-and-Collect (P3)
**Source:** Saleor
**What:** Warehouses flagged for local pickup. Stock availability shown per pickup location. Customer selects pickup point at checkout.
**Adoption:** Add `clickAndCollect` flag to `Warehouse`. Show nearby pickup points at checkout.

---

## 6. ORDERS & FULFILLMENT

### 6.1 Order Editing with Versioning (P1)
**Source:** Medusa, Vendure
**What:** After placement, admin creates an `OrderChange` proposing modifications (add/remove items, change quantities). Order has a `version` field that increments with each confirmed change. If price increased, customer pays difference. If decreased, refund issued. State machine enforces: order enters `Modifying` state, can't leave until payment delta resolved.
**Adoption:** Add `OrderChange` and `OrderChangeAction` models. Workflow: create change → calculate delta → collect/refund → confirm → increment version.

### 6.2 Three-Way RMA: Returns + Swaps + Claims (P1)
**Source:** Medusa
**What:** Three distinct flows: **Return** (items back, refund out), **Swap** (items back, different items out, payment delta calculated), **Claim** (merchant acknowledges defect, sends replacement with or without requiring return).
**Data model:** `Return`, `ReturnItem`, `ReturnReason`. Swaps and claims modeled as `OrderChange` with specific types. Each creates fulfillment records for return shipment.
**Adoption:** Add `Return`, `ReturnItem`, `ReturnReason` models. Swap = Return + new fulfillment + payment delta. Claim = new fulfillment (optional return).

### 6.3 Carton Model: Planned vs Actual Shipping (P2)
**Source:** Solidus
**What:** `Shipment` = planned shipping (created at checkout). `Carton` = actual physical package (created at fulfillment). They can differ — 3PL may pack differently than planned. A single Carton can contain items from multiple orders. Enables accurate tracking of what actually shipped vs what was planned.
**Adoption:** Add `Carton` model separate from shipment tracking. Link to `InventoryUnit` records. Generate packing manifests from cartons.

### 6.4 Order Events Timeline (P1)
**Source:** Saleor
**What:** Every order action recorded as an immutable `OrderEvent` — 30+ event types (placed, paid, fulfilled, shipped, refunded, note added, email sent, etc.). Records which user or app triggered it. Structured parameters per event.
**Adoption:** Add `OrderEvent` model. Append-only. All order mutations create events. Display as timeline in seller dashboard.

### 6.5 Draft Orders / Phone Orders (P1)
**Source:** Saleor, Vendure, Medusa
**What:** Staff creates orders bypassing checkout. Add items, set customer, override prices, apply discounts. Stock not allocated until confirmed. Essential for B2B phone/email orders.
**Adoption:** Add `isDraft` flag to `Order`. Draft order API with price override capability.

---

## 7. PAYMENTS

### 7.1 Transaction-Based Payment Architecture (P1)
**Source:** Saleor
**What:** `TransactionItem` model decouples payment from gateways. Multiple transactions per order (split payments). Each transaction tracks: authorized, charged, refunded, cancelled amounts. Payment apps report state changes via API. Order payment status derived from aggregating all transaction amounts.
**Adoption:** Replace single `paymentRef` on Order with `PaymentTransaction` model supporting multiple transactions per order.

### 7.2 Payment State Machine (P1)
**Source:** Solidus
**What:** Payment progresses through states: checkout → processing → pending → completed, plus failed, void, invalid. Smart cancellation: captured payment → refund; authorized-only → void. Supports partial captures via `PaymentCaptureEvent`.
**Adoption:** Implement payment state machine in gateway abstraction. Track partial captures.

### 7.3 Gift Cards as Payment Instruments (P2)
**Source:** Medusa
**What:** Gift cards are stored-value payment instruments with their own balance, NOT discount codes. Applied at checkout as payment method (reduces amount charged to gateway). Tax calculated on full pre-gift-card amount. Partial redemption supported. Balance tracked via `GiftCardTransaction` ledger.
**Adoption:** Add `GiftCard` model with `code`, `initialBalance`, `currentBalance`, `expiresAt`. `GiftCardTransaction` for usage tracking. Apply as payment method at checkout.

### 7.4 Store Credits with Dual Types (P2)
**Source:** Spree, Solidus
**What:** Store credits as payment source with authorization/capture/void lifecycle. Two types: expiring (used first) and non-expiring. Full audit trail via `StoreCreditEvent`. On refund, configurable: create new credit or adjust existing.
**Adoption:** Add `StoreCredit` model with type, balance tracking, and event log. Priority: expiring credits used first.

### 7.5 AVS/CVV Risk Scoring (P3)
**Source:** Solidus
**What:** Payments automatically classified as risky/non-risky based on AVS and CVV response codes from the gateway. Risky payments flagged for manual review.
**Adoption:** Add `riskScore` field to `PaymentTransaction`. Parse AVS/CVV from gateway responses. Flag risky orders in dashboard.

---

## 8. TAX SYSTEM

### 8.1 Zone-Based Tax with Default Zone Fallback (P1)
**Source:** Sylius, Saleor
**What:** Tax rates defined per zone (country/state). `TaxCategory` groups products (different rates for plants vs supplies). When customer hasn't provided address yet, channel's default tax zone estimates taxes (critical for EU VAT where prices must include tax).
**Adoption:** Already have `TaxRate` model. Add `TaxCategory` on products, zone matching logic, and default zone fallback.

### 8.2 Time-Bounded Tax Rates (P2)
**Source:** Sylius
**What:** Tax rates have `startDate`/`endDate`. New tax rate takes effect on a future date without manual switching. Old rate auto-expires.
**Adoption:** Add `startsAt`/`expiresAt` to `TaxRate`. Tax calculator uses rates valid at order creation time.

### 8.3 Tax-Inclusive vs Tax-Exclusive Pricing (P1)
**Source:** Medusa, Sylius
**What:** Configurable per region/channel. Tax-inclusive: displayed price includes tax (EU/India GST style). Tax-exclusive: tax added on top (US sales tax style). `PricePreference` controls behavior.
**Adoption:** Add `taxInclusive` boolean to tenant settings. Tax calculator handles both modes.

### 8.4 Synchronous Tax Calculation via Webhooks (P2)
**Source:** Saleor
**What:** `CHECKOUT_CALCULATE_TAXES` sync webhook — external tax service (Avalara, TaxJar) calculates taxes in real-time during checkout. Response injected into checkout totals.
**Adoption:** Add optional external tax provider hook. If configured, tax calculation delegates to external service.

---

## 9. SHIPPING

### 9.1 Shipping Method Eligibility Rules (P1)
**Source:** Sylius, Saleor
**What:** Shipping methods have composable rules beyond zone matching: items total ≥/≤ threshold, total weight ≥/≤ threshold, product exclusions (specific products can't use a method). Methods hidden if any rule fails.
**Adoption:** Add `ShippingRule` model with `type` (weight, total, product exclusion) and `check()` logic.

### 9.2 Dynamic Shipping Rates via Webhooks (P2)
**Source:** Saleor
**What:** `SHIPPING_LIST_METHODS_FOR_CHECKOUT` sync webhook — external service dynamically provides shipping rates at checkout time (real-time carrier rate lookups).
**Adoption:** Add optional shipping rate provider hook. If configured, fetch rates from external API (Shiprocket, Delhivery).

---

## 10. MULTI-TENANCY & B2B

### 10.1 B2B Buyer Organizations (P1)
**Source:** Spree 5.3
**What:** Buyer organizations with roles (buyers, approvers, accountants, admins). Approval workflows for purchases — buyer creates order, approver must confirm before processing. Gated storefronts — products/prices hidden until account verified.
**Adoption:** Add `Organization` model with `OrganizationMember` (userId, role). Approval workflow on wholesale orders. Product visibility gating via organization verification status.

### 10.2 Quote/Negotiation Workflow (P2)
**Source:** Bagisto
**What:** B2B quote flow: buyer requests quote → seller responds with price → buyer accepts/counters → final price agreed → converts to order. Full negotiation trail.
**Adoption:** Add `Quote`, `QuoteItem`, `QuoteMessage` models. Status: requested → responded → countered → accepted → converted.

### 10.3 Vendor/Marketplace Features (P3)
**Source:** Mercur
**What:** Multi-vendor marketplace: vendor onboarding with admin approval, product approval queue, commission system (percentage/flat per transaction, configurable per vendor), automatic order splitting across vendors, Stripe Connect for split payments and vendor payouts.
**Adoption:** Future phase. Add `Vendor` model, product approval workflow, commission tracking, split payments.

---

## 11. FRONTEND PERFORMANCE

### 11.1 React Server Components for Product Pages (P0)
**Source:** Vercel Commerce
**What:** Product pages rendered as RSC — zero client-side JS for static content. Only interactive parts (add to cart, variant selector) are client components. Streaming SSR sends shell immediately, fills in data as it loads.
**Adoption:** Next.js App Router gives us this natively. Design product pages as server components with client component islands.

### 11.2 Optimistic Cart Mutations (P1)
**Source:** Vercel Commerce
**What:** `useOptimistic` hook updates cart UI immediately before server confirms. Cart drawer shows updated quantity/total instantly, rolls back if server rejects.
**Adoption:** Implement on all cart operations (add, remove, update quantity, apply coupon).

### 11.3 Partial Prerendering (P1)
**Source:** NextFaster
**What:** Static shell (header, footer, layout) prerendered at build time. Dynamic holes (product data, prices, stock) filled at request time. Combines static speed with dynamic freshness.
**Adoption:** Use Next.js PPR for catalog pages. Static shell + dynamic product grid.

### 11.4 Aggressive Query Caching (P1)
**Source:** NextFaster
**What:** `unstable_cache` with tag-based revalidation. Product queries cached and revalidated only when product data changes. Prepared statements for sub-50ms database queries.
**Adoption:** Cache product/category queries with `revalidateTag`. Invalidate on product update events.

---

## 12. EXTENSIBILITY & INTEGRATION

### 12.1 Synchronous Webhooks (P1)
**Source:** Saleor
**What:** Webhooks that block the operation and wait for response. Used for: payment processing, tax calculation, shipping rate lookup, shipping method filtering. Response injected back into the operation.
**Adoption:** Add `syncWebhook` type to webhook system. Implement for tax, shipping, and payment extension points.

### 12.2 Webhook Payload Customization via GraphQL Subscription (P2)
**Source:** Saleor
**What:** Each webhook app defines a GraphQL subscription query specifying exactly which fields it wants. Webhook payload contains only those fields. Reduces bandwidth and gives apps control.
**Adoption:** Future phase. For v1, send full event payloads. Add subscription-based filtering later.

### 12.3 Per-Entity Metadata (Public + Private) (P1)
**Source:** Saleor
**What:** Every major entity has `metadata` (public, returned in storefront API) and `privateMetadata` (admin-only). Schema-less key-value. Apps use metadata as their storage mechanism.
**Adoption:** Add `metadata` and `privateMetadata` JSON columns to: Product, Order, Customer, Checkout. Expose in API.

### 12.4 Tenant-Configurable Custom Fields (P2)
**Source:** Vendure
**What:** Tenants define custom fields on products, orders, customers via admin settings. Platform auto-generates form controls. No code changes needed.
**Adoption:** `CustomFieldDefinition` table per tenant. Admin UI dynamically renders fields. Store values in entity's `customFields` JSON column.

---

## 13. CONTENT & SEO

### 13.1 Dynamic Page Types (CMS with Configurable Schema) (P2)
**Source:** Saleor
**What:** CMS pages have `PageType` with configurable attributes (same attribute system as products). Content pages have dynamic schemas — a "Recipe" page type has different fields than a "FAQ" page type.
**Adoption:** Add `PageType` with attribute assignments. CMS pages inherit schema from their type.

### 13.2 Product Feeds (Google Shopping, Facebook Catalog) (P1)
**Source:** Common but often plugin-only
**What:** Auto-generated XML/JSON feeds that Google Shopping and Facebook Catalog can pull. Include product title, price, image, availability, GTIN/SKU, taxonomy category.
**Adoption:** Already in spec. Build as API routes: `/api/v1/feeds/google-shopping.xml`, `/api/v1/feeds/facebook-catalog.json`.

---

## 14. UNIQUE TO OUR STACK (Plant Domain — No Source)

These features exist nowhere else and come from our PhytoCommerce modules:

- Tissue culture batch tracking with lineage (parent batch chain)
- Contamination incident logging
- Phytosanitary document management with expiry tracking
- Climate zone suitability checker (797 PIN codes → 15 zones)
- Growth stage labels (deflasked → juvenile → mature → specimen)
- Seasonal shipping/availability blocking
- Care card PDF generation
- Live arrival guarantee with claims
- Acclimation kit suggestions
- Grower journal (purchase-gated community content)
- Plant collection portfolio
- Source/certification badges
- TC production cost calculator
- Botanical taxonomy pack importer

**This is our moat.** No other ecommerce platform has these.

---

## Priority Summary

| Priority | Count | Examples |
|----------|-------|---------|
| **P0** (architectural foundation) | 5 | Workflow engine, event bus, stateful checkout, RSC, dynamic product types |
| **P1** (high value) | 22 | Price lists, multi-warehouse, RMA, order editing, promotion engine, inventory reservation, payment transactions, tax zones, shipping rules, optimistic UI, metadata |
| **P2** (strong differentiator) | 14 | Promotion lanes, custom fields, facets, exchange rates, gift cards, store credits, carton model, quote workflow, catalog promotions, sync webhooks |
| **P3** (nice to have) | 4 | Click-and-collect, AVS risk scoring, vendor marketplace, webhook payload customization |
| **Plant domain** | 14 | Our moat — no source platform has these |

**Total: 59 differential features + 14 plant-domain exclusives = 73 features that make our stack best-in-class.**
