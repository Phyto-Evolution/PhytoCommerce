# PhytoCommerce Cloud — SaaS Platform Spec
## Brief to Claude Code for Full Implementation

---

## What We Are Building

A **self-service SaaS platform** that lets anyone spin up their own fully-functional
PrestaShop ecommerce store in one click after payment. No control panels, no limits,
no ongoing interference — just sign up, pay, and sell.

The landing/signup site is hosted on **GitHub Pages** (same repo — source code lives
here so feature cards auto-update when the codebase gets new modules or features).

---

## Business Rules (Non-Negotiable)

| Item | Value |
|---|---|
| Price | Rs 499 / month (recurring) |
| Transaction fee | +2% of each sale processed through their store |
| Resource per tenant | 1.5 GB RAM, 2 vCPU cores |
| Products | Unlimited |
| Restrictions | None — no product caps, no bandwidth throttle, no category locks |
| Provisioning | Fully automated — zero human touch after payment confirmed |
| Downtime tolerance | Near-zero — if a tenant site breaks, it costs us money and reputation (SLA matters) |

---

## Architecture Overview

```
┌────────────────────────────────────────────────────────┐
│                  GitHub Pages                          │
│          Landing page (docs/ or gh-pages)              │
│   Signup form → Razorpay payment → provisioning API    │
│   Feature cards auto-update from commits via Actions   │
└──────────────────────┬─────────────────────────────────┘
                       │ HTTPS POST /provision
                       ▼
┌────────────────────────────────────────────────────────┐
│              Provisioning API (Node.js)                │
│   Validates Razorpay webhook → spins Docker compose    │
│   Assigns subdomain → issues SSL → sends welcome email │
└──────────────────────┬─────────────────────────────────┘
                       │ docker compose up
                       ▼
┌────────────────────────────────────────────────────────┐
│                    Host Server                         │
│   Traefik (reverse proxy, auto SSL via Let's Encrypt)  │
│   ┌────────────────────────────────────────────────┐   │
│   │  Tenant Container Stack (per customer)         │   │
│   │  - PrestaShop 8.x (1.5GB RAM / 2 vCPU limit)  │   │
│   │  - MySQL 8 (per-tenant isolated DB)            │   │
│   │  - Redis (session/cache)                       │   │
│   │  Subdomain: {store-name}.phytocloud.in         │   │
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
- **Host**: Hetzner CX52 or equivalent (8 vCPU, 16 GB RAM — supports ~8-10 tenants
  comfortably; scale horizontally as needed)

### Provisioning API
- **Node.js (Express)** — lightweight, fast to spin up, good Docker SDK support
- **Razorpay Node SDK** for payment verification (webhook signature validation)
- **dockerode** npm package for programmatic Docker Compose management
- **nodemailer** for sending welcome emails to new tenants
- **SQLite** (via better-sqlite3) for the provisioning DB — tracks tenants, status,
  Razorpay subscription IDs, subdomain assignments

### Payment
- **Razorpay Subscriptions API** for recurring Rs 499/month billing
- **Razorpay webhook** (`subscription.charged`, `subscription.halted`, `subscription.cancelled`)
  drives site lifecycle — charged = active, halted = suspended, cancelled = destroyed
- **2% transaction fee**: collect via Razorpay's Route/split settlement or via a
  separate webhook on `payment.captured` that calculates 2% of order value and
  logs it (actual collection method TBD by finance — build the hook infrastructure now)

### Domains
- **Default**: `{customer-chosen-slug}.phytocloud.in` (subdomain, auto-provisioned)
- **Custom domain**: Phase 2 — customer adds CNAME, API validates and reconfigures
  Traefik labels, Let's Encrypt re-provisions. Scaffold the endpoint now, implement later.

### Monitoring / Self-Healing
- **Docker restart policies**: `restart: unless-stopped` on all tenant containers
- **Uptime Kuma**: monitors every tenant subdomain every 60s; auto-alerts via email +
  Telegram on failure
- **Healthcheck scripts**: cron job every 5 min checks all running containers; if a
  tenant container is unhealthy, it auto-restarts and logs the incident
- **Prometheus + Grafana**: tracks per-tenant CPU/RAM usage; alerts if any tenant
  exceeds 90% of their 1.5GB allocation (so we can upsell or rebalance before it breaks)
- **Automated daily backups**: each tenant DB backed up to a local `/backups/` volume
  with 7-day retention; S3-compatible (Backblaze B2 or Wasabi) offsite copy nightly

### GitHub Pages (Landing Site)
- **Plain HTML + Tailwind CSS** (via CDN — no build step, instant edits)
- **`docs/` folder in this repo** — GitHub Pages serves from `docs/`
- **`docs/data/features.json`** — JSON file listing current platform features/modules
- **GitHub Actions workflow**: on every push to `main`, reads the PhytoCommerce module
  list + release notes and regenerates `features.json` automatically
- The landing page JS reads `features.json` at load time and renders feature cards
  dynamically — so every new module we ship appears on the site with zero manual work
- Signup form on the page submits to the provisioning API endpoint directly
- Razorpay payment widget embedded inline (Razorpay Hosted Checkout or Checkout.js)

---

## Repo Structure to Create

```
PhytoCommerce/                          ← existing repo
├── docs/                               ← GitHub Pages root
│   ├── index.html                      ← Landing page
│   ├── assets/
│   │   ├── css/style.css
│   │   └── js/
│   │       ├── main.js                 ← Feature card renderer
│   │       └── signup.js              ← Razorpay + signup form logic
│   ├── data/
│   │   └── features.json              ← Auto-generated by GitHub Action
│   └── CNAME                          ← If using custom domain for GH Pages
│
├── phytocloud/                         ← NEW: SaaS platform source code
│   ├── api/                            ← Provisioning API (Node.js)
│   │   ├── package.json
│   │   ├── server.js                   ← Express entry point
│   │   ├── routes/
│   │   │   ├── webhook.js              ← Razorpay webhook handler
│   │   │   ├── provision.js            ← Spin up / tear down tenant
│   │   │   └── status.js              ← Tenant status endpoint
│   │   ├── services/
│   │   │   ├── docker.js              ← dockerode wrapper
│   │   │   ├── razorpay.js            ← Payment verification + subscription mgmt
│   │   │   ├── mailer.js              ← Welcome + alert emails
│   │   │   └── db.js                  ← SQLite tenant registry
│   │   ├── templates/
│   │   │   ├── docker-compose.tpl.yml ← Per-tenant compose template
│   │   │   └── traefik-labels.tpl.yml ← Dynamic Traefik config template
│   │   └── scripts/
│   │       ├── healthcheck.sh         ← Cron health monitor
│   │       └── backup.sh              ← Daily DB backup script
│   │
│   ├── monitoring/                    ← Uptime Kuma + Prometheus configs
│   │   ├── docker-compose.monitoring.yml
│   │   ├── prometheus.yml
│   │   └── grafana/
│   │       └── dashboards/
│   │           └── tenant-overview.json
│   │
│   └── traefik/                       ← Traefik config
│       ├── docker-compose.traefik.yml
│       ├── traefik.yml
│       └── acme.json                  ← Let's Encrypt certs (gitignored)
│
├── modules/                           ← existing PhytoCommerce modules
│   └── ...
│
└── .github/
    └── workflows/
        ├── feature-cards.yml          ← Auto-regenerates docs/data/features.json
        └── deploy-api.yml             ← SSH deploy to server on push to main
