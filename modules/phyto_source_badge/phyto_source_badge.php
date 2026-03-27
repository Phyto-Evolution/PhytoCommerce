<?php
/**
 * PhytoCommerce — Phyto Source Badge Module
 *
 * Display sourcing origin badges on product cards and product pages.
 * Filterable in catalog.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoSourceBadgeDef.php';

class Phyto_Source_Badge extends Module
{
    /**
     * Module constructor — sets all metadata required by PrestaShop.
     */
    public function __construct()
    {
        $this->name            = 'phyto_source_badge';
        $this->tab             = 'front_office_features';
        $this->version         = '1.0.0';
        $this->author          = 'PhytoCommerce';
        $this->need_instance   = 0;
        $this->bootstrap       = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Phyto Source Badge');
        $this->description = $this->l('Display sourcing origin badges on product cards and pages.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? All badge assignments will be deleted.');
    }

    // ──────────────────────────────────────────────────────────────
    //  Install / Uninstall
    // ──────────────────────────────────────────────────────────────

    /**
     * Install the module: create tables, register hooks and admin tabs.
     *
     * @return bool
     */
    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHooks()
            && $this->installTabs();
    }

    /**
     * Uninstall the module: remove admin tabs, drop tables.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return $this->uninstallTabs()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // ──────────────────────────────────────────────────────────────
    //  SQL helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Execute a SQL file located in the sql/ directory.
     *
     * @param string $filename  'install' or 'uninstall' (without extension)
     *
     * @return bool
     */
    protected function runSql(string $filename): bool
    {
        $file = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);

        if (empty($sql)) {
            return true;
        }

        // Replace table-prefix placeholder
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        // Split on statement boundaries (semicolons followed by optional whitespace / newlines)
        $statements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '' || strpos($statement, '--') === 0) {
                continue;
            }
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook registration
    // ──────────────────────────────────────────────────────────────

    /**
     * Register all hooks used by this module.
     *
     * @return bool
     */
    protected function registerHooks(): bool
    {
        $hooks = [
            'displayAdminProductsExtra',
            'displayProductPriceBlock',
            'displayProductListItem',
            'displayProductExtraContent',
            'actionProductDelete',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Tab management
    // ──────────────────────────────────────────────────────────────

    /**
     * Install back-office menu tabs.
     *
     * @return bool
     */
    protected function installTabs(): bool
    {
        // Main badge definitions manager — visible under the Catalog menu
        if (!$this->installTab('AdminPhytoSourceBadge', 'Phyto Source Badges', 'AdminCatalog')) {
            return false;
        }

        // Hidden AJAX controller for product ↔ badge assignments
        if (!$this->installTab('AdminPhytoSourceBadgeProduct', 'Phyto Source Badge Products', -1)) {
            return false;
        }

        return true;
    }

    /**
     * Helper to create a single Tab record.
     *
     * @param string     $className  Controller class name
     * @param string     $name       Label shown in the menu
     * @param string|int $parent     Parent controller name or -1 to hide
     *
     * @return bool
     */
    protected function installTab(string $className, string $name, $parent): bool
    {
        $tab = new Tab();
        $tab->active      = 1;
        $tab->class_name  = $className;
        $tab->module      = $this->name;

        if ($parent === -1) {
            $tab->id_parent = -1;
        } else {
            $tab->id_parent = (int) Tab::getIdFromClassName($parent);
        }

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        return (bool) $tab->add();
    }

    /**
     * Remove all tabs registered by this module.
     *
     * @return bool
     */
    protected function uninstallTabs(): bool
    {
        $tabNames = [
            'AdminPhytoSourceBadge',
            'AdminPhytoSourceBadgeProduct',
        ];

        foreach ($tabNames as $className) {
            $id = (int) Tab::getIdFromClassName($className);
            if ($id > 0) {
                $tab = new Tab($id);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook implementations
    // ──────────────────────────────────────────────────────────────

    /**
     * Hook: product edit page — extra tab for badge assignment.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        $idProduct = (int) ($params['id_product'] ?? Tools::getValue('id_product'));

        $allBadges     = $this->getAllBadgeDefs();
        $productBadges = $this->getBadgesForProduct($idProduct);

        // Build an easy-lookup map: id_badge => assignment data
        $assigned = [];
        foreach ($productBadges as $pb) {
            $assigned[(int) $pb['id_badge']] = $pb;
        }

        // Ajax target URL
        $ajaxUrl = $this->context->link->getAdminLink('AdminPhytoSourceBadgeProduct');

        $this->context->smarty->assign([
            'phyto_all_badges'     => $allBadges,
            'phyto_assigned'       => $assigned,
            'phyto_id_product'     => $idProduct,
            'phyto_ajax_url'       => $ajaxUrl,
            'phyto_module_token'   => Tools::getAdminTokenLite('AdminPhytoSourceBadgeProduct'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /**
     * Hook: near product price — display small badge pills.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayProductPriceBlock(array $params): string
    {
        if (!isset($params['type']) || $params['type'] !== 'after_price') {
            return '';
        }

        $product   = $params['product'] ?? null;
        $idProduct = 0;

        if ($product instanceof Product) {
            $idProduct = (int) $product->id;
        } elseif (is_array($product)) {
            $idProduct = (int) ($product['id_product'] ?? 0);
        } elseif (isset($params['id_product'])) {
            $idProduct = (int) $params['id_product'];
        }

        if ($idProduct === 0) {
            return '';
        }

        $badges = $this->getBadgesForProduct($idProduct);

        if (empty($badges)) {
            return '';
        }

        $this->context->smarty->assign('phyto_badges', $badges);

        return $this->display(__FILE__, 'views/templates/hook/product_price_block.tpl');
    }

    /**
     * Hook: product list cards — display mini badge pills.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayProductListItem(array $params): string
    {
        $product   = $params['product'] ?? null;
        $idProduct = 0;

        if ($product instanceof Product) {
            $idProduct = (int) $product->id;
        } elseif (is_array($product)) {
            $idProduct = (int) ($product['id_product'] ?? 0);
        }

        if ($idProduct === 0) {
            return '';
        }

        $badges = $this->getBadgesForProduct($idProduct);

        if (empty($badges)) {
            return '';
        }

        $this->context->smarty->assign('phyto_badges', $badges);

        return $this->display(__FILE__, 'views/templates/hook/product_list.tpl');
    }

    /**
     * Hook: product page extra content panel — expanded badge section.
     *
     * @param array $params
     *
     * @return PrestaShop\PrestaShop\Core\Product\ProductExtraContent|string
     */
    public function hookDisplayProductExtraContent(array $params)
    {
        $product   = $params['product'] ?? null;
        $idProduct = 0;

        if ($product instanceof Product) {
            $idProduct = (int) $product->id;
        } elseif (is_array($product)) {
            $idProduct = (int) ($product['id_product'] ?? 0);
        }

        if ($idProduct === 0) {
            return '';
        }

        $badges = $this->getBadgesForProduct($idProduct);

        if (empty($badges)) {
            return '';
        }

        $this->context->smarty->assign('phyto_badges', $badges);
        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        // Return a ProductExtraContent object so PrestaShop creates a named tab.
        if (class_exists('\PrestaShop\PrestaShop\Core\Product\ProductExtraContent')) {
            $extra = new \PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $extra->setTitle($this->l('Source & Origin'));
            $extra->setContent($content);

            return $extra;
        }

        return $content;
    }

    /**
     * Hook: product delete — clean up badge assignments.
     *
     * @param array $params
     *
     * @return void
     */
    public function hookActionProductDelete(array $params): void
    {
        $idProduct = (int) ($params['id_product'] ?? 0);

        if ($idProduct > 0) {
            Db::getInstance()->delete(
                'phyto_source_badge_product',
                'id_product = ' . $idProduct
            );
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  Data-access helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Return all badge definitions ordered by sort_order.
     *
     * @return array
     */
    public function getAllBadgeDefs(): array
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_source_badge_def`
             ORDER BY `sort_order` ASC, `id_badge` ASC'
        ) ?: [];
    }

    /**
     * Return badge definitions joined with product-specific assignment data.
     *
     * @param int $idProduct
     *
     * @return array
     */
    public function getBadgesForProduct(int $idProduct): array
    {
        return Db::getInstance()->executeS(
            'SELECT d.`id_badge`, d.`badge_label`, d.`badge_slug`, d.`badge_color`,
                    d.`description`, d.`sort_order`,
                    p.`id_link`, p.`permit_ref`, p.`origin_country`
             FROM `' . _DB_PREFIX_ . 'phyto_source_badge_def` d
             INNER JOIN `' . _DB_PREFIX_ . 'phyto_source_badge_product` p
                     ON p.`id_badge` = d.`id_badge`
             WHERE p.`id_product` = ' . (int) $idProduct . '
             ORDER BY d.`sort_order` ASC, d.`id_badge` ASC'
        ) ?: [];
    }

    // ──────────────────────────────────────────────────────────────
    //  Module configuration page (minimal — badge CRUD is in its own
    //  controller; this just provides a redirect link)
    // ──────────────────────────────────────────────────────────────

    /**
     * Render the module configuration page in the module manager.
     *
     * @return string
     */
    public function getContent(): string
    {
        $url = $this->context->link->getAdminLink('AdminPhytoSourceBadge');

        $this->context->smarty->assign([
            'phyto_admin_url' => $url,
            'module_dir'      => $this->_path,
        ]);

        return $this->context->smarty->fetch(
            'string:<div class="alert alert-info">
                <a href="{$phyto_admin_url}" class="btn btn-primary">
                    {l s=\'Manage Source Badges\' mod=\'phyto_source_badge\'}
                </a>
            </div>'
        );
    }
}
