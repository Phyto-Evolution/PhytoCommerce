---
title: "phytoquickadd"
description: "Fast product entry tool with AI-generated descriptions, taxonomy pack imports, multi-image upload, and automatic phyto_image_sec integration."
module_name: "phytoquickadd"
category: "Foundation"
category_id: "foundation"
version: "3.0"
platform: "PrestaShop 8"
weight: 22
---

## Overview

A tabbed back-office tool for adding products quickly — with AI description generation, botanical taxonomy management, multi-category assignment, and bulk image upload that automatically feeds through the image protection pipeline.

## Version History

### v1.0 — First Build
Single-page form: type a product name, generate an AI description, set a price, save. One category only, no image upload.

### v2.0 — Tabs, Categories, AI Toggle
4-tab layout. Dedicated "Add Category" tab with live dropdown tree. AI description toggle. Category management without navigating away.

### v3.0 — Taxonomy Packs, Multi-Category, Notes, Multi-Image Upload (current)
- **Taxonomy Packs tab** — import full botanical family trees (genus, species, cultivar) in one click from GitHub-hosted packs
- **Multi-category** — assign a product to multiple categories at once
- **Notes + hashtags** — `#hashtag` notes saved as PrestaShop product tags automatically
- **Multi-image upload** — first image becomes cover; all images go through `phyto_image_sec` pipeline (watermark → IPTC → WebP) on save

## Admin Pages

| Page | Purpose |
|------|---------|
| Add Product | Main quick-add form with all tabs |
| Add Category | Category tree builder |
| Taxonomy Packs | Browse and import botanical packs |

## Source Layout

| Path | Purpose |
|------|---------|
| `phytoquickadd.php` | Module entry + menu registration |
| `controllers/admin/` | Tab controllers for each admin page |
| `views/templates/admin/` | Smarty templates for each tab |
| `views/js/` | AJAX product save, taxonomy fetch, image preview |
