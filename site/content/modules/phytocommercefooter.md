---
title: "phytocommercefooter"
description: "Replaces the default PrestaShop footer with a custom Phyto-branded one, consistent across all store pages."
module_name: "phytocommercefooter"
category: "Foundation"
category_id: "foundation"
version: "1.0"
platform: "PrestaShop 8"
weight: 20
---

## Overview

Replaces the default PrestaShop footer with a custom Phyto Commerce branded footer. Keeps your store looking consistent and professional without editing theme files. Install and activate — it takes over automatically.

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayFooter` | Injects the Phyto branded footer block |

## Source Layout

| Path | Purpose |
|------|---------|
| `phytocommercefooter.php` | Module entry point + hook handler |
| `views/templates/hook/` | Smarty template for the footer HTML |
| `views/css/` | Footer stylesheet |
