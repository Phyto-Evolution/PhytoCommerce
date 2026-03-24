# phytoerpconnector

Connects PrestaShop 8.x to ERPNext v15 via the ERPNext REST API. Syncs orders, customers, and products bi-directionally using action hooks, with a full sync log for audit.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `actionOrderStatusPostUpdate` | Push order to ERPNext on status change |
| `actionCustomerAccountAdd` | Push new customer to ERPNext |
| `actionObjectProductAddAfter` | Push new product to ERPNext |
| `actionObjectProductUpdateAfter` | Push updated product to ERPNext |

## Configuration Keys

| Key | Description |
|-----|-------------|
| `PHYTO_ERP_URL` | ERPNext base URL (e.g. https://erp.example.com) |
| `PHYTO_ERP_API_KEY` | ERPNext API key |
| `PHYTO_ERP_API_SECRET` | ERPNext API secret |
| `PHYTO_ERP_SYNC_ORDERS` | Enable order sync (0/1) |
| `PHYTO_ERP_SYNC_CUSTOMERS` | Enable customer sync (0/1) |
| `PHYTO_ERP_SYNC_PRODUCTS` | Enable product sync (0/1) |

## DB Tables

### `phyto_erp_sync_log`

| Column | Type | Notes |
|--------|------|-------|
| `id` | INT AUTO_INCREMENT | Primary key |
| `sync_type` | VARCHAR(32) | order / customer / product / invoice |
| `direction` | VARCHAR(8) | push / pull |
| `ps_id` | INT | PrestaShop object ID |
| `erp_name` | VARCHAR(255) | ERPNext document name |
| `status` | VARCHAR(16) | success / error / skipped |
| `message` | TEXT | Error or info message |
| `created_at` | DATETIME | |

## Inter-module Dependencies

None. Standalone module.
