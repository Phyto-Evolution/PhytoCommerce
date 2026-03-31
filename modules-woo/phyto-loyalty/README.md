# Phyto Loyalty (WooCommerce)

Points-based loyalty programme for WooCommerce. Customers earn **Green Points** on completed purchases and can redeem them as cart discounts. Admins manage balances, view full transaction ledgers, and configure all earn/redeem parameters.

---

## Features

### Admin
- WooCommerce Settings tab `phyto_loyalty` — earn rate, redeem rate, minimum points, max order percentage, points label, expiry days
- User edit screen — current balance, manual adjust (add/deduct with reason note), full transaction ledger
- Orders list column — points earned per order

### Front-end
- My Account **My Points** tab — balance card, transaction table, how-to-earn info
- Cart **Redeem Points** block — live balance display, AJAX apply/remove with validation
- Checkout earn preview — shows points to be earned on current order
- `woocommerce_order_status_completed` → credits points (deduplication via order meta)
- `woocommerce_order_status_refunded` → deducts previously credited points

---

## Settings

| Option | Default | Description |
|--------|---------|-------------|
| Points Label | Green Points | Display name for loyalty points |
| Points per ₹ Spent | 0.1 (1pt/₹10) | Earn rate |
| ₹ Value per Point Redeemed | 0.10 | Monetary value of one point |
| Minimum Points to Redeem | 100 | Threshold before redemption is allowed |
| Max % of Order Redeemable | 20% | Cap on points discount |
| Points Expiry (days) | 365 | 0 = never expire |

---

## Developer Hooks

```php
// Modify points earned for an order
add_filter( 'phyto_loyalty_points_earned', function( $points, $order ) {
    // Double points for orders over ₹2000
    if ( $order->get_total() > 2000 ) {
        return $points * 2;
    }
    return $points;
}, 10, 2 );

// Prevent specific users from redeeming
add_filter( 'phyto_loyalty_can_redeem', function( $can_redeem, $user_id ) {
    if ( user_has_no_redemption_privilege( $user_id ) ) {
        return false;
    }
    return $can_redeem;
}, 10, 2 );

// Change the points label dynamically
add_filter( 'phyto_loyalty_points_label', function( $label ) {
    return 'Phyto Points';
} );
```

---

## Database

Table: `{prefix}phyto_loyalty_ledger`

| Column | Type | Notes |
|--------|------|-------|
| id | BIGINT UNSIGNED | Auto-increment PK |
| user_id | BIGINT UNSIGNED | WordPress user |
| order_id | BIGINT UNSIGNED | Nullable |
| points | INT | Positive = credit, negative = debit |
| action | ENUM | `earn`, `redeem`, `manual`, `expire` |
| note | TEXT | Human-readable description |
| created_at | DATETIME | Row timestamp |

---

## File Structure

```
phyto-loyalty/
├── phyto-loyalty.php                          # Bootstrap, constants, activation, WC check
├── includes/
│   ├── class-phyto-loyalty-db.php             # Table creation + CRUD helpers
│   ├── class-phyto-loyalty-settings.php       # WooCommerce Settings tab + static getters
│   ├── class-phyto-loyalty-admin.php          # User edit meta box + orders column
│   └── class-phyto-loyalty-frontend.php       # My Account tab, cart block, AJAX, order hooks
├── assets/
│   ├── css/frontend.css
│   ├── css/admin.css
│   └── js/frontend.js
└── README.md
```

---

## Part of PhytoCommerce Suite

Module 10/10 of the WooCommerce port. See the [main README](../../README.md) for the full suite overview.
