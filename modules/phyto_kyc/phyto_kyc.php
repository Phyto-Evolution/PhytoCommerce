<?php
/**
 * Phyto KYC
 *
 * Customer identity verification module.
 * When enabled, blurs/freezes all prices and sensitive content for
 * customers who have not completed KYC. When disabled, everything is
 * visible to all.
 *
 * Level 1 — PAN verification (retail customers)
 * Level 2 — GST / business verification (B2B / wholesale customers)
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoKycProfile.php';
require_once __DIR__ . '/classes/PhytoKycDocument.php';
require_once __DIR__ . '/classes/PhytoKycSandboxClient.php';

class Phyto_Kyc extends Module
{
    private array $hooksList = [
        'displayHeader',
        'displayMyAccountBlock',
        'displayCustomerAccount',
        'displayProductPriceBlock',
        'displayProductAdditionalInfo',
        'displayTop',
    ];

    public function __construct()
    {
        $this->name          = 'phyto_kyc';
        $this->tab           = 'AdminCustomers';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 1;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto KYC');
        $this->description = $this->l('Enforces identity verification before revealing prices and purchase options, using a two-tier KYC system: Level 1 validates PAN cards for retail customers and Level 2 validates GST numbers for B2B or wholesale buyers, both via live API calls to sandbox.co.in. Unverified visitors see blurred prices and a KYC prompt banner, while verified customers get full access immediately. A KYC tab in the My Account area lets customers submit and track their documents, and staff can review, approve, or reject submissions from a dedicated back-office panel. Designed specifically for Indian plant stores that need to comply with PAN and GST identity requirements.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    // =========================================================================
    // Install / Uninstall
    // =========================================================================

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHooks()
            && $this->installTab()
            && $this->setDefaults();
    }

    public function uninstall(): bool
    {
        $this->uninstallTab();
        $this->runSql('uninstall');
        foreach ([
            'PHYTO_KYC_ENABLED',
            'PHYTO_KYC_SANDBOX_API_KEY',
            'PHYTO_KYC_MODE',
            'PHYTO_KYC_REQUIRE_L1',
            'PHYTO_KYC_REQUIRE_L2',
            'PHYTO_KYC_SANDBOX_TOKEN',
            'PHYTO_KYC_SANDBOX_TOKEN_EXPIRY',
        ] as $key) {
            Configuration::deleteByName($key);
        }
        return parent::uninstall();
    }

    private function runSql(string $type): bool
    {
        $sql = file_get_contents(__DIR__ . '/sql/' . $type . '.sql');
        $sql = str_replace(['ENGINE_TYPE', 'PREFIX_'], [_MYSQL_ENGINE_, _DB_PREFIX_], $sql);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }

    private function registerHooks(): bool
    {
        foreach ($this->hooksList as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }
        return true;
    }

    private function setDefaults(): bool
    {
        Configuration::updateValue('PHYTO_KYC_ENABLED',   0);
        Configuration::updateValue('PHYTO_KYC_MODE',      'sandbox');
        Configuration::updateValue('PHYTO_KYC_REQUIRE_L1', 1);
        Configuration::updateValue('PHYTO_KYC_REQUIRE_L2', 0);
        return true;
    }

    private function installTab(): bool
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoKyc';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCustomers');
        $tab->icon       = 'verified_user';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'KYC Verification';
        }

        return $tab->add();
    }

    private function uninstallTab(): void
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoKyc');
        if ($idTab) {
            (new Tab($idTab))->delete();
        }
    }

    // =========================================================================
    // Admin configuration page
    // =========================================================================

    public function getContent(): string
    {
        $output = '';
        if (Tools::isSubmit('submitPhytoKycConfig')) {
            $output .= $this->saveConfig();
        }
        return $output . $this->renderConfigForm();
    }

    private function saveConfig(): string
    {
        $enabled   = (int) Tools::getValue('PHYTO_KYC_ENABLED');
        $apiKey    = trim(Tools::getValue('PHYTO_KYC_SANDBOX_API_KEY'));
        $mode      = Tools::getValue('PHYTO_KYC_MODE') === 'production' ? 'production' : 'sandbox';
        $requireL1 = (int) Tools::getValue('PHYTO_KYC_REQUIRE_L1');
        $requireL2 = (int) Tools::getValue('PHYTO_KYC_REQUIRE_L2');

        Configuration::updateValue('PHYTO_KYC_ENABLED',        $enabled);
        Configuration::updateValue('PHYTO_KYC_SANDBOX_API_KEY', $apiKey);
        Configuration::updateValue('PHYTO_KYC_MODE',            $mode);
        Configuration::updateValue('PHYTO_KYC_REQUIRE_L1',      $requireL1);
        Configuration::updateValue('PHYTO_KYC_REQUIRE_L2',      $requireL2);

        // Clear cached token when API key changes
        Configuration::deleteByName('PHYTO_KYC_SANDBOX_TOKEN');
        Configuration::deleteByName('PHYTO_KYC_SANDBOX_TOKEN_EXPIRY');

        return $this->displayConfirmation($this->l('Settings saved.'));
    }

    private function renderConfigForm(): string
    {
        $fields = [
            'form' => [
                'legend' => ['title' => $this->l('KYC Settings'), 'icon' => 'icon-cog'],
                'input'  => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Enable KYC'),
                        'name'    => 'PHYTO_KYC_ENABLED',
                        'is_bool' => true,
                        'desc'    => $this->l('When ON, customers who have not completed KYC will see prices blurred.'),
                        'values'  => [
                            ['id' => 'on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'select',
                        'label' => $this->l('API Mode'),
                        'name'  => 'PHYTO_KYC_MODE',
                        'options' => [
                            'query' => [
                                ['id' => 'sandbox',    'name' => 'Sandbox (testing)'],
                                ['id' => 'production', 'name' => 'Production (live)'],
                            ],
                            'id'   => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->l('Sandbox.co.in API Key'),
                        'name'     => 'PHYTO_KYC_SANDBOX_API_KEY',
                        'size'     => 60,
                        'desc'     => $this->l('Your API key from sandbox.co.in for PAN and GST verification.'),
                        'required' => false,
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Require Level 1 (PAN)'),
                        'name'    => 'PHYTO_KYC_REQUIRE_L1',
                        'is_bool' => true,
                        'desc'    => $this->l('Require PAN verification for all customers.'),
                        'values'  => [
                            ['id' => 'l1_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'l1_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Require Level 2 (GST/Business)'),
                        'name'    => 'PHYTO_KYC_REQUIRE_L2',
                        'is_bool' => true,
                        'desc'    => $this->l('Require GST/business verification for wholesale customers.'),
                        'values'  => [
                            ['id' => 'l2_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'l2_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                ],
                'submit' => ['title' => $this->l('Save'), 'class' => 'btn btn-default pull-right'],
            ],
        ];

        $helper                        = new HelperForm();
        $helper->show_toolbar          = false;
        $helper->table                 = $this->table;
        $helper->module                = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->identifier            = $this->identifier;
        $helper->submit_action         = 'submitPhytoKycConfig';
        $helper->currentIndex          = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                 = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['PHYTO_KYC_ENABLED']        = Configuration::get('PHYTO_KYC_ENABLED');
        $helper->fields_value['PHYTO_KYC_MODE']           = Configuration::get('PHYTO_KYC_MODE') ?: 'sandbox';
        $helper->fields_value['PHYTO_KYC_SANDBOX_API_KEY'] = Configuration::get('PHYTO_KYC_SANDBOX_API_KEY');
        $helper->fields_value['PHYTO_KYC_REQUIRE_L1']     = Configuration::get('PHYTO_KYC_REQUIRE_L1');
        $helper->fields_value['PHYTO_KYC_REQUIRE_L2']     = Configuration::get('PHYTO_KYC_REQUIRE_L2');

        return $helper->generateForm([$fields]);
    }

    // =========================================================================
    // Hooks
    // =========================================================================

    /**
     * Inject blur CSS + body-class script into <head>.
     * Also inject the KYC banner just after <body> opens via inline script.
     */
    public function hookDisplayHeader(array $params): string
    {
        if (!$this->isKycBlurActive()) {
            return '';
        }

        $kycUrl   = $this->context->link->getModuleLink('phyto_kyc', 'kyc');
        $this->context->smarty->assign([
            'phyto_kyc_url'      => $kycUrl,
            'phyto_kyc_blur_css' => $this->getBlurCss(),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/kyc_blur_header.tpl');
    }

    /**
     * Top-of-page KYC banner (only for unverified logged-in customers).
     */
    public function hookDisplayTop(array $params): string
    {
        if (!$this->isKycBlurActive()) {
            return '';
        }

        $kycUrl = $this->context->link->getModuleLink('phyto_kyc', 'kyc');
        $this->context->smarty->assign(['phyto_kyc_url' => $kycUrl]);
        return $this->display(__FILE__, 'views/templates/hook/kyc_banner.tpl');
    }

    /**
     * Replace price with blur placeholder on product pages.
     */
    public function hookDisplayProductPriceBlock(array $params): string
    {
        if (!$this->isKycBlurActive()) {
            return '';
        }
        // Only intercept the main price display
        if (($params['type'] ?? '') !== 'price') {
            return '';
        }
        $kycUrl = $this->context->link->getModuleLink('phyto_kyc', 'kyc');
        $this->context->smarty->assign(['phyto_kyc_url' => $kycUrl]);
        return $this->display(__FILE__, 'views/templates/hook/kyc_price_placeholder.tpl');
    }

    /**
     * Add KYC link to My Account block sidebar.
     */
    public function hookDisplayMyAccountBlock(array $params): string
    {
        if (!$this->context->customer->isLogged()) {
            return '';
        }
        $kycUrl     = $this->context->link->getModuleLink('phyto_kyc', 'kyc');
        $profile    = PhytoKycProfile::getByCustomer((int) $this->context->customer->id);
        $kycStatus  = $profile ? $profile->level1_status : 'NotStarted';

        $this->context->smarty->assign([
            'phyto_kyc_url'    => $kycUrl,
            'phyto_kyc_status' => $kycStatus,
        ]);
        return $this->display(__FILE__, 'views/templates/hook/my_account_link.tpl');
    }

    /**
     * Add KYC tile to My Account page.
     */
    public function hookDisplayCustomerAccount(array $params): string
    {
        return $this->hookDisplayMyAccountBlock($params);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Returns true when blur should be active for the current visitor:
     *   - KYC module is enabled
     *   - Customer is logged in
     *   - Customer has not passed the required KYC level
     */
    public function isKycBlurActive(): bool
    {
        if (!(int) Configuration::get('PHYTO_KYC_ENABLED')) {
            return false; // module disabled → show everything
        }
        if (!$this->context->customer->isLogged()) {
            return true; // not logged in → blur (can't verify identity)
        }
        return !$this->isCustomerVerified((int) $this->context->customer->id);
    }

    /**
     * Returns true if the customer has passed all required KYC levels.
     */
    public static function isCustomerVerified(int $idCustomer): bool
    {
        $profile = PhytoKycProfile::getByCustomer($idCustomer);
        if (!$profile) {
            return false;
        }

        if ((int) Configuration::get('PHYTO_KYC_REQUIRE_L1')
            && $profile->level1_status !== 'Verified'
        ) {
            return false;
        }

        if ((int) Configuration::get('PHYTO_KYC_REQUIRE_L2')
            && $profile->level2_status !== 'Verified'
        ) {
            return false;
        }

        return true;
    }

    /**
     * Returns the inline CSS for blurring price elements.
     */
    private function getBlurCss(): string
    {
        return '
body.kyc-blur-active .price,
body.kyc-blur-active .product-price,
body.kyc-blur-active .regular-price,
body.kyc-blur-active .current-price,
body.kyc-blur-active .current-price-value,
body.kyc-blur-active .product-price-and-shipping,
body.kyc-blur-active [itemprop="price"],
body.kyc-blur-active [itemprop="lowPrice"],
body.kyc-blur-active .cart-summary-totals,
body.kyc-blur-active .order-total,
body.kyc-blur-active .total-value,
body.kyc-blur-active .subtotal-value,
body.kyc-blur-active .product-additional-info .product-price,
body.kyc-blur-active .price-final_price,
body.kyc-blur-active .product__price {
    filter: blur(6px) !important;
    pointer-events: none !important;
    user-select: none !important;
    position: relative;
}
body.kyc-blur-active .product-price-and-shipping,
body.kyc-blur-active .current-price {
    display: inline-block;
    min-width: 80px;
    min-height: 1.2em;
}
.phyto-kyc-price-placeholder {
    display: inline-block;
    background: #f0f0f0;
    border-radius: 4px;
    padding: 2px 12px;
    filter: blur(4px);
    user-select: none;
    font-size: 1.2em;
    font-weight: 700;
    color: #333;
    min-width: 80px;
    text-align: center;
}
.phyto-kyc-banner {
    background: #fff3cd;
    border-bottom: 2px solid #ffc107;
    padding: 10px 20px;
    text-align: center;
    font-size: 14px;
    z-index: 9999;
    position: relative;
}
.phyto-kyc-banner a {
    font-weight: 700;
    color: #856404;
    text-decoration: underline;
}
';
    }

    // =========================================================================
    // API client accessor (used by front controllers)
    // =========================================================================

    public function getSandboxClient(): PhytoKycSandboxClient
    {
        $apiKey = (string) Configuration::get('PHYTO_KYC_SANDBOX_API_KEY');
        return new PhytoKycSandboxClient($apiKey);
    }
}
