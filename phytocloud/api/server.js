'use strict';

require('dotenv').config();

const express    = require('express');
const rateLimit  = require('express-rate-limit');
const crypto     = require('crypto');

const webhookRoutes   = require('./routes/webhook');
const statusRoutes    = require('./routes/status');
const provisionRoutes = require('./routes/provision');
const db              = require('./services/db');
const dockerSvc       = require('./services/docker');
const mailer          = require('./services/mailer');

const app  = express();
const PORT = process.env.PORT || 3000;

// ── Global middleware ─────────────────────────────────────────────────────────

app.set('trust proxy', 1);
app.use(express.json());

// Rate limiting — 5 requests/min per IP for all routes
const limiter = rateLimit({
  windowMs: 60 * 1000,
  max: 60, // generous for status checks
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

// Stricter limit for webhook endpoint
const webhookLimiter = rateLimit({
  windowMs: 60 * 1000,
  max: 20,
  standardHeaders: true,
  legacyHeaders: false,
});

// ── Routes ────────────────────────────────────────────────────────────────────

app.use('/webhook', webhookLimiter, webhookRoutes);
app.use('/status',  statusRoutes);
app.use('/provision', provisionRoutes);

// Health check for this API itself
app.get('/health', (req, res) => {
  res.json({ ok: true, ts: new Date().toISOString() });
});

// ── Background jobs ───────────────────────────────────────────────────────────

// Check grace-period-expired tenants every hour
setInterval(async () => {
  const expired = db.getTenantsForGracePeriodCheck();
  for (const tenant of expired) {
    console.log(`[jobs] Grace period expired for ${tenant.slug} — tearing down`);
    try {
      await dockerSvc.teardownTenant(tenant.slug);
      db.updateTenant(tenant.slug, { status: 'cancelled' });
    } catch (err) {
      console.error(`[jobs] teardown failed for ${tenant.slug}:`, err.message);
    }
  }
}, 60 * 60 * 1000);

// Check tenants scheduled for permanent deletion every 6 hours
setInterval(async () => {
  const toDelete = db.getTenantsForDeletion();
  for (const tenant of toDelete) {
    console.log(`[jobs] Permanently destroying ${tenant.slug}`);
    try {
      await dockerSvc.destroyTenant(tenant.slug);
      db.updateTenant(tenant.slug, { delete_at: null });
    } catch (err) {
      console.error(`[jobs] destroy failed for ${tenant.slug}:`, err.message);
    }
  }
}, 6 * 60 * 60 * 1000);

// ── Start ─────────────────────────────────────────────────────────────────────

app.listen(PORT, '127.0.0.1', () => {
  console.log(`[server] Phyto E-Commerce Provisioning API running on port ${PORT}`);
  console.log(`[server] PayU env: ${process.env.PAYU_ENV || 'test'}`);
  console.log(`[server] Subdomain base: ${process.env.SUBDOMAIN_BASE || 'carnivorousplants.in'}`);
});

module.exports = app;
