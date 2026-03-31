---
title: "phytocommerce_branding"
description: "Theme-agnostic CSS design tokens and brand banner — re-skin any licensed PrestaShop theme to Phyto Commerce colors without modifying core files."
module_name: "phytocommerce_branding"
category: "Foundation"
category_id: "foundation"
version: "1.0"
platform: "PrestaShop 8"
weight: 21
---

## Overview

Injects Phyto Commerce's design system — color tokens, typography scale, and brand banner — into any PrestaShop 8 theme via a single CSS override file. No theme core files are touched, so updates to your licensed theme won't break your branding.

## Features

- CSS custom properties (design tokens) for all brand colors
- Brand banner with logo placement configurable from admin
- Survives theme updates (override file, not core edit)
- Toggle banner on/off without disabling the module

## Source Layout

| Path | Purpose |
|------|---------|
| `phytocommerce_branding.php` | Module entry + admin config |
| `views/css/phyto-tokens.css` | CSS design token overrides |
| `views/templates/hook/brand_banner.tpl` | Optional banner Smarty template |
