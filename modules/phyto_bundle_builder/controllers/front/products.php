<?php
/**
 * Phyto_Bundle_BuilderProductsModuleFrontController
 *
 * AJAX endpoint that returns products eligible for a given bundle slot.
 *
 * GET /module/phyto_bundle_builder/products?id_slot=X&q=search
 *
 * Returns a JSON array of:
 *   { id_product, name, price, image_url, reference }
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_bundle_builder/classes/PhytoBundleSlot.php';

class Phyto_Bundle_BuilderProductsModuleFrontController extends ModuleFrontController
{
    public $ajax  = true;
    public $ssl   = true;

    public function initContent(): void
    {
        parent::initContent();

        header('Content-Type: application/json; charset=utf-8');

        $idSlot = (int) Tools::getValue('id_slot');
        $search = trim(Tools::getValue('q', ''));

        if (!$idSlot) {
            echo json_encode(['error' => 'Missing id_slot parameter']);
            exit;
        }

        // Strip tags and limit length for safety
        $search = strip_tags(substr($search, 0, 100));

        $idLang   = (int) $this->context->language->id;
        $products = PhytoBundleSlot::getProductsForSlot($idSlot, $idLang, $search, 50);

        // Format prices using current currency
        $currency = $this->context->currency;
        foreach ($products as &$product) {
            $product['price_formatted'] = Tools::displayPrice(
                $product['price'],
                $currency
            );
        }
        unset($product);

        echo json_encode($products, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
