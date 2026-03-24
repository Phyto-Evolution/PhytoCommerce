'use strict';

const express = require('express');
const router  = express.Router();

const db        = require('../services/db');
const dockerSvc = require('../services/docker');

/**
 * GET /status/:slug
 * Public endpoint — returns sanitised store status (no credentials).
 */
router.get('/:slug', async (req, res) => {
  const { slug } = req.params;

  if (!/^[a-z0-9-]{3,40}$/.test(slug)) {
    return res.status(400).json({ error: 'Invalid slug format' });
  }

  const tenant = db.getTenantBySlug(slug);
  if (!tenant) {
    return res.status(404).json({ error: 'Not found' });
  }

  const dockerStatus = await dockerSvc.getTenantStatus(slug).catch(() => null);

  return res.json({
    slug:       tenant.slug,
    plan:       tenant.plan,
    status:     tenant.status,
    domain:     tenant.plan === 'subdomain' ? tenant.subdomain : tenant.domain,
    created_at: tenant.created_at,
    container:  dockerStatus,
  });
});

/**
 * GET /status/:slug/check-slug
 * Check if a slug is available.
 */
router.get('/check/:slug', (req, res) => {
  const { slug } = req.params;

  if (!/^[a-z0-9-]{3,40}$/.test(slug)) {
    return res.status(400).json({ available: false, error: 'Slug must be 3-40 lowercase alphanumeric characters or hyphens' });
  }

  const existing = db.getTenantBySlug(slug);
  return res.json({ available: !existing });
});

module.exports = router;
