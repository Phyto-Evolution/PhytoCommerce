---
title: "phytoseobooster"
description: "Automated SEO for plant listings — JSON-LD Product schema injection, AI meta-title/description generation, and a bulk audit dashboard."
module_name: "phytoseobooster"
category: "Foundation"
category_id: "foundation"
version: "1.0"
weight: 24
---

## Overview

Handles SEO work automatically so you don't have to. When a product is added without meta tags, this module writes them using AI. Injects structured data (JSON-LD) on every product page so Google understands what you're selling. Includes a bulk audit dashboard.

## Features

- **JSON-LD schema** — injects `Product` structured data on every product page (name, price, availability, image, description)
- **Auto meta generation** — AI-writes `<meta name="description">` and `<title>` for new products when fields are empty
- **Bulk SEO audit** — table showing every product's SEO completeness score; one-click generate for missing fields
- **Carnivorous plant taxonomy aware** — uses botanical names in schema where `phyto_grex_registry` data is present

## Hooks Registered

| Hook | Purpose |
|------|---------|
| `displayHeader` | Injects JSON-LD `<script>` block |
| `actionProductSave` | Auto-generates meta on product save if fields empty |

## Source Layout

| Path | Purpose |
|------|---------|
| `phytoseobooster.php` | Module entry + hook handlers |
| `classes/SchemaBuilder.php` | Builds JSON-LD product schema array |
| `classes/MetaGenerator.php` | AI meta title/description generation |
| `controllers/admin/AdminPhytoSeoAuditController.php` | Bulk audit dashboard |
| `views/templates/hook/jsonld.tpl` | JSON-LD output template |
