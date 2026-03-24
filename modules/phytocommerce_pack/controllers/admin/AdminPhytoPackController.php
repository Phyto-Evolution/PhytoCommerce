<?php
if (!defined('_PS_VERSION_')) exit;

class AdminPhytoPackController extends ModuleAdminController {

    public function __construct() {
        parent::__construct();
        $this->bootstrap = true;
        $this->display   = 'view';
    }

    public function init() {
        ob_start();
        parent::init();
        if (Tools::isSubmit('phyto_ajax')) {
            header('Content-Type: application/json');
            $action = Tools::getValue('phyto_action');
            ob_clean();
            switch ($action) {
                case 'install_module':   $this->ajaxInstallModule();   break;
                case 'uninstall_module': $this->ajaxUninstallModule(); break;
                case 'install_all':      $this->ajaxInstallAll();      break;
                default: echo json_encode(['error' => 'Unknown action']); exit;
            }
        }
    }

    public function renderView() {
        $statuses   = Phytocommerce_Pack::getModuleStatus();
        $installed  = count(array_filter($statuses, fn($s) => $s['installed']));
        $total      = count($statuses);
        $install_log = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'phyto_pack_log ORDER BY installed_at DESC LIMIT 50'
        );

        $this->context->smarty->assign([
            'statuses'    => $statuses,
            'installed'   => $installed,
            'total'       => $total,
            'install_log' => $install_log ?: [],
            'ajax_url'    => $this->context->link->getAdminLink('AdminPhytoPack'),
            'pack_version'=> $this->module->version,
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phytocommerce_pack/views/templates/admin/dashboard.tpl'
        );
    }

    private function ajaxInstallModule() {
        $module_name = Tools::getValue('module_name');
        if (!in_array($module_name, Phytocommerce_Pack::getModuleList())) {
            echo json_encode(['error' => 'Unknown module']); exit;
        }
        $module = Module::getInstanceByName($module_name);
        if (!$module) { echo json_encode(['error' => 'Module could not be loaded']); exit; }
        if ($module->isInstalled()) { echo json_encode(['success' => true, 'status' => 'already_installed']); exit; }
        $ok = $module->install();
        echo json_encode(['success' => $ok, 'status' => $ok ? 'installed' : 'failed']);
        exit;
    }

    private function ajaxUninstallModule() {
        $module_name = Tools::getValue('module_name');
        if (!in_array($module_name, Phytocommerce_Pack::getModuleList())) {
            echo json_encode(['error' => 'Unknown module']); exit;
        }
        $module = Module::getInstanceByName($module_name);
        if (!$module) { echo json_encode(['error' => 'Module could not be loaded']); exit; }
        if (!$module->isInstalled()) { echo json_encode(['success' => true, 'status' => 'not_installed']); exit; }
        $ok = $module->uninstall();
        echo json_encode(['success' => $ok, 'status' => $ok ? 'uninstalled' : 'failed']);
        exit;
    }

    private function ajaxInstallAll() {
        $results = [];
        foreach (Phytocommerce_Pack::getModuleList() as $module_name) {
            $module = Module::getInstanceByName($module_name);
            if (!$module) { $results[$module_name] = 'load_failed'; continue; }
            if ($module->isInstalled()) { $results[$module_name] = 'skipped'; continue; }
            $results[$module_name] = $module->install() ? 'installed' : 'failed';
        }
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }
}
