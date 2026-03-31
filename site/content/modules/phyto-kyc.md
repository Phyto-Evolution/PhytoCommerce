---
title: "phyto_kyc"
description: "PAN and GST identity verification for wholesale or high-value customers — price blur for unverified users, admin review queue."
module_name: "phyto_kyc"
category: "Security & Identity"
category_id: "security-identity"
version: "1.0"
platform: "PrestaShop 8"
weight: 71
---

## Overview

Adds KYC (Know Your Customer) verification for business customers. Unverified customers see prices blurred on selected product categories. Once they submit their PAN/GST details and an admin approves, prices unlock and their account gains "Verified Business" status.

## Features

- **Price blur** — configurable CSS blur effect on prices for unverified customers on selected categories
- **KYC submission form** — customer uploads PAN card, GST certificate, and business name from their account page
- **Admin review queue** — admin views submitted documents, approves or rejects with a note
- **Approval email** — customer notified on approval/rejection
- **Verified badge** — "Verified Business" badge displayed on the customer's account and in admin customer list

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayProductPriceBlock` | Blurs price for unverified customers |
| `displayCustomerAccount` | KYC submission link |
| `actionCustomerAccountAdd` | Auto-emails KYC invite to new B2B customers |

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_kyc.php` | Module entry + hooks |
| `classes/PhytoKycRecord.php` | ORM for KYC submissions |
| `controllers/front/submit.php` | Customer KYC submission form |
| `controllers/admin/AdminPhytoKycController.php` | Admin review queue |
| `sql/install.sql` | Creates KYC records table |
| `views/templates/hook/price_blur.tpl` | Price blur overlay template |
