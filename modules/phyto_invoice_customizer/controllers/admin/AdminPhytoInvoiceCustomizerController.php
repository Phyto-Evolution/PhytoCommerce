<?php
/**
 * AdminPhytoInvoiceCustomizerController
 *
 * Back-office controller for the Phyto Invoice Customizer module.
 * Handles the dedicated admin tab and redirects to the module configuration
 * page when accessed directly from the menu.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoInvoiceCustomizerController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * Redirect to the module configuration page.
     * The main UX lives in Module::getContent() via the HelperForm.
     */
    public function initContent(): void
    {
        $configUrl = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure' => 'phyto_invoice_customizer',
        ]);

        Tools::redirectAdmin($configUrl);
    }
}
