# phyto_acclimation_bundler

Auto-suggest acclimation accessories when TC or deflasked plants are added to cart.

## Purpose

When a TC/deflasked plant is detected in the cart, displays a dismissable widget below the cart summary suggesting acclimation accessories (humidity dome, sphagnum, perlite, distilled water, etc.). Optionally applies a bundle discount when all items are added.

## Hooks Registered

- `displayHeader` — injects kit product data as JSON + registers CSS/JS assets
- `displayShoppingCartFooter` — renders the widget HTML container

## Configuration Keys

| Key | Description | Default |
|-----|-------------|---------|
| `PHYTO_ACCLIM_PRODUCTS` | Comma-separated kit product IDs | `` |
| `PHYTO_ACCLIM_STAGES` | Comma-separated growth stage IDs (from phyto_growth_stage) that trigger the widget | `` |
| `PHYTO_ACCLIM_TAGS` | Comma-separated product tags (fallback when growth_stage module not installed) | `` |
| `PHYTO_ACCLIM_DISCOUNT` | Bundle discount % (0 = disabled) | `0` |
| `PHYTO_ACCLIM_HEADLINE` | Widget headline text | `Your plant needs an acclimation kit` |
| `PHYTO_ACCLIM_MAX_SHOW` | Max number of kit products shown | `3` |

## DB Tables

None. All configuration stored via PrestaShop `Configuration::get/set`.

## Inter-module Dependencies

- **Optional:** `phyto_growth_stage` — if installed and enabled, uses growth stage assignments to detect trigger products
- **Fallback:** Uses product tags when `phyto_growth_stage` is not available

## Behaviour

1. On cart/order pages, `displayHeader` serialises kit product data into a `<script>` block
2. JavaScript (`acclimation.js`) checks if any cart product matches a trigger stage or tag
3. If triggered and kit items not already in cart, the widget is shown
4. Dismiss is session-based (`sessionStorage`) — widget will not re-appear in the same session after dismissal
5. "Add all" button adds all kit items to cart and dismisses the widget
