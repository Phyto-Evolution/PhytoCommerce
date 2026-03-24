'use strict';

const Database = require('better-sqlite3');
const path = require('path');
const fs = require('fs');

const DB_PATH = process.env.DB_PATH || path.join(__dirname, '../data/tenants.db');

// Ensure data directory exists
const dataDir = path.dirname(DB_PATH);
if (!fs.existsSync(dataDir)) {
  fs.mkdirSync(dataDir, { recursive: true });
}

const db = new Database(DB_PATH);

// Enable WAL mode for better concurrency
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

// ── Schema ───────────────────────────────────────────────────────────────────

db.exec(`
  CREATE TABLE IF NOT EXISTS tenants (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    slug                TEXT    UNIQUE NOT NULL,
    email               TEXT    NOT NULL,
    phone               TEXT,
    plan                TEXT    NOT NULL CHECK(plan IN ('subdomain', 'custom')),
    domain              TEXT,
    subdomain           TEXT,
    status              TEXT    NOT NULL DEFAULT 'pending'
                                CHECK(status IN ('pending','provisioning','active',
                                                 'suspended','cancelled','error')),
    monthly_amount      INTEGER NOT NULL,
    txn_fee_pct         REAL    NOT NULL,

    -- PayU
    payu_txnid          TEXT,
    payu_mihpayid       TEXT,
    payu_si_token       TEXT,

    -- PrestaShop credentials (stored for welcome email)
    mysql_password      TEXT,
    ps_admin_path       TEXT,
    ps_admin_email      TEXT,
    ps_admin_password   TEXT,

    -- Timestamps
    provisioned_at      TEXT,
    suspended_at        TEXT,
    cancelled_at        TEXT,
    grace_until         TEXT,
    delete_at           TEXT,
    created_at          TEXT    DEFAULT (datetime('now')),
    updated_at          TEXT    DEFAULT (datetime('now'))
  );

  CREATE TABLE IF NOT EXISTS payment_log (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id       INTEGER NOT NULL REFERENCES tenants(id),
    event_type      TEXT    NOT NULL,
    payu_txnid      TEXT,
    payu_mihpayid   TEXT,
    amount          REAL,
    currency        TEXT    DEFAULT 'INR',
    raw_payload     TEXT,
    created_at      TEXT    DEFAULT (datetime('now'))
  );

  CREATE INDEX IF NOT EXISTS idx_tenants_slug   ON tenants(slug);
  CREATE INDEX IF NOT EXISTS idx_tenants_status ON tenants(status);
  CREATE INDEX IF NOT EXISTS idx_tenants_email  ON tenants(email);
  CREATE INDEX IF NOT EXISTS idx_payment_log_tenant ON payment_log(tenant_id);
`);

// ── Tenant helpers ────────────────────────────────────────────────────────────

function createTenant(data) {
  const stmt = db.prepare(`
    INSERT INTO tenants
      (slug, email, phone, plan, domain, subdomain,
       monthly_amount, txn_fee_pct, status)
    VALUES
      (@slug, @email, @phone, @plan, @domain, @subdomain,
       @monthly_amount, @txn_fee_pct, 'pending')
  `);
  const result = stmt.run(data);
  return getTenantById(result.lastInsertRowid);
}

function getTenantById(id) {
  return db.prepare('SELECT * FROM tenants WHERE id = ?').get(id);
}

function getTenantBySlug(slug) {
  return db.prepare('SELECT * FROM tenants WHERE slug = ?').get(slug);
}

function getTenantByPayuTxnid(txnid) {
  return db.prepare('SELECT * FROM tenants WHERE payu_txnid = ?').get(txnid);
}

function updateTenant(slug, fields) {
  const set = Object.keys(fields)
    .map(k => `${k} = @${k}`)
    .join(', ');
  db.prepare(`UPDATE tenants SET ${set}, updated_at = datetime('now') WHERE slug = @slug`)
    .run({ ...fields, slug });
}

function getAllActiveTenants() {
  return db.prepare("SELECT * FROM tenants WHERE status = 'active'").all();
}

function getTenantsForGracePeriodCheck() {
  return db.prepare(`
    SELECT * FROM tenants
    WHERE status = 'suspended'
      AND grace_until IS NOT NULL
      AND grace_until < datetime('now')
  `).all();
}

function getTenantsForDeletion() {
  return db.prepare(`
    SELECT * FROM tenants
    WHERE status = 'cancelled'
      AND delete_at IS NOT NULL
      AND delete_at < datetime('now')
  `).all();
}

// ── Payment log helpers ───────────────────────────────────────────────────────

function logPaymentEvent(tenantId, eventType, data = {}) {
  db.prepare(`
    INSERT INTO payment_log
      (tenant_id, event_type, payu_txnid, payu_mihpayid, amount, raw_payload)
    VALUES
      (@tenantId, @eventType, @payu_txnid, @payu_mihpayid, @amount, @raw_payload)
  `).run({
    tenantId,
    eventType,
    payu_txnid:    data.txnid      || null,
    payu_mihpayid: data.mihpayid   || null,
    amount:        data.amount     || null,
    raw_payload:   data.rawPayload ? JSON.stringify(data.rawPayload) : null,
  });
}

module.exports = {
  db,
  createTenant,
  getTenantById,
  getTenantBySlug,
  getTenantByPayuTxnid,
  updateTenant,
  getAllActiveTenants,
  getTenantsForGracePeriodCheck,
  getTenantsForDeletion,
  logPaymentEvent,
};
