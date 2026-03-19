<?php
if (!defined('_PS_VERSION_')) exit;

require_once __DIR__ . '/classes/PhytoErpApi.php';

class PhytoErpConnector extends Module {

    public function __construct() {
        $this->name          = 'phytoerpconnector';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'Phyto Evolution';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = 'Phyto ERP Connector';
        $this->description = 'Connects PrestaShop stores to ERPNext v15 — syncs orders, customers, products and invoices';
    }

    public function install() {
        return parent::install()
            && $this->createSyncLogTable()
            && $this->installTab()
            && $this->registerHook('actionOrderStatusPostUpdate')
            && $this->registerHook('actionCustomerAccountAdd')
            && $this->registerHook('actionObjectProductAddAfter')
            && $this->registerHook('actionObjectProductUpdateAfter');
    }

    public function uninstall() {
        return parent::uninstall() && $this->uninstallTab();
    }

    public function getContent() {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoErpConnector')
        );
    }

    // ── Hooks ────────────────────────────────────────────────────────────────

    public function hookActionOrderStatusPostUpdate($params) {
        if (!Configuration::get('PHYTO_ERP_SYNC_ORDERS')) return;
        $api = $this->getApi();
        if (!$api) return;
        $order = new Order((int)$params['id_order']);
        if (!Validate::isLoadedObject($order)) return;
        PhytoErpApi::syncOrder($api, $order, $this->context->language->id);
    }

    public function hookActionCustomerAccountAdd($params) {
        if (!Configuration::get('PHYTO_ERP_SYNC_CUSTOMERS')) return;
        $api = $this->getApi();
        if (!$api) return;
        PhytoErpApi::syncCustomer($api, $params['newCustomer']);
    }

    public function hookActionObjectProductAddAfter($params) {
        if (!Configuration::get('PHYTO_ERP_SYNC_PRODUCTS')) return;
        $api = $this->getApi();
        if (!$api) return;
        PhytoErpApi::syncProduct($api, $params['object'], $this->context->language->id);
    }

    public function hookActionObjectProductUpdateAfter($params) {
        if (!Configuration::get('PHYTO_ERP_SYNC_PRODUCTS')) return;
        $api = $this->getApi();
        if (!$api) return;
        PhytoErpApi::syncProduct($api, $params['object'], $this->context->language->id);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function getApi() {
        $url    = Configuration::get('PHYTO_ERP_URL');
        $key    = Configuration::get('PHYTO_ERP_API_KEY');
        $secret = Configuration::get('PHYTO_ERP_API_SECRET');
        if (empty($url) || empty($key) || empty($secret)) return null;
        return new PhytoErpApi($url, $key, $secret);
    }

    private function createSyncLogTable() {
        return Db::getInstance()->execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'phyto_erp_sync_log` (
                `id`         int(11)      NOT NULL AUTO_INCREMENT,
                `sync_type`  varchar(32)  NOT NULL,
                `direction`  varchar(8)   NOT NULL DEFAULT \'push\',
                `ps_id`      int(11)               DEFAULT NULL,
                `erp_name`   varchar(255)           DEFAULT NULL,
                `status`     varchar(16)  NOT NULL DEFAULT \'success\',
                `message`    text,
                `created_at` datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;'
        );
    }

    private function installTab() {
        $tab = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoErpConnector';
        $tab->name       = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'ERP Connector';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminTools');
        $tab->module    = $this->name;
        return $tab->add();
    }

    private function uninstallTab() {
        $id_tab = (int)Tab::getIdFromClassName('AdminPhytoErpConnector');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }
}
