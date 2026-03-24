'use strict';

/**
 * Phyto E-Commerce — Docker Sidecar
 *
 * A minimal HTTP server bound to 127.0.0.1 ONLY — never exposed via Traefik.
 * Spin components call this after doing all security validation (hash checks, auth).
 *
 * Why Node.js and not Spin?
 *   WASM sandboxes cannot access the Docker socket — this is the one
 *   irreplaceable privileged process. Everything that CAN run in Spin does.
 *
 * Endpoints (all POST, all trusted — Spin already validated secrets):
 *   POST /payment-event   — handle PayU payment success/failure → provision/suspend
 *   POST /suspend         — pause a tenant's containers
 *   POST /resume          — unpause a suspended tenant
 *   POST /cancel          — tear down containers + schedule 30-day deletion
 *   POST /destroy         — permanent deletion (after retention period)
 */

require('dotenv').config();

const express     = require('express');
const db          = require('./services/db');
const provisioner = require('./services/provisioner');

const app  = express();
const PORT = parseInt(process.env.PORT || '3001', 10);

app.use(express.json());

// ── Helper ────────────────────────────────────────────────────────────────────

function ok(res, data = {}) { res.json({ ok: true, ...data }); }
function fail(res, msg, code = 400) { res.status(code).json({ ok: false, error: msg }); }

// Accept-immediately wrapper: respond 202, run heavy work async
function async202(handler) {
  return (req, res) => {
    res.status(202).json({ ok: true, queued: true });
    handler(req.body).catch(err =>
      console.error('[sidecar] Unhandled error:', err.message)
    );
  };
}

// ── POST /payment-event ───────────────────────────────────────────────────────
// Spin webhook component sends verified PayU event data here.

app.post('/payment-event', async202(async (body) => {
  const { status, slug, plan, email, phone, txnid, mihpayid, amount,
          subdomain, monthly_amount, txn_fee_pct } = body;

  if (!slug || !status) return;

  let tenant = db.getTenantBySlug(slug);

  if (status === 'success') {
    if (!tenant) {
      // First ever payment — Spin already wrote the pending record, just fetch it
      tenant = db.getTenantBySlug(slug);
    }

    if (!tenant) {
      console.error(`[sidecar] payment-event: no tenant record found for ${slug} — was Spin webhook writing to same DB?`);
      return;
    }

    if (tenant.status === 'suspended') {
      // Recurring payment after suspension
      await provisioner.resumeTenant(slug);
    } else if (tenant.status === 'pending' || tenant.status === 'error') {
      // New store — provision it
      await provisioner.provisionNew(tenant);
    } else {
      // Recurring on active store — just log (already logged by Spin)
      console.log(`[sidecar] Recurring payment for active tenant: ${slug}`);
    }

  } else if (status === 'failure' || status === 'pending') {
    if (tenant && tenant.status === 'active') {
      await provisioner.suspendTenant(slug);
    }
  }
}));

// ── POST /suspend ─────────────────────────────────────────────────────────────

app.post('/suspend', async202(async ({ slug }) => {
  if (!slug) return;
  const t = db.getTenantBySlug(slug);
  if (!t) { console.warn(`[sidecar] /suspend: unknown slug ${slug}`); return; }
  await provisioner.suspendTenant(slug);
}));

// ── POST /resume ──────────────────────────────────────────────────────────────

app.post('/resume', async202(async ({ slug }) => {
  if (!slug) return;
  const t = db.getTenantBySlug(slug);
  if (!t) { console.warn(`[sidecar] /resume: unknown slug ${slug}`); return; }
  await provisioner.resumeTenant(slug);
}));

// ── POST /cancel ──────────────────────────────────────────────────────────────

app.post('/cancel', async202(async ({ slug }) => {
  if (!slug) return;
  const t = db.getTenantBySlug(slug);
  if (!t) { console.warn(`[sidecar] /cancel: unknown slug ${slug}`); return; }
  await provisioner.cancelTenant(slug);
}));

// ── POST /destroy ─────────────────────────────────────────────────────────────

app.post('/destroy', async202(async ({ slug }) => {
  if (!slug) return;
  const t = db.getTenantBySlug(slug);
  if (!t) { console.warn(`[sidecar] /destroy: unknown slug ${slug}`); return; }
  if (t.status !== 'cancelled') {
    console.warn(`[sidecar] /destroy: ${slug} is not cancelled — refusing`);
    return;
  }
  await provisioner.destroyTenant(slug);
}));

// ── Background jobs ───────────────────────────────────────────────────────────

// Poll for pending tenants that Spin wrote but sidecar hasn't processed yet
// (safety net in case the /payment-event call from Spin failed)
setInterval(async () => {
  const pending = db.getPendingTenants();
  for (const t of pending) {
    console.log(`[jobs] Found pending tenant: ${t.slug} — provisioning`);
    await provisioner.provisionNew(t).catch(err =>
      console.error(`[jobs] provisionNew failed for ${t.slug}:`, err.message)
    );
  }
}, 60 * 1000); // every 1 minute

// Grace period expiry
setInterval(async () => {
  const expired = db.getTenantsForGraceExpiry();
  for (const t of expired) {
    console.log(`[jobs] Grace expired for ${t.slug} — cancelling`);
    await provisioner.cancelTenant(t.slug).catch(err =>
      console.error(`[jobs] cancel failed for ${t.slug}:`, err.message)
    );
  }
}, 60 * 60 * 1000); // every hour

// Permanent deletion
setInterval(async () => {
  const toDelete = db.getTenantsForDeletion();
  for (const t of toDelete) {
    console.log(`[jobs] Retention expired for ${t.slug} — destroying`);
    await provisioner.destroyTenant(t.slug).catch(err =>
      console.error(`[jobs] destroy failed for ${t.slug}:`, err.message)
    );
  }
}, 6 * 60 * 60 * 1000); // every 6 hours

// ── Start (localhost only) ────────────────────────────────────────────────────

app.listen(PORT, '127.0.0.1', () => {
  console.log(`[sidecar] Listening on 127.0.0.1:${PORT}`);
  console.log(`[sidecar] DB: ${process.env.DB_PATH}`);
  console.log(`[sidecar] Compose dir: ${process.env.TENANT_COMPOSE_DIR}`);
});