```

---

## Tenant Provisioning Flow (Step by Step)

```
1. Customer fills signup form on GitHub Pages site
   → enters store name (slug), email, password

2. Razorpay Checkout.js opens
   → customer pays Rs 499
   → Razorpay subscription created

3. Razorpay fires `subscription.charged` webhook to /api/webhook/razorpay

4. Provisioning API:
   a. Validates webhook signature (HMAC-SHA256)
   b. Checks slug is unique → reserves it in SQLite
   c. Generates docker-compose.{slug}.yml from template
      - Sets container_name, network aliases, subdomain labels
      - Clamps resources: mem_limit: 1536m, cpus: '2.0'
   d. Generates MySQL password, PrestaShop admin credentials
   e. Runs: docker compose -f docker-compose.{slug}.yml up -d
   f. Waits for PrestaShop healthcheck to pass (polls /api/health, timeout 5min)
   g. Configures Traefik labels → subdomain goes live with SSL
   h. Sends welcome email with store URL + admin login credentials
   i. Updates SQLite: status=active, provisioned_at=now

5. If payment halted (subscription.halted webhook):
   → Suspend tenant: docker compose pause
   → Email customer "payment failed, store suspended"
   → Grace period: 7 days to pay
   → After grace: docker compose down (data preserved in volume)

6. If subscription cancelled:
   → docker compose down
   → Schedule volume deletion after 30 days (data retention policy)
   → Email with data export link before deletion
