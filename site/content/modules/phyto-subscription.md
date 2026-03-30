---
title: "phyto_subscription"
description: "Recurring mystery box and replenishment subscriptions via Cashfree — plan builder, subscriber management, and admin panel."
module_name: "phyto_subscription"
category: "Commerce"
category_id: "commerce"
version: "1.0"
weight: 61
---

## Overview

Lets customers subscribe to regular deliveries — mystery plant boxes, monthly replenishment orders, or curated collections. Create subscription plans with name, price, and frequency. Recurring payments are handled via Cashfree Subscriptions API.

## Features

- Create unlimited subscription plans (name, price, frequency, description)
- Customer-facing plans listing page with subscribe button (login required)
- Cashfree Subscriptions API integration for recurring billing
- Admin subscriber management: view, pause, cancel subscriptions
- Webhook handler for Cashfree payment events (success, failure, cancel)
- Subscriber email notifications on successful charge and failure

## Plan Types

| Frequency | Example |
|-----------|---------|
| Monthly | "Monthly Mystery Box" |
| Bi-monthly | "Rare Plant Replenishment" |
| Quarterly | "Seasonal Collector Box" |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_subscription.php` | Module entry + hooks |
| `classes/PhytoSubscriptionPlan.php` | ORM for plan definitions |
| `classes/PhytoSubscriber.php` | ORM for subscriber records |
| `classes/CashfreeClient.php` | Cashfree API wrapper |
| `controllers/front/plans.php` | Public plans listing page |
| `controllers/front/webhook.php` | Cashfree webhook endpoint |
| `controllers/admin/AdminPhytoSubscriptionController.php` | Admin panel |
| `sql/install.sql` | Creates plans + subscriber tables |
