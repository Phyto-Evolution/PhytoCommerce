'use strict';

const express  = require('express');
const crypto   = require('crypto');
const router   = express.Router();

const { verifyWebhookHash, parsePlanFromProductInfo, getPlanPricing } = require('../services/payu');
const db       = require('../services/db');
const dockerSvc = require('../services/docker');
const mailer   = require('../services/mailer');

const SUBDOMAIN_BASE = process.env.SUBDOMAIN_BASE || 'carnivorousplants.in';

/**
 * Generate random strings for credentials.
 */
function randomHex(bytes) {
  return crypto.randomBytes(bytes).toString('hex');
}

/**
 * POST /webhook/payu
 *
 * PayU sends a POST with form-encoded body after each payment event.
 * We must respond 200 within 3 seconds — heavy work runs async.
 */
router.post('/payu', express.urlencoded({ extended: true }), async (req, res) => {
  // Respond to PayU immediately
  res.status(200).send('OK');

  // Process asynchronously so we never timeout
  setImmediate(() => handlePayuWebhook(req.body).catch(err => {
    console.error('[webhook] Unhandled error in PayU handler:', err);
  }));
});

async function handlePayuWebhook(payload) {
  const { status, txnid, mihpayid, amount, productinfo, email, firstname } = payload;

  console.log(`[webhook] PayU event: status=${status} txnid=${txnid}`);

  // Verify hash
  let hashValid = false;
  try {
    hashValid = verifyWebhookHash(payload);
  } catch (err) {
    console.error('[webhook] Hash verification error:', err.message);
    return;
  }

  if (!hashValid) {
    console.error(`[webhook] INVALID HASH for txnid=${txnid} — ignoring`);
    return;
  }

  // Parse plan and slug from productinfo (e.g. "subdomain|rosyplants")
  const parsed = parsePlanFromProductInfo(productinfo);
  if (!parsed) {
    console.error(`[webhook] Could not parse productinfo: ${productinfo}`);
    return;
  }

  const { plan, slug } = parsed;

  if (status === 'success') {
    await handlePaymentSuccess({ slug, plan, email, firstname, txnid, mihpayid, amount, payload });
  } else if (status === 'failure' || status === 'pending') {
    await handlePaymentFailure({ slug, txnid, mihpayid, amount, status, payload });
  } else {
    console.log(`[webhook] Unhandled PayU status: ${status}`);
  }
}

async function handlePaymentSuccess({ slug, plan, email, firstname, txnid, mihpayid, amount, payload }) {
  let tenant = db.getTenantBySlug(slug);

  if (!tenant) {
    // First payment — create tenant record
    const pricing = getPlanPricing(plan);
    const subdomain = plan === 'subdomain' ? `${slug}.${SUBDOMAIN_BASE}` : null;

    tenant = db.createTenant({
      slug,
      email,
      phone:          payload.phone || null,
      plan,
      domain:         plan === 'custom' ? null : null, // set later via domain-config endpoint
      subdomain,
      monthly_amount: pricing.monthly_amount,
      txn_fee_pct:    pricing.txn_fee_pct,
    });

    db.updateTenant(slug, {
      payu_txnid:  txnid,
      payu_mihpayid: mihpayid,
      status: 'provisioning',
    });

    db.logPaymentEvent(tenant.id, 'payment_success', { txnid, mihpayid, amount, rawPayload: payload });

    await provisionNewTenant(tenant, { txnid, mihpayid });

  } else if (tenant.status === 'suspended') {
    // Recurring payment after suspension — resume store
    db.updateTenant(slug, {
      payu_txnid: txnid,
      payu_mihpayid: mihpayid,
      status: 'active',
      suspended_at: null,
      grace_until: null,
    });
    db.logPaymentEvent(tenant.id, 'payment_success_resume', { txnid, mihpayid, amount, rawPayload: payload });

    await dockerSvc.resumeTenant(slug);
    console.log(`[webhook] Resumed tenant: ${slug}`);

  } else {
    // Recurring payment on active store — just log it
    db.logPaymentEvent(tenant.id, 'payment_success_recurring', { txnid, mihpayid, amount, rawPayload: payload });
    console.log(`[webhook] Logged recurring payment for: ${slug}`);
  }
}

async function provisionNewTenant(tenant, { txnid, mihpayid }) {
  try {
    // Generate credentials
    const mysql_password    = randomHex(16);
    const ps_admin_path     = `admin-${randomHex(4)}`;
    const ps_admin_email    = tenant.email;
    const ps_admin_password = randomHex(10);

    db.updateTenant(tenant.slug, {
      mysql_password,
      ps_admin_path,
      ps_admin_email,
      ps_admin_password,
    });

    // Re-fetch with credentials
    const fullTenant = db.getTenantBySlug(tenant.slug);

    // Spin up containers
    await dockerSvc.provisionTenant(fullTenant);

    // Wait for health
    await dockerSvc.waitForHealthcheck(tenant.slug);

    // Mark active
    db.updateTenant(tenant.slug, {
      status:         'active',
      provisioned_at: new Date().toISOString(),
    });

    console.log(`[webhook] Tenant ${tenant.slug} provisioned and active`);

    // Send welcome email (subdomain plan uses Mailgun; custom domain gets welcome too)
    const activeTenant = db.getTenantBySlug(tenant.slug);
    await mailer.sendWelcomeEmail(activeTenant);

  } catch (err) {
    console.error(`[webhook] Provisioning failed for ${tenant.slug}:`, err.message);
    db.updateTenant(tenant.slug, { status: 'error' });
    db.logPaymentEvent(tenant.id, 'provisioning_error', {
      txnid,
      rawPayload: { error: err.message },
    });
    await mailer.sendProvisioningErrorEmail(tenant, err.message);
  }
}

async function handlePaymentFailure({ slug, txnid, mihpayid, amount, status, payload }) {
  const tenant = db.getTenantBySlug(slug);
  if (!tenant) {
    console.warn(`[webhook] Payment failure for unknown slug: ${slug}`);
    return;
  }

  db.logPaymentEvent(tenant.id, `payment_${status}`, { txnid, mihpayid, amount, rawPayload: payload });

  if (tenant.status === 'active') {
    // Grace period: 7 days before suspension
    const graceUntil = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();
    db.updateTenant(slug, {
      status:       'suspended',
      suspended_at: new Date().toISOString(),
      grace_until:  graceUntil,
    });

    await dockerSvc.suspendTenant(slug);
    await mailer.sendSuspensionEmail(tenant);
    console.log(`[webhook] Suspended tenant: ${slug} (grace until ${graceUntil})`);
  }
}

module.exports = router;
