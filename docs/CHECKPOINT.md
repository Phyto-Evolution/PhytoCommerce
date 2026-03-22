# PhytoCommerce — Checkpoint File

> **Quick-reference snapshot.** Read this first at the start of any new session.
> Full history: [`docs/ACTIVITY_LOG.md`](./ACTIVITY_LOG.md)

---

## Last Updated
**2026-03-22 12:10 UTC** — Session 4 complete

---

## Current State: ALL DONE ✅

```
22 modules built   |   0 pending   |   0 broken
```

All modules are feature-complete with PHP, TPL views, CSS, JS, and DB install/uninstall routines.

---

## Branch
```
claude/phytocommerce-module-dev-HGpZM
```
All work is on this branch. Latest commit: `c36d55b`

---

## What Was Last Done (2026-03-22, Session 4)

1. Completed views + controllers for 6 previously-pending modules:
   - `phyto_grex_registry` — admin product tab (genus/species/grex/ICPS/conservation)
   - `phyto_growth_stage` — admin tab + front badge
   - `phyto_seasonal_availability` — admin tab + front availability widget
   - `phyto_care_card` — admin tab with AJAX save + PDF preview link
   - `phyto_climate_zone` — pincode widget, zone admin checkboxes, AJAX check controller
   - `phyto_live_arrival` — checkout opt-in panel, order detail disclosure, claim form + file upload

2. Updated README to mark all 6 ✅ complete + added Docker testing guide

3. Created this checkpoint system (`docs/ACTIVITY_LOG.md`, `docs/CHECKPOINT.md`)

---

## Module Quick Reference

| Module | Key Feature | DB Tables |
|--------|-------------|-----------|
| `phytocommercefooter` | Branded footer | — |
| `phytoquickadd` | Admin product/cat add + AI desc + taxonomy import | — |
| `phytoerpconnector` | ERPNext v15 bidirectional sync | `phyto_erp_sync_log` |
| `phytoseobooster` | AI meta generation + schema markup + bulk audit | `phyto_seo_log` |
| `phyto_grex_registry` | Scientific taxonomy per product | `phyto_grex` |
| `phyto_tc_batch_tracker` | TC batch provenance + QR labels + low-stock | `phyto_tc_batch`, `phyto_tc_lineage`, `phyto_tc_contamination` |
| `phyto_growth_stage` | Stage tag + front badge | `phyto_growth_stage` |
| `phyto_seasonal_availability` | Seasonal flag + notify-me email | `phyto_seasonal`, `phyto_seasonal_notify` |
| `phyto_care_card` | Printable PDF care card | `phyto_care_card` |
| `phyto_climate_zone` | USDA/RHS zones + pincode checker | `phyto_climate_product` |
| `phyto_acclimation_bundler` | Cart: suggest acclimation accessories | `phyto_acclimation_bundle` |
| `phyto_live_arrival` | LAG opt-in + fee + claim form | `phyto_lag_optin`, `phyto_lag_claim` |
| `phyto_growers_journal` | Customer grow journal + photo timeline | `phyto_journal_entry`, `phyto_journal_photo` |
| `phyto_collection_widget` | Personal plant collection + public share | `phyto_collection` |
| `phyto_dispatch_logger` | Dispatch event log per order | `phyto_dispatch_event` |
| `phyto_phytosanitary` | Regulatory PDFs + expiry tracking | `phyto_phyto_doc` |
| `phyto_source_badge` | Origin/certification badges | `phyto_source_badge` |
| `phyto_wholesale_portal` | B2B tier + MOQ + tiered pricing | `phyto_wholesale_customer`, `phyto_wholesale_tier` |
| `phyto_subscription` | Mystery-box + replenishment subscriptions | `phyto_subscription`, `phyto_subscription_order` |
| `phyto_tc_cost_calculator` | TC production cost calculator | `phyto_tc_cost` |

---

## What to Do Next Session

If coming back to this project:

1. **Read this file first**
2. Check `docs/ACTIVITY_LOG.md` for detail
3. Verify branch: `git checkout claude/phytocommerce-module-dev-HGpZM`
4. Run `git log --oneline -5` to see where things stand
5. Check if any deployment/testing has surfaced bugs

### Likely Next Steps (future work)
- [ ] Deploy to VPS and run functional tests per the Docker guide in README
- [ ] Integration test: `phytoerpconnector` ↔ live ERPNext instance
- [ ] `phytoseobooster` — wire up Claude API key in admin settings
- [ ] `phyto_subscription` — configure Cashfree keys + webhook endpoint
- [ ] `phyto_climate_zone` — populate `PHYTO_CLIMATE_MAP` JSON with pincode→zone data for target regions

---

## Key Config Values Needed (set in PS admin after install)

| Module | Config Key | Description |
|--------|-----------|-------------|
| `phytoquickadd` | `PHYTO_CLAUDE_API_KEY` | Claude API key for AI descriptions |
| `phytoerpconnector` | `PHYTO_ERP_URL`, `PHYTO_ERP_KEY` | ERPNext base URL + API key |
| `phytoseobooster` | `PHYTO_SEO_CLAUDE_KEY` | Claude API key for SEO generation |
| `phyto_climate_zone` | `PHYTO_CLIMATE_MAP` | JSON: `{"110": "5b", "600": "10a", ...}` |
| `phyto_live_arrival` | `PHYTO_LAG_FEE`, `PHYTO_LAG_CLAIM_DAYS` | LAG fee amount + claim window days |
| `phyto_subscription` | `PHYTO_SUB_CF_KEY`, `PHYTO_SUB_CF_SECRET` | Cashfree credentials |

---

## VPS Access

**Host:** `ubuntu@51.83.192.49`
**SSH key:** `~/.ssh/phytocommerce_vps` (ed25519, set up 2026-03-22)
**Claude Code:** installed on VPS — best to run Claude sessions directly there

Add to `~/.ssh/config`:
```
Host phytocommerce-vps
    HostName 51.83.192.49
    User ubuntu
    IdentityFile ~/.ssh/phytocommerce_vps
```

First-time setup (installs SSH key + clones repo on VPS):
```bash
sshpass -p 'PASSWORD' ssh ubuntu@51.83.192.49 'bash -s' < docs/vps-setup.sh
```

**Note:** Claude Code web sandbox cannot reach the VPS over the network.
VPS work must run in a Claude Code session launched directly on the VPS.

---

*Auto-updated by Claude Code at session end. Do not edit manually.*
