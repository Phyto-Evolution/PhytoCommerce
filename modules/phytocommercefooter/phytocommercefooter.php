<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoCommerceFooter extends Module {
    public function __construct() {
        $this->name = 'phytocommercefooter';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Phyto Evolution';
        parent::__construct();
        $this->displayName = 'Phyto Commerce Footer';
        $this->description = 'Adds Phyto Commerce branding to footer';
    }

    public function install() {
        return parent::install() && $this->registerHook('displayFooter');
    }

    public function hookDisplayFooter($params) {
        return '<div style="text-align:center;padding:10px;font-size:12px;">Created with ❤️ from <a href="https://phytolabs.in" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline;">Phyto Commerce</a>, Phyto Evolution Private Limited</div>';
    }
}
