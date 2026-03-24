# phyto_live_arrival

Live Arrival Guarantee (LAG) opt-in at checkout. Controls shipping window (allowed days), adds LAG fee or shows free-LAG threshold, and generates a LAG claim form linked to orders.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayPaymentTop` | Show LAG opt-in toggle with fee at checkout |
| `displayCheckoutSummaryTop` | Fallback checkout LAG display |
| `updateCart` | Persist LAG opt-in as cart extra field |
| `displayOrderDetail` | Show "File a LAG Claim" button within claim window |

## FrontControllers

- `PhytoLagClaimModuleFrontController` — LAG claim form (name, order, delivery date, issue, photo upload)

## Configuration Keys

| Key | Description |
|-----|-------------|
| `PHYTO_LAG_FEE` | LAG fee (decimal, 0 = free) |
| `PHYTO_LAG_FREE_ABOVE` | Cart total threshold for free LAG (0 = disabled) |
| `PHYTO_LAG_SHIP_DAYS` | JSON array of allowed ship day numbers (0=Sun…6=Sat) |
| `PHYTO_LAG_BLACKOUT` | Newline-separated YYYY-MM-DD blackout dates |
| `PHYTO_LAG_CLAIM_HOURS` | Claim window in hours (e.g. 48) |
| `PHYTO_LAG_TERMS` | LAG terms text displayed at checkout |
| `PHYTO_LAG_CLAIM_INSTRUCTIONS` | Instructions on the claim form |
| `PHYTO_LAG_NOTIFY_EMAIL` | Store email for new claim notifications |

## DB Tables

### `phyto_lag_order`

| Column | Type | Notes |
|--------|------|-------|
| `id_lag` | INT AUTO_INCREMENT | Primary key |
| `id_order` | INT UNIQUE | |
| `lag_opted` | TINYINT(1) | Customer opted in |
| `fee_charged` | DECIMAL(10,2) | Actual fee applied |
| `date_add` | DATETIME | |

### `phyto_lag_claim`

| Column | Type | Notes |
|--------|------|-------|
| `id_claim` | INT AUTO_INCREMENT | Primary key |
| `id_order` | INT | |
| `customer_name` | VARCHAR(150) | |
| `delivery_date` | DATE | |
| `issue_description` | TEXT | |
| `photo_filename` | VARCHAR(255) | Stored in `/img/phyto_lag/` |
| `claim_status` | ENUM | Received / Under Review / Approved / Rejected |
| `store_notes` | TEXT | Internal admin notes |
| `date_add` | DATETIME | |
| `date_upd` | DATETIME | |

## Inter-module Dependencies

Works alongside `phyto_dispatch_logger` — dispatch log evidence is shown to support LAG claims.
