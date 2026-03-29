<?php
/**
 * phyto_restock_alert.php
 *
 * Main module class for Phyto Restock Alert.
 * "Notify me when available" — customers subscribe their email to out-of-stock
 * products and receive an automatic notification when stock is replenished.
 *
 * @author    PhytoCommerce
 * @version   1.0.0
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Restock_Alert extends Module
{
    /** @var array Default configuration values */
    private array $defaultConfig = [
        'PHYTO_RESTOCK_FROM_NAME'      => '',   // falls back to shop name
        'PHYTO_RESTOCK_MAX_PER_RUN'    => 50,
        'PHYTO_RESTOCK_SHOW_FORM_OOS'  => 1,
    ];

    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct()
    {
        $this->name          = 'phyto_restock_alert';
        $this->tab           = 'AdminCatalog';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Restock Alert');
        $this->description = $this->l('Adds a "Notify me when available" subscription form to out-of-stock product pages so customers can register their email address without creating an account. When stock quantity rises above zero, the module automatically dispatches notification emails to all subscribers for that product and clears the subscriber list. Admin can view the full subscriber list per product from a dedicated back-office panel, and settings control the sender name and maximum notifications dispatched per run to avoid mail server flooding.');
        $this->confirmUninstall      = $this->l('Are you sure? All subscriber data will be removed.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        if (
            !parent::install()
            || !$this->runSql('install')
            || !$this->installDefaultConfig()
            || !$this->installTab()
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->registerHook('actionUpdateQuantity')
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('displayHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        if (
            !$this->uninstallTab()
            || !$this->runSql('uninstall')
            || !parent::uninstall()
        ) {
            return false;
        }

        foreach (array_keys($this->defaultConfig) as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // SQL helper
    // -------------------------------------------------------------------------

    protected function runSql(string $file): bool
    {
        $sqlFile = __DIR__ . '/sql/' . $file . '.sql';
        if (!file_exists($sqlFile)) {
            return false;
        }

        $sql = file_get_contents($sqlFile);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', 'InnoDB', $sql);

        foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab helpers
    // -------------------------------------------------------------------------

    protected function installTab(): bool
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoRestockAlert';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->icon       = 'notifications';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Restock Alerts';
        }

        return $tab->add();
    }

    protected function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoRestockAlert');
        if ($idTab) {
            $tab = new Tab($idTab);
            return $tab->delete();
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Default config
    // -------------------------------------------------------------------------

    protected function installDefaultConfig(): bool
    {
        $shopName = Configuration::get('PS_SHOP_NAME');

        foreach ($this->defaultConfig as $key => $value) {
            $val = ($key === 'PHYTO_RESTOCK_FROM_NAME') ? $shopName : $value;
            if (!Configuration::updateValue($key, $val)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Configuration page
    // -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoRestockConfig')) {
            $output .= $this->postProcessConfig();
        }

        return $output . $this->renderConfigForm();
    }

    protected function postProcessConfig(): string
    {
        $fromName   = Tools::getValue('PHYTO_RESTOCK_FROM_NAME');
        $maxPerRun  = (int) Tools::getValue('PHYTO_RESTOCK_MAX_PER_RUN');
        $showFormOos = (int) Tools::getValue('PHYTO_RESTOCK_SHOW_FORM_OOS');

        if (empty($fromName)) {
            return $this->displayError($this->l('Sender name cannot be empty.'));
        }

        if ($maxPerRun < 1 || $maxPerRun > 500) {
            return $this->displayError($this->l('Max emails per run must be between 1 and 500.'));
        }

        Configuration::updateValue('PHYTO_RESTOCK_FROM_NAME', pSQL($fromName));
        Configuration::updateValue('PHYTO_RESTOCK_MAX_PER_RUN', $maxPerRun);
        Configuration::updateValue('PHYTO_RESTOCK_SHOW_FORM_OOS', $showFormOos ? 1 : 0);

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    protected function renderConfigForm(): string
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Restock Alert Settings'),
                    'icon'  => 'icon-bell',
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->l('Sender Name'),
                        'name'     => 'PHYTO_RESTOCK_FROM_NAME',
                        'required' => true,
                        'desc'     => $this->l('Name used in the "From" field of notification emails.'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Max Emails Per Cron Run'),
                        'name'  => 'PHYTO_RESTOCK_MAX_PER_RUN',
                        'desc'  => $this->l('Limits the number of emails sent in a single cron/trigger run (1–500).'),
                        'class' => 'fixed-width-sm',
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Only Show Form When Out of Stock'),
                        'name'    => 'PHYTO_RESTOCK_SHOW_FORM_OOS',
                        'is_bool' => true,
                        'desc'    => $this->l('When enabled, the subscribe form is hidden for in-stock products.'),
                        'values'  => [
                            ['id' => 'oos_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'oos_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
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
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitPhytoRestockConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [
                'PHYTO_RESTOCK_FROM_NAME'     => Configuration::get('PHYTO_RESTOCK_FROM_NAME'),
                'PHYTO_RESTOCK_MAX_PER_RUN'   => (int) Configuration::get('PHYTO_RESTOCK_MAX_PER_RUN'),
                'PHYTO_RESTOCK_SHOW_FORM_OOS' => (int) Configuration::get('PHYTO_RESTOCK_SHOW_FORM_OOS'),
            ],
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    /**
     * displayHeader — enqueue CSS and JS on product pages.
     */
    public function hookDisplayHeader(array $params): void
    {
        if (
            isset($this->context->controller->php_self)
            && $this->context->controller->php_self === 'product'
        ) {
            $this->context->controller->addCSS(
                $this->_path . 'views/css/front.css',
                'all'
            );
            $this->context->controller->addJS(
                $this->_path . 'views/js/front.js'
            );

            Media::addJsDef([
                'phytoRestockAlert' => [
                    'ajaxUrl'  => $this->context->link->getModuleLink(
                        $this->name,
                        'subscribe',
                        [],
                        true
                    ),
                    'token'    => Tools::getToken(false),
                    'msgOk'    => $this->l('You will be notified when this product is back in stock.'),
                    'msgAlready' => $this->l('You are already subscribed to notifications for this product.'),
                    'msgError' => $this->l('An error occurred. Please try again.'),
                ],
            ]);
        }
    }

    /**
     * displayProductAdditionalInfo — render subscribe form on the product page.
     */
    public function hookDisplayProductAdditionalInfo(array $params): string
    {
        $product   = $params['product'] ?? null;
        $showFormOos = (bool) Configuration::get('PHYTO_RESTOCK_SHOW_FORM_OOS');

        if (!$product) {
            return '';
        }

        $quantity = (int) ($product['quantity'] ?? 0);

        // Only show when out of stock (if setting requires it)
        if ($showFormOos && $quantity > 0) {
            return '';
        }

        $idProduct          = (int) ($product['id_product'] ?? 0);
        $idProductAttribute = (int) ($product['id_product_attribute'] ?? 0);

        $this->context->smarty->assign([
            'phyto_restock_id_product'          => $idProduct,
            'phyto_restock_id_product_attribute' => $idProductAttribute,
            'phyto_restock_customer_email'       => $this->context->customer->isLogged()
                ? $this->context->customer->email
                : '',
            'phyto_restock_customer_firstname'   => $this->context->customer->isLogged()
                ? $this->context->customer->firstname
                : '',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/restock_form.tpl');
    }

    /**
     * actionUpdateQuantity — send notifications when stock is replenished.
     *
     * @param array $params Contains: id_product, id_product_attribute, quantity
     */
    public function hookActionUpdateQuantity(array $params): void
    {
        $idProduct          = (int) ($params['id_product'] ?? 0);
        $idProductAttribute = (int) ($params['id_product_attribute'] ?? 0);
        $newQty             = (int) ($params['quantity'] ?? 0);

        if ($newQty > 0 && $idProduct > 0) {
            $this->sendRestockNotifications($idProduct, $idProductAttribute);
        }
    }

    /**
     * actionOrderStatusPostUpdate — secondary stock trigger.
     */
    public function hookActionOrderStatusPostUpdate(array $params): void
    {
        $idOrder     = (int) ($params['id_order'] ?? 0);
        $newOrderState = $params['newOrderStatus'] ?? null;

        if (!$idOrder || !$newOrderState) {
            return;
        }

        // Check if this status transition could indicate a restock (e.g. cancelled/refunded orders)
        $restockingStatuses = [
            (int) Configuration::get('PS_OS_CANCELED'),
            (int) Configuration::get('PS_OS_REFUND'),
            (int) Configuration::get('PS_OS_ERROR'),
        ];

        if (!in_array((int) $newOrderState->id, $restockingStatuses, true)) {
            return;
        }

        // Fetch order details to check which products may be restocked
        $orderDetails = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT od.`id_product`, od.`id_product_attribute`
             FROM `' . _DB_PREFIX_ . 'order_detail` od
             WHERE od.`id_order` = ' . $idOrder
        );

        if (!is_array($orderDetails)) {
            return;
        }

        foreach ($orderDetails as $row) {
            $idProduct          = (int) $row['id_product'];
            $idProductAttribute = (int) $row['id_product_attribute'];

            // Check actual current stock before sending
            $qty = StockAvailable::getQuantityAvailableByProduct($idProduct, $idProductAttribute ?: null);
            if ($qty > 0) {
                $this->sendRestockNotifications($idProduct, $idProductAttribute);
            }
        }
    }

    /**
     * displayAdminProductsExtra — show subscriber list in product admin tab.
     */
    public function hookDisplayAdminProductsExtra(array $params): string
    {
        $idProduct = (int) ($params['id_product'] ?? Tools::getValue('id_product'));
        if (!$idProduct) {
            return '';
        }

        $alerts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT a.`id_alert`, a.`email`, a.`firstname`,
                    a.`id_product_attribute`, a.`date_add`,
                    a.`notified`, a.`date_notified`
             FROM `' . _DB_PREFIX_ . 'phyto_restock_alert` a
             WHERE a.`id_product` = ' . $idProduct . '
             ORDER BY a.`date_add` DESC'
        );

        $this->context->smarty->assign([
            'phyto_restock_alerts'     => $alerts ?: [],
            'phyto_restock_id_product' => $idProduct,
            'phyto_restock_admin_url'  => $this->context->link->getAdminLink('AdminPhytoRestockAlert'),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    // -------------------------------------------------------------------------
    // Core notification logic
    // -------------------------------------------------------------------------

    /**
     * Send restock notifications to all unnotified subscribers for a product.
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     */
    public function sendRestockNotifications(int $idProduct, int $idProductAttribute = 0): void
    {
        $maxPerRun = (int) Configuration::get('PHYTO_RESTOCK_MAX_PER_RUN');
        if ($maxPerRun < 1) {
            $maxPerRun = 50;
        }

        $alerts = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT `id_alert`, `email`, `firstname`
             FROM `' . _DB_PREFIX_ . 'phyto_restock_alert`
             WHERE `id_product` = ' . $idProduct . '
               AND `id_product_attribute` = ' . $idProductAttribute . '
               AND `notified` = 0
             LIMIT ' . $maxPerRun
        );

        if (!is_array($alerts) || empty($alerts)) {
            return;
        }

        // Load product once
        $product = new Product($idProduct, false, (int) Configuration::get('PS_LANG_DEFAULT'));
        if (!Validate::isLoadedObject($product)) {
            return;
        }

        $productLink = $this->context->link->getProductLink(
            $product,
            null,
            null,
            null,
            (int) Configuration::get('PS_LANG_DEFAULT')
        );

        $fromName = Configuration::get('PHYTO_RESTOCK_FROM_NAME')
            ?: Configuration::get('PS_SHOP_NAME');
        $fromEmail = Configuration::get('PS_SHOP_EMAIL');

        $notifiedIds = [];

        foreach ($alerts as $alert) {
            $email     = $alert['email'];
            $firstname = $alert['firstname'] ?: '';
            $idAlert   = (int) $alert['id_alert'];

            $templateVars = [
                '{firstname}'    => htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8'),
                '{product_name}' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                '{product_link}' => $productLink,
                '{shop_name}'    => htmlspecialchars(Configuration::get('PS_SHOP_NAME'), ENT_QUOTES, 'UTF-8'),
                '{shop_url}'     => Tools::getShopDomainSsl(true),
            ];

            $sent = Mail::Send(
                (int) Configuration::get('PS_LANG_DEFAULT'),
                'restock_alert',
                Mail::l('Good news! ' . $product->name . ' is back in stock'),
                $templateVars,
                $email,
                $firstname ?: null,
                $fromEmail,
                $fromName,
                null,
                null,
                __DIR__ . '/views/templates/hook/email/',
                false,
                null,
                null
            );

            if ($sent) {
                $notifiedIds[] = $idAlert;
            }
        }

        if (!empty($notifiedIds)) {
            $ids = implode(',', array_map('intval', $notifiedIds));
            Db::getInstance()->execute(
                'UPDATE `' . _DB_PREFIX_ . 'phyto_restock_alert`
                 SET `notified` = 1,
                     `date_notified` = NOW()
                 WHERE `id_alert` IN (' . $ids . ')'
            );
        }
    }
}
