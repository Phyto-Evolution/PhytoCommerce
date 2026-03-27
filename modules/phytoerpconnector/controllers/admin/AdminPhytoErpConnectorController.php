<?php
if (!defined('_PS_VERSION_')) exit;

require_once _PS_MODULE_DIR_ . 'phytoerpconnector/classes/PhytoErpApi.php';

class AdminPhytoErpConnectorController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->display   = 'view';
    }

    public function init() {
        ob_start();
        parent::init();
        if (Tools::isSubmit('phyto_erp_ajax')) {
            header('Content-Type: application/json');
            $action = Tools::getValue('erp_action');
            ob_clean();
            switch ($action) {
                case 'sync_orders':    $this->ajaxSyncOrders();    break;
                case 'sync_customers': $this->ajaxSyncCustomers(); break;
                case 'sync_products':  $this->ajaxSyncProducts();  break;
                case 'pull_invoices':  $this->ajaxPullInvoices();  break;
                case 'test_connection':$this->ajaxTestConnection(); break;
                default: echo json_encode(['error' => 'Unknown action']); exit;
            }
        }
    }

    public function postProcess() {
        if (Tools::isSubmit('saveErpSettings')) {
            Configuration::updateValue('PHYTO_ERP_URL',     Tools::getValue('erp_url'));
            Configuration::updateValue('PHYTO_ERP_API_KEY', Tools::getValue('erp_api_key'));
            // Only overwrite the secret if a new value was explicitly submitted
            $newSecret = Tools::getValue('erp_api_secret');
            if (!empty($newSecret)) {
                Configuration::updateValue('PHYTO_ERP_API_SECRET', $newSecret);
            }
            Configuration::updateValue('PHYTO_ERP_SYNC_ORDERS',  (int)Tools::getValue('sync_orders'));
            Configuration::updateValue('PHYTO_ERP_SYNC_CUSTOMERS',(int)Tools::getValue('sync_customers'));
            Configuration::updateValue('PHYTO_ERP_SYNC_PRODUCTS', (int)Tools::getValue('sync_products'));
            Configuration::updateValue('PHYTO_ERP_SYNC_INVOICES', (int)Tools::getValue('sync_invoices'));
            $this->confirmations[] = 'ERP connector settings saved.';
        }
    }

    public function renderView() {
        $log = PhytoErpApi::getLog(50);

        $stats = [
            'total'    => count($log),
            'success'  => count(array_filter($log, fn($r) => $r['status'] === 'success')),
            'errors'   => count(array_filter($log, fn($r) => $r['status'] === 'error')),
            'skipped'  => count(array_filter($log, fn($r) => $r['status'] === 'skipped')),
        ];

        $this->context->smarty->assign([
            'erp_url'          => Configuration::get('PHYTO_ERP_URL') ?: 'https://erp.phytolabs.in',
            'erp_api_key'         => Configuration::get('PHYTO_ERP_API_KEY') ?: '',
            'erp_secret_set'      => !empty(Configuration::get('PHYTO_ERP_API_SECRET')),
            'sync_orders'      => (int)Configuration::get('PHYTO_ERP_SYNC_ORDERS'),
            'sync_customers'   => (int)Configuration::get('PHYTO_ERP_SYNC_CUSTOMERS'),
            'sync_products'    => (int)Configuration::get('PHYTO_ERP_SYNC_PRODUCTS'),
            'sync_invoices'    => (int)Configuration::get('PHYTO_ERP_SYNC_INVOICES'),
            'sync_log'         => $log,
            'sync_stats'       => $stats,
            'ajax_url'         => $this->context->link->getAdminLink('AdminPhytoErpConnector'),
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phytoerpconnector/views/templates/admin/erpconnector.tpl'
        );
    }

    // ── AJAX handlers ────────────────────────────────────────────────────────

    private function ajaxTestConnection() {
        $api = $this->module->getApi();
        if (!$api) { echo json_encode(['error' => 'ERPNext credentials not configured.']); exit; }
        $result = $api->request('GET', '/api/resource/Item', ['limit' => 1]);
        if (isset($result['error'])) {
            echo json_encode(['error' => $result['error']]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Connected to ERPNext successfully.']);
        }
        exit;
    }

    private function ajaxSyncOrders() {
        $api = $this->module->getApi();
        if (!$api) { echo json_encode(['error' => 'ERPNext credentials not configured.']); exit; }
        $count = $api->manualSyncAllOrders($this->context->language->id);
        echo json_encode(['success' => true, 'synced' => $count, 'message' => $count . ' orders synced to ERPNext.']);
        exit;
    }

    private function ajaxSyncCustomers() {
        $api = $this->module->getApi();
        if (!$api) { echo json_encode(['error' => 'ERPNext credentials not configured.']); exit; }
        $count = $api->manualSyncAllCustomers();
        echo json_encode(['success' => true, 'synced' => $count, 'message' => $count . ' customers synced to ERPNext.']);
        exit;
    }

    private function ajaxSyncProducts() {
        $api = $this->module->getApi();
        if (!$api) { echo json_encode(['error' => 'ERPNext credentials not configured.']); exit; }
        $count = $api->manualSyncAllProducts($this->context->language->id);
        echo json_encode(['success' => true, 'synced' => $count, 'message' => $count . ' products synced to ERPNext.']);
        exit;
    }

    private function ajaxPullInvoices() {
        $api = $this->module->getApi();
        if (!$api) { echo json_encode(['error' => 'ERPNext credentials not configured.']); exit; }
        $from  = date('Y-m-d', strtotime('-30 days'));
        $count = PhytoErpApi::pullInvoices($api, $from);
        echo json_encode(['success' => true, 'synced' => $count, 'message' => $count . ' invoices pulled from ERPNext.']);
        exit;
    }
}
