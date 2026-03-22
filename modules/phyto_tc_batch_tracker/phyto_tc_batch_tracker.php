<?php
/**
 * PhytoCommerce TC Batch Tracker
 *
 * Links tissue-culture products to propagation batch records.
 * v1.1 adds: inventory auto-decrement, contamination log, mother-batch
 * lineage, printable QR labels, and low-stock email alerts.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoTcBatch.php';
require_once dirname(__FILE__) . '/classes/PhytoTcContaminationLog.php';

class Phyto_Tc_Batch_Tracker extends Module
{
    private $configDefaults = array(
        'PHYTO_TC_SHIPPED_STATUS'      => 4,
        'PHYTO_TC_LOW_STOCK_THRESHOLD' => 10,
        'PHYTO_TC_ALERT_EMAIL'         => '',
        'PHYTO_TC_AUTO_DECREMENT'      => 1,
    );

    public function __construct()
    {
        $this->name          = 'phyto_tc_batch_tracker';
        $this->tab           = 'administration';
        $this->version       = '1.1.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('TC Batch Tracker');
        $this->description = $this->l(
            'Link tissue-culture products to propagation batch records. '
            . 'Buyers see full provenance; admin manages inventory with lineage, '
            . 'contamination log, QR labels, and low-stock alerts.'
        );
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install()
    {
        foreach ($this->configDefaults as $key => $value) {
            if (!Configuration::hasKey($key)) {
                Configuration::updateValue($key, $value);
            }
        }

        return parent::install()
            && $this->runSql('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionOrderStatusUpdate')
            && $this->installTab();
    }

    public function uninstall()
    {
        foreach (array_keys($this->configDefaults) as $key) {
            Configuration::deleteByName($key);
        }

        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helpers
    // -------------------------------------------------------------------------

    private function runSql($filename)
    {
        $file = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        foreach (preg_split('/;\s*[\r\n]+/', $sql) as $stmt) {
            $stmt = trim($stmt);
            if ($stmt !== '' && !Db::getInstance()->execute($stmt)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab management
    // -------------------------------------------------------------------------

    private function installTab()
    {
        $languages = Language::getLanguages(false);

        // Visible tab: TC Batches under Catalog
        $tab             = new Tab();
        $tab->class_name = 'AdminPhytoTcBatches';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->icon       = 'science';
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'TC Batches';
        }
        if (!$tab->add()) {
            return false;
        }

        // Hidden: AJAX product-linking controller
        $ajaxTab             = new Tab();
        $ajaxTab->class_name = 'AdminPhytoTcBatchProduct';
        $ajaxTab->module     = $this->name;
        $ajaxTab->id_parent  = -1;
        $ajaxTab->icon       = '';
        foreach ($languages as $lang) {
            $ajaxTab->name[$lang['id_lang']] = 'TC Batch Product Link';
        }
        if (!$ajaxTab->add()) {
            return false;
        }

        // Hidden: Contamination log CRUD
        $contamTab             = new Tab();
        $contamTab->class_name = 'AdminPhytoTcContamination';
        $contamTab->module     = $this->name;
        $contamTab->id_parent  = -1;
        $contamTab->icon       = '';
        foreach ($languages as $lang) {
            $contamTab->name[$lang['id_lang']] = 'TC Contamination Log';
        }

        return $contamTab->add();
    }

    private function uninstallTab()
    {
        foreach (array('AdminPhytoTcBatches', 'AdminPhytoTcBatchProduct', 'AdminPhytoTcContamination') as $cls) {
            $idTab = (int) Tab::getIdFromClassName($cls);
            if ($idTab) {
                $tab = new Tab($idTab);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Module configuration page
    // -------------------------------------------------------------------------

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoTcConfig')) {
            $output .= $this->postProcess();
        }

        // Surface any currently low-stock batches as a warning
        $lowStock = PhytoTcBatch::getLowStockBatches();
        if (!empty($lowStock)) {
            $output .= $this->displayWarning(
                sprintf(
                    $this->l('%d batch(es) are at or below the low-stock threshold.'),
                    count($lowStock)
                )
            );
        }

        return $output . $this->renderConfigForm();
    }

    private function postProcess()
    {
        $shippedStatus = (int) Tools::getValue('PHYTO_TC_SHIPPED_STATUS');
        $threshold     = (int) Tools::getValue('PHYTO_TC_LOW_STOCK_THRESHOLD');
        $alertEmail    = trim(Tools::getValue('PHYTO_TC_ALERT_EMAIL'));
        $autoDecrement = (int) Tools::getValue('PHYTO_TC_AUTO_DECREMENT');

        if ($threshold < 1) {
            return $this->displayError($this->l('Low-stock threshold must be at least 1.'));
        }

        if ($alertEmail && !Validate::isEmail($alertEmail)) {
            return $this->displayError($this->l('Invalid alert email address.'));
        }

        Configuration::updateValue('PHYTO_TC_SHIPPED_STATUS', $shippedStatus);
        Configuration::updateValue('PHYTO_TC_LOW_STOCK_THRESHOLD', $threshold);
        Configuration::updateValue('PHYTO_TC_ALERT_EMAIL', $alertEmail);
        Configuration::updateValue('PHYTO_TC_AUTO_DECREMENT', $autoDecrement);

        return $this->displayConfirmation($this->l('Settings saved.'));
    }

    private function renderConfigForm()
    {
        $orderStatuses = OrderState::getOrderStates((int) $this->context->language->id);
        $statusOpts    = array();
        foreach ($orderStatuses as $s) {
            $statusOpts[] = array('id' => $s['id_order_state'], 'name' => $s['name']);
        }

        $fieldsForm = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('TC Batch Tracker — Settings'),
                    'icon'  => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type'    => 'switch',
                        'label'   => $this->l('Auto-decrement stock on shipment'),
                        'name'    => 'PHYTO_TC_AUTO_DECREMENT',
                        'is_bool' => true,
                        'desc'    => $this->l('Deduct sold quantities from units_remaining when an order reaches the configured shipped status.'),
                        'values'  => array(
                            array('id' => 'decr_on',  'value' => 1, 'label' => $this->l('Enabled')),
                            array('id' => 'decr_off', 'value' => 0, 'label' => $this->l('Disabled')),
                        ),
                    ),
                    array(
                        'type'    => 'select',
                        'label'   => $this->l('"Shipped" order status'),
                        'name'    => 'PHYTO_TC_SHIPPED_STATUS',
                        'desc'    => $this->l('Stock is decremented when an order transitions to this status.'),
                        'options' => array(
                            'query' => $statusOpts,
                            'id'    => 'id',
                            'name'  => 'name',
                        ),
                    ),
                    array(
                        'type'   => 'text',
                        'label'  => $this->l('Low-stock alert threshold'),
                        'name'   => 'PHYTO_TC_LOW_STOCK_THRESHOLD',
                        'class'  => 'fixed-width-sm',
                        'desc'   => $this->l('Send an alert when units_remaining drops to this value or below.'),
                        'suffix' => $this->l('units'),
                    ),
                    array(
                        'type'  => 'text',
                        'label' => $this->l('Alert email address'),
                        'name'  => 'PHYTO_TC_ALERT_EMAIL',
                        'class' => 'fixed-width-xxl',
                        'desc'  => $this->l('Leave blank to use the shop email. One alert per batch until stock is replenished above threshold.'),
                    ),
                ),
                'submit' => array('title' => $this->l('Save')),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitPhytoTcConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = array(
            'PHYTO_TC_AUTO_DECREMENT'      => (int) Configuration::get('PHYTO_TC_AUTO_DECREMENT', null, null, null, 1),
            'PHYTO_TC_SHIPPED_STATUS'      => (int) Configuration::get('PHYTO_TC_SHIPPED_STATUS', null, null, null, 4),
            'PHYTO_TC_LOW_STOCK_THRESHOLD' => (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10),
            'PHYTO_TC_ALERT_EMAIL'         => Configuration::get('PHYTO_TC_ALERT_EMAIL'),
        );

        return $helper->generateForm(array($fieldsForm));
    }

    // -------------------------------------------------------------------------
    // Hook: displayBackOfficeHeader
    // -------------------------------------------------------------------------

    public function hookDisplayBackOfficeHeader($params)
    {
        $controller = Tools::getValue('controller');

        if (in_array($controller, array('AdminProducts', 'AdminPhytoTcBatches', 'AdminPhytoTcContamination'))) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    // -------------------------------------------------------------------------
    // Hook: displayHeader
    // -------------------------------------------------------------------------

    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
    }

    // -------------------------------------------------------------------------
    // Hook: displayAdminProductsExtra
    // -------------------------------------------------------------------------

    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct   = (int) $params['id_product'];
        $linkedBatch = PhytoTcBatch::getBatchByProduct($idProduct);
        $allBatches  = PhytoTcBatch::getAllForDropdown();
        $ajaxUrl     = $this->context->link->getAdminLink('AdminPhytoTcBatchProduct');
        $contamUrl   = $this->context->link->getAdminLink('AdminPhytoTcContamination');

        $lineage = array();
        $children = array();
        $contamLog = array();

        if ($linkedBatch) {
            $idBatch   = (int) $linkedBatch['id_batch'];
            $lineage   = PhytoTcBatch::getLineageChain($idBatch);
            $children  = PhytoTcBatch::getChildren($idBatch);
            $contamLog = PhytoTcContaminationLog::getByBatch($idBatch);
        }

        $this->context->smarty->assign(array(
            'phyto_tc_id_product'   => $idProduct,
            'phyto_tc_linked_batch' => $linkedBatch,
            'phyto_tc_all_batches'  => $allBatches,
            'phyto_tc_ajax_url'     => $ajaxUrl,
            'phyto_tc_contam_url'   => $contamUrl,
            'phyto_tc_generations'  => PhytoTcBatch::getGenerationChoices(),
            'phyto_tc_statuses'     => PhytoTcBatch::getStatusChoices(),
            'phyto_tc_lineage'      => $lineage,
            'phyto_tc_children'     => $children,
            'phyto_tc_contam_log'   => $contamLog,
            'phyto_tc_contam_types' => PhytoTcContaminationLog::getTypeChoices(),
        ));

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: displayProductExtraContent
    // -------------------------------------------------------------------------

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->getId();
        $batch     = PhytoTcBatch::getBatchByProduct($idProduct);

        if (!$batch) {
            return array();
        }

        $generations = PhytoTcBatch::getGenerationChoices();
        $batch['generation_label'] = isset($generations[$batch['generation']])
            ? $generations[$batch['generation']]
            : $batch['generation'];

        $batch['date_deflask_formatted']   = $batch['date_deflask']   ? Tools::displayDate($batch['date_deflask'])   : '';
        $batch['date_certified_formatted'] = $batch['date_certified'] ? Tools::displayDate($batch['date_certified']) : '';

        $lineage = PhytoTcBatch::getLineageChain((int) $batch['id_batch']);

        $this->context->smarty->assign(array(
            'phyto_tc_batch'   => $batch,
            'phyto_tc_lineage' => $lineage,
        ));

        $content      = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');
        $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $extraContent->setTitle($this->l('Batch Provenance'));
        $extraContent->setContent($content);

        return array($extraContent);
    }

    // -------------------------------------------------------------------------
    // Hook: actionOrderStatusUpdate — inventory auto-decrement
    // -------------------------------------------------------------------------

    public function hookActionOrderStatusUpdate($params)
    {
        if (!(int) Configuration::get('PHYTO_TC_AUTO_DECREMENT', null, null, null, 1)) {
            return;
        }

        $shippedStatusId = (int) Configuration::get('PHYTO_TC_SHIPPED_STATUS', null, null, null, 4);
        $newStatusId     = (int) $params['newOrderStatus']->id;

        if ($newStatusId !== $shippedStatusId) {
            return;
        }

        $idOrder = (int) $params['id_order'];
        $order   = new Order($idOrder);

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        foreach ($order->getProducts() as $product) {
            $idProduct          = (int) $product['id_product'];
            $idProductAttribute = (int) $product['id_product_attribute'];
            $qty                = (int) $product['product_quantity'];

            $batch = PhytoTcBatch::getBatchByProduct($idProduct, $idProductAttribute);
            if (!$batch) {
                continue;
            }

            $idBatch = (int) $batch['id_batch'];
            PhytoTcBatch::decrementUnits($idBatch, $qty);
            PhytoTcBatch::checkLowStockAlert($idBatch);
        }
    }
}
