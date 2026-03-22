<?php
/**
 * Phyto_Wholesale_PortalApplyModuleFrontController
 *
 * Handles the wholesale account application form.
 *
 * GET  /module/phyto_wholesale_portal/apply  → display form (apply.tpl)
 * POST /module/phyto_wholesale_portal/apply  → validate, save, notify, redirect
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_wholesale_portal/classes/PhytoWholesaleApplication.php';

class Phyto_Wholesale_PortalApplyModuleFrontController extends ModuleFrontController
{
    public $auth        = true;   // customer must be logged in
    public $authRedirection = 'my-account';
    public $ssl         = true;

    public function initContent(): void
    {
        parent::initContent();

        $this->context->controller->registerStylesheet(
            'phyto-wholesale-front',
            'modules/phyto_wholesale_portal/views/css/front.css',
            ['media' => 'all', 'priority' => 200]
        );

        if (Tools::isSubmit('submitWholesaleApp')) {
            $this->processForm();
            return;
        }

        $this->displayForm();
    }

    // -------------------------------------------------------------------------
    // Display the application form
    // -------------------------------------------------------------------------

    private function displayForm(): void
    {
        $customer  = $this->context->customer;
        $existing  = PhytoWholesaleApplication::getByCustomer((int) $customer->id);

        $this->context->smarty->assign([
            'phyto_ws_customer'  => $customer,
            'phyto_ws_existing'  => $existing,
            'phyto_ws_form_url'  => $this->context->link->getModuleLink('phyto_wholesale_portal', 'apply'),
            'phyto_ws_token'     => Tools::getToken(false),
        ]);

        $this->setTemplate('module:phyto_wholesale_portal/views/templates/front/apply.tpl');
    }

    // -------------------------------------------------------------------------
    // Process submitted form
    // -------------------------------------------------------------------------

    private function processForm(): void
    {
        if (!Tools::checkToken()) {
            $this->errors[] = $this->module->l('Invalid security token. Please refresh and try again.');
            $this->displayForm();
            return;
        }

        $customer     = $this->context->customer;
        $businessName = trim(Tools::getValue('business_name', ''));
        $phone        = trim(Tools::getValue('phone', ''));
        $gstNumber    = trim(Tools::getValue('gst_number', ''));
        $address      = trim(Tools::getValue('address', ''));
        $website      = trim(Tools::getValue('website', ''));
        $message      = trim(Tools::getValue('message', ''));

        // Validation
        if (empty($businessName)) {
            $this->errors[] = $this->module->l('Business name is required.');
        }
        if (empty($phone)) {
            $this->errors[] = $this->module->l('Phone number is required.');
        }
        if (!empty($website) && !Validate::isUrl($website)) {
            $this->errors[] = $this->module->l('Please enter a valid website URL.');
        }

        if (!empty($this->errors)) {
            $this->context->smarty->assign([
                'phyto_ws_errors'        => $this->errors,
                'phyto_ws_form_values'   => [
                    'business_name' => $businessName,
                    'phone'         => $phone,
                    'gst_number'    => $gstNumber,
                    'address'       => $address,
                    'website'       => $website,
                    'message'       => $message,
                ],
            ]);
            $this->displayForm();
            return;
        }

        // Build and save application
        $app                = new PhytoWholesaleApplication();
        $app->id_customer   = (int) $customer->id;
        $app->business_name = $businessName;
        $app->gst_number    = $gstNumber;
        $app->address       = $address;
        $app->phone         = $phone;
        $app->website       = $website;
        $app->message       = $message;
        $app->status        = 'Pending';

        if (!$app->add()) {
            $this->errors[] = $this->module->l('There was an error saving your application. Please try again.');
            $this->displayForm();
            return;
        }

        // Auto-approve if approval is not required
        $requireApproval = (int) Configuration::get('PHYTO_WHOLESALE_REQUIRE_APPROVAL', null, null, null, 1);
        if (!$requireApproval) {
            $app->status = 'Approved';
            $app->update();
            Phyto_Wholesale_Portal::addCustomerToWholesaleGroup((int) $customer->id);
        }

        // Send notification email to store admin
        $this->sendAdminNotification($app);

        // Redirect to success page
        Tools::redirect(
            $this->context->link->getModuleLink('phyto_wholesale_portal', 'apply', ['success' => 1])
        );
    }

    // -------------------------------------------------------------------------
    // Admin notification email
    // -------------------------------------------------------------------------

    private function sendAdminNotification(PhytoWholesaleApplication $app): void
    {
        $shopEmail = Configuration::get('PS_SHOP_EMAIL');
        $shopName  = Configuration::get('PS_SHOP_NAME');

        if (!$shopEmail) {
            return;
        }

        $adminUrl = Context::getContext()->link->getAdminLink('AdminPhytoWholesale')
            . '&id_app=' . (int) $app->id . '&updatephyto_wholesale_application=1';

        $templateVars = [
            '{business_name}' => $app->business_name,
            '{customer_name}' => $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
            '{customer_email}' => $this->context->customer->email,
            '{phone}'         => $app->phone,
            '{gst_number}'    => $app->gst_number ?: 'N/A',
            '{message}'       => nl2br(htmlspecialchars($app->message ?? '', ENT_QUOTES, 'UTF-8')),
            '{admin_url}'     => $adminUrl,
            '{shop_name}'     => $shopName,
        ];

        // Inline email body (no separate template file needed)
        $subject = '[' . $shopName . '] New Wholesale Application — ' . $app->business_name;
        $body    = "A new wholesale account application has been submitted.\n\n"
            . "Business: " . $app->business_name . "\n"
            . "Contact:  " . $this->context->customer->firstname . ' ' . $this->context->customer->lastname . "\n"
            . "Email:    " . $this->context->customer->email . "\n"
            . "Phone:    " . $app->phone . "\n"
            . "GST:      " . ($app->gst_number ?: 'N/A') . "\n\n"
            . "Review: " . $adminUrl;

        // Use PrestaShop's built-in mailer
        Mail::Send(
            (int) $this->context->language->id,
            'phyto_wholesale_notification',
            $subject,
            $templateVars,
            $shopEmail,
            $shopName,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . 'phyto_wholesale_portal/mails/'
        );
    }

    // -------------------------------------------------------------------------
    // Handle ?success=1 redirect target
    // -------------------------------------------------------------------------

    public function postProcess(): void
    {
        if ((int) Tools::getValue('success') === 1) {
            $this->context->smarty->assign([
                'phyto_ws_require_approval' => (int) Configuration::get('PHYTO_WHOLESALE_REQUIRE_APPROVAL', null, null, null, 1),
            ]);
            $this->setTemplate('module:phyto_wholesale_portal/views/templates/front/apply_success.tpl');
        }
    }
}
