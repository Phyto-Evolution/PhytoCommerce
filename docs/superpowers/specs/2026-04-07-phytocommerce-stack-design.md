# PhytoCommerce Stack — Platform Design Spec

> **Date:** 2026-04-07
> **Status:** Draft — awaiting review
> **Repo:** New standalone repo (`phytocommerce-stack`)
> **License:** Open source (MIT)

---

## 1. Vision

A standalone, open source, self-service ecommerce platform purpose-built for plant sellers — nurseries, tissue-culture producers, rare plant retailers, and horticultural businesses. Replaces PrestaShop/WooCommerce with a single Next.js + PostgreSQL stack that has every plant commerce feature built natively.

**What it is not:** A fork, a plugin system, or a wrapper around an existing CMS. The 28 PhytoCommerce module features and the core capabilities of PrestaShop/WooCommerce are reimplemented as native platform functionality.

**Inspired by:** [Plant-Selling-Ecommerce](https://github.com/Aaditya-Ajju/Plant-Selling-Ecommerce) (React/Node/MongoDB plant marketplace). We take the concept and build a production-grade multi-tenant platform far beyond its scope.

---

## 2. Tech Stack

| Layer | Technology | License |
|-------|-----------|---------|
| Framework | Next.js (App Router, SSR) | MIT |
| Language | TypeScript | Apache 2.0 |
| Database | PostgreSQL (1 platform DB + 1 per tenant) | PostgreSQL |
| ORM | Prisma | Apache 2.0 |
| Auth | NextAuth.js | ISC |
| Search | Meilisearch | MIT |
| Email | Listmonk (embedded) | AGPL-3.0 |
| Analytics | Umami (embedded) | MIT |
| Image processing | Sharp | Apache 2.0 |
| PDF generation | react-pdf | MIT |
| Job queue | BullMQ + Redis | MIT / BSD |
| Styling | Tailwind CSS | MIT |
| Deployment | Docker Compose (self-hosted) | — |

Everything open source. No proprietary dependencies.

---

## 3. Multi-Tenancy

### Model: Separate database per tenant

Each tenant gets an isolated PostgreSQL database. A shared platform database handles tenant registry, plans, and billing.

### Tenant resolution

```
Request hits Next.js middleware
  ├── {slug}.phytolabs.in → extract slug from subdomain
  ├── custom-domain.com   → lookup in platform DB → resolve slug
  └── Inject tenant context (DB connection, config) into request
```

### Provisioning (self-service)

1. Seller signs up at phytolabs.in
2. Picks a plan, enters store name → slug generated
3. Platform creates tenant record + PostgreSQL database
4. Prisma runs tenant schema migration
5. Meilisearch index created for tenant
6. Umami site created for tenant
7. Store live at `{slug}.phytolabs.in` within seconds

### Tenant config flags

```typescript
{
  store_mode: "b2c" | "b2b" | "both",
  payment_gateways: ["razorpay", "stripe", "payu", "cashfree", "cod", "bank_transfer"],
  currency: "INR",
  timezone: "Asia/Kolkata",
  features: {
    wholesale: boolean,
    subscriptions: boolean,
    loyalty: boolean,
    live_arrival_guarantee: boolean,
    grower_journal: boolean,
    taxonomy_packs: boolean,
    climate_zones: boolean,
    // ... all features toggleable
  }
}
```

---

## 4. Auth & Roles

### Three auth scopes

| Scope | Users | Method |
|-------|-------|--------|
| Platform | Super admins (your team) | Email + password, 2FA |
| Seller | Store owner + staff | Email + password, Google OAuth |
| Buyer | B2C + B2B customers | Email + password, Google/Facebook OAuth, guest checkout |

### Per-tenant roles

| Role | Access |
|------|--------|
| `owner` | Full store control, billing, staff management |
| `manager` | Products, orders, customers — no billing |
| `staff` | Orders, fulfillment, dispatch logging only |
| `wholesale_buyer` | Storefront + wholesale pricing + invoice payment |
| `buyer` | Storefront, cart, orders, journal, collection |
| `guest` | Browse, cart, guest checkout |

### Session strategy

- JWT for `/api/*` routes (stateless, headless-friendly)
- Server-side sessions for SSR pages (secure httpOnly cookies)
- Tenant ID embedded in session — every DB query scoped automatically

---

## 5. Database Schema

### Platform DB (`phytocommerce_platform`)

```prisma
model Tenant {
  id            String   @id @default(cuid())
  slug          String   @unique
  name          String
  customDomain  String?  @unique
  plan          Plan     @relation(fields: [planId], references: [id])
  planId        String
  config        Json     // store_mode, features, currency, timezone
  status        TenantStatus @default(ACTIVE)
  dbUrl         String   // connection string to tenant DB
  createdAt     DateTime @default(now())
}

model Plan {
  id            String   @id @default(cuid())
  name          String
  price         Decimal
  interval      BillingInterval
  features      Json     // feature flags included in plan
  maxProducts   Int?
  maxStaff      Int?
  tenants       Tenant[]
}

model PlatformPayment {
  id            String   @id @default(cuid())
  tenantId      String
  amount        Decimal
  gateway       String
  gatewayRef    String
  status        PaymentStatus
  createdAt     DateTime @default(now())
}

model TaxonomyPack {
  id            String   @id @default(cuid())
  name          String   // "Carnivorous", "Aroids", "Orchids"
  family        String
  data          Json     // genus → species → cultivars tree
  version       Int
  createdAt     DateTime @default(now())
}
```

### Tenant DB (per-tenant, `phyto_{slug}`)

```prisma
// ─── USERS & AUTH ───

model User {
  id              String   @id @default(cuid())
  email           String   @unique
  passwordHash    String?
  name            String
  phone           String?
  role            UserRole @default(BUYER)
  emailVerified   Boolean  @default(false)
  avatarUrl       String?
  createdAt       DateTime @default(now())
  updatedAt       DateTime @updatedAt

  addresses       Address[]
  orders          Order[]
  cart            Cart?
  reviews         Review[]
  journalEntries  JournalEntry[]
  collection      CollectionItem[]
  loyaltyLedger   LoyaltyEntry[]
  loyaltyTier     LoyaltyTier  @default(BRONZE)
  loyaltyPoints   Int          @default(0)
  restockAlerts   RestockAlert[]
  wishlist        WishlistItem[]
  wholesaleApp    WholesaleApplication?
  kycVerification KycVerification?
}

model Address {
  id          String  @id @default(cuid())
  userId      String
  user        User    @relation(fields: [userId], references: [id])
  label       String? // "Home", "Office"
  name        String
  phone       String
  line1       String
  line2       String?
  city        String
  state       String
  postalCode  String
  country     String  @default("IN")
  isDefault   Boolean @default(false)
}

model KycVerification {
  id          String    @id @default(cuid())
  userId      String    @unique
  user        User      @relation(fields: [userId], references: [id])
  panNumber   String?
  gstNumber   String?
  businessName String?
  documents   Json?     // uploaded doc references
  status      KycStatus @default(PENDING)
  reviewedBy  String?
  reviewedAt  DateTime?
  createdAt   DateTime  @default(now())
}

model WholesaleApplication {
  id           String    @id @default(cuid())
  userId       String    @unique
  user         User      @relation(fields: [userId], references: [id])
  businessName String
  gstNumber    String
  website      String?
  expectedVolume String?
  status       ApprovalStatus @default(PENDING)
  reviewedBy   String?
  reviewedAt   DateTime?
  createdAt    DateTime  @default(now())
}

// ─── CATALOG ───

model Product {
  id                String   @id @default(cuid())
  name              String
  slug              String   @unique
  description       String   @db.Text
  shortDescription  String?
  sku               String?  @unique
  barcode           String?
  type              ProductType @default(SIMPLE)

  // Pricing
  price             Decimal
  salePrice         Decimal?
  saleStart         DateTime?
  saleEnd           DateTime?
  costPrice         Decimal? // internal, never shown to buyers

  // Stock
  trackStock        Boolean  @default(true)
  stockQty          Int      @default(0)
  lowStockThreshold Int      @default(5)
  allowBackorders   Boolean  @default(false)
  stockStatus       StockStatus @default(IN_STOCK)

  // Physical
  weight            Decimal?
  length            Decimal?
  width             Decimal?
  height            Decimal?
  shippingClass     String?  // "fragile", "standard", "oversized"

  // SEO
  metaTitle         String?
  metaDescription   String?
  canonicalUrl      String?

  // Plant-specific
  genus             String?
  species           String?
  cultivar          String?
  grexName          String?
  hybridizer        String?
  conservationStatus String?  // IUCN or CITES
  growthStage       GrowthStage?
  careInstructions  Json?    // { light, water, humidity, temp, soil, dormancy }
  seasonalBlocking  Json?    // { blockedMonths: [5,6,7], reason: "Too hot to ship" }

  // Relations
  categories        ProductCategory[]
  images            ProductImage[]
  attributes        ProductAttribute[]
  variations        ProductVariation[]
  reviews           Review[]
  batches           Batch[]
  badges            ProductBadge[]
  documents         ProductDocument[]
  restockAlerts     RestockAlert[]
  journalEntries    JournalEntry[]
  wishlistItems     WishlistItem[]
  bundleSlots       BundleSlot[]

  // Wholesale pricing
  wholesalePrices   WholesalePrice[]

  status            ProductStatus @default(DRAFT)
  publishedAt       DateTime?
  createdAt         DateTime @default(now())
  updatedAt         DateTime @updatedAt
}

model ProductImage {
  id          String  @id @default(cuid())
  productId   String
  product     Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  url         String
  alt         String?
  position    Int     @default(0)
  isCover     Boolean @default(false)
  watermarked Boolean @default(false)
  iptcData    Json?
}

model ProductAttribute {
  id          String  @id @default(cuid())
  productId   String
  product     Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  name        String  // "Pot Size", "Age", "Height"
  values      Json    // ["3 inch", "4 inch", "6 inch"]
}

model ProductVariation {
  id          String  @id @default(cuid())
  productId   String
  product     Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  sku         String? @unique
  attributes  Json    // { "Pot Size": "4 inch", "Age": "6 months" }
  price       Decimal
  salePrice   Decimal?
  stockQty    Int     @default(0)
  weight      Decimal?
  imageId     String?
}

model Category {
  id          String     @id @default(cuid())
  name        String
  slug        String     @unique
  description String?
  parentId    String?
  parent      Category?  @relation("CategoryTree", fields: [parentId], references: [id])
  children    Category[] @relation("CategoryTree")
  position    Int        @default(0)
  imageUrl    String?
  metaTitle   String?
  metaDescription String?
  products    ProductCategory[]
}

model ProductCategory {
  productId   String
  categoryId  String
  product     Product  @relation(fields: [productId], references: [id], onDelete: Cascade)
  category    Category @relation(fields: [categoryId], references: [id], onDelete: Cascade)
  @@id([productId, categoryId])
}

model WholesalePrice {
  id          String  @id @default(cuid())
  productId   String
  product     Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  minQty      Int
  price       Decimal
}

// ─── PLANT PROVENANCE ───

model Batch {
  id                String   @id @default(cuid())
  productId         String
  product           Product  @relation(fields: [productId], references: [id])
  batchCode         String   @unique  // auto-generated
  generation        String   // G0, G1, G2...
  parentBatchId     String?
  parentBatch       Batch?   @relation("BatchLineage", fields: [parentBatchId], references: [id])
  childBatches      Batch[]  @relation("BatchLineage")
  unitCount         Int
  initiationDate    DateTime
  deflaskingDate    DateTime?
  certificationDate DateTime?
  status            BatchStatus @default(ACTIVE)
  contaminations    Contamination[]
  createdAt         DateTime @default(now())
}

model Contamination {
  id          String   @id @default(cuid())
  batchId     String
  batch       Batch    @relation(fields: [batchId], references: [id])
  type        ContaminationType // BACTERIAL, FUNGAL, VIRAL, PEST
  severity    Severity
  notes       String?
  resolved    Boolean  @default(false)
  resolvedAt  DateTime?
  createdAt   DateTime @default(now())
}

model ProductBadge {
  id          String  @id @default(cuid())
  productId   String
  product     Product @relation(fields: [productId], references: [id], onDelete: Cascade)
  badge       Badge   @relation(fields: [badgeId], references: [id])
  badgeId     String
}

model Badge {
  id          String  @id @default(cuid())
  name        String  // "Tissue Culture", "Wild Collected", "Certified Organic"
  slug        String  @unique
  icon        String?
  color       String?
  products    ProductBadge[]
}

model ProductDocument {
  id          String   @id @default(cuid())
  productId   String
  product     Product  @relation(fields: [productId], references: [id], onDelete: Cascade)
  name        String
  type        DocType  // PHYTOSANITARY, IMPORT_PERMIT, CITES, OTHER
  fileUrl     String
  expiresAt   DateTime?
  isPublic    Boolean  @default(false)
  createdAt   DateTime @default(now())
}

// ─── CLIMATE ZONES ───

model ClimateZone {
  id          String @id @default(cuid())
  code        String @unique  // "PCC-IN-01"
  name        String          // "Kerala Humid Tropical"
  monthlyData Json            // { temp: [...], humidity: [...], rainfall: [...] }
  frostRisk   Boolean @default(false)
  monsoonMonths Json?         // [6,7,8,9]
  exampleCities Json          // ["Kochi", "Trivandrum"]
  pinMappings PinZoneMapping[]
}

model PinZoneMapping {
  pinPrefix   String @id      // "682" (3-digit)
  zoneId      String
  zone        ClimateZone @relation(fields: [zoneId], references: [id])
}

// ─── COMMERCE ───

model Cart {
  id          String     @id @default(cuid())
  userId      String?    @unique
  user        User?      @relation(fields: [userId], references: [id])
  sessionId   String?    @unique // for guest carts
  items       CartItem[]
  couponCode  String?
  updatedAt   DateTime   @updatedAt
}

model CartItem {
  id          String  @id @default(cuid())
  cartId      String
  cart        Cart    @relation(fields: [cartId], references: [id], onDelete: Cascade)
  productId   String
  variationId String?
  quantity    Int
  lagOptIn    Boolean @default(false) // Live Arrival Guarantee
}

model Order {
  id              String   @id @default(cuid())
  orderNumber     String   @unique  // human-readable, sequential
  userId          String?
  user            User?    @relation(fields: [userId], references: [id])
  guestEmail      String?
  status          OrderStatus @default(PENDING)

  // Addresses (snapshot at time of order)
  shippingAddress Json
  billingAddress  Json

  // Financials
  subtotal        Decimal
  shippingCost    Decimal  @default(0)
  taxAmount       Decimal  @default(0)
  discountAmount  Decimal  @default(0)
  lagFee          Decimal  @default(0)
  total           Decimal
  currency        String   @default("INR")

  // Payment
  paymentMethod   String?
  paymentGateway  String?
  paymentRef      String?
  paymentStatus   PaymentStatus @default(PENDING)
  paidAt          DateTime?

  // Shipping
  shippingMethod  String?
  trackingNumber  String?
  carrier         String?
  shippedAt       DateTime?
  deliveredAt     DateTime?

  // Meta
  customerNote    String?
  adminNote       String?
  couponCode      String?
  isWholesale     Boolean @default(false)
  invoicePayment  Boolean @default(false) // B2B pay later

  items           OrderItem[]
  refunds         Refund[]
  dispatchLog     DispatchLog?
  lagClaim        LagClaim?

  createdAt       DateTime @default(now())
  updatedAt       DateTime @updatedAt
}

model OrderItem {
  id          String  @id @default(cuid())
  orderId     String
  order       Order   @relation(fields: [orderId], references: [id])
  productId   String
  variationId String?
  name        String  // snapshot
  sku         String? // snapshot
  price       Decimal
  quantity    Int
  total       Decimal
  batchId     String? // TC batch linked to this sale
}

model Refund {
  id          String   @id @default(cuid())
  orderId     String
  order       Order    @relation(fields: [orderId], references: [id])
  amount      Decimal
  type        RefundType // FULL, PARTIAL, STORE_CREDIT
  reason      String?
  gatewayRef  String?
  createdAt   DateTime @default(now())
}

// ─── SHIPPING ───

model ShippingZone {
  id          String   @id @default(cuid())
  name        String
  regions     Json     // country/state codes
  methods     ShippingMethod[]
}

model ShippingMethod {
  id          String   @id @default(cuid())
  zoneId      String
  zone        ShippingZone @relation(fields: [zoneId], references: [id])
  name        String   // "Standard", "Express", "Pickup"
  type        ShippingType // FLAT, WEIGHT_BASED, PRICE_BASED, FREE, PICKUP
  cost        Decimal?
  freeAbove   Decimal? // free shipping threshold
  rules       Json?    // weight brackets, price brackets
  enabled     Boolean  @default(true)
}

// ─── TAX ───

model TaxRate {
  id          String  @id @default(cuid())
  name        String  // "GST 5%", "GST 18%"
  rate        Decimal // 0.05, 0.18
  country     String
  state       String? // null = whole country
  postalCodes Json?   // specific postal codes
  productClass String? // tax class for products
  priority    Int     @default(1)
  compound    Boolean @default(false)
  enabled     Boolean @default(true)
}

// ─── DISPATCH & FULFILLMENT ───

model DispatchLog {
  id              String   @id @default(cuid())
  orderId         String   @unique
  order           Order    @relation(fields: [orderId], references: [id])
  temperature     Decimal? // °C at packing
  humidity        Decimal? // % at packing
  packingMethod   String?
  heatPack        Boolean  @default(false)
  gelPack         Boolean  @default(false)
  photos          Json?    // array of URLs
  notes           String?
  packedBy        String?
  packedAt        DateTime @default(now())
}

model LagClaim {
  id          String   @id @default(cuid())
  orderId     String   @unique
  order       Order    @relation(fields: [orderId], references: [id])
  description String
  photos      Json     // array of URLs
  status      ClaimStatus @default(OPEN)
  resolution  String?
  refundId    String?
  createdAt   DateTime @default(now())
  resolvedAt  DateTime?
}

// ─── COUPONS & DISCOUNTS ───

model Coupon {
  id              String   @id @default(cuid())
  code            String   @unique
  type            CouponType // PERCENTAGE, FIXED, FREE_SHIPPING, BOGO
  value           Decimal    // percentage or fixed amount
  minSpend        Decimal?
  maxDiscount     Decimal?   // cap for percentage coupons
  usageLimit      Int?       // total uses allowed
  perCustomerLimit Int?
  usageCount      Int        @default(0)
  applicableTo    Json?      // { productIds: [], categoryIds: [] }
  excludes        Json?      // { productIds: [], categoryIds: [] }
  startsAt        DateTime?
  expiresAt       DateTime?
  enabled         Boolean    @default(true)
  createdAt       DateTime   @default(now())
}

model AutoDiscount {
  id          String   @id @default(cuid())
  name        String
  type        AutoDiscountType // SPEND_X_GET_Y_PCT, BUY_X_GET_Y
  rules       Json     // { minSpend: 2000, discountPct: 10 }
  priority    Int      @default(0)
  startsAt    DateTime?
  expiresAt   DateTime?
  enabled     Boolean  @default(true)
}

// ─── LOYALTY ───

model LoyaltyEntry {
  id          String   @id @default(cuid())
  userId      String
  user        User     @relation(fields: [userId], references: [id])
  type        LoyaltyType // EARNED, REDEEMED, ADJUSTED, EXPIRED
  points      Int
  description String
  orderId     String?
  adjustedBy  String?  // admin who made manual adjustment
  createdAt   DateTime @default(now())
}

// ─── SUBSCRIPTIONS ───

model SubscriptionPlan {
  id          String   @id @default(cuid())
  name        String
  description String?
  price       Decimal
  frequency   Frequency // WEEKLY, BIWEEKLY, MONTHLY, QUARTERLY
  imageUrl    String?
  maxSubscribers Int?
  enabled     Boolean  @default(true)
  subscriptions Subscription[]
  createdAt   DateTime @default(now())
}

model Subscription {
  id          String   @id @default(cuid())
  planId      String
  plan        SubscriptionPlan @relation(fields: [planId], references: [id])
  userId      String
  status      SubStatus @default(ACTIVE)
  gatewaySubId String? // recurring payment reference
  nextBillingDate DateTime
  createdAt   DateTime @default(now())
}

// ─── BUNDLES ───

model Bundle {
  id          String   @id @default(cuid())
  name        String
  slug        String   @unique
  description String?
  discountType CouponType // PERCENTAGE, FIXED
  discountValue Decimal
  minSlots    Int      @default(2)
  maxSlots    Int      @default(5)
  slots       BundleSlot[]
  enabled     Boolean  @default(true)
}

model BundleSlot {
  id          String  @id @default(cuid())
  bundleId    String
  bundle      Bundle  @relation(fields: [bundleId], references: [id], onDelete: Cascade)
  productId   String?
  product     Product? @relation(fields: [productId], references: [id])
  categoryId  String? // allow any product from category
  position    Int
}

// ─── CUSTOMER ENGAGEMENT ───

model Review {
  id          String   @id @default(cuid())
  productId   String
  product     Product  @relation(fields: [productId], references: [id])
  userId      String
  user        User     @relation(fields: [userId], references: [id])
  rating      Int      // 1-5
  title       String?
  body        String?
  photos      Json?
  verified    Boolean  @default(false) // purchase-verified
  approved    Boolean  @default(false) // moderation
  createdAt   DateTime @default(now())
}

model JournalEntry {
  id          String   @id @default(cuid())
  productId   String
  product     Product  @relation(fields: [productId], references: [id])
  userId      String
  user        User     @relation(fields: [userId], references: [id])
  title       String
  body        String   @db.Text
  photos      Json?
  milestone   String?  // "First Leaf", "First Flower", "Repotted"
  approved    Boolean  @default(false)
  createdAt   DateTime @default(now())
}

model CollectionItem {
  id          String  @id @default(cuid())
  userId      String
  user        User    @relation(fields: [userId], references: [id])
  productId   String
  orderId     String
  isPublic    Boolean @default(true)
  careNotes   String?
  addedAt     DateTime @default(now())
}

model RestockAlert {
  id          String   @id @default(cuid())
  productId   String
  product     Product  @relation(fields: [productId], references: [id])
  userId      String?
  user        User?    @relation(fields: [userId], references: [id])
  email       String
  name        String?
  notified    Boolean  @default(false)
  notifiedAt  DateTime?
  createdAt   DateTime @default(now())
}

model WishlistItem {
  id          String   @id @default(cuid())
  userId      String
  user        User     @relation(fields: [userId], references: [id])
  productId   String
  product     Product  @relation(fields: [productId], references: [id])
  createdAt   DateTime @default(now())
  @@unique([userId, productId])
}

// ─── CMS & BLOG ───

model Page {
  id          String   @id @default(cuid())
  title       String
  slug        String   @unique
  body        String   @db.Text
  metaTitle   String?
  metaDescription String?
  published   Boolean  @default(false)
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
}

model BlogPost {
  id          String   @id @default(cuid())
  title       String
  slug        String   @unique
  excerpt     String?
  body        String   @db.Text
  coverImage  String?
  authorId    String
  tags        Json?
  metaTitle   String?
  metaDescription String?
  published   Boolean  @default(false)
  publishedAt DateTime?
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
}

// ─── NOTIFICATIONS & EVENTS ───

model Webhook {
  id          String   @id @default(cuid())
  event       String   // "order.created", "product.published", etc.
  url         String
  secret      String   // HMAC signing secret
  enabled     Boolean  @default(true)
  createdAt   DateTime @default(now())
}

model ActivityLog {
  id          String   @id @default(cuid())
  userId      String?
  action      String   // "product.created", "order.refunded"
  entity      String   // "Product", "Order"
  entityId    String
  details     Json?
  ip          String?
  createdAt   DateTime @default(now())
}

model EmailTemplate {
  id          String @id @default(cuid())
  slug        String @unique // "order-confirmation", "welcome", "restock-alert"
  subject     String
  body        String @db.Text // HTML template with variables
  enabled     Boolean @default(true)
}

// ─── STORE SETTINGS ───

model StoreSetting {
  key         String @id
  value       Json
}

// ─── ENUMS ───

enum UserRole {
  OWNER
  MANAGER
  STAFF
  WHOLESALE_BUYER
  BUYER
}

enum TenantStatus {
  ACTIVE
  SUSPENDED
  CANCELLED
}

enum BillingInterval {
  MONTHLY
  QUARTERLY
  YEARLY
}

enum PaymentStatus {
  PENDING
  PAID
  FAILED
  REFUNDED
  PARTIALLY_REFUNDED
}

enum ProductType {
  SIMPLE
  VARIABLE
  DIGITAL
  GROUPED
}

enum ProductStatus {
  DRAFT
  PUBLISHED
  ARCHIVED
}

enum StockStatus {
  IN_STOCK
  OUT_OF_STOCK
  ON_BACKORDER
}

enum GrowthStage {
  DEFLASKED
  JUVENILE
  SEMI_MATURE
  MATURE
  SPECIMEN
}

enum OrderStatus {
  PENDING
  CONFIRMED
  PROCESSING
  PACKED
  SHIPPED
  DELIVERED
  CANCELLED
  RETURNED
}

enum RefundType {
  FULL
  PARTIAL
  STORE_CREDIT
}

enum ShippingType {
  FLAT
  WEIGHT_BASED
  PRICE_BASED
  FREE
  PICKUP
}

enum CouponType {
  PERCENTAGE
  FIXED
  FREE_SHIPPING
  BOGO
}

enum AutoDiscountType {
  SPEND_X_GET_Y_PCT
  BUY_X_GET_Y
}

enum LoyaltyTier {
  BRONZE
  SILVER
  GOLD
  PLATINUM
}

enum LoyaltyType {
  EARNED
  REDEEMED
  ADJUSTED
  EXPIRED
}

enum Frequency {
  WEEKLY
  BIWEEKLY
  MONTHLY
  QUARTERLY
}

enum SubStatus {
  ACTIVE
  PAUSED
  CANCELLED
  EXPIRED
}

enum BatchStatus {
  ACTIVE
  DEPLETED
  CONTAMINATED
  ARCHIVED
}

enum ContaminationType {
  BACTERIAL
  FUNGAL
  VIRAL
  PEST
}

enum Severity {
  LOW
  MEDIUM
  HIGH
  CRITICAL
}

enum DocType {
  PHYTOSANITARY
  IMPORT_PERMIT
  CITES
  OTHER
}

enum ClaimStatus {
  OPEN
  UNDER_REVIEW
  APPROVED
  DENIED
  RESOLVED
}

enum KycStatus {
  PENDING
  VERIFIED
  REJECTED
}

enum ApprovalStatus {
  PENDING
  APPROVED
  REJECTED
}
```

---

## 6. Application Structure

```
phytocommerce-stack/
├── src/
│   ├── app/                          # Next.js App Router
│   │   ├── (storefront)/             # Buyer-facing pages (SSR)
│   │   │   ├── [store]/              # Tenant-scoped routes
│   │   │   │   ├── page.tsx          # Store homepage
│   │   │   │   ├── products/
│   │   │   │   │   ├── page.tsx      # Catalog with filters
│   │   │   │   │   └── [slug]/
│   │   │   │   │       └── page.tsx  # Product detail (SSR for SEO)
│   │   │   │   ├── categories/
│   │   │   │   │   └── [slug]/page.tsx
│   │   │   │   ├── cart/page.tsx
│   │   │   │   ├── checkout/page.tsx
│   │   │   │   ├── account/
│   │   │   │   │   ├── page.tsx      # Dashboard
│   │   │   │   │   ├── orders/
│   │   │   │   │   ├── addresses/
│   │   │   │   │   ├── wishlist/
│   │   │   │   │   ├── collection/   # Plant collection portfolio
│   │   │   │   │   ├── journal/      # Grow diary
│   │   │   │   │   └── loyalty/      # Points & tier
│   │   │   │   ├── wholesale/
│   │   │   │   │   ├── apply/page.tsx
│   │   │   │   │   └── portal/page.tsx
│   │   │   │   ├── subscriptions/page.tsx
│   │   │   │   ├── bundles/page.tsx
│   │   │   │   ├── blog/
│   │   │   │   └── [page]/page.tsx   # CMS pages
│   │   │   └── layout.tsx            # Storefront layout (nav, footer, branding)
│   │   │
│   │   ├── (dashboard)/              # Seller admin panel
│   │   │   ├── layout.tsx            # Admin layout (sidebar, auth guard)
│   │   │   ├── page.tsx              # Sales dashboard + charts
│   │   │   ├── products/
│   │   │   │   ├── page.tsx          # Product list
│   │   │   │   ├── new/page.tsx      # Quick add (AI description, multi-image)
│   │   │   │   ├── [id]/page.tsx     # Edit product (all fields)
│   │   │   │   ├── categories/
│   │   │   │   ├── taxonomy/         # Pack import, tree view
│   │   │   │   ├── badges/
│   │   │   │   ├── batches/          # TC batch management
│   │   │   │   └── import-export/
│   │   │   ├── orders/
│   │   │   │   ├── page.tsx          # Order list + filters
│   │   │   │   ├── [id]/page.tsx     # Order detail + dispatch log
│   │   │   │   └── claims/           # LAG claims
│   │   │   ├── customers/
│   │   │   │   ├── page.tsx
│   │   │   │   ├── wholesale/        # Applications, KYC review
│   │   │   │   └── restock-alerts/
│   │   │   ├── compliance/
│   │   │   │   ├── documents/        # Phytosanitary certs
│   │   │   │   └── expiry-alerts/
│   │   │   ├── marketing/
│   │   │   │   ├── coupons/
│   │   │   │   ├── auto-discounts/
│   │   │   │   ├── loyalty/
│   │   │   │   ├── subscriptions/
│   │   │   │   ├── bundles/
│   │   │   │   └── blog/
│   │   │   ├── analytics/
│   │   │   │   ├── page.tsx          # Umami embed + custom charts
│   │   │   │   ├── sales/
│   │   │   │   └── inventory/
│   │   │   ├── settings/
│   │   │   │   ├── general/          # Store name, currency, timezone
│   │   │   │   ├── payments/         # Gateway config
│   │   │   │   ├── shipping/         # Zones, methods, rates
│   │   │   │   ├── tax/              # Tax rates
│   │   │   │   ├── branding/         # Logo, colors, design tokens
│   │   │   │   ├── domains/          # Custom domain setup
│   │   │   │   ├── staff/            # Staff accounts + roles
│   │   │   │   ├── emails/           # Template customization
│   │   │   │   ├── webhooks/         # External integrations
│   │   │   │   ├── climate-zones/    # Zone data management
│   │   │   │   └── seo/              # Redirects, default meta
│   │   │   └── tools/
│   │   │       └── cost-calculator/  # TC production cost tool
│   │   │
│   │   ├── (platform)/               # Super admin (PhytoLabs team)
│   │   │   ├── tenants/
│   │   │   ├── plans/
│   │   │   ├── billing/
│   │   │   ├── taxonomy-packs/       # Manage shared packs
│   │   │   └── system/               # Health, monitoring
│   │   │
│   │   └── api/                      # Headless API (versioned)
│   │       ├── v1/
│   │       │   ├── auth/
│   │       │   ├── products/
│   │       │   ├── categories/
│   │       │   ├── cart/
│   │       │   ├── checkout/
│   │       │   ├── orders/
│   │       │   ├── customers/
│   │       │   ├── reviews/
│   │       │   ├── subscriptions/
│   │       │   ├── loyalty/
│   │       │   ├── climate/
│   │       │   ├── feeds/            # Google Shopping XML, Facebook JSON
│   │       │   └── webhooks/         # Payment gateway callbacks
│   │       └── health/
│   │
│   ├── lib/
│   │   ├── db/
│   │   │   ├── platform.ts           # Platform Prisma client
│   │   │   ├── tenant.ts             # Tenant DB resolver + connection pool
│   │   │   └── migrations/           # Tenant migration runner
│   │   ├── auth/
│   │   │   ├── config.ts             # NextAuth config
│   │   │   ├── roles.ts              # Permission matrix
│   │   │   └── middleware.ts          # Route guards
│   │   ├── payments/
│   │   │   ├── gateway.ts            # Abstract gateway interface
│   │   │   ├── razorpay.ts
│   │   │   ├── stripe.ts
│   │   │   ├── payu.ts
│   │   │   ├── cashfree.ts
│   │   │   └── cod.ts
│   │   ├── storage/
│   │   │   ├── upload.ts             # File upload handler
│   │   │   ├── images.ts             # Sharp: watermark, WebP, resize, IPTC
│   │   │   └── providers/            # Local disk, S3, Cloudinary
│   │   ├── email/
│   │   │   ├── listmonk.ts           # Listmonk API client
│   │   │   ├── templates.ts          # Template renderer
│   │   │   └── triggers.ts           # Event → email mapping
│   │   ├── search/
│   │   │   ├── meilisearch.ts        # Client + index management
│   │   │   └── sync.ts              # Product → search index sync
│   │   ├── taxonomy/
│   │   │   ├── importer.ts           # Pack import logic
│   │   │   └── tree.ts              # Nested category operations
│   │   ├── climate/
│   │   │   ├── zones.ts              # Zone lookup by PIN/ZIP
│   │   │   └── suitability.ts       # Plant × zone compatibility check
│   │   ├── seo/
│   │   │   ├── meta.ts               # Auto-generate meta titles/descriptions
│   │   │   ├── jsonld.ts             # Structured data generators
│   │   │   ├── sitemap.ts            # Dynamic sitemap.xml
│   │   │   └── feeds.ts             # Google Shopping, Facebook catalog
│   │   ├── pdf/
│   │   │   ├── invoice.ts            # Branded invoice PDF
│   │   │   ├── packing-slip.ts       # Packing slip with phytosanitary refs
│   │   │   ├── care-card.ts          # Downloadable care guide
│   │   │   └── qr-label.ts          # Batch QR label (88mm card)
│   │   ├── events/
│   │   │   ├── bus.ts                # Internal event emitter
│   │   │   ├── types.ts              # Event type definitions
│   │   │   └── handlers/             # Built-in event handlers
│   │   │       ├── stock.ts          # Stock change → restock alerts, batch decrement
│   │   │       ├── order.ts          # Order events → loyalty, email, dispatch
│   │   │       ├── payment.ts        # Payment confirmed → order status
│   │   │       └── webhooks.ts       # Fire tenant-configured webhooks
│   │   ├── jobs/
│   │   │   ├── queue.ts              # BullMQ setup
│   │   │   ├── cart-abandonment.ts
│   │   │   ├── cert-expiry.ts        # Phytosanitary doc expiry checks
│   │   │   ├── subscription-billing.ts
│   │   │   ├── loyalty-expiry.ts
│   │   │   └── image-processing.ts
│   │   ├── shipping/
│   │   │   ├── calculator.ts         # Rate calculation engine
│   │   │   └── seasonal.ts          # Seasonal shipping blackout logic
│   │   ├── tax/
│   │   │   └── calculator.ts         # GST/VAT calculation
│   │   ├── discounts/
│   │   │   ├── coupon.ts             # Coupon validation + application
│   │   │   └── auto.ts             # Automatic discount evaluation
│   │   └── analytics/
│   │       ├── umami.ts              # Umami API client
│   │       └── internal.ts          # Sales, AOV, conversion queries
│   │
│   ├── components/
│   │   ├── storefront/               # Buyer-facing UI components
│   │   │   ├── ProductCard.tsx
│   │   │   ├── ProductGrid.tsx
│   │   │   ├── CartDrawer.tsx
│   │   │   ├── CheckoutForm.tsx
│   │   │   ├── ClimateChecker.tsx
│   │   │   ├── CareCard.tsx
│   │   │   ├── BatchProvenance.tsx
│   │   │   ├── GrowthStageBadge.tsx
│   │   │   ├── ReviewList.tsx
│   │   │   ├── JournalFeed.tsx
│   │   │   ├── BundleBuilder.tsx
│   │   │   ├── SubscriptionPlans.tsx
│   │   │   ├── LoyaltyWidget.tsx
│   │   │   ├── RestockAlertForm.tsx
│   │   │   ├── SeasonalNotice.tsx
│   │   │   └── AcclimationSuggestion.tsx
│   │   ├── dashboard/                # Seller admin UI components
│   │   │   ├── SalesChart.tsx
│   │   │   ├── OrderTable.tsx
│   │   │   ├── ProductForm.tsx
│   │   │   ├── DispatchLogForm.tsx
│   │   │   ├── BatchManager.tsx
│   │   │   ├── CostCalculator.tsx
│   │   │   ├── KycReviewCard.tsx
│   │   │   └── DocExpiryAlert.tsx
│   │   └── shared/                   # Shared across both
│   │       ├── ui/                   # Buttons, inputs, modals, tables
│   │       └── layout/              # Nav, footer, sidebar
│   │
│   └── types/
│       ├── product.ts
│       ├── order.ts
│       ├── user.ts
│       └── ...
│
├── prisma/
│   ├── platform.prisma               # Platform DB schema
│   └── tenant.prisma                 # Per-tenant DB schema
│
├── docker/
│   ├── docker-compose.yml            # Full stack local dev
│   ├── docker-compose.prod.yml       # Production
│   ├── Dockerfile                    # Next.js app
│   ├── meilisearch/
│   ├── listmonk/
│   ├── umami/
│   └── redis/
│
├── scripts/
│   ├── provision-tenant.ts           # CLI tenant provisioning
│   ├── seed-taxonomy.ts              # Import taxonomy packs
│   └── migrate-tenants.ts            # Run migrations across all tenant DBs
│
├── public/
├── tailwind.config.ts
├── next.config.ts
├── package.json
├── tsconfig.json
└── README.md
```

---

## 7. Events System

Every meaningful action fires an event. Built-in handlers process core logic. Tenants connect external tools via webhooks.

### Event types

```typescript
// Commerce events
"order.created" | "order.confirmed" | "order.packed" | "order.shipped" |
"order.delivered" | "order.cancelled" | "payment.received" | "payment.failed" |
"refund.issued" |

// Catalog events
"product.created" | "product.published" | "product.updated" |
"product.archived" | "stock.changed" | "stock.zero" |

// Customer events
"customer.registered" | "customer.wholesale.applied" |
"customer.wholesale.approved" | "customer.kyc.verified" |

// Plant-specific events
"batch.created" | "batch.depleted" | "batch.contaminated" |
"cert.expiring" | "cert.expired" |

// Engagement events
"review.submitted" | "journal.submitted" |
"subscription.created" | "subscription.cancelled" |
"loyalty.tier.changed"
```

### Built-in handlers

| Event | Handler |
|-------|---------|
| `order.created` | Send confirmation email, create loyalty entry, update analytics |
| `order.packed` | Attach dispatch log, generate packing slip PDF |
| `order.shipped` | Send shipping email with tracking, attach phytosanitary docs |
| `stock.zero` | Fire restock alert emails, update product status |
| `stock.changed` | Sync Meilisearch index, decrement batch units |
| `batch.depleted` | Update batch status, alert admin |
| `batch.contaminated` | Create contamination record, alert admin |
| `cert.expiring` | Email admin 30/7/1 days before expiry |
| `payment.received` | Update order status, fire `order.confirmed` |
| `customer.registered` | Send welcome email, create Umami identity |
| `review.submitted` | Queue for moderation, email admin |

### Webhook delivery

Tenant configures webhook URLs in settings. For each event:
1. Build JSON payload
2. Sign with tenant's webhook secret (HMAC-SHA256)
3. POST to URL via BullMQ (retry 3x with exponential backoff)
4. Log delivery status in `ActivityLog`

---

## 8. Payment Gateway Abstraction

```typescript
interface PaymentGateway {
  name: string;
  createOrder(amount: Decimal, currency: string, metadata: OrderMeta): Promise<GatewayOrder>;
  verifyPayment(paymentId: string, signature: string): Promise<boolean>;
  refund(paymentId: string, amount: Decimal, reason?: string): Promise<RefundResult>;
  createSubscription(plan: SubPlan, customer: CustomerInfo): Promise<GatewaySub>;
  cancelSubscription(subId: string): Promise<void>;
  handleWebhook(body: unknown, headers: Headers): Promise<WebhookEvent>;
}
```

Implementations: `RazorpayGateway`, `StripeGateway`, `PayUGateway`, `CashfreeGateway`, `CodGateway`, `BankTransferGateway`.

Tenant selects active gateways in settings. Checkout page shows only enabled gateways.

---

## 9. Build Phases

| Phase | Scope | Depends on |
|-------|-------|-----------|
| **Phase 1: Platform Core** | Tenancy, auth, DB provisioning, self-service signup, basic storefront shell, branding, settings | — |
| **Phase 2: Catalog** | Products (CRUD, variations, attributes), categories, taxonomy packs, images (Sharp pipeline), Meilisearch, SEO (meta, JSON-LD, sitemaps), product feeds | Phase 1 |
| **Phase 3: Commerce** | Cart, checkout, payment gateway abstraction (all 6), shipping zones/methods/rates, tax calculation, coupons, auto-discounts | Phase 2 |
| **Phase 4: Orders & Fulfillment** | Order lifecycle, dispatch logging, invoices/packing slips (PDF), refunds, tracking, email notifications via Listmonk, abandoned cart recovery | Phase 3 |
| **Phase 5: Plant Domain** | Batches (lineage, contamination, QR labels), growth stages, seasonal blocking, climate zones, care cards (PDF), source badges, phytosanitary docs, live arrival guarantee, acclimation suggestions, TC cost calculator | Phase 2 |
| **Phase 6: Engagement** | Loyalty (4 tiers, ledger), subscriptions, bundles, reviews, grower journal, collection widget, restock alerts, wishlist, blog/CMS | Phase 4 |
| **Phase 7: B2B** | Wholesale portal, application/approval, KYC, tiered pricing, MOQ, invoice payment | Phase 4 |
| **Phase 8: Growth** | Umami analytics embed, sales dashboards, customer analytics, email campaigns (Listmonk), webhooks system, activity log, staff roles | Phase 4 |

Each phase ships a working, deployable increment. Phase 1-3 = functional store. Phase 5 = plant differentiator.

---

## 10. Deployment

### Docker Compose (production)

```yaml
services:
  app:            # Next.js (port 3000)
  postgres:       # Platform DB + tenant DBs
  redis:          # BullMQ + sessions
  meilisearch:    # Search engine
  listmonk:       # Email
  umami:          # Analytics
  traefik:        # Reverse proxy, SSL, subdomain routing
```

### Tenant isolation

- Each tenant DB is a separate PostgreSQL database on the same server (scales to hundreds)
- Connection pooling via Prisma's connection pool per tenant
- Meilisearch: separate index per tenant (prefixed by slug)
- Listmonk: separate subscriber list per tenant
- Umami: separate site per tenant

### Scaling path (when needed)

- Move tenant DBs to dedicated PostgreSQL instances
- Add read replicas for heavy-read tenants
- Horizontal scale Next.js behind load balancer
- Meilisearch cluster mode

---

## 11. Open Source

- License: MIT
- Repo: `phytocommerce-stack` (new, standalone)
- No proprietary dependencies
- Taxonomy packs shipped as seed data
- Climate zone data shipped as seed data (797 Indian PIN prefixes, 15 zones)