```

---

## Security Requirements

- Razorpay webhook endpoint validates `X-Razorpay-Signature` header — reject anything that
  doesn't match. Log and alert on invalid signature attempts.
- Tenant containers run in isolated Docker networks — no cross-tenant communication possible
- MySQL root password randomly generated per tenant (stored encrypted in SQLite)
- PrestaShop admin path randomised per tenant (not `/admin` — generate `/admin-{random8}`)
- Rate limit provisioning API: max 5 requests/min per IP
- All API endpoints behind HTTPS (Traefik handles SSL termination)
- Provisioning API never exposed publicly except the `/webhook` and `/status/{slug}` endpoints
- `acme.json` (Let's Encrypt certs), `.env` files, SQLite DB — all gitignored, never committed

---

## GitHub Pages Landing Page Requirements

### Content / Copy
- Hero: "Your store. Your rules. Online in 60 seconds."
- Pricing: Rs 499/month + 2% per transaction. No hidden fees. Unlimited products.
- Feature cards section (auto-populated from `features.json`)
- How it works: 3 steps — Sign up → Pay → Sell
- FAQ section addressing "what happens if I miss payment", "can I use my own domain", etc.
- CTA button: "Start Your Store — Rs 499/month"

### Feature Cards Auto-Update (GitHub Actions)
Workflow trigger: push to `main`
Script reads:
- `modules/` directory — each module folder = one feature card (uses module name + description from its main PHP file or a `module.json` metadata file)
- `CHANGELOG.md` (if present) — latest 3 entries shown as "What's new"
Writes to `docs/data/features.json`
GitHub Pages rebuild picks it up immediately

---

## Immediate Open Questions for Claude Code to Resolve Before Writing Code

1. **Platform domain**: What domain is `phytocloud.in` or similar registered on?
   If not registered, pick a placeholder like `phytocommerce.cloud` and make it
   configurable via a single `.env` variable (`PLATFORM_DOMAIN`).

2. **Server access for deploy**: The GitHub Actions deploy workflow needs SSH access
   to the production server. Generate an SSH keypair, document where to add the
   public key, and store private key as a GitHub secret (`SERVER_SSH_KEY`).

3. **Razorpay credentials**: Need `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`,
   `RAZORPAY_WEBHOOK_SECRET` — these go in `.env`, never hardcoded.

4. **SMTP for emails**: Need an SMTP config for welcome and alert emails.
   Recommend Mailgun India or Brevo (Sendinblue) free tier to start.
   Variables: `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`, `FROM_EMAIL`.

5. **Backup storage**: For S3-compatible offsite backups, use Backblaze B2 (cheapest).
   Variables: `B2_KEY_ID`, `B2_APP_KEY`, `B2_BUCKET_NAME`.

---

## What Claude Code Should Deliver (in order)

### Phase 1 — Infrastructure (do first)
- [ ] `phytocloud/traefik/` — full Traefik v3 config with Let's Encrypt, dashboard disabled in prod
- [ ] `phytocloud/api/templates/docker-compose.tpl.yml` — tenant compose template with all resource limits
- [ ] `phytocloud/api/scripts/healthcheck.sh` — cron-ready health monitor
- [ ] `phytocloud/api/scripts/backup.sh` — daily backup script

### Phase 2 — Provisioning API
- [ ] Full Express API with all routes and services listed above
- [ ] SQLite schema for tenant registry
- [ ] Razorpay subscription webhook handler (all subscription event types)
- [ ] Docker provisioning service (create, pause, resume, destroy)
- [ ] Welcome email template (HTML)
- [ ] Suspension + cancellation email templates

### Phase 3 — GitHub Pages Landing Site
- [ ] `docs/index.html` — full landing page, mobile responsive, Tailwind via CDN
- [ ] `docs/assets/js/main.js` — feature card renderer from `features.json`
- [ ] `docs/assets/js/signup.js` — signup form + Razorpay Checkout.js integration
- [ ] `docs/data/features.json` — initial version, manually seeded

### Phase 4 — GitHub Actions
- [ ] `.github/workflows/feature-cards.yml` — auto-regenerates `features.json` on push
- [ ] `.github/workflows/deploy-api.yml` — SSH deploy provisioning API to server on push

### Phase 5 — Monitoring
- [ ] `phytocloud/monitoring/docker-compose.monitoring.yml` — Uptime Kuma + Prometheus + Grafana
- [ ] `phytocloud/monitoring/prometheus.yml` — scrape configs for all tenant containers
- [ ] Grafana dashboard JSON for tenant resource overview

---

## Non-Negotiable Quality Gates

- Zero hardcoded secrets anywhere — all via `.env`
- All `.env` files in `.gitignore` — provide `.env.example` with placeholder values
- Every tenant container must have Docker healthchecks defined
- PrestaShop must be fully configured (shop name, country, currency INR, admin account)
  via CLI/environment variables at container boot — no manual setup wizard
- Provisioning must be idempotent — running provision twice for same slug must be safe
- Webhook must respond 200 to Razorpay within 3 seconds (do heavy work async/queued)
- All financial events (payment received, subscription created, suspended, cancelled)
  must be logged to SQLite with timestamp, amount, Razorpay IDs — non-negotiable for audit

---

## Notes from Previous Context

- This platform sits alongside the existing PhytoCommerce modules repo
- Existing modules (phyto_climate_zone, phyto_care_card, phyto_seasonal_availability, etc.)
  should auto-appear as feature cards on the landing page — they demonstrate the quality
  and richness of the PrestaShop ecosystem customers will get
- The `phyto_climate_zone` module warning (`zone_reference` undefined array key) is already
  fixed — do not touch that file
- Dev server is at `dev.carnivorousplants.in` — production server is separate (TBD)
- All module work continues in parallel in `modules/` — this SaaS platform is additive,
  not a replacement for module development
