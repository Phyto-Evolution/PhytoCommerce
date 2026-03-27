<?php
if (!defined('_PS_VERSION_')) exit;

require_once __DIR__ . '/classes/PhytoSeo.php';

class PhytoSeoBooster extends Module {

    public function __construct() {
        $this->name          = 'phytoseobooster';
        $this->tab           = 'seo';
        $this->version       = '1.0.0';
        $this->author        = 'Phyto Evolution';
        $this->need_instance = 0;
        $this->bootstrap     = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
        parent::__construct();
        $this->displayName = 'Phyto SEO Booster';
        $this->description = 'AI-powered SEO automation for plant listings — auto meta, schema markup, alt text, and bulk audit';
    }

    public function install() {
        return parent::install()
            && $this->runSql('install')
            && $this->installTab()
            && $this->registerHook('displayHeader')
            && $this->registerHook('actionObjectProductAddAfter')
            && $this->registerHook('actionObjectProductUpdateAfter');
    }

    public function uninstall() {
        return $this->runSql('uninstall')
            && $this->uninstallTab()
            && parent::uninstall();
    }

    private function runSql(string $type): bool
    {
        $file = __DIR__ . '/sql/' . $type . '.sql';
        if (!file_exists($file)) {
            return true;
        }
        $sql = file_get_contents($file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }
        return true;
    }

    public function getContent() {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoSeoBooster')
        );
    }

    // ── Hooks ────────────────────────────────────────────────────────────────

    /**
     * Inject Product JSON-LD schema markup on product pages (front-end).
     */
    public function hookDisplayHeader($params) {
        $controller = $this->context->controller;
        if (!($controller instanceof ProductController)) return '';

        $id_product = (int)Tools::getValue('id_product');
        if (!$id_product) return '';
        $id_lang = $this->context->language->id;
        $product = new Product($id_product, false, $id_lang);
        if (!Validate::isLoadedObject($product)) return '';

        return PhytoSeo::generateSchemaMarkup($product, $id_lang, $this->context);
    }

    /**
     * Auto-generate meta for new products if fields are empty.
     */
    public function hookActionObjectProductAddAfter($params) {
        if (!Configuration::get('PHYTO_SEO_AUTO_META')) return;
        PhytoSeo::autoGenerateMeta($params['object'], $this->context->language->id);
    }

    /**
     * Auto-generate meta for updated products if fields are empty.
     */
    public function hookActionObjectProductUpdateAfter($params) {
        if (!Configuration::get('PHYTO_SEO_AUTO_META')) return;
        PhytoSeo::autoGenerateMeta($params['object'], $this->context->language->id);
    }

    // ── Tab installation ─────────────────────────────────────────────────────

    private function installTab() {
        $tab = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoSeoBooster';
        $tab->name       = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Phyto SEO Booster';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentMeta');
        $tab->module    = $this->name;
        return $tab->add();
    }

    private function uninstallTab() {
        $id_tab = (int)Tab::getIdFromClassName('AdminPhytoSeoBooster');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }
}
