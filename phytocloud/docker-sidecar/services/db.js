'use strict';

const Database = require('better-sqlite3');
const path     = require('path');
const fs       = require('fs');

const DB_PATH = process.env.DB_PATH || path.join(__dirname, '../../data/tenants.db');

const dataDir = path.dirname(DB_PATH);
if (!fs.existsSync(dataDir)) fs.mkdirSync(dataDir, { recursive: true });

const db = new Database(DB_PATH);
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

// Schema is created by Spin on first request, but ensure tables exist here too.
db.exec(`
  CREATE TABLE IF NOT EXISTS tenants (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    slug                TEXT    UNIQUE NOT NULL,
    email               TEXT    NOT NULL,
    phone               TEXT,
    plan                TEXT    NOT NULL,
    domain              TEXT,
    subdomain           TEXT,
    status              TEXT    NOT NULL DEFAULT 'pending',
    monthly_amount      INTEGER NOT NULL,
    txn_fee_pct         REAL    NOT NULL,
    payu_txnid          TEXT,
    payu_mihpayid       TEXT,
    mysql_password      TEXT,
    ps_admin_path       TEXT,
    ps_admin_email      TEXT,
    ps_admin_password   TEXT,
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
    tenant_id       INTEGER NOT NULL,
    event_type      TEXT    NOT NULL,
    payu_txnid      TEXT,
    payu_mihpayid   TEXT,
    amount          REAL,
    raw_payload     TEXT,
    created_at      TEXT    DEFAULT (datetime('now'))
  );

  CREATE INDEX IF NOT EXISTS idx_tenants_slug   ON tenants(slug);
  CREATE INDEX IF NOT EXISTS idx_tenants_status ON tenants(status);
`);

const getTenantBySlug = slug =>
  db.prepare('SELECT * FROM tenants WHERE slug = ?').get(slug);

const updateTenant = (slug, fields) => {
  const set = Object.keys(fields).map(k => `${k} = @${k}`).join(', ');
  db.prepare(`UPDATE tenants SET ${set}, updated_at = datetime('now') WHERE slug = @slug`)
    .run({ ...fields, slug });
};

const getPendingTenants = () =>
  db.prepare("SELECT * FROM tenants WHERE status = 'pending'").all();

const getTenantsForGraceExpiry = () =>
  db.prepare(`SELECT * FROM tenants WHERE status='suspended' AND grace_until < datetime('now')`).all();

const getTenantsForDeletion = () =>
  db.prepare(`SELECT * FROM tenants WHERE status='cancelled' AND delete_at IS NOT NULL AND delete_at < datetime('now')`).all();

const logPaymentEvent = (tenantId, eventType, data = {}) =>
  db.prepare(`
    INSERT INTO payment_log (tenant_id, event_type, payu_txnid, payu_mihpayid, amount, raw_payload)
    VALUES (@tenantId, @eventType, @txnid, @mihpayid, @amount, @raw)
  `).run({
    tenantId,
    eventType,
    txnid:    data.txnid    || null,
    mihpayid: data.mihpayid || null,
    amount:   data.amount   || null,
    raw:      data.raw      ? JSON.stringify(data.raw) : null,
  });

module.exports = { db, getTenantBySlug, updateTenant, getPendingTenants,
                   getTenantsForGraceExpiry, getTenantsForDeletion, logPaymentEvent };
