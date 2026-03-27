<?php
if (!defined('_PS_VERSION_')) exit;

require_once __DIR__ . '/classes/PhytoTaxonomy.php';

class PhytoQuickAdd extends Module {

    public function __construct() {
        $this->name        = 'phytoquickadd';
        $this->tab         = 'administration';
        $this->version     = '3.0.0';
        $this->author      = 'Phyto Evolution';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = 'Phyto Quick Add';
        $this->description = 'Quickly add products and categories with taxonomy intelligence';
    }

    public function install() {
        return parent::install() && $this->installTab();
    }

    public function uninstall() {
        return parent::uninstall() && $this->uninstallTab();
    }

    public function getContent() {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoQuickAdd')
        );
    }

    private function installTab() {
        $tab = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoQuickAdd';
        $tab->name       = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Phyto Quick Add';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $tab->module    = $this->name;
        return $tab->add();
    }

    private function uninstallTab() {
        $id_tab = (int)Tab::getIdFromClassName('AdminPhytoQuickAdd');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }
}
