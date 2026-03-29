<?php
/**
 * phyto_subscription.php
 *
 * Main module class for Phyto Subscription.
 * Recurring mystery box and replenishment subscriptions via Cashfree.
 *
 * @author    PhytoCommerce
 * @version   1.0.0
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoSubscriptionPlan.php';
require_once __DIR__ . '/classes/PhytoSubscriptionCustomer.php';

class Phyto_Subscription extends Module
{
    public function __construct()
    {
        $this->name          = 'phyto_subscription';
        $this->tab           = 'AdminCatalog';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 1;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Subscription');
        $this->description = $this->l('Enables recurring subscription plans for mystery plant boxes and regular replenishment orders, processing payments through the Cashfree Subscriptions API. Admin creates plan templates with billing cycles and pricing; customers subscribe from their account and can view and manage active subscriptions. Supports both Sandbox and Production Cashfree environments and verifies webhook signatures for secure payment event handling. Designed for Indian plant stores that want to offer curated monthly deliveries in INR.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install()
    {
        if (
            !parent::install()
            || !$this->runSql('install')
            || !$this->registerHook('displayMyAccountBlock')
            || !$this->installTabs()
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (
            !$this->uninstallTabs()
            || !$this->runSql('uninstall')
            || !parent::uninstall()
        ) {
            return false;
        }

        // Clean up configuration values
        $configKeys = [
            'PHYTO_SUB_CF_CLIENT_ID',
            'PHYTO_SUB_CF_CLIENT_SECRET',
            'PHYTO_SUB_CF_API_VERSION',
            'PHYTO_SUB_CF_ENV',
            'PHYTO_SUB_CF_WEBHOOK_SECRET',
        ];
        foreach ($configKeys as $key) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // SQL helpers
    // -------------------------------------------------------------------------

    protected function runSql($file)
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
    // Tabs
    // -------------------------------------------------------------------------

    protected function installTabs()
    {
        // Tab: Plan Management under AdminCatalog
        if (!$this->createTab('AdminPhytoSubscription', 'Subscription Plans', 'AdminCatalog')) {
            return false;
        }

        // Tab: Subscriber List under AdminParentOrders
        if (!$this->createTab('AdminPhytoSubscriberList', 'Subscribers', 'AdminParentOrders')) {
            return false;
        }

        return true;
    }

    protected function createTab($className, $name, $parentClass)
    {
        $parentId = (int) Tab::getIdFromClassName($parentClass);

        $tab = new Tab();
        $tab->active      = 1;
        $tab->class_name  = $className;
        $tab->module      = $this->name;
        $tab->id_parent   = $parentId;
        $tab->icon        = '';

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l($name);
        }

        return $tab->add();
    }

    protected function uninstallTabs()
    {
        foreach (['AdminPhytoSubscription', 'AdminPhytoSubscriberList'] as $className) {
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

    // -------------------------------------------------------------------------
    // Configuration page
    // -------------------------------------------------------------------------

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoSubscriptionConfig')) {
            $output .= $this->postProcessConfig();
        }

        return $output . $this->renderConfigForm();
    }

    protected function postProcessConfig()
    {
        $fields = [
            'PHYTO_SUB_CF_CLIENT_ID'      => Tools::getValue('PHYTO_SUB_CF_CLIENT_ID'),
            'PHYTO_SUB_CF_CLIENT_SECRET'  => Tools::getValue('PHYTO_SUB_CF_CLIENT_SECRET'),
            'PHYTO_SUB_CF_API_VERSION'    => Tools::getValue('PHYTO_SUB_CF_API_VERSION') ?: '2023-08-01',
            'PHYTO_SUB_CF_ENV'            => Tools::getValue('PHYTO_SUB_CF_ENV'),
            'PHYTO_SUB_CF_WEBHOOK_SECRET' => Tools::getValue('PHYTO_SUB_CF_WEBHOOK_SECRET'),
        ];

        foreach ($fields as $key => $value) {
            Configuration::updateValue($key, $value);
        }

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    protected function renderConfigForm()
    {
        $fieldsForm = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Cashfree API Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->l('Client ID'),
                        'name'     => 'PHYTO_SUB_CF_CLIENT_ID',
                        'required' => true,
                        'desc'     => $this->l('Your Cashfree Client ID.'),
                    ],
                    [
                        'type'     => 'password',
                        'label'    => $this->l('Client Secret'),
                        'name'     => 'PHYTO_SUB_CF_CLIENT_SECRET',
                        'required' => true,
                        'desc'     => $this->l('Your Cashfree Client Secret.'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('API Version'),
                        'name'  => 'PHYTO_SUB_CF_API_VERSION',
                        'desc'  => $this->l('Cashfree API version header (e.g. 2023-08-01).'),
                    ],
                    [
                        'type'    => 'select',
                        'label'   => $this->l('Environment'),
                        'name'    => 'PHYTO_SUB_CF_ENV',
                        'options' => [
                            'query' => [
                                ['id' => 'Sandbox',    'name' => $this->l('Sandbox')],
                                ['id' => 'Production', 'name' => $this->l('Production')],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'     => 'password',
                        'label'    => $this->l('Webhook Secret'),
                        'name'     => 'PHYTO_SUB_CF_WEBHOOK_SECRET',
                        'required' => false,
                        'desc'     => $this->l('Used to verify Cashfree webhook signatures.'),
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
        $helper->submit_action            = 'submitPhytoSubscriptionConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => [
                'PHYTO_SUB_CF_CLIENT_ID'      => Configuration::get('PHYTO_SUB_CF_CLIENT_ID'),
                'PHYTO_SUB_CF_CLIENT_SECRET'  => Configuration::get('PHYTO_SUB_CF_CLIENT_SECRET'),
                'PHYTO_SUB_CF_API_VERSION'    => Configuration::get('PHYTO_SUB_CF_API_VERSION') ?: '2023-08-01',
                'PHYTO_SUB_CF_ENV'            => Configuration::get('PHYTO_SUB_CF_ENV') ?: 'Sandbox',
                'PHYTO_SUB_CF_WEBHOOK_SECRET' => Configuration::get('PHYTO_SUB_CF_WEBHOOK_SECRET'),
            ],
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$fieldsForm]);
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    public function hookDisplayMyAccountBlock($params)
    {
        $plansUrl = $this->context->link->getModuleLink('phyto_subscription', 'plans');
        $mySubUrl = $this->context->link->getModuleLink('phyto_subscription', 'mysubscriptions');

        $this->context->smarty->assign([
            'phyto_sub_plans_url'  => $plansUrl,
            'phyto_sub_mysub_url'  => $mySubUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/myaccount_block.tpl');
    }

    // -------------------------------------------------------------------------
    // Cashfree API helper
    // -------------------------------------------------------------------------

    public function cashfreeRequest($method, $endpoint, $data = null)
    {
        $env     = Configuration::get('PHYTO_SUB_CF_ENV', 'Sandbox');
        $baseUrl = ($env === 'Production')
            ? 'https://api.cashfree.com'
            : 'https://sandbox.cashfree.com';

        $url = $baseUrl . $endpoint;

        $headers = [
            'x-client-id: '     . Configuration::get('PHYTO_SUB_CF_CLIENT_ID'),
            'x-client-secret: ' . Configuration::get('PHYTO_SUB_CF_CLIENT_SECRET'),
            'x-api-version: '   . (Configuration::get('PHYTO_SUB_CF_API_VERSION') ?: '2023-08-01'),
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PATCH') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            PrestaShopLogger::addLog(
                '[PhytoSubscription] cURL error for ' . $method . ' ' . $endpoint . ': ' . $curlError,
                3,
                null,
                'PhytoSubscription',
                0,
                true
            );
            return ['code' => 0, 'body' => null, 'error' => $curlError];
        }

        return ['code' => $httpCode, 'body' => json_decode($response, true)];
    }
}
