# phyto_tc_batch_tracker

Links tissue-culture (TC) products to propagation batch records. Buyers see full provenance (batch code, generation, protocol, deflask/cert dates) on the product page; admins manage batch-grouped inventory from a dedicated back-office controller.

## Features

- **Batch CRUD** — batch code (auto-suggested `YYYYMM-GENUS-001`), species/clone name, generation (G0–G3+, Acclimated, Hardened), initiation / deflask / certification dates, sterility protocol, units produced/remaining, status
- **Per-product/attribute linking** — AJAX link and unlink from the product edit tab
- **Front-office Provenance tab** — timeline of initiation → deflask → certification; status badge; units available
- **Status colours** — Active (green), Quarantined (red), Depleted (grey), Archived (amber) in both list and front card
- **Auto batch-code suggestion** on species name entry (back office)

## Installation

1. Upload `phyto_tc_batch_tracker/` to `/modules/`.
2. Install from **Modules > Module Manager**.
3. Manage batches via **Catalog > TC Batches**.

## Database Tables

| Table | Description |
|-------|-------------|
| `phyto_tc_batch` | Batch records (code, species, dates, inventory) |
| `phyto_tc_batch_product` | Product ↔ batch link (unique per product+attribute) |

## Admin Controllers

| Controller | Tab | Description |
|------------|-----|-------------|
| `AdminPhytoTcBatches` | Catalog | Full CRUD for TC batch records |
| `AdminPhytoTcBatchProduct` | Hidden | AJAX endpoint for product-tab link/unlink |

## Hooks

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Batch-linking tab on product edit page |
| `displayProductExtraContent` | Provenance tab on product front page |
| `displayBackOfficeHeader` | Inject admin CSS + JS on relevant pages |
| `displayHeader` | Inject front CSS |

## Generation Values

`G0` (explant) → `G1` → `G2` → `G3+` → `Acclimated` → `Hardened`

## Uninstall

Drops both database tables and removes both admin tabs.
