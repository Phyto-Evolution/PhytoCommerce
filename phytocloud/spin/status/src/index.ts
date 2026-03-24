/**
 * Phyto E-Commerce — Status API Component
 *
 * Public endpoints. Reads SQLite directly — no sidecar round-trip needed.
 * Fast by design: a status check should complete in <5ms.
 *
 * Routes:
 *   GET /status/{slug}         — full store status (sanitised, no credentials)
 *   GET /status/check/{slug}   — availability check: { available: bool }
 */

import { ResponseBuilder, Sqlite } from "@fermyon/spin-sdk";

// ── Schema bootstrap (idempotent) ─────────────────────────────────────────────

function ensureSchema(db: ReturnType<typeof Sqlite.openDefault>) {
  db.execute(`
    CREATE TABLE IF NOT EXISTS tenants (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      slug TEXT UNIQUE NOT NULL,
      email TEXT NOT NULL,
      plan TEXT NOT NULL,
      domain TEXT,
      subdomain TEXT,
      status TEXT NOT NULL DEFAULT 'pending',
      monthly_amount INTEGER NOT NULL,
      txn_fee_pct REAL NOT NULL,
      provisioned_at TEXT,
      created_at TEXT DEFAULT (datetime('now')),
      updated_at TEXT DEFAULT (datetime('now'))
    )
  `, []);
}

// ── JSON response helper ──────────────────────────────────────────────────────

function json(res: ResponseBuilder, data: unknown, status = 200): void {
  res.status(status);
  res.set({ "content-type": "application/json" });
  res.send(JSON.stringify(data));
}

// ── Handler ───────────────────────────────────────────────────────────────────

export async function handler(req: Request, res: ResponseBuilder): Promise<void> {
  if (req.method !== "GET") {
    json(res, { error: "Method not allowed" }, 405);
    return;
  }

  const url = new URL(req.url);
  const segments = url.pathname.split("/").filter(Boolean);
  // segments: ["status", "check", slug] or ["status", slug]

  const isCheck = segments[1] === "check";
  const slug = isCheck ? segments[2] : segments[1];

  if (!slug || !/^[a-z0-9-]{3,40}$/.test(slug)) {
    json(res, { error: "Invalid slug" }, 400);
    return;
  }

  const db = Sqlite.openDefault();
  ensureSchema(db);

  if (isCheck) {
    // ── Slug availability check ───────────────────────────────────────────
    const result = db.execute(
      "SELECT slug FROM tenants WHERE slug = ?",
      [slug]
    );
    json(res, { available: result.rows.length === 0, slug });
    return;
  }

  // ── Full status ───────────────────────────────────────────────────────────
  const result = db.execute(
    `SELECT slug, plan, status, subdomain, domain, monthly_amount, txn_fee_pct,
            provisioned_at, created_at
     FROM tenants WHERE slug = ?`,
    [slug]
  );

  if (result.rows.length === 0) {
    json(res, { error: "Not found" }, 404);
    return;
  }

  const row = result.rows[0] as Record<string, unknown>;
  const domain = row["plan"] === "subdomain" ? row["subdomain"] : row["domain"];

  json(res, {
    slug:           row["slug"],
    plan:           row["plan"],
    status:         row["status"],
    domain,
    monthly_amount: row["monthly_amount"],
    txn_fee_pct:    row["txn_fee_pct"],
    provisioned_at: row["provisioned_at"],
    created_at:     row["created_at"],
  });
}
