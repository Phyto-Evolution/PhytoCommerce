<?php
/**
 * Front controller — handles "Notify me when in season" email capture.
 *
 * Class name follows PrestaShop convention:
 *   <module_name> + <ControllerName> + ModuleFrontController
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class phyto_seasonal_availabilityNotifyModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $ssl = true;

    /**
     * Handle the POST form submission.
     */
    public function postProcess()
    {
        if (!Tools::isSubmit('phyto_seasonal_notify_submit')) {
            return;
        }

        // CSRF validation
        $token = Tools::getValue('token');
        if ($token !== Tools::getToken(false)) {
            $this->errors[] = $this->module->l('Invalid security token. Please try again.', 'notify');
            return;
        }

        $idProduct = (int) Tools::getValue('id_product');
        $email     = trim(Tools::getValue('phyto_seasonal_email', ''));
        $name      = trim(Tools::getValue('phyto_seasonal_name', ''));

        // Validate product
        if ($idProduct <= 0) {
            $this->errors[] = $this->module->l('Invalid product.', 'notify');
            return;
        }

        // Validate email
        if (empty($email) || !Validate::isEmail($email)) {
            $this->errors[] = $this->module->l('Please enter a valid email address.', 'notify');
            return;
        }

        // Validate name
        if (empty($name) || !Validate::isGenericName($name)) {
            $this->errors[] = $this->module->l('Please enter a valid name.', 'notify');
            return;
        }

        // Check that notify is actually enabled for this product
        $row = Phyto_Seasonal_Availability::getProductSeasonal($idProduct);
        if (!$row || !(int) $row['enable_notify']) {
            $this->errors[] = $this->module->l('Notifications are not enabled for this product.', 'notify');
            return;
        }

        // Prevent duplicate subscriptions for same email + product
        $exists = Db::getInstance()->getValue(
            'SELECT `id_notify` FROM `' . _DB_PREFIX_ . 'phyto_seasonal_notify`
             WHERE `id_product` = ' . $idProduct . '
               AND `email` = \'' . pSQL($email) . '\''
        );

        if ($exists) {
            $this->success[] = $this->module->l('You are already signed up for notifications on this product.', 'notify');
            $this->redirectToProduct($idProduct);
            return;
        }

        // Insert
        $ok = Db::getInstance()->insert('phyto_seasonal_notify', [
            'id_product' => $idProduct,
            'email'      => pSQL($email),
            'name'       => pSQL($name),
            'notified'   => 0,
            'date_add'   => date('Y-m-d H:i:s'),
        ]);

        if (!$ok) {
            $this->errors[] = $this->module->l('An error occurred. Please try again.', 'notify');
            return;
        }

        // Send confirmation email
        $this->sendConfirmationEmail($idProduct, $email, $name);

        // Redirect back to product with success flag
        $this->redirectToProduct($idProduct, true);
    }

    /**
     * Send a simple confirmation email via PrestaShop Mail::Send().
     */
    private function sendConfirmationEmail($idProduct, $email, $name)
    {
        $product     = new Product($idProduct, false, $this->context->language->id);
        $productName = $product->name;

        $templateVars = [
            '{customer_name}' => $name,
            '{product_name}'  => $productName,
            '{shop_name}'     => Configuration::get('PS_SHOP_NAME'),
        ];

        // Try module mail templates first; fallback is handled by PrestaShop
        $mailDir = _PS_MODULE_DIR_ . $this->module->name . '/mails/';

        // If module mail templates don't exist, send a raw text email
        if (!is_dir($mailDir)) {
            $subject = sprintf(
                '%s — %s',
                $this->module->l('Seasonal notification confirmed', 'notify'),
                $productName
            );

            $body = sprintf(
                $this->module->l('Hi %s, you will be notified when %s is back in season at %s.', 'notify'),
                $name,
                $productName,
                Configuration::get('PS_SHOP_NAME')
            );

            // Use a generic PrestaShop mail
            @Mail::Send(
                (int) $this->context->language->id,
                'phyto_seasonal_notify',
                $subject,
                $templateVars,
                $email,
                $name,
                null,
                null,
                null,
                null,
                $mailDir,
                false,
                (int) $this->context->shop->id
            );

            return;
        }

        @Mail::Send(
            (int) $this->context->language->id,
            'phyto_seasonal_notify',
            sprintf(
                '%s — %s',
                $this->module->l('Seasonal notification confirmed', 'notify'),
                $productName
            ),
            $templateVars,
            $email,
            $name,
            null,
            null,
            null,
            null,
            $mailDir,
            false,
            (int) $this->context->shop->id
        );
    }

    /**
     * Redirect back to product page.
     *
     * @param int  $idProduct
     * @param bool $success
     */
    private function redirectToProduct($idProduct, $success = false)
    {
        $product = new Product($idProduct, false, $this->context->language->id);
        $url     = $this->context->link->getProductLink($product);

        if ($success) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . 'phyto_seasonal_subscribed=1';
        }

        Tools::redirect($url);
    }

    /**
     * If there are no errors we've already redirected, so this only fires on error.
     */
    public function initContent()
    {
        parent::initContent();

        // On error, redirect back to product with errors in session
        $idProduct = (int) Tools::getValue('id_product');
        if ($idProduct > 0) {
            $this->context->cookie->phyto_seasonal_error = implode(' ', $this->errors);
            $this->redirectToProduct($idProduct);
        }

        // Fallback: redirect home
        Tools::redirect($this->context->link->getPageLink('index'));
    }
}
