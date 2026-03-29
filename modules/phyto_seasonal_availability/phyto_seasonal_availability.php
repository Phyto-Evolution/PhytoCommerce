<?php
/**
 * Phyto Seasonal Availability
 *
 * Mark products with dormancy/shipping windows, block purchase during
 * incompatible months, and offer "notify me" email capture when out of season.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Seasonal_Availability extends Module
{
    /** @var string[] Month labels indexed 1–12 */
    public static $monthLabels = [
        1  => 'January',
        2  => 'February',
        3  => 'March',
        4  => 'April',
        5  => 'May',
        6  => 'June',
        7  => 'July',
        8  => 'August',
        9  => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    public function __construct()
    {
        $this->name          = 'phyto_seasonal_availability';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Seasonal Availability');
        $this->description = $this->l('Marks each product with the months it can be safely shipped and any dormancy periods, then automatically blocks purchase outside the allowed window and displays a customisable out-of-season message. A visual month-grid on the product page shows buyers exactly when the plant ships, and an optional "Notify me when back in season" email capture collects interested buyers for later outreach. Essential for tropical and temperate plant sellers whose stock availability changes with the season.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    /* ------------------------------------------------------------------
     *  INSTALL / UNINSTALL
     * ------------------------------------------------------------------ */

    public function install()
    {
        return parent::install()
            && $this->executeSqlFile('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->installTab('AdminPhytoSeasonalNotify', 'Seasonal Notifications', 'AdminCatalog')
            && $this->installTab('AdminPhytoSeasonalProduct', 'Seasonal Product AJAX', 'AdminCatalog', false);
    }

    public function uninstall()
    {
        return $this->executeSqlFile('uninstall')
            && $this->uninstallTab('AdminPhytoSeasonalNotify')
            && $this->uninstallTab('AdminPhytoSeasonalProduct')
            && parent::uninstall();
    }

    /* ------------------------------------------------------------------
     *  SQL HELPER
     * ------------------------------------------------------------------ */

    private function executeSqlFile($filename)
    {
        $path = dirname(__FILE__) . '/sql/' . $filename . '.sql';
        if (!file_exists($path)) {
            return false;
        }

        $sql = str_replace(
            ['PREFIX_', 'ENGINE_TYPE'],
            [_DB_PREFIX_, _MYSQL_ENGINE_],
            file_get_contents($path)
        );

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
     *  TAB HELPERS
     * ------------------------------------------------------------------ */

    private function installTab($className, $name, $parent, $visible = true)
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = $className;
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName($parent);

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        if (!$visible) {
            $tab->id_parent = -1; // hidden
        }

        return $tab->add();
    }

    private function uninstallTab($className)
    {
        $id = (int) Tab::getIdFromClassName($className);
        if ($id) {
            $tab = new Tab($id);
            return $tab->delete();
        }
        return true;
    }

    /* ------------------------------------------------------------------
     *  DATA ACCESS HELPERS
     * ------------------------------------------------------------------ */

    /**
     * Retrieve seasonal settings for a product.
     *
     * @param int $idProduct
     * @return array|false
     */
    public static function getProductSeasonal($idProduct)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_seasonal_product`
             WHERE `id_product` = ' . (int) $idProduct
        );
    }

    /**
     * Check whether the current month is within the allowed shipping months.
     *
     * @param string|null $shipMonthsCsv Comma-separated month numbers
     * @return bool
     */
    public static function isInSeason($shipMonthsCsv)
    {
        if (empty($shipMonthsCsv)) {
            return true; // no restriction
        }

        $months = array_map('intval', explode(',', $shipMonthsCsv));
        return in_array((int) date('n'), $months, true);
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayAdminProductsExtra — product-edit tab
     * ------------------------------------------------------------------ */

    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];
        $row       = self::getProductSeasonal($idProduct);

        $shipMonths    = $row ? array_map('intval', explode(',', (string) $row['ship_months'])) : [];
        $dormMonths    = $row ? array_map('intval', explode(',', (string) $row['dormancy_months'])) : [];
        $blockPurchase = $row ? (int) $row['block_purchase'] : 0;
        $outMsg        = $row ? $row['out_of_season_msg'] : '';
        $enableNotify  = $row ? (int) $row['enable_notify'] : 1;

        $this->context->smarty->assign([
            'phyto_seasonal_id_product'   => $idProduct,
            'phyto_seasonal_months'       => self::$monthLabels,
            'phyto_seasonal_ship_months'  => $shipMonths,
            'phyto_seasonal_dorm_months'  => $dormMonths,
            'phyto_seasonal_block'        => $blockPurchase,
            'phyto_seasonal_msg'          => $outMsg,
            'phyto_seasonal_notify'       => $enableNotify,
            'phyto_seasonal_ajax_url'     => $this->context->link->getAdminLink('AdminPhytoSeasonalProduct'),
            'phyto_seasonal_admin_token'  => Tools::getAdminTokenLite('AdminPhytoSeasonalProduct'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayProductButtons — out-of-season message + notify form
     * ------------------------------------------------------------------ */

    public function hookDisplayProductButtons($params)
    {
        $idProduct = (int) $params['product']['id_product'];
        $row       = self::getProductSeasonal($idProduct);

        if (!$row) {
            return '';
        }

        $inSeason      = self::isInSeason($row['ship_months']);
        $blockPurchase = (int) $row['block_purchase'];
        $enableNotify  = (int) $row['enable_notify'];

        if ($inSeason || !$blockPurchase) {
            return '';
        }

        $this->context->smarty->assign([
            'phyto_seasonal_out_msg'      => $row['out_of_season_msg'],
            'phyto_seasonal_enable_notify' => $enableNotify,
            'phyto_seasonal_id_product'   => $idProduct,
            'phyto_seasonal_notify_url'   => $this->context->link->getModuleLink(
                $this->name,
                'notify',
                [],
                true
            ),
            'phyto_seasonal_token'        => Tools::getToken(false),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/product_buttons.tpl');
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayProductExtraContent — month grid
     * ------------------------------------------------------------------ */

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->id;
        $row       = self::getProductSeasonal($idProduct);

        if (!$row || empty($row['ship_months'])) {
            return [];
        }

        $shipMonths = array_map('intval', explode(',', (string) $row['ship_months']));
        $dormMonths = !empty($row['dormancy_months'])
            ? array_map('intval', explode(',', (string) $row['dormancy_months']))
            : [];

        $this->context->smarty->assign([
            'phyto_seasonal_months'      => self::$monthLabels,
            'phyto_seasonal_ship_months' => $shipMonths,
            'phyto_seasonal_dorm_months' => $dormMonths,
            'phyto_seasonal_current'     => (int) date('n'),
        ]);

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $tab = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $tab->setTitle($this->l('Shipping Season'))
            ->setContent($content);

        return [$tab];
    }

    /* ------------------------------------------------------------------
     *  HOOK: actionFrontControllerSetMedia — enqueue CSS / JS
     * ------------------------------------------------------------------ */

    public function hookActionFrontControllerSetMedia()
    {
        if ($this->context->controller instanceof ProductController) {
            $this->context->controller->registerStylesheet(
                'phyto-seasonal-front',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 150]
            );

            $this->context->controller->registerJavascript(
                'phyto-seasonal-front-js',
                'modules/' . $this->name . '/views/js/front.js',
                ['position' => 'bottom', 'priority' => 150]
            );
        }
    }
}
