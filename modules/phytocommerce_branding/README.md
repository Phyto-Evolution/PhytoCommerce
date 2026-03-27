# phytocommerce_branding

Theme-agnostic branding module that helps you re-skin a licensed PrestaShop theme to Phyto Commerce colors and messaging without modifying core theme files.

## What this module does

- Injects global CSS variables (`--phyto-brand-primary`, `--phyto-brand-secondary`, `--phyto-brand-accent`)
- Adds a top-of-page Phyto Commerce brand strip (optional logo + tagline)
- Applies brand colors to common PrestaShop UI elements (buttons, links, prices, focus states)
- Provides a back-office configuration form for quick brand customization

## Hooks

| Hook | Purpose |
|------|---------|
| `actionFrontControllerSetMedia` | Loads module stylesheet on front office pages |
| `displayHeader` | Injects CSS custom-property tokens based on module settings |
| `displayAfterBodyOpeningTag` | Renders top brand banner |

## Configuration keys

- `PHYTO_BRAND_NAME`
- `PHYTO_BRAND_TAGLINE`
- `PHYTO_BRAND_PRIMARY`
- `PHYTO_BRAND_SECONDARY`
- `PHYTO_BRAND_ACCENT`
- `PHYTO_BRAND_LOGO_URL`
- `PHYTO_BRAND_CONTACT_EMAIL`
- `PHYTO_BRAND_CONTACT_PHONE`
- `PHYTO_BRAND_CONTACT_ADDRESS`

## Recommended legal workflow

1. Buy/download a theme under a valid license.
2. Install that theme in PrestaShop.
3. Install this module.
4. Tune the tokens in **Modules → Phyto Commerce Theme Branding → Configure**.
5. For deeper layout changes, add child-theme overrides while preserving the original license terms.

## Default contact values

- Email: `aphytoevolution@gmail.com`
- Phone: `+91 82489 84778`
- Address: `Phyto Evolution Private Limited, Forest Studio Labs, Chennai - 603103.`
