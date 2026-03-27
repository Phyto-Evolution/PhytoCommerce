# bundled/

When deploying PhytoCommerce Pack as a **standalone zip** (without the full repo),
copy each module directory here before zipping.

```
phytocommerce_pack/
└── bundled/
    ├── phytocommercefooter/
    ├── phytoquickadd/
    ├── phytoerpconnector/
    ├── phytoseobooster/
    ├── phyto_grex_registry/
    ├── phyto_tc_batch_tracker/
    ├── phyto_growth_stage/
    ├── phyto_seasonal_availability/
    ├── phyto_care_card/
    ├── phyto_climate_zone/
    ├── phyto_acclimation_bundler/
    ├── phyto_live_arrival/
    ├── phyto_growers_journal/
    ├── phyto_collection_widget/
    ├── phyto_source_badge/
    ├── phyto_dispatch_logger/
    ├── phyto_phytosanitary/
    ├── phyto_tc_cost_calculator/
    ├── phyto_wholesale_portal/
    └── phyto_subscription/
```

**When deploying from a full repo checkout** (all modules already in PrestaShop's
`/modules/` directory), this folder can remain empty — the pack detects modules
in `_PS_MODULE_DIR_` automatically.

## Building the deployment zip

```bash
# From the repo root:
cd modules
cp -r phyto_* phytocommercefooter phytocommerce_branding phytoquickadd phytoerpconnector phytoseobooster \
      phytocommerce_pack/bundled/
zip -r phytocommerce_pack.zip phytocommerce_pack/
# Upload phytocommerce_pack.zip to PrestaShop → Modules → Upload a module
```
