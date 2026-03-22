<?php
/**
 * Phyto Wholesale Portal
 *
 * B2B wholesale tier with MOQ, tiered pricing and invoice payment.
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoWholesaleApplication.php';

class Phyto_Wholesale_Portal extends Module
{
    /**
     * @var array Hook list registered on install
     */
    private $hooksList = [
        'displayAdminProductsExtra',
        'displayProductPriceBlock',
        'actionCartUpdateQuantityBefore',
        'displayMyAccountBlock',
        'actionProductDelete',
    ];

    public function __construct()
    {
        $this->name          = 'phyto_wholesale_portal';
        $this->tab           = 'AdminCustomers';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 1;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Wholesale Portal');
        $this->description = $this->l('B2B wholesale tier with MOQ, tiered pricing and invoice payment.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHooks()
            && $this->installTab()
            && $this->createWholesaleGroup();
    }

    public function uninstall(): bool
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helpers
    // -------------------------------------------------------------------------

    private function runSql(string $type): bool
    {
        $file = __DIR__ . '/sql/' . $type . '.sql';
        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);
        if (empty($sql)) {
            return true;
        }

        $sql     = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $queries = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query === '') {
                continue;
            }
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab helpers
    // -------------------------------------------------------------------------

    private function installTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPhytoWholesale');
        if ($tabId) {
            return true; // already installed
        }

        $parentTabId = (int) Tab::getIdFromClassName('AdminCustomers');

        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoWholesale';
        $tab->module     = $this->name;
        $tab->id_parent  = $parentTabId > 0 ? $parentTabId : -1;
        $tab->icon       = 'business_center';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Wholesale Applications';
        }

        return $tab->add();
    }

    private function uninstallTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPhytoWholesale');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    // -------------------------------------------------------------------------
    // Wholesale customer group
    // -------------------------------------------------------------------------

    private function createWholesaleGroup(): bool
    {
        // Check if group already exists
        $existingId = (int) Db::getInstance()->getValue(
            'SELECT `id_group` FROM `' . _DB_PREFIX_ . 'group_lang`
             WHERE `name` = \'Wholesale\' LIMIT 1'
        );

        if ($existingId > 0) {
            Configuration::updateValue('PHYTO_WHOLESALE_GROUP_ID', $existingId);
            return true;
        }

        $group              = new Group();
        $group->price_display_method = 0; // tax included
        $group->reduction   = 0;
        $group->show_prices = 1;

        foreach (Language::getLanguages(false) as $lang) {
            $group->name[$lang['id_lang']] = 'Wholesale';
        }

        if (!$group->add()) {
            return false;
        }

        Configuration::updateValue('PHYTO_WHOLESALE_GROUP_ID', (int) $group->id);
        return true;
    }

    // -------------------------------------------------------------------------
    // Hook registration
    // -------------------------------------------------------------------------

    private function registerHooks(): bool
    {
        foreach ($this->hooksList as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Module configuration page
    // -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submit_phyto_wholesale')) {
            $output .= $this->postProcess();
        }

        return $output . $this->renderConfigForm();
    }

    private function postProcess(): string
    {
        $groupId      = (int) Tools::getValue('PHYTO_WHOLESALE_GROUP_ID');
        $requireAppr  = (int) Tools::getValue('PHYTO_WHOLESALE_REQUIRE_APPROVAL');
        $invoiceDel   = (int) Tools::getValue('PHYTO_WHOLESALE_INVOICE_DELIVERY');
        $invoiceDays  = (int) Tools::getValue('PHYTO_WHOLESALE_INVOICE_DAYS');

        if ($invoiceDays < 1) {
            $invoiceDays = 30;
        }

        Configuration::updateValue('PHYTO_WHOLESALE_GROUP_ID', $groupId);
        Configuration::updateValue('PHYTO_WHOLESALE_REQUIRE_APPROVAL', $requireAppr);
        Configuration::updateValue('PHYTO_WHOLESALE_INVOICE_DELIVERY', $invoiceDel);
        Configuration::updateValue('PHYTO_WHOLESALE_INVOICE_DAYS', $invoiceDays);

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    private function renderConfigForm(): string
    {
        // Build group list for select
        $groups   = Group::getGroups((int) $this->context->language->id);
        $groupOpts = [];
        foreach ($groups as $g) {
            $groupOpts[] = ['id' => $g['id_group'], 'name' => $g['name']];
        }

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Wholesale Portal Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input'  => [
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Wholesale Customer Group'),
                        'name'    => 'PHYTO_WHOLESALE_GROUP_ID',
                        'options' => [
                            'query' => $groupOpts,
                            'id'    => 'id',
                            'name'  => 'name',
                        ],
                        'desc'    => $this->l('Customer group assigned to approved wholesale accounts.'),
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Require Manual Approval'),
                        'name'    => 'PHYTO_WHOLESALE_REQUIRE_APPROVAL',
                        'is_bool' => true,
                        'desc'    => $this->l('When enabled, applications must be manually approved before access is granted.'),
                        'values'  => [
                            ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Invoice on Delivery'),
                        'name'    => 'PHYTO_WHOLESALE_INVOICE_DELIVERY',
                        'is_bool' => true,
                        'desc'    => $this->l('Allow wholesale customers to pay on invoice after delivery.'),
                        'values'  => [
                            ['id' => 'inv_on',  'value' => 1, 'label' => $this->l('Enabled')],
                            ['id' => 'inv_off', 'value' => 0, 'label' => $this->l('Disabled')],
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Invoice Payment Days'),
                        'name'  => 'PHYTO_WHOLESALE_INVOICE_DAYS',
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('Number of days allowed for invoice payment (e.g. 30).'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = (int) $this->context->language->id;
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submit_phyto_wholesale';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['PHYTO_WHOLESALE_GROUP_ID']       = (int) Configuration::get('PHYTO_WHOLESALE_GROUP_ID');
        $helper->fields_value['PHYTO_WHOLESALE_REQUIRE_APPROVAL'] = (int) Configuration::get('PHYTO_WHOLESALE_REQUIRE_APPROVAL', null, null, null, 1);
        $helper->fields_value['PHYTO_WHOLESALE_INVOICE_DELIVERY']  = (int) Configuration::get('PHYTO_WHOLESALE_INVOICE_DELIVERY', null, null, null, 1);
        $helper->fields_value['PHYTO_WHOLESALE_INVOICE_DAYS']    = (int) Configuration::get('PHYTO_WHOLESALE_INVOICE_DAYS', null, null, null, 30);

        return $helper->generateForm([$fields_form]);
    }

    // -------------------------------------------------------------------------
    // Hook: displayAdminProductsExtra
    // -------------------------------------------------------------------------

    public function hookDisplayAdminProductsExtra(array $params): string
    {
        $idProduct = (int) ($params['id_product'] ?? Tools::getValue('id_product'));

        if (!$idProduct) {
            return '';
        }

        $wsData = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_wholesale_product`
             WHERE `id_product` = ' . $idProduct
        );

        $moq           = $wsData ? (int) $wsData['moq'] : 0;
        $wholesaleOnly = $wsData ? (int) $wsData['wholesale_only'] : 0;
        $priceTiers    = [];

        if ($wsData && !empty($wsData['price_tiers'])) {
            $decoded = json_decode($wsData['price_tiers'], true);
            if (is_array($decoded)) {
                $priceTiers = $decoded;
            }
        }

        $this->context->smarty->assign([
            'phyto_ws_id_product'   => $idProduct,
            'phyto_ws_moq'          => $moq,
            'phyto_ws_wholesale_only' => $wholesaleOnly,
            'phyto_ws_price_tiers'  => $priceTiers,
            'phyto_ws_admin_token'  => Tools::getAdminTokenLite('AdminPhytoWholesale'),
            'phyto_ws_admin_url'    => $this->context->link->getAdminLink('AdminPhytoWholesale'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: displayProductPriceBlock
    // -------------------------------------------------------------------------

    public function hookDisplayProductPriceBlock(array $params): string
    {
        if (!isset($params['type']) || $params['type'] !== 'after_price') {
            return '';
        }

        if (!$this->isWholesaleCustomer()) {
            return '';
        }

        $idProduct = (int) ($params['product']['id_product'] ?? $params['product']->id ?? 0);
        if (!$idProduct) {
            return '';
        }

        $wsData = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_wholesale_product`
             WHERE `id_product` = ' . $idProduct
        );

        if (!$wsData || empty($wsData['price_tiers'])) {
            return '';
        }

        $tiers = json_decode($wsData['price_tiers'], true);
        if (!is_array($tiers) || empty($tiers)) {
            return '';
        }

        $this->context->smarty->assign([
            'phyto_ws_tiers' => $tiers,
            'phyto_ws_moq'   => (int) $wsData['moq'],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/product_price_block.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: actionCartUpdateQuantityBefore
    // -------------------------------------------------------------------------

    public function hookActionCartUpdateQuantityBefore(array $params): void
    {
        if (!$this->isWholesaleCustomer()) {
            return;
        }

        $idProduct = (int) ($params['id_product'] ?? 0);
        $quantity  = (int) ($params['quantity'] ?? 0);

        if (!$idProduct) {
            return;
        }

        $moq = (int) Db::getInstance()->getValue(
            'SELECT `moq` FROM `' . _DB_PREFIX_ . 'phyto_wholesale_product`
             WHERE `id_product` = ' . $idProduct . ' AND `moq` > 0'
        );

        if ($moq > 0 && $quantity < $moq) {
            $message = sprintf(
                $this->l('Minimum order quantity for this product is %d units.'),
                $moq
            );
            // Attach error to context for cart controller to display
            $this->context->controller->errors[] = $message;
            // Throw to halt further processing in hooks
            throw new PrestaShopException($message);
        }
    }

    // -------------------------------------------------------------------------
    // Hook: displayMyAccountBlock
    // -------------------------------------------------------------------------

    public function hookDisplayMyAccountBlock(array $params): string
    {
        $customer = $this->context->customer;

        if (!$customer->isLogged()) {
            return '';
        }

        $wsGroupId   = (int) Configuration::get('PHYTO_WHOLESALE_GROUP_ID');
        $isWholesale = $this->isWholesaleCustomer();

        $this->context->smarty->assign([
            'phyto_ws_is_wholesale'  => $isWholesale,
            'phyto_ws_apply_url'     => $this->context->link->getModuleLink($this->name, 'apply'),
            'phyto_ws_dashboard_url' => $this->context->link->getModuleLink($this->name, 'apply'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/my_account_block.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: actionProductDelete
    // -------------------------------------------------------------------------

    public function hookActionProductDelete(array $params): void
    {
        $idProduct = (int) ($params['id_product'] ?? ($params['product']->id ?? 0));

        if ($idProduct) {
            Db::getInstance()->delete(
                'phyto_wholesale_product',
                '`id_product` = ' . $idProduct
            );
        }
    }

    // -------------------------------------------------------------------------
    // Utility helpers
    // -------------------------------------------------------------------------

    public function isWholesaleCustomer(): bool
    {
        $customer = $this->context->customer;

        if (!$customer || !$customer->isLogged()) {
            return false;
        }

        $wsGroupId = (int) Configuration::get('PHYTO_WHOLESALE_GROUP_ID');
        if (!$wsGroupId) {
            return false;
        }

        $groups = Customer::getGroupsStatic((int) $customer->id);

        return in_array($wsGroupId, $groups, true);
    }

    /**
     * Add customer to the wholesale group.
     */
    public static function addCustomerToWholesaleGroup(int $idCustomer): bool
    {
        $wsGroupId = (int) Configuration::get('PHYTO_WHOLESALE_GROUP_ID');
        if (!$wsGroupId || !$idCustomer) {
            return false;
        }

        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        $currentGroups = Customer::getGroupsStatic($idCustomer);

        if (!in_array($wsGroupId, $currentGroups, true)) {
            $currentGroups[] = $wsGroupId;
            $customer->updateGroup($currentGroups);
        }

        return true;
    }
}
