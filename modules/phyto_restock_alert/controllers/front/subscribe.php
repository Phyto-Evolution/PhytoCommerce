<?php
/**
 * subscribe.php
 *
 * FrontController — handles AJAX subscribe/unsubscribe for restock notifications.
 *
 * POST params:
 *   - email               : subscriber email
 *   - firstname           : optional first name
 *   - id_product          : product ID
 *   - id_product_attribute: combination ID (0 for base product)
 *   - token               : PrestaShop front token (CSRF)
 *   - action              : 'subscribe' | 'unsubscribe'
 *
 * Returns JSON: { success: bool, message: string }
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Restock_AlertSubscribeModuleFrontController extends ModuleFrontController
{
    public $ajax = true;

    public function postProcess(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Only accept POST requests
        if (!$_SERVER['REQUEST_METHOD'] || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Invalid request method.'),
            ]));
            return;
        }

        // CSRF token validation
        $token = Tools::getValue('token');
        if (!$token || !Tools::validate($token)) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Invalid security token.'),
            ]));
            return;
        }

        $action             = Tools::getValue('action', 'subscribe');
        $idProduct          = (int) Tools::getValue('id_product');
        $idProductAttribute = (int) Tools::getValue('id_product_attribute');
        $email              = trim(Tools::getValue('email', ''));
        $firstname          = trim(Tools::getValue('firstname', ''));

        // Basic validation
        if ($idProduct <= 0) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Invalid product.'),
            ]));
            return;
        }

        if (!Validate::isEmail($email)) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Please enter a valid email address.'),
            ]));
            return;
        }

        if ($firstname && !Validate::isName($firstname)) {
            $firstname = '';
        }

        // Verify the product exists
        $product = new Product($idProduct);
        if (!Validate::isLoadedObject($product)) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Product not found.'),
            ]));
            return;
        }

        // Dispatch action
        if ($action === 'unsubscribe') {
            $this->handleUnsubscribe($email, $idProduct, $idProductAttribute);
        } else {
            $this->handleSubscribe($email, $firstname, $idProduct, $idProductAttribute);
        }
    }

    // -------------------------------------------------------------------------
    // Subscribe
    // -------------------------------------------------------------------------

    protected function handleSubscribe(
        string $email,
        string $firstname,
        int $idProduct,
        int $idProductAttribute
    ): void {
        // Check for duplicate
        $existing = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `id_alert`
             FROM `' . _DB_PREFIX_ . 'phyto_restock_alert`
             WHERE `id_product` = ' . $idProduct . '
               AND `id_product_attribute` = ' . $idProductAttribute . '
               AND `email` = \'' . pSQL($email) . '\''
        );

        if ($existing) {
            $this->ajaxRender(json_encode([
                'success' => false,
                'already' => true,
                'message' => $this->module->l('You are already subscribed to notifications for this product.'),
            ]));
            return;
        }

        // Resolve customer ID if logged in
        $idCustomer = $this->context->customer->isLogged()
            ? (int) $this->context->customer->id
            : 0;

        $inserted = Db::getInstance()->insert(
            'phyto_restock_alert',
            [
                'id_product'           => $idProduct,
                'id_product_attribute' => $idProductAttribute,
                'id_customer'          => $idCustomer,
                'email'                => pSQL($email),
                'firstname'            => pSQL($firstname),
                'date_add'             => date('Y-m-d H:i:s'),
                'notified'             => 0,
                'date_notified'        => null,
            ]
        );

        if ($inserted) {
            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('You will be notified when this product is back in stock.'),
            ]));
        } else {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Could not save your subscription. Please try again.'),
            ]));
        }
    }

    // -------------------------------------------------------------------------
    // Unsubscribe
    // -------------------------------------------------------------------------

    protected function handleUnsubscribe(
        string $email,
        int $idProduct,
        int $idProductAttribute
    ): void {
        $deleted = Db::getInstance()->delete(
            'phyto_restock_alert',
            '`id_product` = ' . $idProduct
            . ' AND `id_product_attribute` = ' . $idProductAttribute
            . ' AND `email` = \'' . pSQL($email) . '\''
        );

        if ($deleted) {
            $this->ajaxRender(json_encode([
                'success' => true,
                'message' => $this->module->l('You have been unsubscribed from notifications for this product.'),
            ]));
        } else {
            $this->ajaxRender(json_encode([
                'success' => false,
                'message' => $this->module->l('Subscription not found.'),
            ]));
        }
    }
}
