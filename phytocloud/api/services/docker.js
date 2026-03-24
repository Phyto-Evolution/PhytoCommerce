'use strict';

const Docker = require('dockerode');
const fs = require('fs');
const path = require('path');
const { execSync, execFile } = require('child_process');
const { promisify } = require('util');

const execFileAsync = promisify(execFile);

const TENANT_COMPOSE_DIR   = process.env.TENANT_COMPOSE_DIR   || '/opt/phyto-tenants';
const COMPOSE_TEMPLATE_PATH = process.env.COMPOSE_TEMPLATE_PATH ||
  path.join(__dirname, '../templates/docker-compose.tpl.yml');

const docker = new Docker({ socketPath: process.env.DOCKER_SOCKET || '/var/run/docker.sock' });

// Ensure compose directory exists
if (!fs.existsSync(TENANT_COMPOSE_DIR)) {
  fs.mkdirSync(TENANT_COMPOSE_DIR, { recursive: true });
}

/**
 * Generate a per-tenant docker-compose file from the template.
 */
function generateComposeFile(tenant) {
  const template = fs.readFileSync(COMPOSE_TEMPLATE_PATH, 'utf8');

  const domain = tenant.plan === 'subdomain'
    ? tenant.subdomain
    : tenant.domain;

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

/**
 * Run docker compose command for a tenant.
 */
async function composeRun(slug, ...args) {
  const composePath = path.join(TENANT_COMPOSE_DIR, `docker-compose.${slug}.yml`);
  if (!fs.existsSync(composePath)) {
    throw new Error(`Compose file not found for tenant: ${slug}`);
  }

  const { stdout, stderr } = await execFileAsync(
    'docker',
    ['compose', '-f', composePath, ...args],
    { timeout: 300_000 } // 5 min
  );

  if (stdout) console.log(`[docker:${slug}]`, stdout);
  if (stderr) console.error(`[docker:${slug}]`, stderr);
  return { stdout, stderr };
}

/**
 * Spin up a new tenant's containers.
 * Idempotent — safe to call if already running.
 */
async function provisionTenant(tenant) {
  const composePath = generateComposeFile(tenant);
  console.log(`[docker] Provisioning tenant: ${tenant.slug} (${composePath})`);

  await composeRun(tenant.slug, 'up', '-d', '--remove-orphans');
  return true;
}

/**
 * Wait for PrestaShop to pass its healthcheck.
 * Polls every 10s, times out after 5 minutes.
 */
async function waitForHealthcheck(slug, timeoutMs = 300_000) {
  const start = Date.now();
  const containerName = `ps_${slug}`;

  while (Date.now() - start < timeoutMs) {
    try {
      const container = docker.getContainer(containerName);
      const inspect = await container.inspect();
      const health = inspect.State?.Health?.Status;

      if (health === 'healthy') {
        console.log(`[docker] ${slug} is healthy`);
        return true;
      }
      console.log(`[docker] ${slug} health: ${health || 'starting'} — waiting...`);
    } catch (err) {
      console.log(`[docker] ${slug} container not ready yet — waiting...`);
    }

    await new Promise(r => setTimeout(r, 10_000));
  }

  throw new Error(`Healthcheck timed out for tenant: ${slug}`);
}

/**
 * Pause a suspended tenant (payment failed).
 */
async function suspendTenant(slug) {
  console.log(`[docker] Suspending tenant: ${slug}`);
  await composeRun(slug, 'pause');
}

/**
 * Resume a paused tenant (payment restored).
 */
async function resumeTenant(slug) {
  console.log(`[docker] Resuming tenant: ${slug}`);
  await composeRun(slug, 'unpause');
}

/**
 * Tear down a tenant's containers (data volumes preserved).
 */
async function teardownTenant(slug) {
  console.log(`[docker] Tearing down tenant: ${slug}`);
  await composeRun(slug, 'down');
}

/**
 * Destroy a tenant's containers AND volumes (permanent deletion).
 */
async function destroyTenant(slug) {
  console.log(`[docker] DESTROYING tenant: ${slug}`);
  await composeRun(slug, 'down', '--volumes', '--remove-orphans');

  // Remove compose file
  const composePath = path.join(TENANT_COMPOSE_DIR, `docker-compose.${slug}.yml`);
  if (fs.existsSync(composePath)) {
    fs.unlinkSync(composePath);
  }
}

/**
 * Get container status for a tenant.
 */
async function getTenantStatus(slug) {
  try {
    const container = docker.getContainer(`ps_${slug}`);
    const inspect = await container.inspect();
    return {
      running: inspect.State.Running,
      paused:  inspect.State.Paused,
      health:  inspect.State?.Health?.Status || 'none',
      status:  inspect.State.Status,
    };
  } catch (err) {
    return { running: false, paused: false, health: 'none', status: 'not found' };
  }
}

module.exports = {
  provisionTenant,
  waitForHealthcheck,
  suspendTenant,
  resumeTenant,
  teardownTenant,
  destroyTenant,
  getTenantStatus,
  generateComposeFile,
};
