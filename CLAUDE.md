# PhytoCommerce — Claude Code Working Rules

## Module Architecture

### Two parallel sets — BOTH must always be kept in sync

**Set A — Standalone modules** (`modules/phyto_*/`, `modules/phytocommerce*/`, etc.)
These are individual PrestaShop modules that clients install one-by-one.
Existing clients are running these. **Do not remove, rename, or restructure them.**

Current standalone modules:
- `phytocommercefooter`
- `phytoquickadd`
- `phytoerpconnector`
- `phytoseobooster`
- `phyto_grex_registry`
- `phyto_tc_batch_tracker`
- `phyto_growth_stage`
- `phyto_seasonal_availability`
- `phyto_care_card`
- `phyto_climate_zone`
- `phyto_acclimation_bundler`
- `phyto_live_arrival`
- `phyto_growers_journal`
- `phyto_collection_widget`
- `phyto_source_badge`
- `phyto_dispatch_logger`
- `phyto_phytosanitary`
- `phyto_tc_cost_calculator`
- `phyto_wholesale_portal`
- `phyto_subscription`
- `phyto_image_sec`
- `phytocommerce_branding`
- `phyto_kyc`

**Set B — PhytoCommerce Pack** (`modules/phytocommerce_pack/`)
A meta-installer module that installs all of the above in one click.
Intended for fresh e-commerce rollouts (used by the **Phyto E-Commerce - Stack** project).
This is an *addition*, not a replacement of Set A.

### The golden rule
> **Any new module, feature change, bug fix, or config update applied to a standalone module MUST also be reflected in the pack (and vice versa).**

Specifically:
- Adding a new standalone module → add it to `MODULES` const in `phytocommerce_pack.php` + `bundled/README.md`
- Removing/renaming a standalone module → update the pack's `MODULES` list accordingly (but do not remove the standalone module itself unless explicitly asked)
- Schema or install SQL changes in a module → verify pack's install flow still works for that module
- New pack-level feature → ensure it doesn't break standalone installs

## Git / Branch conventions

- Default working branch: `claude/phytocommerce-module-dev-HGpZM`
- Main branch is protected; changes land via PR
- PhytoCloud SaaS work lives under `phytocloud/` — separate deployment, do not mix with PS module code

## Related projects

- **Phyto E-Commerce - Stack** — separate Claude Code chat/session handling the full-stack e-commerce rollout using `phytocommerce_pack` for module deployment. Coordinate any pack API or install-flow changes with that session.
