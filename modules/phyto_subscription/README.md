# phyto_subscription

Recurring mystery box and replenishment subscriptions for PhytoCommerce, powered by the Cashfree Subscriptions API.

## Features

- Subscription plan management (back-office)
- Customer subscription sign-up (front-office)
- Cashfree webhook handling for renewal events
- Subscriber list with status tracking
- My Account block link to view plans

## Installation

1. Upload the `phyto_subscription` folder to `/modules/`.
2. Install from **Modules > Module Manager**.
3. Configure Cashfree API credentials via **Modules > Configure**.

## Configuration

| Key | Description |
|-----|-------------|
| `PHYTO_SUB_CF_CLIENT_ID` | Cashfree Client ID |
| `PHYTO_SUB_CF_CLIENT_SECRET` | Cashfree Client Secret |
| `PHYTO_SUB_CF_API_VERSION` | API version header (e.g. `2023-08-01`) |
| `PHYTO_SUB_CF_ENV` | `Sandbox` or `Production` |
| `PHYTO_SUB_CF_WEBHOOK_SECRET` | Webhook signature verification secret |

## Database Tables

| Table | Description |
|-------|-------------|
| `phyto_subscription_plan` | Available subscription plans |
| `phyto_subscription_customer` | Customer subscriptions and Cashfree subscription IDs |

## Admin Controllers

| Controller | Tab | Description |
|------------|-----|-------------|
| `AdminPhytoSubscription` | Catalog | Manage subscription plans |
| `AdminPhytoSubscriberList` | Orders | View and manage active subscribers |

## Hooks

| Hook | Purpose |
|------|---------|
| `displayMyAccountBlock` | Plans link in My Account sidebar |

## Front Controllers

| Controller | URL | Description |
|------------|-----|-------------|
| `plans` | `/module/phyto_subscription/plans` | Public plan listing |
| `subscribe` | `/module/phyto_subscription/subscribe` | Plan sign-up (requires login) |

## Uninstall

Uninstalling drops both database tables and removes both admin tabs.
