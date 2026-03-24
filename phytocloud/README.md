# Phyto E-Commerce — SaaS Platform

Part of [PhytoLabs](https://phytolabs.in). Sign up at **yourshop.phytolabs.in**.

---

## Architecture

```
Internet
  │  HTTPS (Traefik → Let's Encrypt)
  ▼
┌─────────────────────────────────────────────────┐
│           Spin (WebAssembly)                    │
│  /webhook/payu  → webhook component (TS)        │ ← validates PayU SHA512
│  /status/:slug  → status component (TS)         │ ← reads SQLite directly
│  /provision/... → provision component (TS)      │ ← auth-gated admin
│  /health        → health component (TS)         │
└─────────────────────┬───────────────────────────┘
                      │ outbound HTTP (localhost only)
                      ▼
┌─────────────────────────────────────────────────┐
│    Docker Sidecar (Node.js · 127.0.0.1:3001)   │
│  POST /payment-event  → provision / suspend     │
│  POST /suspend|resume|cancel|destroy            │ ← Docker socket access
│  Background jobs: pending poll, grace expiry    │ ← Mailgun emails
└─────────────────────┬───────────────────────────┘
                      │ docker compose
                      ▼
┌─────────────────────────────────────────────────┐
│  Tenant Containers (per customer)               │
│  PrestaShop 8 + MySQL 8 + Redis                 │
│  1.5 GB RAM / 2 vCPU limit                     │
│  {slug}.{your-domain}  OR  customer's own domain │
└─────────────────────────────────────────────────┘
```

**Why this split?**
- Spin runs the entire public HTTP surface — ~10ms cold start, WASM sandboxed
- Sidecar is the *only* Node.js process; it never touches the internet directly
- PayU hash verification happens in Spin before the sidecar sees any request

**Shared SQLite**: both Spin and the sidecar open the same file at `DB_PATH` (configured in `spin/runtime-config.toml` and `docker-sidecar/.env`). SQLite WAL mode handles concurrent access.

---

## Quick Start (Server Setup)

### Prerequisites
```bash
# Install Spin
curl -fsSL https://developer.fermyon.com/downloads/install.sh | bash

# Install js2wasm plugin (for TypeScript components)
spin plugin install js2wasm --yes

# Install Spin JS/TS templates
spin templates install --git https://github.com/fermyon/spin-js-sdk
```

### 1. Start Traefik
```bash
cd phytocloud/traefik
cp /dev/null acme.json && chmod 600 acme.json
ACME_EMAIL=admin@phytolabs.in docker compose -f docker-compose.traefik.yml up -d
```

### 2. Build Spin Components
```bash
cd phytocloud/spin

# Install deps for each component
for dir in webhook status provision health; do
  (cd "$dir" && npm install)
done

# Build all WASM components
spin build
```

### 3. Configure and Run Spin
```bash
cd phytocloud/spin
cp runtime-config.toml.example runtime-config.toml
# Edit runtime-config.toml with real PayU keys + SQLite path

spin up --runtime-config-file runtime-config.toml
```

### 4. Start Docker Sidecar
```bash
cd phytocloud/docker-sidecar
cp .env.example .env
# Edit .env — DB_PATH must match [sqlite_database.default].path in runtime-config.toml
npm install
npm start
```

### 5. Start Monitoring
```bash
cd phytocloud/monitoring
docker compose -f docker-compose.monitoring.yml up -d
```

### 6. Set Up Cron Jobs
```cron
*/5 * * * * /opt/phyto-ecommerce/phytocloud/api/scripts/healthcheck.sh >> /var/log/phyto-healthcheck.log 2>&1
0 2 * * * /opt/phyto-ecommerce/phytocloud/api/scripts/backup.sh >> /var/log/phyto-backup.log 2>&1
```

---

## Pricing

| Plan | Monthly | Transaction Fee | Domain |
|---|---|---|---|
| Subdomain | Rs 349 | +1% | `{slug}.{your-domain}` |
| Custom Domain | Rs 499 | +2% | Customer's own domain |

---

## Spin API Endpoints

| Endpoint | Auth | Description |
|---|---|---|
| `POST /webhook/payu` | PayU hash | PayU payment webhook |
| `GET /status/:slug` | None | Store status |
| `GET /status/check/:slug` | None | Slug availability |
| `POST /provision/domain` | `X-Api-Secret` | Set custom domain |
| `POST /provision/suspend` | `X-Api-Secret` | Suspend store |
| `POST /provision/resume` | `X-Api-Secret` | Resume store |
| `POST /provision/cancel` | `X-Api-Secret` | Cancel + schedule deletion |
| `POST /provision/destroy` | `X-Api-Secret` | Permanent destruction |
| `GET /provision/tenants` | `X-Api-Secret` | List active tenants |
| `GET /health` | None | Platform health |

---

## Key Config

**`spin/runtime-config.toml`** (gitignored — copy from `.example`):
- `[sqlite_database.default].path` — shared SQLite file path
- `[variables]` — PayU keys, subdomain base, sidecar URL, API secret

**`docker-sidecar/.env`** (gitignored — copy from `.example`):
- `DB_PATH` — must match SQLite path above
- `MAILGUN_API_KEY`, `MAILGUN_DOMAIN`
- Docker compose paths

---

See [docs/PHYTOCLOUD_SAAS_SPEC.md](../docs/PHYTOCLOUD_SAAS_SPEC.md) for full spec.
