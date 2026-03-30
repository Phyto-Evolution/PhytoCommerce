---
title: "phytocommerce_pack"
description: "1-click meta-installer that deploys all PhytoCommerce modules in the correct dependency order with a live status dashboard."
module_name: "phytocommerce_pack"
category: "Pack Installer"
category_id: "pack"
version: "1.0"
weight: 10
---

## Overview

Think of this as the **"install everything" button**. Instead of uploading and installing each module one by one, you upload just this single pack and it installs all PhytoCommerce modules automatically in the correct order. A back-office dashboard shows which modules are installed and which aren't.

## What it does

- Upload a single zip to PrestaShop → all modules install automatically
- Resolves install order so dependencies land first
- Live status dashboard: **Admin → Advanced Parameters → PhytoCommerce Pack**
- Install or uninstall individual modules from the dashboard without touching the file system

## Source Layout

| Path | Purpose |
|------|---------|
| `phytocommerce_pack.php` | Module entry point, install orchestration |
| `bundled/` | All standalone module directories bundled inside |
| `bundled/README.md` | Lists every bundled module and its purpose |

## Installation

```bash
# Zip up the pack (including bundled modules)
zip -r phytocommerce_pack.zip modules/phytocommerce_pack/
# Admin → Modules → Upload a module → Install
```

After install go to **Admin → Advanced Parameters → PhytoCommerce Pack** and click **Install All**.
