<?php
/**
 * Phyto_Bundle_BuilderBuilderModuleFrontController
 *
 * Main bundle builder page.
 *
 * GET  ?id_bundle=X  — Show the bundle template with slots and product pickers
 * GET  (no id_bundle) — Show list of all active bundles
 * POST               — Validate selections, apply discount, add products to cart
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_bundle_builder/classes/PhytoBundle.php';
require_once _PS_MODULE_DIR_ . 'phyto_bundle_builder/classes/PhytoBundleSlot.php';

class Phyto_Bundle_BuilderBuilderModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function initContent(): void
    {
        parent::initContent();

        $this->context->controller->registerStylesheet(
            'phyto-bundle-builder-css',
            'modules/phyto_bundle_builder/views/css/front.css',
            ['media' => 'all', 'priority' => 200]
        );
        $this->context->controller->registerJavascript(
            'phyto-bundle-builder-js',
            'modules/phyto_bundle_builder/views/js/front.js',
            ['position' => 'bottom', 'priority' => 200]
        );

        if (Tools::isSubmit('submitBundleToCart')) {
            $this->processAddToCart();
            return;
        }

        $idBundle = (int) Tools::getValue('id_bundle');

        if ($idBundle > 0) {
            $this->showBuilder($idBundle);
        } else {
            $this->showBundleList();
        }
    }

    // -------------------------------------------------------------------------
    // Show the list of active bundles
    // -------------------------------------------------------------------------

    private function showBundleList(): void
    {
        $idLang  = (int) $this->context->language->id;
        $bundles = PhytoBundle::getActiveBundles($idLang);

        $this->context->smarty->assign([
            'phyto_bundles'      => $bundles,
            'phyto_builder_base' => $this->context->link->getModuleLink('phyto_bundle_builder', 'builder'),
        ]);

        $this->setTemplate('module:phyto_bundle_builder/views/templates/front/builder.tpl');
    }

    // -------------------------------------------------------------------------
    // Show the bundle builder for a specific bundle
    // -------------------------------------------------------------------------

    private function showBuilder(int $idBundle): void
    {
        $idLang = (int) $this->context->language->id;
        $bundle = PhytoBundle::getBundleWithLang($idBundle, $idLang);

        if (!$bundle || !(int) $bundle['active']) {
            Tools::redirect($this->context->link->getModuleLink('phyto_bundle_builder', 'builder'));
        }

        $slots        = PhytoBundle::getSlots($idBundle);
        $slotsWithProducts = [];

        foreach ($slots as $slot) {
            $products = PhytoBundleSlot::getProductsForSlot(
                (int) $slot['id_slot'],
                $idLang,
                '',
                50
            );
            $slotsWithProducts[] = array_merge($slot, ['products' => $products]);
        }

        $productsUrl = $this->context->link->getModuleLink('phyto_bundle_builder', 'products');
        $builderUrl  = $this->context->link->getModuleLink('phyto_bundle_builder', 'builder');

        $this->context->smarty->assign([
            'phyto_bundle'        => $bundle,
            'phyto_slots'         => $slotsWithProducts,
            'phyto_products_url'  => $productsUrl,
            'phyto_builder_url'   => $builderUrl,
            'phyto_form_token'    => Tools::getToken(false),
            'phyto_cta_text'      => Configuration::get('PHYTO_BUNDLE_CTA_TEXT', null, null, null, 'Add Bundle to Cart'),
            'phyto_show_savings'  => (bool) Configuration::get('PHYTO_BUNDLE_SHOW_SAVINGS', null, null, null, 1),
            'phyto_currency'      => $this->context->currency,
        ]);

        $this->setTemplate('module:phyto_bundle_builder/views/templates/front/builder.tpl');
    }

    // -------------------------------------------------------------------------
    // Process form POST: validate and add to cart
    // -------------------------------------------------------------------------

    private function processAddToCart(): void
    {
        if (!Tools::checkToken()) {
            $this->errors[] = $this->module->l('Invalid security token. Please try again.');
            $this->redirectBackToBuilder();
            return;
        }

        $idBundle = (int) Tools::getValue('id_bundle');
        $idLang   = (int) $this->context->language->id;
        $bundle   = PhytoBundle::getBundleWithLang($idBundle, $idLang);

        if (!$bundle || !(int) $bundle['active']) {
            Tools::redirect($this->context->link->getModuleLink('phyto_bundle_builder', 'builder'));
        }

        $slots      = PhytoBundle::getSlots($idBundle);
        $selections = [];
        $errors     = [];

        foreach ($slots as $slot) {
            $idSlot     = (int) $slot['id_slot'];
            $idProduct  = (int) Tools::getValue('slot_' . $idSlot);

            if ($idProduct > 0) {
                // Validate that the chosen product actually belongs to this slot
                if (!$this->isProductValidForSlot($idProduct, $idSlot, $idLang)) {
                    $errors[] = sprintf(
                        $this->module->l('Invalid product selected for slot "%s".'),
                        $slot['slot_name']
                    );
                    continue;
                }
                $selections[$idSlot] = $idProduct;
            } elseif ((int) $slot['required']) {
                $errors[] = sprintf(
                    $this->module->l('Please select a product for "%s".'),
                    $slot['slot_name']
                );
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $this->errors[] = $err;
            }
            $this->redirectBackToBuilder($idBundle);
            return;
        }

        // Calculate prices and discount
        $subtotal        = 0.0;
        $idShop          = (int) $this->context->shop->id;
        $idCurrency      = (int) $this->context->currency->id;
        $useReduction    = false;

        foreach ($selections as $idProduct) {
            $price = (float) Product::getPriceStatic(
                $idProduct,
                true,
                null,
                6,
                null,
                false,
                true,
                1,
                false,
                null,
                null,
                null,
                $null1,
                true,
                true
            );
            $subtotal += $price;
        }

        $discountType  = $bundle['discount_type'];
        $discountValue = (float) $bundle['discount_value'];
        $savingsAmount = 0.0;

        if ($discountType === 'percent' && $discountValue > 0) {
            $savingsAmount = round($subtotal * $discountValue / 100, 2);
        } elseif ($discountType === 'amount' && $discountValue > 0) {
            $savingsAmount = min($discountValue, $subtotal);
        }

        $bundleTotal = max(0.0, $subtotal - $savingsAmount);

        // Add each selected product to cart
        $cart    = $this->context->cart;
        $success = true;

        foreach ($selections as $idProduct) {
            $result = $cart->addProduct($idProduct, 1);
            if ($result !== true && $result !== false) {
                // addProduct returns error string on failure in some PS versions
                $errors[] = $result;
                $success  = false;
            } elseif ($result === false) {
                $errors[] = sprintf(
                    $this->module->l('Could not add product #%d to cart.'),
                    $idProduct
                );
                $success = false;
            }
        }

        if (!$success) {
            foreach ($errors as $err) {
                $this->errors[] = $err;
            }
            $this->redirectBackToBuilder($idBundle);
            return;
        }

        // Apply cart rule (discount) if applicable
        if ($savingsAmount > 0) {
            $this->applyBundleDiscount($bundle, $savingsAmount, $discountType, $discountValue);
        }

        // Store bundle meta in session for actionCartSave hook
        $this->context->cookie->__set('phyto_pending_bundle', json_encode([
            'id_bundle'  => $idBundle,
            'selections' => $selections,
        ]));

        Tools::redirect($this->context->link->getPageLink('cart', true, null, ['action' => 'show']));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check that the given product is a valid choice for a slot
     * (i.e. it belongs to the slot's restricted category, or there is no restriction).
     */
    private function isProductValidForSlot(int $idProduct, int $idSlot, int $idLang): bool
    {
        $slot = new PhytoBundleSlot($idSlot);
        if (!Validate::isLoadedObject($slot)) {
            return false;
        }

        $idCategory = (int) $slot->id_category;
        if ($idCategory === 0) {
            // No restriction — just check the product is active
            $product = new Product($idProduct, false, $idLang);
            return Validate::isLoadedObject($product) && (bool) $product->active;
        }

        $count = (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'category_product`
             WHERE `id_product` = ' . $idProduct . '
               AND `id_category` = ' . $idCategory
        );

        return $count > 0;
    }

    /**
     * Create a one-time cart rule for the bundle discount and add it to the cart.
     */
    private function applyBundleDiscount(array $bundle, float $savingsAmount, string $discountType, float $discountValue): void
    {
        $cartRule = new CartRule();
        $cartRule->date_from     = date('Y-m-d H:i:s');
        $cartRule->date_to       = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $cartRule->quantity      = 1;
        $cartRule->quantity_per_user = 1;
        $cartRule->minimum_amount = 0;
        $cartRule->active        = 1;

        $bundleName = $bundle['name'] ?? ('Bundle #' . $bundle['id_bundle']);

        foreach (Language::getLanguages(false) as $lang) {
            $cartRule->name[$lang['id_lang']] = $bundleName . ' ' . $this->module->l('Discount');
        }

        if ($discountType === 'percent') {
            $cartRule->reduction_percent = (float) $discountValue;
        } else {
            $cartRule->reduction_amount         = (float) $savingsAmount;
            $cartRule->reduction_currency       = (int) $this->context->currency->id;
            $cartRule->reduction_tax            = true;
        }

        // Generate a unique code
        $cartRule->code = 'BUNDLE-' . strtoupper(substr(md5(uniqid((string) $bundle['id_bundle'], true)), 0, 8));

        if ($cartRule->add()) {
            $this->context->cart->addCartRule((int) $cartRule->id);
        }
    }

    private function redirectBackToBuilder(int $idBundle = 0): void
    {
        $params = $idBundle > 0 ? ['id_bundle' => $idBundle] : [];
        Tools::redirect(
            $this->context->link->getModuleLink('phyto_bundle_builder', 'builder', $params)
        );
    }
}
