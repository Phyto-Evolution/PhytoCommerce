<?php
/**
 * phyto_loyalty.php
 *
 * Points-based loyalty programme for PhytoCommerce.
 * Customers earn points on purchases and redeem them as cart discounts.
 *
 * @author    PhytoCommerce
 * @version   1.0.0
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoLoyaltyAccount.php';
require_once __DIR__ . '/classes/PhytoLoyaltyTransaction.php';

class Phyto_Loyalty extends Module
{
    // -------------------------------------------------------------------------
    // Constructor
    // -------------------------------------------------------------------------

    public function __construct()
    {
        $this->name          = 'phyto_loyalty';
        $this->tab           = 'AdminPhytoLoyalty';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 1;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Loyalty');
        $this->description = $this->l('Points-based loyalty programme. Customers earn points on purchases and redeem them as cart discounts.');
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
            || !$this->registerHook('actionOrderStatusPostUpdate')
            || !$this->registerHook('displayCustomerAccount')
            || !$this->registerHook('displayMyAccountBlock')
            || !$this->registerHook('displayShoppingCartFooter')
            || !$this->registerHook('displayHeader')
            || !$this->registerHook('displayAdminCustomersForm')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        $this->uninstallTab();
        $this->runSql('uninstall');

        $configKeys = [
            'PHYTO_LOYALTY_EARN_RATE',
            'PHYTO_LOYALTY_REDEEM_RATE',
            'PHYTO_LOYALTY_MIN_REDEEM',
            'PHYTO_LOYALTY_MAX_REDEEM_PCT',
            'PHYTO_LOYALTY_EXPIRY_DAYS',
            'PHYTO_LOYALTY_ENABLED',
        ];
        foreach ($configKeys as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helpers
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
    // Config defaults
    // -------------------------------------------------------------------------

    protected function installDefaultConfig(): bool
    {
        $defaults = [
            'PHYTO_LOYALTY_EARN_RATE'       => '0.1',   // 1 point per ₹10
            'PHYTO_LOYALTY_REDEEM_RATE'     => '0.50',  // ₹0.50 per point
            'PHYTO_LOYALTY_MIN_REDEEM'      => '100',
            'PHYTO_LOYALTY_MAX_REDEEM_PCT'  => '20',
            'PHYTO_LOYALTY_EXPIRY_DAYS'     => '365',
            'PHYTO_LOYALTY_ENABLED'         => '1',
        ];

        foreach ($defaults as $key => $value) {
            if (Configuration::get($key) === false) {
                if (!Configuration::updateValue($key, $value)) {
                    return false;
                }
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab
    // -------------------------------------------------------------------------

    protected function installTab(): bool
    {
        $parentId = (int) Tab::getIdFromClassName('AdminParentOrders');

        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoLoyalty';
        $tab->module     = $this->name;
        $tab->id_parent  = $parentId;
        $tab->icon       = 'loyalty';

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Phyto Loyalty');
        }

        return $tab->add();
    }

    protected function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoLoyalty');
        if ($idTab) {
            $tab = new Tab($idTab);
            $tab->delete();
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Configuration page
    // -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoLoyaltyConfig')) {
            $output .= $this->postProcessConfig();
        }

        return $output . $this->renderConfigForm();
    }

    protected function postProcessConfig(): string
    {
        $fields = [
            'PHYTO_LOYALTY_EARN_RATE'      => ['isFloat',        $this->l('Earn rate must be a positive decimal.')],
            'PHYTO_LOYALTY_REDEEM_RATE'    => ['isFloat',        $this->l('Redeem rate must be a positive decimal.')],
            'PHYTO_LOYALTY_MIN_REDEEM'     => ['isUnsignedInt',  $this->l('Minimum redeem must be a positive integer.')],
            'PHYTO_LOYALTY_MAX_REDEEM_PCT' => ['isUnsignedInt',  $this->l('Max redeem % must be an integer 1–100.')],
            'PHYTO_LOYALTY_EXPIRY_DAYS'    => ['isUnsignedInt',  $this->l('Expiry days must be a non-negative integer.')],
        ];

        $errors = [];
        foreach ($fields as $key => [$validator, $message]) {
            $value = Tools::getValue($key);
            if (!Validate::$validator($value)) {
                $errors[] = $message;
            } else {
                Configuration::updateValue($key, (string) $value);
            }
        }

        // Boolean toggle
        Configuration::updateValue('PHYTO_LOYALTY_ENABLED', (int) Tools::getValue('PHYTO_LOYALTY_ENABLED'));

        if ($errors) {
            return $this->displayError(implode('<br>', $errors));
        }

        return $this->displayConfirmation($this->l('Settings saved.'));
    }

    protected function renderConfigForm(): string
    {
        $fieldsForm = [
            [
                'form' => [
                    'legend' => [
                        'title' => $this->l('Loyalty Programme Settings'),
                        'icon'  => 'icon-cog',
                    ],
                    'input' => [
                        [
                            'type'  => 'switch',
                            'label' => $this->l('Enable Loyalty Programme'),
                            'name'  => 'PHYTO_LOYALTY_ENABLED',
                            'is_bool' => true,
                            'values' => [
                                ['id' => 'enabled_on',  'value' => 1, 'label' => $this->l('Enabled')],
                                ['id' => 'enabled_off', 'value' => 0, 'label' => $this->l('Disabled')],
                            ],
                        ],
                        [
                            'type'  => 'text',
                            'label' => $this->l('Earn Rate (points per ₹1 spent)'),
                            'name'  => 'PHYTO_LOYALTY_EARN_RATE',
                            'desc'  => $this->l('Default 0.1 = 1 point per ₹10. Set to 1 for 1 point per ₹1.'),
                        ],
                        [
                            'type'  => 'text',
                            'label' => $this->l('Redeem Rate (₹ value per point)'),
                            'name'  => 'PHYTO_LOYALTY_REDEEM_RATE',
                            'desc'  => $this->l('Default 0.50 = ₹0.50 discount per point redeemed.'),
                        ],
                        [
                            'type'  => 'text',
                            'label' => $this->l('Minimum Points to Redeem'),
                            'name'  => 'PHYTO_LOYALTY_MIN_REDEEM',
                            'desc'  => $this->l('Customer must redeem at least this many points in one transaction.'),
                        ],
                        [
                            'type'  => 'text',
                            'label' => $this->l('Max Discount as % of Order Value'),
                            'name'  => 'PHYTO_LOYALTY_MAX_REDEEM_PCT',
                            'desc'  => $this->l('Points redemption cannot exceed this percentage of the cart total.'),
                        ],
                        [
                            'type'  => 'text',
                            'label' => $this->l('Points Expiry (days of inactivity)'),
                            'name'  => 'PHYTO_LOYALTY_EXPIRY_DAYS',
                            'desc'  => $this->l('Set to 0 to disable expiry. Points expire after this many days with no activity.'),
                        ],
                    ],
                    'submit' => [
                        'title' => $this->l('Save'),
                    ],
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
        $helper->submit_action            = 'submitPhytoLoyaltyConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [
                'PHYTO_LOYALTY_ENABLED'         => (int) Configuration::get('PHYTO_LOYALTY_ENABLED'),
                'PHYTO_LOYALTY_EARN_RATE'        => Configuration::get('PHYTO_LOYALTY_EARN_RATE'),
                'PHYTO_LOYALTY_REDEEM_RATE'      => Configuration::get('PHYTO_LOYALTY_REDEEM_RATE'),
                'PHYTO_LOYALTY_MIN_REDEEM'       => Configuration::get('PHYTO_LOYALTY_MIN_REDEEM'),
                'PHYTO_LOYALTY_MAX_REDEEM_PCT'   => Configuration::get('PHYTO_LOYALTY_MAX_REDEEM_PCT'),
                'PHYTO_LOYALTY_EXPIRY_DAYS'      => Configuration::get('PHYTO_LOYALTY_EXPIRY_DAYS'),
            ],
            'languages'   => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm($fieldsForm);
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    /**
     * Earn points on order complete; reverse on cancel/refund.
     */
    public function hookActionOrderStatusPostUpdate(array $params): void
    {
        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            return;
        }

        /** @var OrderState $newOrderStatus */
        $newOrderStatus = $params['newOrderStatus'];
        /** @var Order $order */
        $order = new Order((int) $params['id_order']);

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $idCustomer = (int) $order->id_customer;

        // Order complete states (PS default: 5 = Delivered)
        $completeStates = [
            (int) Configuration::get('PS_OS_DELIVERED'),
            (int) Configuration::get('PS_OS_PAYMENT'),
        ];

        // Cancel/refund states
        $reverseStates = [
            (int) Configuration::get('PS_OS_CANCELED'),
            (int) Configuration::get('PS_OS_REFUND'),
            (int) Configuration::get('PS_OS_ERROR'),
        ];

        $stateId = (int) $newOrderStatus->id;

        if (in_array($stateId, $completeStates, true)) {
            $this->creditOrderPoints($order, $idCustomer);
        } elseif (in_array($stateId, $reverseStates, true)) {
            $this->reverseOrderPoints($order, $idCustomer);
        }
    }

    /**
     * "My Points" link on customer account page.
     */
    public function hookDisplayCustomerAccount(array $params): string
    {
        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            return '';
        }

        $accountUrl = $this->context->link->getModuleLink('phyto_loyalty', 'account');
        $this->context->smarty->assign('phyto_loyalty_account_url', $accountUrl);

        return $this->display(__FILE__, 'views/templates/hook/account_link.tpl');
    }

    /**
     * Points balance widget in account sidebar.
     */
    public function hookDisplayMyAccountBlock(array $params): string
    {
        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            return '';
        }

        $idCustomer = (int) $this->context->customer->id;
        if (!$idCustomer) {
            return '';
        }

        $account    = PhytoLoyaltyAccount::getByCustomer($idCustomer);
        $balance    = $account ? (int) $account->points_balance : 0;
        $tier       = $account ? $account->tier : 'seed';
        $accountUrl = $this->context->link->getModuleLink('phyto_loyalty', 'account');

        $this->context->smarty->assign([
            'phyto_loyalty_balance'     => $balance,
            'phyto_loyalty_tier'        => $tier,
            'phyto_loyalty_account_url' => $accountUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/account_block.tpl');
    }

    /**
     * Redeem points widget in shopping cart footer.
     */
    public function hookDisplayShoppingCartFooter(array $params): string
    {
        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            return '';
        }

        $idCustomer = (int) $this->context->customer->id;
        if (!$idCustomer) {
            return '';
        }

        $account = PhytoLoyaltyAccount::getByCustomer($idCustomer);
        if (!$account) {
            return '';
        }

        // Expire stale points before showing widget
        $expiryDays = (int) Configuration::get('PHYTO_LOYALTY_EXPIRY_DAYS');
        $account->expireStalePoints($expiryDays);

        $balance        = (int) $account->points_balance;
        $redeemRate     = (float) Configuration::get('PHYTO_LOYALTY_REDEEM_RATE');
        $minRedeem      = (int) Configuration::get('PHYTO_LOYALTY_MIN_REDEEM');
        $maxRedeemPct   = (int) Configuration::get('PHYTO_LOYALTY_MAX_REDEEM_PCT');
        $cartTotal      = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $maxByCart      = ($maxRedeemPct / 100) * $cartTotal;
        $maxPoints      = ($redeemRate > 0) ? (int) floor($maxByCart / $redeemRate) : 0;
        $maxRedeemable  = min($balance, $maxPoints);
        $redeemUrl      = $this->context->link->getModuleLink('phyto_loyalty', 'redeem');

        $this->context->smarty->assign([
            'phyto_loyalty_balance'      => $balance,
            'phyto_loyalty_min_redeem'   => $minRedeem,
            'phyto_loyalty_max_redeem'   => $maxRedeemable,
            'phyto_loyalty_redeem_rate'  => $redeemRate,
            'phyto_loyalty_redeem_url'   => $redeemUrl,
            'phyto_loyalty_can_redeem'   => ($balance >= $minRedeem && $maxRedeemable >= $minRedeem),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/cart_widget.tpl');
    }

    /**
     * Inject CSS/JS in front-end header.
     */
    public function hookDisplayHeader(array $params): void
    {
        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            return;
        }

        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
        $this->context->controller->addJS($this->_path . 'views/js/front.js');
    }

    /**
     * Show loyalty account info in customer admin page.
     */
    public function hookDisplayAdminCustomersForm(array $params): string
    {
        $idCustomer = (int) ($params['id_customer'] ?? 0);
        if (!$idCustomer) {
            return '';
        }

        $account      = PhytoLoyaltyAccount::getByCustomer($idCustomer);
        $transactions = $account
            ? PhytoLoyaltyTransaction::getForCustomer($idCustomer, 1, 10)
            : [];
        $adjustUrl    = $this->context->link->getAdminLink('AdminPhytoLoyalty')
            . '&action=adjustPoints&id_customer=' . $idCustomer;

        $this->context->smarty->assign([
            'phyto_loyalty_account'      => $account,
            'phyto_loyalty_transactions' => $transactions,
            'phyto_loyalty_adjust_url'   => $adjustUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/customer_block.tpl');
    }

    // -------------------------------------------------------------------------
    // Points engine (atomic)
    // -------------------------------------------------------------------------

    /**
     * Credit earned points for a completed order.
     */
    public function creditOrderPoints(Order $order, int $idCustomer): void
    {
        // Prevent double-credit
        $alreadyEarned = PhytoLoyaltyTransaction::getEarnedForOrder((int) $order->id, $idCustomer);
        if ($alreadyEarned > 0) {
            return;
        }

        $earnRate = (float) Configuration::get('PHYTO_LOYALTY_EARN_RATE');
        $total    = (float) $order->total_paid_real;
        $points   = (int) floor($total * $earnRate);

        if ($points <= 0) {
            return;
        }

        Db::getInstance()->execute('START TRANSACTION');

        try {
            $account = PhytoLoyaltyAccount::getOrCreate($idCustomer);

            $newBalance          = (int) $account->points_balance + $points;
            $newLifetime         = (int) $account->points_lifetime + $points;
            $account->points_balance  = $newBalance;
            $account->points_lifetime = $newLifetime;
            $account->recalculateTier();
            $account->date_upd   = date('Y-m-d H:i:s');
            $account->update();

            PhytoLoyaltyTransaction::record(
                $idCustomer,
                (int) $order->id,
                'earn',
                $points,
                $newBalance,
                'Earned for order #' . (int) $order->id
            );

            Db::getInstance()->execute('COMMIT');

            $this->sendPointsEarnedEmail($order, $account, $points);
        } catch (\Throwable $e) {
            Db::getInstance()->execute('ROLLBACK');
            PrestaShopLogger::addLog(
                '[PhytoLoyalty] creditOrderPoints failed: ' . $e->getMessage(),
                3, null, 'PhytoLoyalty', (int) $order->id, true
            );
        }
    }

    /**
     * Reverse earned points for a cancelled/refunded order.
     */
    public function reverseOrderPoints(Order $order, int $idCustomer): void
    {
        $earned = PhytoLoyaltyTransaction::getEarnedForOrder((int) $order->id, $idCustomer);
        if ($earned <= 0) {
            return;
        }

        Db::getInstance()->execute('START TRANSACTION');

        try {
            $account = PhytoLoyaltyAccount::getOrCreate($idCustomer);

            $deduct             = min($earned, (int) $account->points_balance);
            $newBalance         = (int) $account->points_balance - $deduct;
            $account->points_balance = max(0, $newBalance);
            $account->recalculateTier();
            $account->date_upd  = date('Y-m-d H:i:s');
            $account->update();

            PhytoLoyaltyTransaction::record(
                $idCustomer,
                (int) $order->id,
                'refund',
                -$deduct,
                (int) $account->points_balance,
                'Reversed for cancelled/refunded order #' . (int) $order->id
            );

            Db::getInstance()->execute('COMMIT');
        } catch (\Throwable $e) {
            Db::getInstance()->execute('ROLLBACK');
            PrestaShopLogger::addLog(
                '[PhytoLoyalty] reverseOrderPoints failed: ' . $e->getMessage(),
                3, null, 'PhytoLoyalty', (int) $order->id, true
            );
        }
    }

    /**
     * Admin manual point adjustment (add/deduct).
     */
    public function adjustPoints(int $idCustomer, int $points, string $note): bool
    {
        if ($points === 0) {
            return false;
        }

        Db::getInstance()->execute('START TRANSACTION');

        try {
            $account    = PhytoLoyaltyAccount::getOrCreate($idCustomer);
            $newBalance = max(0, (int) $account->points_balance + $points);

            if ($points > 0) {
                $account->points_lifetime = (int) $account->points_lifetime + $points;
            }

            $account->points_balance = $newBalance;
            $account->recalculateTier();
            $account->date_upd = date('Y-m-d H:i:s');
            $account->update();

            PhytoLoyaltyTransaction::record(
                $idCustomer,
                0,
                'adjust',
                $points,
                $newBalance,
                $note ?: 'Manual adjustment'
            );

            Db::getInstance()->execute('COMMIT');

            return true;
        } catch (\Throwable $e) {
            Db::getInstance()->execute('ROLLBACK');
            PrestaShopLogger::addLog(
                '[PhytoLoyalty] adjustPoints failed: ' . $e->getMessage(),
                3, null, 'PhytoLoyalty', $idCustomer, true
            );

            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Email
    // -------------------------------------------------------------------------

    protected function sendPointsEarnedEmail(Order $order, PhytoLoyaltyAccount $account, int $points): void
    {
        try {
            $customer  = new Customer((int) $order->id_customer);
            if (!Validate::isLoadedObject($customer)) {
                return;
            }

            $nextTier  = $account->nextTierInfo();
            $tierLabel = ucfirst($account->tier);

            Mail::Send(
                (int) $this->context->language->id,
                'points_earned',
                $this->l('You earned loyalty points!'),
                [
                    '{firstname}'        => $customer->firstname,
                    '{lastname}'         => $customer->lastname,
                    '{order_reference}'  => $order->reference,
                    '{points_earned}'    => $points,
                    '{balance}'          => (int) $account->points_balance,
                    '{tier}'             => $tierLabel,
                    '{next_tier}'        => $nextTier['next_tier'] ? ucfirst($nextTier['next_tier']) : $this->l('Maximum tier reached'),
                    '{points_to_next}'   => $nextTier['points_needed'],
                    '{account_url}'      => $this->context->link->getModuleLink('phyto_loyalty', 'account'),
                ],
                $customer->email,
                $customer->firstname . ' ' . $customer->lastname,
                null,
                null,
                null,
                null,
                __DIR__ . '/views/templates/hook/email/'
            );
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                '[PhytoLoyalty] sendPointsEarnedEmail failed: ' . $e->getMessage(),
                2, null, 'PhytoLoyalty', (int) $order->id, true
            );
        }
    }
}
