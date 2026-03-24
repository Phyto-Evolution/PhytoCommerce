'use strict';

const express = require('express');
const router  = express.Router();

const db        = require('../services/db');
const dockerSvc = require('../services/docker');
const mailer    = require('../services/mailer');

const API_SECRET = process.env.API_SECRET;

/**
 * Middleware — internal endpoints require API_SECRET header.
 */
function requireSecret(req, res, next) {
  const provided = req.headers['x-api-secret'];
  if (!API_SECRET || provided !== API_SECRET) {
    return res.status(403).json({ error: 'Forbidden' });
  }
  next();
}

/**
 * POST /provision/domain
 * Let a custom-plan tenant set their custom domain.
 * Called from the post-signup setup wizard.
 */
router.post('/domain', async (req, res) => {
  const { slug, domain } = req.body;
  if (!slug || !domain) {
    return res.status(400).json({ error: 'slug and domain required' });
  }

  const tenant = db.getTenantBySlug(slug);
  if (!tenant) return res.status(404).json({ error: 'Tenant not found' });
  if (tenant.plan !== 'custom') return res.status(400).json({ error: 'Only custom plan tenants can set a domain' });

  db.updateTenant(slug, { domain });
  return res.json({ ok: true, domain });
});

/**
 * POST /provision/cancel  [internal]
 * Cancel a subscription and schedule data deletion.
 */
router.post('/cancel', requireSecret, async (req, res) => {
  const { slug } = req.body;
  if (!slug) return res.status(400).json({ error: 'slug required' });

  const tenant = db.getTenantBySlug(slug);
  if (!tenant) return res.status(404).json({ error: 'Tenant not found' });

  const deleteAt = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString();
  db.updateTenant(slug, {
    status:       'cancelled',
    cancelled_at: new Date().toISOString(),
    delete_at:    deleteAt,
  });

  await dockerSvc.teardownTenant(slug);
  await mailer.sendCancellationEmail(tenant);

  console.log(`[provision] Cancelled tenant ${slug}, data deletion scheduled for ${deleteAt}`);
  return res.json({ ok: true, delete_at: deleteAt });
});

/**
 * POST /provision/destroy  [internal]
 * Permanently destroy tenant data (run after 30-day retention expires).
 */
router.post('/destroy', requireSecret, async (req, res) => {
  const { slug } = req.body;
  if (!slug) return res.status(400).json({ error: 'slug required' });

  const tenant = db.getTenantBySlug(slug);
  if (!tenant) return res.status(404).json({ error: 'Tenant not found' });
  if (tenant.status !== 'cancelled') {
    return res.status(400).json({ error: 'Tenant must be cancelled before destruction' });
  }

  await dockerSvc.destroyTenant(slug);
  console.log(`[provision] Destroyed tenant: ${slug}`);
  return res.json({ ok: true });
});

/**
 * GET /provision/tenants  [internal]
 * List all active tenants.
 */
router.get('/tenants', requireSecret, (req, res) => {
  const tenants = db.getAllActiveTenants();
  return res.json(tenants.map(t => ({
    slug:      t.slug,
    plan:      t.plan,
    status:    t.status,
    domain:    t.subdomain || t.domain,
    email:     t.email,
    created_at: t.created_at,
  })));
});

module.exports = router;
