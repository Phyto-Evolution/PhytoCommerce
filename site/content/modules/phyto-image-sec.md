---
title: "phyto_image_sec"
description: "Image protection pipeline — logo watermark, IPTC copyright metadata embedding, WebP conversion, optional product-name text overlay, and a batch processor for existing catalogues."
module_name: "phyto_image_sec"
category: "Security & Identity"
category_id: "security-identity"
version: "0.3"
platform: "PrestaShop 8"
weight: 70
---

## Overview

Protects your plant photography automatically. Every uploaded product image goes through a pipeline: logo watermark → IPTC copyright metadata → WebP copy generation → optional text overlay. The only version ever served is the watermarked one. A batch processor applies the pipeline to your existing catalogue.

## Version History

### v0.1 — First Build
Logo watermark stamped on every product image at upload. Configurable position and opacity. Right-click blocker on the front end.

### v0.2 — WebP Generation and IPTC Copyright Metadata
- **IPTC metadata** — shop name and URL embedded as invisible copyright data inside every JPEG; travels with the file wherever it goes
- **WebP generation** — compressed WebP copy generated alongside every JPEG (30–40% smaller); browser always loads the watermarked WebP, not the clean original

### v0.3 — Product Name Text Overlay, Batch Processor, QuickAdd Integration (current)
- **Text overlay** — plant name embedded directly onto the image in white text with dark outline; default position: bottom-left, rotated 90° (upward); admin-configurable position, font size, and toggle
- **Batch processor** — walks entire product catalogue in chunks of 20, applying full pipeline without timing out
- **phytoquickadd integration** — all images uploaded through Quick Add automatically pass through the pipeline

## Pipeline Order

```
Upload → Watermark → IPTC Embed → WebP Generate → Text Overlay → Save
```

## Source Layout

| Path | Purpose |
|------|---------|
| `phyto_image_sec.php` | Module entry + hooks |
| `classes/PhytoImagePipeline.php` | Orchestrates the full pipeline |
| `classes/PhytoWatermarker.php` | GD watermark stamping |
| `classes/PhytoIptcWriter.php` | IPTC metadata injection |
| `classes/PhytoWebPConverter.php` | WebP generation via GD/libwebp |
| `classes/PhytoTextOverlay.php` | Product name text overlay |
| `controllers/admin/AdminPhytoBatchProcessController.php` | Batch processor UI |
