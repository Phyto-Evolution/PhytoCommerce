# phyto_wholesale_portal

B2B wholesale tier module for PhytoCommerce. Supports MOQ enforcement, tiered pricing, application workflow, and optional invoice-on-delivery for approved wholesale accounts.

## Features

- Wholesale account application form (front-office)
- Manual or automatic approval workflow
- Tiered pricing table on the product page (wholesale customers only)
- MOQ (Minimum Order Quantity) enforcement on cart update
- Dedicated admin controller for managing applications
- Customer group-based access control

## Installation

1. Upload the `phyto_wholesale_portal` folder to `/modules/`.
2. Install from **Modules > Module Manager**.
3. Configure via **Modules > Configure** (Wholesale Portal Settings).

## Configuration

| Key | Description |
|-----|-------------|
| `PHYTO_WHOLESALE_GROUP_ID` | Customer group ID for approved wholesale accounts |
| `PHYTO_WHOLESALE_REQUIRE_APPROVAL` | 1 = manual review, 0 = auto-approve |
| `PHYTO_WHOLESALE_INVOICE_DELIVERY` | Allow invoice-on-delivery payment |
| `PHYTO_WHOLESALE_INVOICE_DAYS` | Days allowed for invoice payment (default 30) |

## Database Tables

| Table | Description |
|-------|-------------|
| `phyto_wholesale_application` | Submitted applications |
| `phyto_wholesale_product` | Per-product MOQ and tiered pricing JSON |

## Hooks

| Hook | Purpose |
|------|---------|
| `displayAdminProductsExtra` | Wholesale settings tab in product edit |
| `displayProductPriceBlock` | Tiered pricing table (wholesale customers only) |
| `actionCartUpdateQuantityBefore` | Enforce MOQ |
| `displayMyAccountBlock` | Apply / dashboard link in My Account |
| `actionProductDelete` | Clean up wholesale product data |

## Uninstall

Uninstalling the module drops both database tables and removes the admin tab. The wholesale customer group is **not** deleted to preserve customer assignments.
