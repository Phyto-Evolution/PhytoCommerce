<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoQuickAdd extends Module {
    public function __construct() {
        $this->name = 'phytoquickadd';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Phyto Evolution';
        $this->need_instance = 0;
        parent::__construct();
        $this->displayName = 'Phyto Quick Add';
        $this->description = 'Quickly add products with AI-generated descriptions';
    }

    public function install() {
        return parent::install() &&
               $this->installTab();
    }

    public function uninstall() {
        return parent::uninstall() &&
               $this->uninstallTab();
    }

    private function installTab() {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminPhytoQuickAdd';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Phyto Quick Add';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminCatalog');
        $tab->module = $this->name;
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

    public function getContent() {
        return $this->context->smarty->fetch($this->local_path . 'views/templates/admin/quickadd.tpl');
    }
}
