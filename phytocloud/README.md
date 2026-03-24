# Phyto E-Commerce — SaaS Platform

Part of [PhytoLabs](https://phytolabs.in). Sign up at **yourshop.phytolabs.in**.

---

## Quick Start (Server Setup)

### 1. Start Traefik (reverse proxy + SSL)
```bash
cd phytocloud/traefik
cp /dev/null acme.json && chmod 600 acme.json
ACME_EMAIL=admin@phytolabs.in docker compose -f docker-compose.traefik.yml up -d
```

### 2. Start Provisioning API
```bash
cd phytocloud/api
cp .env.example .env
# Edit .env with real PayU keys, Mailgun key, etc.
npm install
npm start
```

### 3. Start Monitoring Stack
```bash
cd phytocloud/monitoring
docker compose -f docker-compose.monitoring.yml up -d
```

### 4. Set up Cron Jobs
```cron
# Healthcheck every 5 minutes
*/5 * * * * /opt/phyto-ecommerce-api/scripts/healthcheck.sh >> /var/log/phyto-healthcheck.log 2>&1

# Daily backup at 2am
0 2 * * * /opt/phyto-ecommerce-api/scripts/backup.sh >> /var/log/phyto-backup.log 2>&1
```

---

## Pricing

| Plan | Monthly | Transaction Fee | Domain |
|---|---|---|---|
| Subdomain | Rs 349 | +1% | `{slug}.carnivorousplants.in` |
| Custom Domain | Rs 499 | +2% | Customer's own domain |

---

## Key Endpoints

| Endpoint | Description |
|---|---|
| `POST /webhook/payu` | PayU payment webhook |
| `GET /status/:slug` | Public store status |
| `GET /status/check/:slug` | Check slug availability |
| `POST /provision/domain` | Set custom domain (post-signup) |
| `POST /provision/cancel` | Cancel subscription (internal) |
| `POST /provision/destroy` | Permanent deletion (internal) |

---

## Environment Variables

See `phytocloud/api/.env.example` for all required variables.

Critical ones:
- `PAYU_KEY`, `PAYU_SALT`, `PAYU_ENV` — PayU credentials
- `MAILGUN_API_KEY`, `MAILGUN_DOMAIN` — Email for subdomain tenants
- `API_SECRET` — Protects internal endpoints
- `SUBDOMAIN_BASE` — Default: `carnivorousplants.in`

---

## Architecture

See [docs/PHYTOCLOUD_SAAS_SPEC.md](../docs/PHYTOCLOUD_SAAS_SPEC.md) for the full spec.
