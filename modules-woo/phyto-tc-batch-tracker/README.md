# Phyto TC Batch Tracker for WooCommerce

**Version:** 1.0.0
**Platform:** WooCommerce (WordPress)
**Category:** Plant Science
**Plugin slug:** `phyto-tc-batch-tracker`

---

## What it does

Tracks tissue-culture (TC) batch provenance for WooCommerce products. Each product can be linked to one or more `phyto_tc_batch` records. A batch record captures:

- **Batch Code** — unique identifier (e.g. `TC-2025-001`)
- **Parent Plant / Donor Clone** — the mother plant or accession used for explants
- **Agar Medium Formula** — e.g. `MS + 0.1 mg/L BAP`
- **Deflask Date** — when plantlets were transferred out of sterile culture
- **Lab Technician / Operator** — the responsible person
- **Batch Notes** — contamination events, viability rates, observations
- **Status** — `active`, `depleted`, or `quarantined`

On the single product page a **Batch Provenance** tab lists all linked batches (batch code, deflask date, status badge). The tab is hidden when no batches are linked.

---

## File structure

```
phyto-tc-batch-tracker/
├── phyto-tc-batch-tracker.php          Bootstrap, constants, WC check, hook registration
├── includes/
│   ├── class-phyto-tcb-cpt.php         phyto_tc_batch CPT, batch details meta box, get_all_batches()
│   ├── class-phyto-tcb-admin.php       Product "TC Batch Links" meta box (Select2) + list-table column
│   └── class-phyto-tcb-frontend.php    Batch Provenance product tab, CSS enqueue
├── assets/
│   ├── css/admin.css                   Meta box, list column, and frontend tab styles
│   └── js/admin.js                     Select2 initialisation + option formatter
└── README.md
```

---

## Admin workflow

1. Go to **WooCommerce → TC Batches** → **Add New Batch**.
   Fill in the batch code, donor clone, medium, deflask date, operator, notes, and status.
2. Open any WooCommerce product. In the **TC Batch Links** meta box (main column), search and select one or more batches.
3. Save the product. The products list table shows a count badge in the **TC Batches** column.

---

## Developer hooks

### `phyto_tcb_tab_title` (filter)

Change the front-end tab title.

```php
add_filter( 'phyto_tcb_tab_title', function( $title ) {
    return __( 'Lab Provenance', 'my-theme' );
} );
```

### `phyto_tcb_batch_fields` (filter)

Customise which fields appear in the front-end provenance table.

```php
add_filter( 'phyto_tcb_batch_fields', function( $fields ) {
    // Add operator column.
    $fields[] = array(
        'key'   => 'operator',
        'label' => __( 'Lab Operator', 'my-theme' ),
    );
    return $fields;
} );
```

### `phyto_tcb_status_labels` (filter)

Override the status badge labels (used in both admin and frontend).

```php
add_filter( 'phyto_tcb_status_labels', function( $labels ) {
    $labels['quarantined'] = __( 'Under Review', 'my-theme' );
    return $labels;
} );
```

---

## Constants

| Constant | Value |
|----------|-------|
| `PHYTO_TCB_VERSION` | `1.0.0` |
| `PHYTO_TCB_PATH` | Absolute path to plugin directory (with trailing slash) |
| `PHYTO_TCB_URL` | URL to plugin directory (with trailing slash) |

---

## Meta keys

| Meta key | Post type | Description |
|----------|-----------|-------------|
| `_phyto_tc_batches` | `product` | Array of linked `phyto_tc_batch` post IDs |
| `_phyto_tcb_batch_code` | `phyto_tc_batch` | Batch identifier string |
| `_phyto_tcb_parent_plant` | `phyto_tc_batch` | Donor clone / parent plant |
| `_phyto_tcb_agar_medium` | `phyto_tc_batch` | Culture medium formula |
| `_phyto_tcb_deflask_date` | `phyto_tc_batch` | Deflask date (YYYY-MM-DD) |
| `_phyto_tcb_operator` | `phyto_tc_batch` | Lab technician name |
| `_phyto_tcb_notes` | `phyto_tc_batch` | Internal batch notes |
| `_phyto_tcb_status` | `phyto_tc_batch` | `active` \| `depleted` \| `quarantined` |
