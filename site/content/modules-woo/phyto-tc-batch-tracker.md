---
title: "Phyto TC Batch Tracker (WooCommerce)"
description: "Link WooCommerce products to tissue-culture batch records — track provenance, deflask dates, and lab operators on your storefront."
module_name: "phyto-tc-batch-tracker"
platform: "WooCommerce"
category: "Plant Science"
category_id: "woo-plant-science"
version: "1.0.0"
weight: 36
---

## Overview

Phyto TC Batch Tracker brings full tissue-culture provenance transparency to WooCommerce product pages. Specialty plant retailers selling TC-propagated material often have detailed lab records — batch codes, donor clones, culture medium formulas, deflask dates, responsible technicians — but no practical way to surface that information to customers or to tie it back to specific product listings. This plugin closes that gap.

Store owners maintain a library of `phyto_tc_batch` records under **WooCommerce → TC Batches**. Each record captures the complete lineage of a batch: its unique batch code, the parent plant or donor clone used for explants, the agar medium formula, the date plants were transferred from sterile culture to substrate (deflask date), the lab technician responsible, internal notes, and a current status (`active`, `depleted`, or `quarantined`). Products are then linked to one or more batches via a searchable Select2 picker in the product editor — a single product can represent plant material from multiple batches, and the same batch can be linked to multiple products.

On the customer-facing side, the single product page gains a **Batch Provenance** tab that lists all linked batches with their batch code, deflask date, and a colour-coded status badge. The tab is suppressed automatically when no batches are linked, so it never appears for non-TC products. A products list-table column in the admin shows a linked batch count at a glance, making it easy for shop managers to audit which products have provenance records attached.

---

## Features

- **`phyto_tc_batch` custom post type** — not publicly queryable; managed under the WooCommerce admin menu as "TC Batches"
- **Full batch record fields** — batch code, parent plant/donor clone, agar medium formula, deflask date, lab operator, notes, and status
- **Three batch statuses** — `active`, `depleted`, and `quarantined`, each rendered as a distinct colour-coded badge
- **Product linking** — searchable Select2 multi-select on the product edit screen; supports linking multiple batches per product
- **Batch Provenance tab** — automatically added to the single product page when batches are linked; hidden otherwise
- **Products list-table column** — shows linked batch count with a clickable badge for quick access
- **Translation-ready** — all strings wrapped in `__()` / `esc_html__()` with the `phyto-tc-batch-tracker` text domain
- **Secure saving** — nonce verification, `check_admin_referer`, `current_user_can`, and full input sanitisation on every save

---

## Developer Hooks

### `phyto_tcb_tab_title`

Filter the title of the Batch Provenance tab on the single product page.

```php
/**
 * @param string $title Default tab title ("Batch Provenance").
 * @return string Modified tab title.
 */
add_filter( 'phyto_tcb_tab_title', function( $title ) {
    return __( 'Lab Provenance', 'my-theme' );
} );
```

### `phyto_tcb_batch_fields`

Filter the field columns displayed in the front-end provenance table. Each entry is an array with a `key` (matching a key in the batch data array) and a `label` (the column heading shown to the customer). Use this to add columns such as operator, agar medium, or parent plant, or to remove the defaults entirely.

```php
/**
 * @param array $fields Array of field definition arrays (key, label).
 * @return array Modified fields array.
 */
add_filter( 'phyto_tcb_batch_fields', function( $fields ) {
    $fields[] = array(
        'key'   => 'operator',
        'label' => __( 'Lab Operator', 'my-theme' ),
    );
    return $fields;
} );
```

### `phyto_tcb_status_labels`

Filter the human-readable labels for the three batch statuses. Applied in both the admin Select2 picker and the front-end status badge.

```php
/**
 * @param array $labels Associative array: status slug => display label.
 * @return array Modified labels.
 */
add_filter( 'phyto_tcb_status_labels', function( $labels ) {
    $labels['quarantined'] = __( 'Under Review', 'my-theme' );
    return $labels;
} );
```
