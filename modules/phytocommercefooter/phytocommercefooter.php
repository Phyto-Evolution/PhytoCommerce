<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoCommerceFooter extends Module {
    public function __construct() {
        $this->name        = 'phytocommercefooter';
        $this->tab         = 'front_office_features';
        $this->version     = '1.0.0';
        $this->author      = 'Phyto Evolution';
        $this->need_instance = 0;
        $this->bootstrap   = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = 'Phyto Commerce Footer';
        $this->description = 'Adds Phyto Commerce branding to footer';
    }

    public function install() {
        return parent::install() && $this->registerHook('displayFooter');
    }

    public function uninstall() {
        return parent::uninstall();
    }

    public function hookDisplayFooter($params) {
        return '<div style="text-align:center;padding:10px;font-size:12px;">Created with ❤️ from <a href="https://phytolabs.in" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline;">Phyto Commerce</a>, Phyto Evolution Private Limited</div>';
    }
}
