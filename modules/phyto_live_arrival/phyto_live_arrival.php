<?php
/**
 * Phyto Live Arrival Guarantee Module
 *
 * Live Arrival Guarantee opt-in at checkout. Controls shipping window
 * (configurable allowed days), adds fee or shows free LAG threshold,
 * and generates LAG claim forms for customers.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   Commercial
 * @version   1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Live_Arrival extends Module
{
    /** @var array Default configuration values */
    private $defaultConfig = [
        'PHYTO_LAG_FEE'             => '9.99',
        'PHYTO_LAG_FREE_ABOVE'      => '200.00',
        'PHYTO_LAG_SHIP_DAYS'       => '1,2,3',  // Mon=1, Tue=2, Wed=3
        'PHYTO_LAG_HOLIDAYS'        => '',
        'PHYTO_LAG_CLAIM_WINDOW'    => '2',
        'PHYTO_LAG_TERMS'           => 'By opting in to the Live Arrival Guarantee, you agree that shipments are only dispatched on approved shipping days. If your live goods arrive deceased or in critical condition, you may file a claim within the specified window.',
        'PHYTO_LAG_CLAIM_INSTR'     => 'Please provide clear photos of the packaging and the specimens. Claims must be filed within the allowed claim window from the date of delivery.',
        'PHYTO_LAG_NOTIFY_EMAIL'    => '',
    ];

    public function __construct()
    {
        $this->name = 'phyto_live_arrival';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Live Arrival Guarantee');
        $this->description = $this->l('Live Arrival Guarantee opt-in at checkout with configurable shipping windows, fee management, and claim forms.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the Live Arrival Guarantee module? All LAG data will be removed.');
    }

    /**
     * Module installation
     */
    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('displayBeforeCarrier')
            || !$this->registerHook('displayOrderDetail')
            || !$this->registerHook('actionValidateOrder')
            || !$this->registerHook('displayHeader')
            || !$this->executeSqlFile('install')
            || !$this->installDefaultConfig()
        ) {
            return false;
        }

        // Create upload directory for claim photos
        $uploadDir = _PS_IMG_DIR_ . 'phyto_lag/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
            @file_put_contents($uploadDir . 'index.php', '<?php header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");header("Cache-Control: no-store, no-cache, must-revalidate");header("Cache-Control: post-check=0, pre-check=0", false);header("Pragma: no-cache");header("Location: ../");exit;');
        }

        return true;
    }

    /**
     * Module uninstallation
     */
    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->executeSqlFile('uninstall')
            || !$this->removeConfig()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Execute SQL file (install or uninstall)
     *
     * @param string $type 'install' or 'uninstall'
     * @return bool
     */
    private function executeSqlFile($type)
    {
        $filePath = dirname(__FILE__) . '/sql/' . $type . '.sql';
        if (!file_exists($filePath)) {
            return false;
        }

        $sql = file_get_contents($filePath);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        $queries = preg_split('/;\s*[\r\n]+/', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                if (!Db::getInstance()->execute($query)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Install default configuration values
     */
    private function installDefaultConfig()
    {
        foreach ($this->defaultConfig as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        // Default notify email to shop email
        if (empty(Configuration::get('PHYTO_LAG_NOTIFY_EMAIL'))) {
            Configuration::updateValue('PHYTO_LAG_NOTIFY_EMAIL', Configuration::get('PS_SHOP_EMAIL'));
        }

        return true;
    }

    /**
     * Remove configuration on uninstall
     */
    private function removeConfig()
    {
        foreach (array_keys($this->defaultConfig) as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    // =========================================================================
    // Back Office Configuration
    // =========================================================================

    /**
     * Module configuration page
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoLagConfig')) {
            $output .= $this->processConfiguration();
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Process configuration form submission
     */
    private function processConfiguration()
    {
        $errors = [];

        $fee = Tools::getValue('PHYTO_LAG_FEE');
        if (!Validate::isPrice($fee)) {
            $errors[] = $this->l('Invalid LAG fee value.');
        }

        $freeAbove = Tools::getValue('PHYTO_LAG_FREE_ABOVE');
        if (!Validate::isPrice($freeAbove)) {
            $errors[] = $this->l('Invalid free LAG threshold value.');
        }

        $claimWindow = (int) Tools::getValue('PHYTO_LAG_CLAIM_WINDOW');
        if ($claimWindow < 1 || $claimWindow > 30) {
            $errors[] = $this->l('Claim window must be between 1 and 30 days.');
        }

        $notifyEmail = Tools::getValue('PHYTO_LAG_NOTIFY_EMAIL');
        if (!empty($notifyEmail) && !Validate::isEmail($notifyEmail)) {
            $errors[] = $this->l('Invalid notification email address.');
        }

        // Validate holiday dates
        $holidays = trim(Tools::getValue('PHYTO_LAG_HOLIDAYS'));
        if (!empty($holidays)) {
            $lines = preg_split('/\r?\n/', $holidays);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $line)) {
                    $errors[] = $this->l('Invalid holiday date format. Use YYYY-MM-DD, one per line.');
                    break;
                }
            }
        }

        // Allowed ship days
        $shipDays = Tools::getValue('PHYTO_LAG_SHIP_DAYS');
        if (empty($shipDays) || !is_array($shipDays)) {
            $errors[] = $this->l('Please select at least one allowed shipping day.');
        }

        if (!empty($errors)) {
            return $this->displayError(implode('<br>', $errors));
        }

        Configuration::updateValue('PHYTO_LAG_FEE', (float) $fee);
        Configuration::updateValue('PHYTO_LAG_FREE_ABOVE', (float) $freeAbove);
        Configuration::updateValue('PHYTO_LAG_SHIP_DAYS', implode(',', array_map('intval', $shipDays)));
        Configuration::updateValue('PHYTO_LAG_HOLIDAYS', $holidays);
        Configuration::updateValue('PHYTO_LAG_CLAIM_WINDOW', $claimWindow);
        Configuration::updateValue('PHYTO_LAG_TERMS', Tools::getValue('PHYTO_LAG_TERMS'), true);
        Configuration::updateValue('PHYTO_LAG_CLAIM_INSTR', Tools::getValue('PHYTO_LAG_CLAIM_INSTR'), true);
        Configuration::updateValue('PHYTO_LAG_NOTIFY_EMAIL', $notifyEmail);

        return $this->displayConfirmation($this->l('Settings updated successfully.'));
    }

    /**
     * Render the back office configuration form
     */
    private function renderConfigForm()
    {
        $dayOptions = [
            ['id' => 1, 'name' => $this->l('Monday')],
            ['id' => 2, 'name' => $this->l('Tuesday')],
            ['id' => 3, 'name' => $this->l('Wednesday')],
            ['id' => 4, 'name' => $this->l('Thursday')],
            ['id' => 5, 'name' => $this->l('Friday')],
            ['id' => 6, 'name' => $this->l('Saturday')],
            ['id' => 7, 'name' => $this->l('Sunday')],
        ];

        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Live Arrival Guarantee Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->l('LAG Fee'),
                        'name'     => 'PHYTO_LAG_FEE',
                        'desc'     => $this->l('Fee charged for Live Arrival Guarantee. Set to 0 to make it free for all orders.'),
                        'suffix'   => Context::getContext()->currency->sign,
                        'class'    => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->l('Free LAG Above Cart Total'),
                        'name'     => 'PHYTO_LAG_FREE_ABOVE',
                        'desc'     => $this->l('If the cart total exceeds this amount, LAG is free. Set to 0 to disable threshold (fee always applies unless fee itself is 0).'),
                        'suffix'   => Context::getContext()->currency->sign,
                        'class'    => 'fixed-width-sm',
                        'required' => true,
                    ],
                    [
                        'type'   => 'checkbox',
                        'label'  => $this->l('Allowed Shipping Days'),
                        'name'   => 'PHYTO_LAG_SHIP_DAYS',
                        'desc'   => $this->l('Select days when live shipments may be dispatched.'),
                        'values' => [
                            'query' => $dayOptions,
                            'id'    => 'id',
                            'name'  => 'name',
                        ],
                    ],
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Holiday Blackout Dates'),
                        'name'  => 'PHYTO_LAG_HOLIDAYS',
                        'desc'  => $this->l('One date per line in YYYY-MM-DD format. No shipments on these dates.'),
                        'rows'  => 6,
                        'cols'  => 40,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->l('LAG Claim Window (days)'),
                        'name'     => 'PHYTO_LAG_CLAIM_WINDOW',
                        'desc'     => $this->l('Number of days after delivery within which the customer can file a LAG claim.'),
                        'class'    => 'fixed-width-xs',
                        'required' => true,
                    ],
                    [
                        'type'     => 'textarea',
                        'label'    => $this->l('LAG Terms Text'),
                        'name'     => 'PHYTO_LAG_TERMS',
                        'desc'     => $this->l('Terms displayed to the customer at checkout when opting in to LAG.'),
                        'rows'     => 5,
                        'cols'     => 60,
                        'autoload_rte' => false,
                    ],
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Claim Form Instructions'),
                        'name'  => 'PHYTO_LAG_CLAIM_INSTR',
                        'desc'  => $this->l('Instructions displayed on the claim form page.'),
                        'rows'  => 4,
                        'cols'  => 60,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->l('Notification Email'),
                        'name'     => 'PHYTO_LAG_NOTIFY_EMAIL',
                        'desc'     => $this->l('Email address to receive claim notifications. Defaults to shop email.'),
                        'class'    => 'fixed-width-xl',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitPhytoLagConfig';

        // Load current values
        $helper->fields_value['PHYTO_LAG_FEE'] = Configuration::get('PHYTO_LAG_FEE');
        $helper->fields_value['PHYTO_LAG_FREE_ABOVE'] = Configuration::get('PHYTO_LAG_FREE_ABOVE');
        $helper->fields_value['PHYTO_LAG_HOLIDAYS'] = Configuration::get('PHYTO_LAG_HOLIDAYS');
        $helper->fields_value['PHYTO_LAG_CLAIM_WINDOW'] = Configuration::get('PHYTO_LAG_CLAIM_WINDOW');
        $helper->fields_value['PHYTO_LAG_TERMS'] = Configuration::get('PHYTO_LAG_TERMS');
        $helper->fields_value['PHYTO_LAG_CLAIM_INSTR'] = Configuration::get('PHYTO_LAG_CLAIM_INSTR');
        $helper->fields_value['PHYTO_LAG_NOTIFY_EMAIL'] = Configuration::get('PHYTO_LAG_NOTIFY_EMAIL');

        // Checkbox values for shipping days
        $selectedDays = explode(',', Configuration::get('PHYTO_LAG_SHIP_DAYS'));
        foreach ($dayOptions as $day) {
            $helper->fields_value['PHYTO_LAG_SHIP_DAYS_' . $day['id']] = in_array($day['id'], $selectedDays);
        }

        return $helper->generateForm([$fields]);
    }

    // =========================================================================
    // Front Office Hooks
    // =========================================================================

    /**
     * Add CSS/JS to front office header
     */
    public function hookDisplayHeader($params)
    {
        $controller = Tools::getValue('controller');
        if ($controller === 'order' || $controller === 'order-opc') {
            $this->context->controller->registerStylesheet(
                'phyto-lag-front-css',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 200]
            );
            $this->context->controller->registerJavascript(
                'phyto-lag-checkout-js',
                'modules/' . $this->name . '/views/js/lag_checkout.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }

        // Also load CSS on order detail and claim pages
        if ($controller === 'order-detail' || $controller === 'claim') {
            $this->context->controller->registerStylesheet(
                'phyto-lag-front-css',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 200]
            );
        }
    }

    /**
     * Display LAG opt-in widget at checkout (before carrier selection)
     */
    public function hookDisplayBeforeCarrier($params)
    {
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            return '';
        }

        $cartTotal = (float) $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
        $fee = (float) Configuration::get('PHYTO_LAG_FEE');
        $freeAbove = (float) Configuration::get('PHYTO_LAG_FREE_ABOVE');
        $terms = Configuration::get('PHYTO_LAG_TERMS');

        // Determine if LAG is free for this order
        $lagIsFree = ($fee == 0) || ($freeAbove > 0 && $cartTotal >= $freeAbove);
        $effectiveFee = $lagIsFree ? 0.00 : $fee;

        // Shipping day logic
        $allowedDays = $this->getAllowedShipDays();
        $holidays = $this->getHolidayDates();
        $today = new DateTime();
        $todayDow = (int) $today->format('N'); // 1=Mon ... 7=Sun
        $todayStr = $today->format('Y-m-d');

        $todayIsAllowed = in_array($todayDow, $allowedDays) && !in_array($todayStr, $holidays);
        $nextShipDate = $this->getNextValidShipDate($allowedDays, $holidays);

        // Check session for existing opt-in
        $lagOptedIn = false;
        if (isset($this->context->cookie->phyto_lag_opted)) {
            $lagOptedIn = (bool) $this->context->cookie->phyto_lag_opted;
        }

        $this->context->smarty->assign([
            'phyto_lag_fee'            => $effectiveFee,
            'phyto_lag_fee_formatted'  => Tools::displayPrice($effectiveFee),
            'phyto_lag_is_free'        => $lagIsFree,
            'phyto_lag_free_above'     => $freeAbove,
            'phyto_lag_terms'          => $terms,
            'phyto_lag_today_allowed'  => $todayIsAllowed,
            'phyto_lag_next_ship_date' => $nextShipDate,
            'phyto_lag_opted_in'       => $lagOptedIn,
            'phyto_lag_currency_sign'  => $this->context->currency->sign,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/checkout_lag.tpl');
    }

    /**
     * Display LAG status and claim button on order detail page
     */
    public function hookDisplayOrderDetail($params)
    {
        if (!isset($params['order'])) {
            return '';
        }

        $order = $params['order'];
        $idOrder = (int) $order->id;

        // Get LAG record for this order
        $lagRecord = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_lag_order`
             WHERE `id_order` = ' . $idOrder
        );

        if (!$lagRecord || !(int) $lagRecord['lag_opted']) {
            return '';
        }

        // Check if within claim window
        $claimWindow = (int) Configuration::get('PHYTO_LAG_CLAIM_WINDOW');
        $orderDate = new DateTime($order->date_add);
        $now = new DateTime();
        $daysSinceOrder = (int) $now->diff($orderDate)->days;
        $canClaim = ($daysSinceOrder <= $claimWindow);

        // Check if a claim already exists
        $existingClaim = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_lag_claim`
             WHERE `id_order` = ' . $idOrder
        );

        $claimUrl = $this->context->link->getModuleLink(
            $this->name,
            'claim',
            ['id_order' => $idOrder]
        );

        $this->context->smarty->assign([
            'phyto_lag_opted'          => true,
            'phyto_lag_fee_charged'    => Tools::displayPrice((float) $lagRecord['fee_charged']),
            'phyto_lag_can_claim'      => $canClaim,
            'phyto_lag_claim_window'   => $claimWindow,
            'phyto_lag_claim_url'      => $claimUrl,
            'phyto_lag_existing_claim' => $existingClaim,
            'phyto_lag_claim_status'   => $existingClaim ? $existingClaim['claim_status'] : '',
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order_detail.tpl');
    }

    /**
     * Save LAG opt-in when order is validated
     */
    public function hookActionValidateOrder($params)
    {
        if (!isset($params['order'])) {
            return;
        }

        $order = $params['order'];
        $lagOpted = 0;
        $feeCharged = 0.00;

        if (isset($this->context->cookie->phyto_lag_opted) && $this->context->cookie->phyto_lag_opted) {
            $lagOpted = 1;

            $cart = new Cart((int) $order->id_cart);
            $cartTotal = (float) $cart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING);
            $fee = (float) Configuration::get('PHYTO_LAG_FEE');
            $freeAbove = (float) Configuration::get('PHYTO_LAG_FREE_ABOVE');

            $lagIsFree = ($fee == 0) || ($freeAbove > 0 && $cartTotal >= $freeAbove);
            $feeCharged = $lagIsFree ? 0.00 : $fee;
        }

        Db::getInstance()->insert('phyto_lag_order', [
            'id_order'    => (int) $order->id,
            'lag_opted'   => $lagOpted,
            'fee_charged' => (float) $feeCharged,
            'date_add'    => date('Y-m-d H:i:s'),
        ]);

        // Clear LAG cookie after order is placed
        unset($this->context->cookie->phyto_lag_opted);
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Get allowed shipping days as array of ISO day numbers (1=Mon .. 7=Sun)
     *
     * @return array
     */
    public function getAllowedShipDays()
    {
        $raw = Configuration::get('PHYTO_LAG_SHIP_DAYS');
        if (empty($raw)) {
            return [1, 2, 3]; // Default Mon-Wed
        }

        return array_map('intval', explode(',', $raw));
    }

    /**
     * Get holiday blackout dates as flat array of 'Y-m-d' strings
     *
     * @return array
     */
    public function getHolidayDates()
    {
        $raw = Configuration::get('PHYTO_LAG_HOLIDAYS');
        if (empty($raw)) {
            return [];
        }

        $dates = [];
        $lines = preg_split('/\r?\n/', $raw);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $line)) {
                $dates[] = $line;
            }
        }

        return $dates;
    }

    /**
     * Get the next valid shipping date from today
     *
     * @param array $allowedDays
     * @param array $holidays
     * @return string Formatted date string
     */
    public function getNextValidShipDate(array $allowedDays, array $holidays)
    {
        $date = new DateTime();

        // Look up to 30 days ahead
        for ($i = 0; $i < 30; $i++) {
            $dow = (int) $date->format('N');
            $dateStr = $date->format('Y-m-d');

            if (in_array($dow, $allowedDays) && !in_array($dateStr, $holidays)) {
                return $date->format('l, F j, Y');
            }

            $date->modify('+1 day');
        }

        return $this->l('No valid shipping date found within 30 days');
    }
}
