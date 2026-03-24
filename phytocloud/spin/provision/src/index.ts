/**
 * Phyto E-Commerce — Provision Management Component
 *
 * Internal admin endpoints — all protected by X-Api-Secret header.
 * For Docker operations (suspend, resume, cancel, destroy), delegates to sidecar.
 * For reads (list tenants, tenant detail), reads SQLite directly.
 *
 * Routes:
 *   GET  /provision/tenants         — list all active tenants
 *   GET  /provision/tenant/:slug    — single tenant detail
 *   POST /provision/domain          — set custom domain for a custom-plan tenant
 *   POST /provision/suspend         — suspend a tenant
 *   POST /provision/resume          — resume a suspended tenant
 *   POST /provision/cancel          — cancel subscription + schedule deletion
 *   POST /provision/destroy         — permanent destruction (after 30-day retention)
 */

import { ResponseBuilder, Variables, Sqlite } from "@fermyon/spin-sdk";

// ── Helpers ───────────────────────────────────────────────────────────────────

function json(res: ResponseBuilder, data: unknown, status = 200): void {
  res.status(status);
  res.set({ "content-type": "application/json" });
  res.send(JSON.stringify(data));
}

function requireSecret(req: Request, secret: string): boolean {
  return req.headers.get("x-api-secret") === secret;
}

async function callSidecar(
  sidecarUrl: string,
  path: string,
  body: Record<string, unknown>,
): Promise<{ ok: boolean; data: unknown }> {
  const resp = await fetch(`${sidecarUrl}${path}`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
  const data = await resp.json();
  return { ok: resp.ok, data };
}

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
      suspended_at TEXT,
      cancelled_at TEXT,
      delete_at TEXT,
      created_at TEXT DEFAULT (datetime('now')),
      updated_at TEXT DEFAULT (datetime('now'))
    )
  `, []);
}

// ── Route dispatch ────────────────────────────────────────────────────────────

export async function handler(req: Request, res: ResponseBuilder): Promise<void> {
  const API_SECRET   = Variables.get("api_secret")          ?? "";
  const SIDECAR_URL  = Variables.get("docker_sidecar_url")  ?? "http://127.0.0.1:3001";

  if (!requireSecret(req, API_SECRET)) {
    json(res, { error: "Forbidden" }, 403);
    return;
  }

  const url = new URL(req.url);
  const path = url.pathname; // e.g. /provision/tenants
  const db = Sqlite.openDefault();
  ensureSchema(db);

  // ── GET /provision/tenants ────────────────────────────────────────────────
  if (req.method === "GET" && path === "/provision/tenants") {
    const rows = db.execute(
      "SELECT slug, plan, status, subdomain, domain, email, created_at FROM tenants WHERE status = 'active'",
      []
    ).rows as Record<string, unknown>[];

    json(res, rows.map(r => ({
      slug:   r["slug"],
      plan:   r["plan"],
      status: r["status"],
      domain: r["plan"] === "subdomain" ? r["subdomain"] : r["domain"],
      email:  r["email"],
      created_at: r["created_at"],
    })));
    return;
  }

  // ── GET /provision/tenant/:slug ───────────────────────────────────────────
  const tenantMatch = path.match(/^\/provision\/tenant\/([a-z0-9-]{3,40})$/);
  if (req.method === "GET" && tenantMatch) {
    const slug = tenantMatch[1];
    const rows = db.execute("SELECT * FROM tenants WHERE slug = ?", [slug]).rows;
    if (rows.length === 0) { json(res, { error: "Not found" }, 404); return; }
    json(res, rows[0]);
    return;
  }

  // ── POST endpoints — parse JSON body ─────────────────────────────────────
  if (req.method !== "POST") {
    json(res, { error: "Method not allowed" }, 405);
    return;
  }

  let body: Record<string, unknown> = {};
  try {
    body = await req.json() as Record<string, unknown>;
  } catch {
    json(res, { error: "Invalid JSON body" }, 400);
    return;
  }

  // ── POST /provision/domain ────────────────────────────────────────────────
  if (path === "/provision/domain") {
    const { slug, domain } = body as { slug?: string; domain?: string };
    if (!slug || !domain) { json(res, { error: "slug and domain required" }, 400); return; }

    const rows = db.execute("SELECT plan FROM tenants WHERE slug=?", [slug]).rows;
    if (rows.length === 0) { json(res, { error: "Not found" }, 404); return; }
    if ((rows[0] as Record<string, unknown>)["plan"] !== "custom") {
      json(res, { error: "Only custom-plan tenants can set a domain" }, 400);
      return;
    }

    db.execute("UPDATE tenants SET domain=?, updated_at=datetime('now') WHERE slug=?", [domain, slug]);
    json(res, { ok: true, domain });
    return;
  }

  // ── POST /provision/suspend ───────────────────────────────────────────────
  if (path === "/provision/suspend") {
    const { slug } = body as { slug?: string };
    if (!slug) { json(res, { error: "slug required" }, 400); return; }
    const result = await callSidecar(SIDECAR_URL, "/suspend", { slug });
    json(res, result.data, result.ok ? 200 : 502);
    return;
  }

  // ── POST /provision/resume ────────────────────────────────────────────────
  if (path === "/provision/resume") {
    const { slug } = body as { slug?: string };
    if (!slug) { json(res, { error: "slug required" }, 400); return; }
    const result = await callSidecar(SIDECAR_URL, "/resume", { slug });
    json(res, result.data, result.ok ? 200 : 502);
    return;
  }

  // ── POST /provision/cancel ────────────────────────────────────────────────
  if (path === "/provision/cancel") {
    const { slug } = body as { slug?: string };
    if (!slug) { json(res, { error: "slug required" }, 400); return; }
    const result = await callSidecar(SIDECAR_URL, "/cancel", { slug });
    json(res, result.data, result.ok ? 200 : 502);
    return;
  }

  // ── POST /provision/destroy ───────────────────────────────────────────────
  if (path === "/provision/destroy") {
    const { slug } = body as { slug?: string };
    if (!slug) { json(res, { error: "slug required" }, 400); return; }
    const result = await callSidecar(SIDECAR_URL, "/destroy", { slug });
    json(res, result.data, result.ok ? 200 : 502);
    return;
  }

  json(res, { error: "Not found" }, 404);
}
