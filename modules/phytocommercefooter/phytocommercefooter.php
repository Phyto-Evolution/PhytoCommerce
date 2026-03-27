<?php
if (!defined('_PS_VERSION_')) exit;

class PhytoCommerceFooter extends Module {
    public function __construct() {
        $this->name = 'phytocommercefooter';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Phyto Commerce';
        parent::__construct();
        $this->displayName = 'Phyto Commerce Footer';
        $this->description = 'Adds Phyto Commerce branding to footer';
    }

    public function install() {
        return parent::install() && $this->registerHook('displayFooter');
    }

    public function hookDisplayFooter($params) {
        return '<div style="text-align:center;padding:10px;font-size:12px;line-height:1.55;">'
            . 'Created with ❤️ from <a href="https://phytocommerce.com" target="_blank" rel="noopener noreferrer" style="color:inherit;text-decoration:underline;">Phyto Commerce</a><br>'
            . 'Contact: <a href="mailto:aphytoevolution@gmail.com" style="color:inherit;text-decoration:underline;">aphytoevolution@gmail.com</a> · <a href="tel:+918248984778" style="color:inherit;text-decoration:underline;">+91 82489 84778</a><br>'
            . 'Address: Phyto Evolution Private Limited, Forest Studio Labs, Chennai - 603103.'
            . '</div>';
    }
}
