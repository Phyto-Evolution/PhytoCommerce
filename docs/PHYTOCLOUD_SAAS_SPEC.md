# Phyto E-Commerce — SaaS Platform Spec
## Brief to Claude Code for Full Implementation

---

## What We Are Building

A **self-service SaaS platform** that lets anyone spin up their own fully-functional
PrestaShop ecommerce store in one click after payment. No control panels, no limits,
no ongoing interference — just sign up, pay, and sell.

The platform is part of **PhytoLabs** (`phytolabs.in`). The signup/landing page lives at
`yourshop.phytolabs.in`. It is purpose-built for plant sellers — carnivorous plants,
succulents, rare aroids, tissue-culture growers — who are not technically savvy and need
the easiest possible path to a professional online store.

---

## Business Rules (Non-Negotiable)

| Item | Subdomain Plan | Custom Domain Plan |
|---|---|---|
| Monthly price | Rs 349 / month (recurring) | Rs 499 / month (recurring) |
| Transaction fee | +1% of each sale | +2% of each sale |
| Subdomain | `{slug}.carnivorousplants.in` | Customer's own domain |
| Resource per tenant | 1.5 GB RAM, 2 vCPU (dynamic) | 1.5 GB RAM, 2 vCPU (dynamic) |
| Products | Unlimited | Unlimited |
| Restrictions | None — no product caps, no bandwidth throttle, no category locks | |
| Provisioning | Fully automated — zero human touch after payment confirmed | |
| Downtime tolerance | Near-zero — SLA matters | |

---

## Architecture Overview

```
┌────────────────────────────────────────────────────────┐
│              yourshop.phytolabs.in                     │
│   Landing + signup page (static HTML on phytolabs.in)  │
│   Plan selector → PayU payment → provisioning API      │
│   Feature cards auto-update from commits via Actions   │
└──────────────────────┬─────────────────────────────────┘
                       │ HTTPS POST /provision
                       ▼
┌────────────────────────────────────────────────────────┐
│              Provisioning API (Node.js)                │
│   Validates PayU webhook → spins Docker compose        │
│   Assigns subdomain → issues SSL → sends welcome email │
└──────────────────────┬─────────────────────────────────┘
                       │ docker compose up
                       ▼
┌────────────────────────────────────────────────────────┐
│                    Host Server (existing VPS)          │
│   Traefik (reverse proxy, auto SSL via Let's Encrypt)  │
│   ┌────────────────────────────────────────────────┐   │
│   │  Tenant Container Stack (per customer)         │   │
│   │  - PrestaShop 8.x (1.5GB RAM / 2 vCPU limit)  │   │
│   │  - MySQL 8 (per-tenant isolated DB)            │   │
│   │  - Redis (session/cache)                       │   │
│   │  Subdomain plan: {slug}.carnivorousplants.in   │   │
│   │  Custom plan:    customer's own domain         │   │
│   └────────────────────────────────────────────────┘   │
│   Uptime Kuma (monitoring all tenant containers)       │
│   Grafana + Prometheus (resource usage dashboards)     │
└────────────────────────────────────────────────────────┘
```

---

## Stack Decisions (Claude Code: use exactly these)

### Infrastructure
- **Docker + Docker Compose** per tenant (one compose file generated per customer)
- **Traefik v3** as reverse proxy — auto-discovers containers via labels, auto-provisions
  Let's Encrypt SSL for each subdomain without restart
- **Host**: Existing VPS (already hosts other sites; max 1.5 GB RAM + 2 vCPU per tenant)

### Provisioning API
- **Node.js (Express)** — lightweight, fast to spin up, good Docker SDK support
- **PayU** for payment verification (webhook signature validation via SHA512)
- **dockerode** npm package for programmatic Docker Compose management
- **nodemailer + Mailgun** for sending emails to subdomain-plan tenants
  - Subdomain tenants: Mailgun configured on `mg.carnivorousplants.in`
  - Custom domain tenants: customer self-configures their own SMTP on rollout
- **SQLite** (via better-sqlite3) for the provisioning DB — tracks tenants, status,
  PayU transaction IDs, subdomain assignments, plan type, fee percentages

