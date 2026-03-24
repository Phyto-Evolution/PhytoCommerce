/**
 * Phyto E-Commerce — PayU Webhook Component
 *
 * Responsibility:
 *   1. Accept POST /webhook/payu from PayU
 *   2. Verify SHA512 signature (security layer — sidecar never sees unverified requests)
 *   3. Write initial tenant record to SQLite (status=pending)
 *   4. Fire-and-forget POST to Docker sidecar for async provisioning/lifecycle
 *   5. Return 200 to PayU within 3 seconds (PayU requires fast ACK)
 *
 * The Docker sidecar does all slow work: container spin-up, healthcheck, email.
 */

import { ResponseBuilder, Variables, Sqlite } from "@fermyon/spin-sdk";

// ── Helpers ───────────────────────────────────────────────────────────────────

async function sha512hex(str: string): Promise<string> {
  const data = new TextEncoder().encode(str);
  const hashBuffer = await crypto.subtle.digest("SHA-512", data);
  return Array.from(new Uint8Array(hashBuffer))
    .map(b => b.toString(16).padStart(2, "0"))
    .join("");
}

/**
 * Verify PayU response hash.
 * Formula: sha512(SALT|status|||||||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|KEY)
 */
async function verifyPayuHash(
  payload: Record<string, string>,
  key: string,
  salt: string,
): Promise<boolean> {
  const {
    txnid = "", amount = "", productinfo = "", firstname = "", email = "",
    udf1 = "", udf2 = "", udf3 = "", udf4 = "", udf5 = "",
    status = "", hash = "",
  } = payload;

  const parts = [
    salt, status,
    "", "", "", "", "", "", "", "", "", "",  // 10 empty fields
    udf5, udf4, udf3, udf2, udf1,
    email, firstname, productinfo, amount, txnid,
    key,
  ];

  const computed = await sha512hex(parts.join("|"));
  return computed === hash;
}

/**
 * Parse plan and slug from productinfo.
 * Format: "subdomain|rosyplants" or "custom|rosyplants"
 */
function parsePlan(productinfo: string): { plan: string; slug: string } | null {
  const [plan, slug] = productinfo.split("|");
  if (!plan || !slug || !["subdomain", "custom"].includes(plan)) return null;
  return { plan, slug };
}

// ── SQLite schema bootstrap ───────────────────────────────────────────────────

function ensureSchema(db: ReturnType<typeof Sqlite.openDefault>) {
  db.execute(`
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
    )
  `, []);

  db.execute(`
    CREATE TABLE IF NOT EXISTS payment_log (
      id              INTEGER PRIMARY KEY AUTOINCREMENT,
      tenant_id       INTEGER NOT NULL,
      event_type      TEXT    NOT NULL,
      payu_txnid      TEXT,
      payu_mihpayid   TEXT,
      amount          REAL,
      raw_payload     TEXT,
      created_at      TEXT    DEFAULT (datetime('now'))
    )
  `, []);
}

// ── Handler ───────────────────────────────────────────────────────────────────

export async function handler(req: Request, res: ResponseBuilder): Promise<void> {
  if (req.method !== "POST") {
    res.status(405);
    res.send("Method not allowed");
    return;
  }

  // Read variables inside handler (Spin requirement)
  const PAYU_KEY         = Variables.get("payu_key")         ?? "";
  const PAYU_SALT        = Variables.get("payu_salt")        ?? "";
  const SUBDOMAIN_BASE   = Variables.get("subdomain_base")   ?? "carnivorousplants.in";
  const SIDECAR_URL      = Variables.get("docker_sidecar_url") ?? "http://127.0.0.1:3001";

  const body = await req.text();
  const params = new URLSearchParams(body);
  const payload: Record<string, string> = {};
  params.forEach((v, k) => { payload[k] = v; });

  const { status, txnid, mihpayid, amount, productinfo, email, phone } = payload;

  // ── Verify hash immediately ───────────────────────────────────────────────
  const hashValid = await verifyPayuHash(payload, PAYU_KEY, PAYU_SALT);
  if (!hashValid) {
    // Log failed attempt but still 200 to PayU (avoid retry storms)
    console.error(`[webhook] INVALID HASH — txnid=${txnid} ip=${req.headers.get("x-forwarded-for")}`);
    res.status(200);
    res.send("OK");
    return;
  }

  // ── Parse plan ────────────────────────────────────────────────────────────
  const parsed = parsePlan(productinfo ?? "");
  if (!parsed) {
    console.error(`[webhook] Cannot parse productinfo: ${productinfo}`);
    res.status(200);
    res.send("OK");
    return;
  }

  const { plan, slug } = parsed;
  const monthlyAmount = plan === "subdomain" ? 349 : 499;
  const txnFeePct     = plan === "subdomain" ? 1.0 : 2.0;
  const subdomain     = plan === "subdomain" ? `${slug}.${SUBDOMAIN_BASE}` : null;

  // ── Write to SQLite ───────────────────────────────────────────────────────
  const db = Sqlite.openDefault();
  ensureSchema(db);

  try {
    // Upsert tenant (idempotent)
    const existing = db.execute(
      "SELECT id, status FROM tenants WHERE slug = ?",
      [slug]
    );

    if (existing.rows.length === 0 && status === "success") {
      // New tenant — create record
      db.execute(`
        INSERT OR IGNORE INTO tenants
          (slug, email, phone, plan, subdomain, monthly_amount, txn_fee_pct,
           payu_txnid, payu_mihpayid, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
      `, [slug, email, phone ?? null, plan, subdomain, monthlyAmount, txnFeePct, txnid, mihpayid ?? null]);
    } else if (existing.rows.length > 0) {
      db.execute(
        "UPDATE tenants SET payu_txnid=?, payu_mihpayid=?, updated_at=datetime('now') WHERE slug=?",
        [txnid, mihpayid ?? null, slug]
      );
    }

    // Log payment event
    const tenantRow = db.execute("SELECT id FROM tenants WHERE slug=?", [slug]);
    if (tenantRow.rows.length > 0) {
      const tenantId = tenantRow.rows[0]["id"];
      db.execute(`
        INSERT INTO payment_log (tenant_id, event_type, payu_txnid, payu_mihpayid, amount, raw_payload)
        VALUES (?, ?, ?, ?, ?, ?)
      `, [tenantId, `payu_${status}`, txnid, mihpayid ?? null, parseFloat(amount ?? "0"), body]);
    }
  } catch (dbErr) {
    console.error("[webhook] SQLite error:", dbErr);
  }

  // ── Respond to PayU immediately ───────────────────────────────────────────
  res.status(200);
  res.send("OK");

  // ── Trigger sidecar async (best-effort) ──────────────────────────────────
  // The sidecar handles all slow work: Docker, healthcheck, email.
  // We fire this after responding so PayU gets its 200 instantly.
  try {
    await fetch(`${SIDECAR_URL}/payment-event`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        status,
        slug,
        plan,
        email,
        phone: phone ?? null,
        txnid,
        mihpayid: mihpayid ?? null,
        amount: parseFloat(amount ?? "0"),
        subdomain,
        monthly_amount: monthlyAmount,
        txn_fee_pct: txnFeePct,
      }),
    });
  } catch (err) {
    console.error("[webhook] Sidecar trigger failed:", err);
    // Non-fatal — sidecar will pick up pending tenant on next poll
  }
}
