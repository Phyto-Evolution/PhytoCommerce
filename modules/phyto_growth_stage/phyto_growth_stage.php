<?php
/**
 * Phyto Growth Stage — PrestaShop 8.1 module
 *
 * Replace or augment combination size labels with named growth stages,
 * each carrying care-difficulty and time-to-maturity metadata.
 *
 * @author    PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoGrowthStageDef.php';

class Phyto_Growth_Stage extends Module
{
    public function __construct()
    {
        $this->name                   = 'phyto_growth_stage';
        $this->tab                    = 'front_office_features';
        $this->version                = '1.0.0';
        $this->author                 = 'PhytoCommerce';
        $this->need_instance          = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
        $this->bootstrap              = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Growth Stages');
        $this->description = $this->l('Replace or augment combination size labels with named growth stages, each carrying care-difficulty and time-to-maturity metadata.');

        $this->confirmUninstall = $this->l('Are you sure? All growth stage data will be deleted.');
    }

    /* ------------------------------------------------------------------
     *  Install / Uninstall
     * ------------------------------------------------------------------ */

    public function install()
    {
        return parent::install()
            && $this->runSql('install')
            && $this->installTab()
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayProductPriceBlock')
            && $this->registerHook('actionFrontControllerSetMedia');
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    /* ------------------------------------------------------------------
     *  SQL helper
     * ------------------------------------------------------------------ */

    /**
     * Run an SQL file from the sql/ directory.
     *
     * @param string $filename File basename without extension (e.g. "install")
     *
     * @return bool
     */
    private function runSql($filename)
    {
        $file = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        $statements = preg_split('/;\s*[\r\n]+/', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                if (!Db::getInstance()->execute($statement)) {
                    return false;
                }
            }
        }

        return true;
    }

    /* ------------------------------------------------------------------
     *  Admin tab helpers
     * ------------------------------------------------------------------ */

    /**
     * Install back-office menu tabs.
     *
     * @return bool
     */
    private function installTab()
    {
        // Main visible tab: Catalog → Growth Stages
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoGrowthStages';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCatalog');

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'Growth Stages';
        }

        if (!$tab->add()) {
            return false;
        }

        // Hidden tab for product-stage AJAX
        $tabProduct             = new Tab();
        $tabProduct->active     = 1;
        $tabProduct->class_name = 'AdminPhytoGrowthStageProduct';
        $tabProduct->module     = $this->name;
        $tabProduct->id_parent  = -1; // hidden

        foreach ($languages as $lang) {
            $tabProduct->name[$lang['id_lang']] = 'Growth Stage Product AJAX';
        }

        return $tabProduct->add();
    }

    /**
     * Uninstall back-office menu tabs.
     *
     * @return bool
     */
    private function uninstallTab()
    {
        $tabs = ['AdminPhytoGrowthStages', 'AdminPhytoGrowthStageProduct'];

        foreach ($tabs as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);
            if ($idTab) {
                $tab = new Tab($idTab);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    /* ------------------------------------------------------------------
     *  Hooks — Back office
     * ------------------------------------------------------------------ */

    /**
     * Product edit: extra tab with growth-stage assignment form.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];

        if (!$idProduct) {
            return '';
        }

        // Fetch product combinations
        $product      = new Product($idProduct);
        $combinations = $product->getAttributeCombinations(Context::getContext()->language->id);

        // Build a simplified list: id_product_attribute => label
        $comboList = [];
        foreach ($combinations as $combo) {
            $key = (int) $combo['id_product_attribute'];
            if (!isset($comboList[$key])) {
                $comboList[$key] = '';
            }
            $comboList[$key] .= ($comboList[$key] ? ' / ' : '') . $combo['group_name'] . ': ' . $combo['attribute_name'];
        }

        // If no combinations, we still allow mapping to the base product (attribute 0)
        if (empty($comboList)) {
            $comboList[0] = $this->l('Default (no combinations)');
        }

        // Fetch existing mappings
        $mappings = PhytoGrowthStageDef::getStagesForProduct($idProduct);
        $mapped   = [];
        foreach ($mappings as $m) {
            $mapped[(int) $m['id_product_attribute']] = $m;
        }

        // Fetch all stage definitions for dropdown
        $stages = PhytoGrowthStageDef::getAllStages();

        $this->context->smarty->assign([
            'id_product'    => $idProduct,
            'combinations'  => $comboList,
            'stages'        => $stages,
            'mapped'        => $mapped,
            'ajax_url'      => $this->context->link->getAdminLink('AdminPhytoGrowthStageProduct'),
            'module_name'   => $this->name,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /* ------------------------------------------------------------------
     *  Hooks — Front office
     * ------------------------------------------------------------------ */

    /**
     * Register front CSS and JS.
     */
    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductControllerCore
            || $this->context->controller instanceof ProductController
        ) {
            $this->context->controller->registerStylesheet(
                'phyto-growth-stage-front',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 150]
            );

            $this->context->controller->registerJavascript(
                'phyto-growth-stage-front-js',
                'modules/' . $this->name . '/views/js/front.js',
                ['position' => 'bottom', 'priority' => 150]
            );
        }
    }

    /**
     * Display the growth-stage card on product pages.
     *
     * @param array $params
     *
     * @return array Array of PrestaShop\PrestaShop\Core\Product\ProductExtraContent
     */
    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->id;

        // Determine selected combination (default 0)
        $idProductAttribute = (int) Tools::getValue('id_product_attribute', 0);

        $stageData = PhytoGrowthStageDef::getStagesForProduct($idProduct, $idProductAttribute);

        if (empty($stageData)) {
            // Try fallback to default product (attribute 0)
            $stageData = PhytoGrowthStageDef::getStagesForProduct($idProduct, 0);
        }

        if (empty($stageData)) {
            return [];
        }

        $stage    = $stageData[0];
        $position = PhytoGrowthStageDef::getStagePosition((int) $stage['id_stage']);

        $weeksDisplay = ($stage['weeks_override'] !== null)
            ? (int) $stage['weeks_override']
            : (int) $stage['weeks_to_next'];

        $difficultyColors = [
            'Beginner'     => '#28a745',
            'Intermediate' => '#007bff',
            'Advanced'     => '#fd7e14',
            'Expert'       => '#dc3545',
        ];

        $this->context->smarty->assign([
            'growth_stage'       => $stage,
            'stage_index'        => $position['index'],
            'stage_total'        => $position['total'],
            'weeks_display'      => $weeksDisplay,
            'difficulty_color'   => isset($difficultyColors[$stage['difficulty']])
                ? $difficultyColors[$stage['difficulty']]
                : '#6c757d',
        ]);

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $extraContent->setTitle($this->l('Growth Stage'));
        $extraContent->setContent($content);

        return [$extraContent];
    }

    /**
     * Inject a compact stage badge near the Add to Cart / price area.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayProductPriceBlock($params)
    {
        if (!isset($params['type']) || $params['type'] !== 'after_price') {
            return '';
        }

        $idProduct = (int) $params['product']['id_product'];

        $idProductAttribute = (int) Tools::getValue('id_product_attribute', 0);

        $stageData = PhytoGrowthStageDef::getStagesForProduct($idProduct, $idProductAttribute);

        if (empty($stageData)) {
            $stageData = PhytoGrowthStageDef::getStagesForProduct($idProduct, 0);
        }

        if (empty($stageData)) {
            return '';
        }

        $stage = $stageData[0];

        $difficultyColors = [
            'Beginner'     => '#28a745',
            'Intermediate' => '#007bff',
            'Advanced'     => '#fd7e14',
            'Expert'       => '#dc3545',
        ];

        $this->context->smarty->assign([
            'badge_stage'      => $stage,
            'badge_diff_color' => isset($difficultyColors[$stage['difficulty']])
                ? $difficultyColors[$stage['difficulty']]
                : '#6c757d',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/product_price_block.tpl');
    }
}