### Payment — PayU
- **PayU Subscriptions (Standing Instructions)** for recurring monthly billing
- **PayU webhook** fires on each payment event — success, failure, refund
- Webhook hash verification: `sha512(SALT|status|||||||||||udf5|...|txnid|KEY)`
- Payment events drive store lifecycle:
  - `payment_success` → activate / resume store
  - `payment_failed` / `failure` → suspend after grace period
  - cancellation → schedule destruction
- **Transaction fee collection**: PayU webhook on `payment_success` calculates 1% or 2%
  of order value from tenant's store and logs it to SQLite for manual/automated collection
- Test credentials: `PAYU_KEY=gtKFFx`, `PAYU_SALT=eCwWELxi`, `PAYU_ENV=test`

### Domains
- **Subdomain plan**: `{slug}.carnivorousplants.in` — auto-provisioned via Traefik labels
- **Custom domain plan**: customer points their domain DNS to the server IP; API validates
  DNS propagation and reconfigures Traefik labels; Let's Encrypt re-provisions

### Email
- **Subdomain plan tenants**: Mailgun on `mg.carnivorousplants.in`
  (configured server-side, fully managed by PhytoLabs)
- **Custom domain tenants**: customer sets up their own email/SMTP during store configuration
  wizard after login; API provides SMTP settings page in their admin
- Variables: `MAILGUN_API_KEY`, `MAILGUN_DOMAIN` (= `mg.carnivorousplants.in`)

### Monitoring / Self-Healing
- **Docker restart policies**: `restart: unless-stopped` on all tenant containers
- **Uptime Kuma**: monitors every tenant subdomain every 60s
- **Healthcheck scripts**: cron job every 5 min checks all running containers
- **Prometheus + Grafana**: tracks per-tenant CPU/RAM usage
- **Automated daily backups**: each tenant DB backed up to `/backups/` with 7-day retention;
  S3-compatible (Backblaze B2) offsite copy nightly

---

## Repo Structure to Create

```
PhytoCommerce/
├── docs/
│   └── PHYTOCLOUD_SAAS_SPEC.md         ← this file
│
├── phytocloud/                          ← NEW: SaaS platform source code
│   ├── api/                             ← Provisioning API (Node.js)
│   │   ├── package.json
│   │   ├── server.js                    ← Express entry point
│   │   ├── .env.example
│   │   ├── routes/
│   │   │   ├── webhook.js               ← PayU webhook handler
│   │   │   ├── provision.js             ← Spin up / tear down tenant
│   │   │   └── status.js               ← Tenant status endpoint
│   │   ├── services/
│   │   │   ├── docker.js               ← dockerode wrapper
│   │   │   ├── payu.js                 ← PayU hash verification + subscription mgmt
│   │   │   ├── mailer.js               ← Mailgun welcome + alert emails
│   │   │   └── db.js                   ← SQLite tenant registry
│   │   ├── templates/
│   │   │   └── docker-compose.tpl.yml  ← Per-tenant compose template
│   │   └── scripts/
│   │       ├── healthcheck.sh          ← Cron health monitor
│   │       └── backup.sh               ← Daily DB backup script
│   │
│   ├── monitoring/
│   │   ├── docker-compose.monitoring.yml
│   │   ├── prometheus.yml
│   │   └── grafana/dashboards/tenant-overview.json
│   │
│   └── traefik/
│       ├── docker-compose.traefik.yml
│       ├── traefik.yml
│       └── acme.json                    ← gitignored; Let's Encrypt certs
│
├── modules/                             ← existing PhytoCommerce modules
│   └── ...
│
└── .github/workflows/
    ├── feature-cards.yml                ← Auto-regenerates features.json
    └── deploy-api.yml                   ← SSH deploy to server on push
```

---

## Tenant Provisioning Flow

```
1. Customer visits yourshop.phytolabs.in
   → selects plan (subdomain Rs 349 or custom domain Rs 499)
   → enters store name (slug), email, phone
   → PayU payment page opens

2. PayU processes payment, fires webhook to /api/webhook/payu

3. Provisioning API:
   a. Validates PayU hash (SHA512)
   b. Checks slug is unique → reserves it in SQLite
   c. Determines plan type → sets monthly_amount + txn_fee_pct
   d. Generates docker-compose.{slug}.yml from template
      - Sets container_name, network aliases, domain labels
      - Resource limits: mem_limit: 1536m, cpus: '2.0'
   e. Generates MySQL password, PrestaShop admin credentials
   f. Runs: docker compose -f docker-compose.{slug}.yml up -d
   g. Waits for PrestaShop healthcheck (polls /health, timeout 5min)
   h. Configures Traefik labels → domain goes live with SSL
   i. Subdomain plan: sends Mailgun welcome email with store URL + admin creds
      Custom plan: sends welcome email (via Mailgun) with setup wizard link
   j. Updates SQLite: status=active, provisioned_at=now

4. If payment fails:
   → Suspend: docker compose pause
   → Email: "payment failed, store suspended"
   → Grace: 7 days to pay
   → After grace: docker compose down (data preserved in volume)

5. If subscription cancelled:
   → docker compose down
   → Schedule volume deletion after 30 days
   → Email data export link before deletion
```

