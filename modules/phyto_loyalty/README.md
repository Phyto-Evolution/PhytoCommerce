# phyto_loyalty

Points-based loyalty programme for PhytoCommerce.

## Features

- Earn points on every order (configurable rate per ₹ spent)
- Redeem points as cart discount (configurable ₹ value per point)
- Four tiers based on lifetime points: Seed → Sprout → Bloom → Rare
- Full transaction ledger (earn, redeem, expire, adjust, refund)
- Admin manual point adjustments per customer
- Points expiry after configurable days of inactivity
- Email notification on points earned (with tier progress)
- "My Loyalty Points" page in customer account
- Redeem widget in shopping cart

## Configuration

| Key | Default | Description |
|-----|---------|-------------|
| `PHYTO_LOYALTY_EARN_RATE` | `0.1` | Points per ₹ spent (0.1 = 1 point per ₹10) |
| `PHYTO_LOYALTY_REDEEM_RATE` | `0.50` | ₹ value per point redeemed |
| `PHYTO_LOYALTY_MIN_REDEEM` | `100` | Minimum points required to redeem |
| `PHYTO_LOYALTY_MAX_REDEEM_PCT` | `20` | Max % of order value redeemable with points |
| `PHYTO_LOYALTY_EXPIRY_DAYS` | `365` | Days of inactivity before points expire (0 = never) |
| `PHYTO_LOYALTY_ENABLED` | `1` | Master on/off switch |

## Tier Thresholds (Lifetime Points)

| Tier | Points Required |
|------|----------------|
| Seed | 0 – 499 |
| Sprout | 500 – 1,999 |
| Bloom | 2,000 – 4,999 |
| Rare | 5,000+ |

## Database Tables

- `ps_phyto_loyalty_account` — one row per customer (balance, tier, lifetime stats)
- `ps_phyto_loyalty_transaction` — full points ledger

## Hooks

- `actionOrderStatusPostUpdate` — award/reverse points on order complete/cancel
- `displayCustomerAccount` — "My Points" link
- `displayMyAccountBlock` — balance widget in account sidebar
- `displayShoppingCartFooter` — redeem widget in cart
- `displayHeader` — CSS/JS assets
- `displayAdminCustomersForm` — loyalty info in customer admin page
