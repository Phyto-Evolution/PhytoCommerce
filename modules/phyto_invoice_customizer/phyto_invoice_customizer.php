<?php
/**
 * Phyto Invoice Customizer
 *
 * Customises PrestaShop 8 PDF invoices to include phytosanitary certificate
 * details, TC batch numbers, Live Arrival Guarantee text, and a branded
 * header/footer.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   Commercial
 * @version   1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Invoice_Customizer extends Module
{
    /** @var array<string, mixed> Default configuration values */
    private array $defaultConfig = [
        'PHYTO_INV_SHOW_LAG'    => 1,
        'PHYTO_INV_LAG_TEXT'    => 'This order is covered by our Live Arrival Guarantee. If your plants arrive dead or severely damaged, contact us within 2 hours with photos.',
        'PHYTO_INV_SHOW_BATCH'  => 1,
        'PHYTO_INV_SHOW_PHYTO'  => 1,
        'PHYTO_INV_FOOTER_NOTE' => '',
        'PHYTO_INV_BRAND_NAME'  => '',
    ];

    public function __construct()
    {
        $this->name          = 'phyto_invoice_customizer';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Invoice Customizer');
        $this->description = $this->l('Customises PrestaShop 8 PDF invoices for plant stores by injecting TC batch numbers from Phyto TC Batch Tracker, phytosanitary certificate references from Phyto Phytosanitary, and a configurable Live Arrival Guarantee clause onto every order invoice. A branded header carries your store name and a custom footer note closes each document, all configurable from the module settings page without editing any template files. The module hooks into the standard PrestaShop PDF invoice generation pipeline, so customisations apply automatically to new and reprinted invoices.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Phyto Invoice Customizer?');
    }

    // =========================================================================
    // Install / Uninstall
    // =========================================================================

    /**
     * Module installation.
     */
    public function install(): bool
    {
        return parent::install()
            && $this->registerHook('displayPDFInvoice')
            && $this->registerHook('displayPDFInvoiceFooter')
            && $this->registerHook('displayPDFInvoiceHeader')
            && $this->installTab()
            && $this->installDefaultConfig();
    }

    /**
     * Module uninstallation.
     */
    public function uninstall(): bool
    {
        return $this->uninstallTab()
            && $this->removeConfig()
            && parent::uninstall();
    }

    // =========================================================================
    // Tab management
    // =========================================================================

    /**
     * Install the back-office menu tab under the Phyto Suite parent tab if it
     * exists, otherwise fall back to the top-level administration menu.
     */
    private function installTab(): bool
    {
        // Prefer to nest under AdminPhytoSuite if present
        $idParent = (int) Tab::getIdFromClassName('AdminPhytoSuite');
        if ($idParent <= 0) {
            $idParent = (int) Tab::getIdFromClassName('AdminAdminPreferences');
        }
        if ($idParent <= 0) {
            $idParent = (int) Tab::getIdFromClassName('DEFAULT');
        }

        $tab              = new Tab();
        $tab->active      = 1;
        $tab->class_name  = 'AdminPhytoInvoiceCustomizer';
        $tab->module      = $this->name;
        $tab->id_parent   = $idParent;
        $tab->name        = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Invoice Customizer');
        }

        return (bool) $tab->add();
    }

    /**
     * Remove the back-office tab on uninstall.
     */
    private function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoInvoiceCustomizer');
        if ($idTab > 0) {
            $tab = new Tab($idTab);
            return (bool) $tab->delete();
        }
        return true;
    }

    // =========================================================================
    // Configuration helpers
    // =========================================================================

    /**
     * Write default configuration values on first install.
     */
    private function installDefaultConfig(): bool
    {
        foreach ($this->defaultConfig as $key => $value) {
            if (!Configuration::hasKey($key)) {
                if (!Configuration::updateValue($key, $value)) {
                    return false;
                }
            }
        }

        // Default brand name to shop name
        if (empty(Configuration::get('PHYTO_INV_BRAND_NAME'))) {
            Configuration::updateValue(
                'PHYTO_INV_BRAND_NAME',
                Configuration::get('PS_SHOP_NAME')
            );
        }

        return true;
    }

    /**
     * Delete all module configuration keys on uninstall.
     */
    private function removeConfig(): bool
    {
        foreach (array_keys($this->defaultConfig) as $key) {
            Configuration::deleteByName($key);
        }
        return true;
    }

    // =========================================================================
    // Back-office configuration page
    // =========================================================================

    /**
     * Module configuration page rendered from Modules > Configure.
     */
    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoInvConfig')) {
            $output .= $this->processConfigForm();
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Validate and save configuration form data.
     */
    private function processConfigForm(): string
    {
        $errors = [];

        $brandName = Tools::getValue('PHYTO_INV_BRAND_NAME');
        if (empty(trim((string) $brandName))) {
            $errors[] = $this->l('Brand name cannot be empty.');
        }

        if (!empty($errors)) {
            return $this->displayError(implode('<br>', $errors));
        }

        Configuration::updateValue('PHYTO_INV_SHOW_LAG',    (int) Tools::getValue('PHYTO_INV_SHOW_LAG'));
        Configuration::updateValue('PHYTO_INV_LAG_TEXT',    Tools::getValue('PHYTO_INV_LAG_TEXT'), true);
        Configuration::updateValue('PHYTO_INV_SHOW_BATCH',  (int) Tools::getValue('PHYTO_INV_SHOW_BATCH'));
        Configuration::updateValue('PHYTO_INV_SHOW_PHYTO',  (int) Tools::getValue('PHYTO_INV_SHOW_PHYTO'));
        Configuration::updateValue('PHYTO_INV_FOOTER_NOTE', Tools::getValue('PHYTO_INV_FOOTER_NOTE'), true);
        Configuration::updateValue('PHYTO_INV_BRAND_NAME',  trim((string) $brandName));

        return $this->displayConfirmation($this->l('Settings updated successfully.'));
    }

    /**
     * Build and return the HelperForm-based configuration form HTML.
     */
    private function renderConfigForm(): string
    {
        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Invoice Customizer Settings'),
                    'icon'  => 'icon-file-text',
                ],
                'input' => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('Brand Name'),
                        'name'  => 'PHYTO_INV_BRAND_NAME',
                        'desc'  => $this->l('Brand name printed in the invoice header. Defaults to the shop name.'),
                        'class' => 'fixed-width-xl',
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Show Live Arrival Guarantee Text'),
                        'name'    => 'PHYTO_INV_SHOW_LAG',
                        'desc'    => $this->l('Include the LAG statement in the invoice body.'),
                        'values'  => [
                            ['id' => 'lag_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'lag_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('LAG Text'),
                        'name'  => 'PHYTO_INV_LAG_TEXT',
                        'desc'  => $this->l('The Live Arrival Guarantee statement printed on the invoice.'),
                        'rows'  => 4,
                        'cols'  => 60,
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Show TC Batch Numbers'),
                        'name'    => 'PHYTO_INV_SHOW_BATCH',
                        'desc'    => $this->l('Print tissue-culture batch codes next to each product line (requires phyto_tc_batch_tracker).'),
                        'values'  => [
                            ['id' => 'batch_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'batch_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Show Phytosanitary Certificate Reference'),
                        'name'    => 'PHYTO_INV_SHOW_PHYTO',
                        'desc'    => $this->l('Print phytosanitary certificate reference numbers (requires phyto_phytosanitary).'),
                        'values'  => [
                            ['id' => 'phyto_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'phyto_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Custom Footer Note'),
                        'name'  => 'PHYTO_INV_FOOTER_NOTE',
                        'desc'  => $this->l('Optional text printed in the invoice footer above the "Generated by PhytoCommerce" line.'),
                        'rows'  => 3,
                        'cols'  => 60,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper                         = new HelperForm();
        $helper->module                 = $this;
        $helper->name_controller        = $this->name;
        $helper->token                  = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex           = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language  = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title                  = $this->displayName;
        $helper->submit_action          = 'submitPhytoInvConfig';

        $helper->fields_value['PHYTO_INV_BRAND_NAME']  = Configuration::get('PHYTO_INV_BRAND_NAME');
        $helper->fields_value['PHYTO_INV_SHOW_LAG']    = (int) Configuration::get('PHYTO_INV_SHOW_LAG');
        $helper->fields_value['PHYTO_INV_LAG_TEXT']    = Configuration::get('PHYTO_INV_LAG_TEXT');
        $helper->fields_value['PHYTO_INV_SHOW_BATCH']  = (int) Configuration::get('PHYTO_INV_SHOW_BATCH');
        $helper->fields_value['PHYTO_INV_SHOW_PHYTO']  = (int) Configuration::get('PHYTO_INV_SHOW_PHYTO');
        $helper->fields_value['PHYTO_INV_FOOTER_NOTE'] = Configuration::get('PHYTO_INV_FOOTER_NOTE');

        return $helper->generateForm([$fields]);
    }

    // =========================================================================
    // Hook: displayPDFInvoiceHeader
    // =========================================================================

    /**
     * Inject a branded header block into the PDF invoice.
     * Note: displayPDFInvoiceHeader is not available in all PS8 builds; the hook
     * is registered optimistically and gracefully returns empty if never called.
     *
     * @param array<string, mixed> $params
     */
    public function hookDisplayPDFInvoiceHeader(array $params): string
    {
        $brandName = htmlspecialchars(
            (string) Configuration::get('PHYTO_INV_BRAND_NAME'),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->context->smarty->assign([
            'phyto_inv_brand_name' => $brandName,
        ]);

        return $this->fetch('module:phyto_invoice_customizer/views/templates/hook/invoice_header.tpl');
    }

    // =========================================================================
    // Hook: displayPDFInvoice
    // =========================================================================

    /**
     * Inject batch numbers, phytosanitary references, and LAG text into the
     * PDF invoice body.
     *
     * @param array<string, mixed> $params
     */
    public function hookDisplayPDFInvoice(array $params): string
    {
        $idOrder = $this->extractOrderId($params);
        if ($idOrder <= 0) {
            return '';
        }

        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $showLag   = (bool) Configuration::get('PHYTO_INV_SHOW_LAG');
        $showBatch = (bool) Configuration::get('PHYTO_INV_SHOW_BATCH');
        $showPhyto = (bool) Configuration::get('PHYTO_INV_SHOW_PHYTO');
        $lagText   = (string) Configuration::get('PHYTO_INV_LAG_TEXT');

        // Fetch batch data per product (gracefully skipped if table absent)
        $batchData   = $showBatch ? $this->getBatchDataForOrder($order) : [];

        // Fetch phytosanitary references for this order's products
        $phytoRefs   = $showPhyto ? $this->getPhytoRefsForOrder($order) : [];

        // Nothing custom to show at all — return early
        if (!$showLag && empty($batchData) && empty($phytoRefs)) {
            return '';
        }

        $brandName = htmlspecialchars(
            (string) Configuration::get('PHYTO_INV_BRAND_NAME'),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->context->smarty->assign([
            'phyto_inv_show_lag'   => $showLag,
            'phyto_inv_lag_text'   => htmlspecialchars($lagText, ENT_QUOTES, 'UTF-8'),
            'phyto_inv_show_batch' => $showBatch,
            'phyto_inv_batch_data' => $batchData,
            'phyto_inv_show_phyto' => $showPhyto,
            'phyto_inv_phyto_refs' => $phytoRefs,
            'phyto_inv_brand_name' => $brandName,
        ]);

        return $this->fetch('module:phyto_invoice_customizer/views/templates/hook/invoice_extra.tpl');
    }

    // =========================================================================
    // Hook: displayPDFInvoiceFooter
    // =========================================================================

    /**
     * Inject a branded footer block into the PDF invoice.
     *
     * @param array<string, mixed> $params
     */
    public function hookDisplayPDFInvoiceFooter(array $params): string
    {
        $brandName  = htmlspecialchars(
            (string) Configuration::get('PHYTO_INV_BRAND_NAME'),
            ENT_QUOTES,
            'UTF-8'
        );
        $footerNote = htmlspecialchars(
            (string) Configuration::get('PHYTO_INV_FOOTER_NOTE'),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->context->smarty->assign([
            'phyto_inv_brand_name'  => $brandName,
            'phyto_inv_footer_note' => $footerNote,
        ]);

        return $this->fetch('module:phyto_invoice_customizer/views/templates/hook/invoice_footer.tpl');
    }

    // =========================================================================
    // Data helpers
    // =========================================================================

    /**
     * Extract an order ID from the various parameter shapes PS8 may pass to
     * PDF invoice hooks (OrderInvoice object, Order object, or plain id_order).
     *
     * @param array<string, mixed> $params
     */
    private function extractOrderId(array $params): int
    {
        // PS8 typically passes an OrderInvoice object as $params['object']
        if (isset($params['object'])) {
            $obj = $params['object'];
            if (is_object($obj)) {
                if (isset($obj->id_order)) {
                    return (int) $obj->id_order;
                }
                if (isset($obj->id)) {
                    return (int) $obj->id;
                }
            }
        }

        // Some hooks pass the Order directly
        if (isset($params['order'])) {
            $order = $params['order'];
            if (is_object($order) && isset($order->id)) {
                return (int) $order->id;
            }
            if (is_numeric($order)) {
                return (int) $order;
            }
        }

        // Plain integer
        if (isset($params['id_order'])) {
            return (int) $params['id_order'];
        }

        return 0;
    }

    /**
     * Retrieve TC batch codes for every product line in the order.
     * Gracefully returns an empty array when the batch tracker tables do not
     * exist (module not installed).
     *
     * @return array<int, array{product_name: string, batch_code: string, species_name: string, generation: string}>
     */
    private function getBatchDataForOrder(Order $order): array
    {
        // Guard: check that the required tables exist
        if (!$this->tableExists(_DB_PREFIX_ . 'phyto_tc_batch')
            || !$this->tableExists(_DB_PREFIX_ . 'phyto_tc_batch_product')
        ) {
            return [];
        }

        $results = [];

        $orderDetails = $order->getProductsDetail();
        if (empty($orderDetails)) {
            return [];
        }

        foreach ($orderDetails as $detail) {
            $idProduct          = (int) $detail['product_id'];
            $idProductAttribute = (int) ($detail['product_attribute_id'] ?? 0);

            $row = Db::getInstance()->getRow(
                'SELECT b.`batch_code`, b.`species_name`, b.`generation`
                 FROM `' . _DB_PREFIX_ . 'phyto_tc_batch_product` bp
                 INNER JOIN `' . _DB_PREFIX_ . 'phyto_tc_batch` b
                     ON b.`id_batch` = bp.`id_batch`
                 WHERE bp.`id_product` = ' . $idProduct . '
                   AND bp.`id_product_attribute` = ' . $idProductAttribute
            );

            if (!empty($row)) {
                $results[] = [
                    'product_name' => htmlspecialchars(
                        (string) ($detail['product_name'] ?? ''),
                        ENT_QUOTES,
                        'UTF-8'
                    ),
                    'batch_code'   => htmlspecialchars((string) $row['batch_code'],   ENT_QUOTES, 'UTF-8'),
                    'species_name' => htmlspecialchars((string) $row['species_name'], ENT_QUOTES, 'UTF-8'),
                    'generation'   => htmlspecialchars((string) $row['generation'],   ENT_QUOTES, 'UTF-8'),
                ];
            }
        }

        return $results;
    }

    /**
     * Retrieve phytosanitary certificate reference numbers for the products in
     * the order.  Gracefully returns an empty array when the phytosanitary
     * module tables do not exist.
     *
     * @return array<int, array{product_name: string, reference_number: string, doc_type: string, issuing_authority: string}>
     */
    private function getPhytoRefsForOrder(Order $order): array
    {
        if (!$this->tableExists(_DB_PREFIX_ . 'phyto_phytosanitary_doc')) {
            return [];
        }

        $orderDetails = $order->getProductsDetail();
        if (empty($orderDetails)) {
            return [];
        }

        $productIds = array_unique(array_map(
            static fn (array $d): int => (int) $d['product_id'],
            $orderDetails
        ));

        if (empty($productIds)) {
            return [];
        }

        $idList = implode(',', $productIds);

        $rows = Db::getInstance()->executeS(
            'SELECT d.`id_product`, d.`reference_number`, d.`doc_type`, d.`issuing_authority`
             FROM `' . _DB_PREFIX_ . 'phyto_phytosanitary_doc` d
             WHERE d.`id_product` IN (' . $idList . ')
               AND d.`reference_number` != \'\'
             ORDER BY d.`id_product`, d.`id_doc`'
        );

        if (empty($rows)) {
            return [];
        }

        // Build a product_id -> product_name map from order details
        $nameMap = [];
        foreach ($orderDetails as $detail) {
            $nameMap[(int) $detail['product_id']] = (string) ($detail['product_name'] ?? '');
        }

        $results = [];
        foreach ($rows as $row) {
            $idProduct = (int) $row['id_product'];
            $results[] = [
                'product_name'     => htmlspecialchars($nameMap[$idProduct] ?? '', ENT_QUOTES, 'UTF-8'),
                'reference_number' => htmlspecialchars((string) $row['reference_number'], ENT_QUOTES, 'UTF-8'),
                'doc_type'         => htmlspecialchars((string) $row['doc_type'],         ENT_QUOTES, 'UTF-8'),
                'issuing_authority' => htmlspecialchars((string) $row['issuing_authority'], ENT_QUOTES, 'UTF-8'),
            ];
        }

        return $results;
    }

    /**
     * Check whether a fully-qualified table name (with prefix) exists in the DB.
     *
     * @param string $tableName  Full table name including DB prefix.
     */
    private function tableExists(string $tableName): bool
    {
        $escapedName = pSQL($tableName);
        $result = Db::getInstance()->executeS("SHOW TABLES LIKE '" . $escapedName . "'");
        return !empty($result);
    }
}
