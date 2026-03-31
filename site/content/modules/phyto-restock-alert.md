---
title: "phyto_restock_alert"
description: "'Notify me when available' — out-of-stock email subscriptions with automatic dispatch on restock, admin subscriber management, and manual send capability."
module_name: "phyto_restock_alert"
category: "Invoicing & Notifications"
category_id: "invoicing-notifications"
version: "1.0"
platform: "PrestaShop 8"
weight: 81
---

## Overview

When a product is out of stock, a widget on the product page lets customers subscribe with their email. The moment stock goes above zero — from any source — all subscribers for that product receive a branded email with a direct buy link. No manual step required.

## Customer Flow

1. Product page shows "Out of Stock" + "Notify me when available" widget
2. Customer enters name + email (works for guests and logged-in customers)
3. On any stock update (manual or import) that brings quantity above 0, all subscribers receive the notification email automatically
4. Email contains product name, image, price, and a direct "Buy Now" link

## Admin Features

- **Restock Alert Subscriptions** panel: list all subscriptions filterable by product or notified status
- Delete individual subscriptions
- Mark as notified (for manual tracking)
- Manual send: trigger notification email for any row from the admin
- Per-product subscriber count shown inside the product edit page

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_restock_alert.php` | Module entry + hooks |
| `classes/PhytoRestockSubscriber.php` | ORM for subscriber records |
| `classes/PhytoRestockMailer.php` | Email notification dispatch |
| `controllers/front/subscribe.php` | AJAX subscription endpoint |
| `controllers/admin/AdminPhytoRestockAlertController.php` | Admin subscriber list |
| `sql/install.sql` | Creates subscribers table |
| `views/templates/hook/widget.tpl` | Product page subscribe widget |