---

## Security Requirements

- PayU webhook validates hash header — reject non-matching. Log invalid attempts.
- Tenant containers in isolated Docker networks — no cross-tenant communication
- MySQL root password randomly generated per tenant (stored encrypted in SQLite)
- PrestaShop admin path randomised per tenant: `/admin-{random8}`
- Rate limit provisioning API: max 5 requests/min per IP
- All API endpoints behind HTTPS (Traefik SSL termination)
- Provisioning API not exposed publicly except `/webhook` and `/status/{slug}`
- `acme.json`, `.env` files, SQLite DB — all gitignored, never committed

---

## PayU Integration Details

### Environment Variables
```
PAYU_KEY=gtKFFx                   # test; swap for prod key
PAYU_SALT=eCwWELxi                 # test; swap for prod salt
PAYU_ENV=test                     # 'test' | 'prod'
PAYU_WEBHOOK_SECRET=              # optional additional layer
```

### PayU URLs
- Test: `https://test.payu.in/_payment`
- Prod: `https://secure.payu.in/_payment`

### Hash Verification (response from PayU)
```
sha512(SALT|status|||||||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY)
```

### Subscription / Standing Instructions
PayU SI (Standing Instructions) API handles recurring monthly billing.
Each tenant gets a `si_token` stored in SQLite. Monthly charges fire webhook on success/failure.

---

## What Claude Code Should Deliver (in order)

### Phase 1 — Infrastructure
- [x] `phytocloud/traefik/` — Traefik v3 config with Let's Encrypt
- [x] `phytocloud/api/templates/docker-compose.tpl.yml` — tenant compose template
- [x] `phytocloud/api/scripts/healthcheck.sh`
- [x] `phytocloud/api/scripts/backup.sh`

### Phase 2 — Provisioning API (current)
- [ ] Full Express API with all routes and services
- [ ] SQLite schema for tenant registry (two plan types)
- [ ] PayU webhook handler (all payment event types)
- [ ] Docker provisioning service (create, pause, resume, destroy)
- [ ] Mailgun welcome email (subdomain tenants)
- [ ] Suspension + cancellation email templates

### Phase 3 — Landing Page (yourshop.phytolabs.in)
- [ ] `docs/index.html` — creative minimal, botanical, mobile-first
- [ ] Plan selector UI (subdomain Rs 349 vs custom domain Rs 499)
- [ ] PayU Checkout integration
- [ ] Feature cards from `features.json`

### Phase 4 — GitHub Actions
- [ ] `.github/workflows/feature-cards.yml`
- [ ] `.github/workflows/deploy-api.yml`

### Phase 5 — Monitoring
- [ ] `phytocloud/monitoring/` — Uptime Kuma + Prometheus + Grafana

---

## Non-Negotiable Quality Gates

- Zero hardcoded secrets — all via `.env`; `.env.example` with placeholders committed
- All `.env` files in `.gitignore`
- Every tenant container must have Docker healthchecks defined
- PrestaShop fully configured via env vars at boot — no manual setup wizard
- Provisioning is idempotent — running provision twice for same slug must be safe
- Webhook responds 200 to PayU within 3 seconds (heavy work async/queued)
- All financial events (payment received, suspended, cancelled) logged to SQLite with
  timestamp, amount, PayU txnid — non-negotiable for audit

---

## Notes

- Platform sits alongside existing PhytoCommerce modules repo
- Existing modules auto-appear as feature cards on the landing page
- Dev server: `dev.carnivorousplants.in` — production server is the existing VPS
- `phyto_climate_zone` warning fix already merged — do not touch that file
- Module development continues in parallel in `modules/`
