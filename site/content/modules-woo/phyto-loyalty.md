---
title: "Phyto Loyalty (WooCommerce)"
description: "Points-based loyalty programme — customers earn Green Points on purchases and redeem them as cart discounts."
module_name: "phyto-loyalty"
platform: "WooCommerce"
category: "Commerce"
category_id: "woo-commerce"
version: "1.0.0"
weight: 40
---

## Overview

Phyto Loyalty brings a complete points-based reward programme to WooCommerce stores. Every time a customer completes an order, they automatically earn Green Points calculated from their order total. Those points accumulate in a personal ledger and can be redeemed as a cart discount on any future purchase — giving customers a concrete incentive to return and spend more.

The plugin was designed with specialty plant retailers in mind, where repeat customers are the backbone of the business. Tissue-culture collectors, rare-aroid buyers, and carnivorous-plant hobbyists all tend to make multiple purchases per season; Phyto Loyalty turns that behaviour into a measurable retention loop. Points are displayed prominently in My Account and at the cart, so customers always know how close they are to their next reward.

On the admin side, store owners have full visibility and control. Every earn, redeem, manual adjustment, and expiry is stored in a dedicated ledger table with a timestamp and note, making it easy to audit balances, resolve disputes, and run ad-hoc promotions by manually crediting extra points to specific customers. All earn/redeem parameters are configurable from a dedicated WooCommerce Settings tab without touching code.

---

## Features

### Admin
- WooCommerce Settings tab (`phyto_loyalty`) with six configurable parameters: earn rate, redeem rate, minimum redemption threshold, maximum discount percentage, points label, and expiry window.
- User edit screen section showing current balance, a manual adjust form (add or deduct any amount with a reason note), and a full paginated transaction ledger.
- "Points Earned" column added to the WooCommerce orders list, compatible with both HPOS (High-Performance Order Storage) and legacy CPT orders.

### Front-end
- My Account **My Points** tab — balance card, recent transactions table, and a plain-language how-to-earn summary.
- Cart **Redeem Points** block — displayed below cart totals; shows current balance, accepts a points input, and applies/removes the discount via AJAX without a full page reload.
- Applied redemption creates a negative cart fee (`phyto_loyalty_discount`) capped at the configured maximum percentage of the order total.
- Checkout earn preview — a green info bar telling the customer exactly how many points they will earn on the current order before they confirm payment.
- Points are credited automatically when an order is marked **Completed**; duplicate crediting is prevented via order meta.
- Points are deducted automatically when an order is **Refunded**, reversing previously credited points.

---

## Settings

| Setting | Default | Description |
|---------|---------|-------------|
| Points Label | Green Points | Display name shown to customers throughout the store |
| Points per ₹ Spent | 0.1 (= 1 pt per ₹10) | Earn rate — multiplied by the order total to calculate points |
| ₹ Value per Point Redeemed | ₹0.10 | Monetary value of one point when applied as a discount |
| Minimum Points to Redeem | 100 | Customer must hold at least this balance before redeeming |
| Max % of Order Redeemable | 20% | Upper cap on the points discount as a percentage of order subtotal |
| Points Expiry (days) | 365 | Days before earned points expire; set to 0 to disable expiry entirely |

---

## Developer Hooks

### `phyto_loyalty_points_earned`

Filter the number of points credited when an order is completed. Use this to implement bonus-point campaigns, category multipliers, or VIP tiers.

```php
/**
 * @param int       $points Calculated points.
 * @param WC_Order  $order  The completed order.
 */
add_filter( 'phyto_loyalty_points_earned', function( $points, $order ) {
    // Double points for orders over ₹2000.
    if ( $order->get_total() > 2000 ) {
        return $points * 2;
    }
    return $points;
}, 10, 2 );
```

### `phyto_loyalty_can_redeem`

Filter whether a given user is permitted to redeem points at the cart. Return `false` to hide the redeem block for specific roles or accounts.

```php
/**
 * @param bool $can_redeem Whether redemption is currently allowed.
 * @param int  $user_id    WordPress user ID.
 */
add_filter( 'phyto_loyalty_can_redeem', function( $can_redeem, $user_id ) {
    // Disable redemption for wholesale customers.
    if ( user_has_role( $user_id, 'wholesale_customer' ) ) {
        return false;
    }
    return $can_redeem;
}, 10, 2 );
```

### `phyto_loyalty_points_label`

Filter the display label for loyalty points. Useful for white-labelling or translating the points name without changing the settings value.

```php
/**
 * @param string $label Current label string.
 */
add_filter( 'phyto_loyalty_points_label', function( $label ) {
    return __( 'Phyto Points', 'my-theme' );
} );
```
