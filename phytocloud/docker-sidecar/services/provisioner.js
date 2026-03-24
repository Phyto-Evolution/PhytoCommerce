'use strict';

/**
 * Async provisioner — handles all slow Docker + email work.
 * Called by the sidecar HTTP server after responding 202 to Spin.
 */

const crypto     = require('crypto');
const path       = require('path');
const fs         = require('fs');
const { execFile } = require('child_process');
const { promisify } = require('util');
const Docker     = require('dockerode');
const db         = require('./db');
const mailer     = require('./mailer');

const execFileAsync = promisify(execFile);

const TENANT_COMPOSE_DIR    = process.env.TENANT_COMPOSE_DIR    || '/opt/phyto-tenants';
const COMPOSE_TEMPLATE_PATH = process.env.COMPOSE_TEMPLATE_PATH ||
  path.join(__dirname, '../../api/templates/docker-compose.tpl.yml');

const docker = new Docker({ socketPath: process.env.DOCKER_SOCKET || '/var/run/docker.sock' });

if (!fs.existsSync(TENANT_COMPOSE_DIR)) fs.mkdirSync(TENANT_COMPOSE_DIR, { recursive: true });

function randomHex(bytes) { return crypto.randomBytes(bytes).toString('hex'); }

function generateComposeFile(tenant) {
  const template = fs.readFileSync(COMPOSE_TEMPLATE_PATH, 'utf8');
  const domain   = tenant.plan === 'subdomain' ? tenant.subdomain : tenant.domain;
  const composed = template
    .replace(/\{\{SLUG\}\}/g,              tenant.slug)
    .replace(/\{\{DOMAIN\}\}/g,            domain)
    .replace(/\{\{MYSQL_PASSWORD\}\}/g,    tenant.mysql_password)
    .replace(/\{\{PS_ADMIN_EMAIL\}\}/g,    tenant.ps_admin_email)
    .replace(/\{\{PS_ADMIN_PASSWORD\}\}/g, tenant.ps_admin_password)
    .replace(/\{\{PS_ADMIN_PATH\}\}/g,     tenant.ps_admin_path);

  const composePath = path.join(TENANT_COMPOSE_DIR, `docker-compose.${tenant.slug}.yml`);
  fs.writeFileSync(composePath, composed, 'utf8');
  return composePath;
}

async function composeRun(slug, ...args) {
  const composePath = path.join(TENANT_COMPOSE_DIR, `docker-compose.${slug}.yml`);
  if (!fs.existsSync(composePath)) throw new Error(`No compose file for: ${slug}`);
  const { stdout, stderr } = await execFileAsync(
    'docker', ['compose', '-f', composePath, ...args], { timeout: 300_000 }
  );
  if (stdout) console.log(`[docker:${slug}]`, stdout);
  if (stderr) console.error(`[docker:${slug}]`, stderr);
}

async function waitForHealthy(slug, timeoutMs = 300_000) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    try {
      const c = docker.getContainer(`ps_${slug}`);
      const info = await c.inspect();
      if (info.State?.Health?.Status === 'healthy') return true;
    } catch { /* not ready yet */ }
    await new Promise(r => setTimeout(r, 10_000));
  }
  throw new Error(`Healthcheck timeout for: ${slug}`);
}

// ── Public API ────────────────────────────────────────────────────────────────

async function provisionNew(tenant) {
  try {
    const mysql_password    = randomHex(16);
    const ps_admin_path     = `admin-${randomHex(4)}`;
    const ps_admin_password = randomHex(10);

    db.updateTenant(tenant.slug, {
      mysql_password,
      ps_admin_path,
      ps_admin_email:    tenant.email,
      ps_admin_password,
      status: 'provisioning',
    });

    const full = db.getTenantBySlug(tenant.slug);
    generateComposeFile(full);
    await composeRun(full.slug, 'up', '-d', '--remove-orphans');
    await waitForHealthy(full.slug);

    db.updateTenant(full.slug, { status: 'active', provisioned_at: new Date().toISOString() });
    db.logPaymentEvent(full.id, 'provisioned', { txnid: full.payu_txnid });

    const active = db.getTenantBySlug(full.slug);
    await mailer.sendWelcomeEmail(active);
    console.log(`[provisioner] ✓ ${full.slug} is live`);

  } catch (err) {
    console.error(`[provisioner] ✗ ${tenant.slug}:`, err.message);
    db.updateTenant(tenant.slug, { status: 'error' });
    await mailer.sendProvisioningErrorEmail(tenant, err.message).catch(() => {});
  }
}

async function suspendTenant(slug) {
  await composeRun(slug, 'pause');
  const graceUntil = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();
  db.updateTenant(slug, { status: 'suspended', suspended_at: new Date().toISOString(), grace_until: graceUntil });
  const t = db.getTenantBySlug(slug);
  db.logPaymentEvent(t.id, 'suspended');
  await mailer.sendSuspensionEmail(t).catch(() => {});
  console.log(`[provisioner] Suspended: ${slug}`);
}

async function resumeTenant(slug) {
  await composeRun(slug, 'unpause');
  db.updateTenant(slug, { status: 'active', suspended_at: null, grace_until: null });
  const t = db.getTenantBySlug(slug);
  db.logPaymentEvent(t.id, 'resumed');
  console.log(`[provisioner] Resumed: ${slug}`);
}

async function cancelTenant(slug) {
  await composeRun(slug, 'down');
  const deleteAt = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString();
  db.updateTenant(slug, { status: 'cancelled', cancelled_at: new Date().toISOString(), delete_at: deleteAt });
  const t = db.getTenantBySlug(slug);
  db.logPaymentEvent(t.id, 'cancelled');
  await mailer.sendCancellationEmail(t).catch(() => {});
  console.log(`[provisioner] Cancelled: ${slug}`);
}

async function destroyTenant(slug) {
  await composeRun(slug, 'down', '--volumes', '--remove-orphans');
  const composePath = path.join(TENANT_COMPOSE_DIR, `docker-compose.${slug}.yml`);
  if (fs.existsSync(composePath)) fs.unlinkSync(composePath);
  db.updateTenant(slug, { delete_at: null });
  console.log(`[provisioner] Destroyed: ${slug}`);
}

module.exports = { provisionNew, suspendTenant, resumeTenant, cancelTenant, destroyTenant };
