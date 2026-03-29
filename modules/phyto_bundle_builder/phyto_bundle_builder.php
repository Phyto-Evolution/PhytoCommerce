<?php
/**
 * Phyto Bundle Builder
 *
 * Customer-facing bundle creator. Admin defines bundle templates
 * (e.g. "Starter Kit" = 1 plant + 1 pot + 1 substrate). Customer picks
 * specific products from each slot, gets a combined discount, and the
 * selection is added to the cart as a pack.
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoBundle.php';
require_once __DIR__ . '/classes/PhytoBundleSlot.php';

class Phyto_Bundle_Builder extends Module
{
    /** @var array Default configuration values */
    private array $configDefaults = [
        'PHYTO_BUNDLE_MAX_SLOTS'    => 5,
        'PHYTO_BUNDLE_SHOW_SAVINGS' => 1,
        'PHYTO_BUNDLE_CTA_TEXT'     => 'Add Bundle to Cart',
    ];

    public function __construct()
    {
        $this->name          = 'phyto_bundle_builder';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Bundle Builder');
        $this->description = $this->l(
            'Let customers build custom bundles from predefined slot templates and get a combined discount.'
        );
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        foreach ($this->configDefaults as $key => $value) {
            if (!Configuration::hasKey($key)) {
                Configuration::updateValue($key, $value);
            }
        }

        return parent::install()
            && $this->runSql('install')
            && $this->installTab()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayHome')
            && $this->registerHook('displayLeftColumn')
            && $this->registerHook('displayRightColumn')
            && $this->registerHook('actionCartSave');
    }

    public function uninstall(): bool
    {
        foreach (array_keys($this->configDefaults) as $key) {
            Configuration::deleteByName($key);
        }

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
        if (empty(trim($sql))) {
            return true;
        }

        $sql     = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql     = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);
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
        $tabId = (int) Tab::getIdFromClassName('AdminPhytoBundleBuilder');
        if ($tabId) {
            return true;
        }

        $parentTabId = (int) Tab::getIdFromClassName('AdminCatalog');

        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoBundleBuilder';
        $tab->module     = $this->name;
        $tab->id_parent  = $parentTabId > 0 ? $parentTabId : -1;
        $tab->icon       = 'layers';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Bundle Builder';
        }

        return $tab->add();
    }

    private function uninstallTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPhytoBundleBuilder');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    // -------------------------------------------------------------------------
    // Back-office configuration page
    // -------------------------------------------------------------------------

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoBundleConfig')) {
            $output .= $this->postProcess();
        }

        return $output . $this->renderConfigForm();
    }

    private function postProcess(): string
    {
        $maxSlots    = max(1, (int) Tools::getValue('PHYTO_BUNDLE_MAX_SLOTS'));
        $showSavings = (int) Tools::getValue('PHYTO_BUNDLE_SHOW_SAVINGS');
        $ctaText     = trim(Tools::getValue('PHYTO_BUNDLE_CTA_TEXT', 'Add Bundle to Cart'));

        if (empty($ctaText)) {
            $ctaText = 'Add Bundle to Cart';
        }

        Configuration::updateValue('PHYTO_BUNDLE_MAX_SLOTS', $maxSlots);
        Configuration::updateValue('PHYTO_BUNDLE_SHOW_SAVINGS', $showSavings);
        Configuration::updateValue('PHYTO_BUNDLE_CTA_TEXT', $ctaText);

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    private function renderConfigForm(): string
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Bundle Builder Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('Max Slots per Bundle'),
                        'name'  => 'PHYTO_BUNDLE_MAX_SLOTS',
                        'class' => 'fixed-width-sm',
                        'desc'  => $this->l('Maximum number of product slots allowed per bundle template.'),
                    ],
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Show "You Save" on Builder Page'),
                        'name'    => 'PHYTO_BUNDLE_SHOW_SAVINGS',
                        'is_bool' => true,
                        'desc'    => $this->l('Display the savings amount and percentage on the bundle builder page.'),
                        'values'  => [
                            ['id' => 'savings_on',  'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'savings_off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('CTA Button Text'),
                        'name'  => 'PHYTO_BUNDLE_CTA_TEXT',
                        'size'  => 100,
                        'desc'  => $this->l('Text displayed on the "Add to Cart" button on the bundle builder page.'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper                        = new HelperForm();
        $helper->show_toolbar          = false;
        $helper->table                 = $this->table;
        $helper->module                = $this;
        $helper->default_form_language = (int) $this->context->language->id;
        $helper->identifier            = $this->identifier;
        $helper->submit_action         = 'submitPhytoBundleConfig';
        $helper->currentIndex          = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token                 = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['PHYTO_BUNDLE_MAX_SLOTS']    = (int) Configuration::get('PHYTO_BUNDLE_MAX_SLOTS', null, null, null, 5);
        $helper->fields_value['PHYTO_BUNDLE_SHOW_SAVINGS'] = (int) Configuration::get('PHYTO_BUNDLE_SHOW_SAVINGS', null, null, null, 1);
        $helper->fields_value['PHYTO_BUNDLE_CTA_TEXT']     = Configuration::get('PHYTO_BUNDLE_CTA_TEXT', null, null, null, 'Add Bundle to Cart');

        return $helper->generateForm([$fields_form]);
    }

    // -------------------------------------------------------------------------
    // Hook: displayHeader — enqueue CSS and JS
    // -------------------------------------------------------------------------

    public function hookDisplayHeader(): void
    {
        // Only load assets on the bundle builder front controller
        if ($this->context->controller instanceof ModuleFrontController
            && $this->context->controller->module instanceof self
        ) {
            $this->context->controller->registerStylesheet(
                'phyto-bundle-builder-css',
                'modules/phyto_bundle_builder/views/css/front.css',
                ['media' => 'all', 'priority' => 200]
            );
            $this->context->controller->registerJavascript(
                'phyto-bundle-builder-js',
                'modules/phyto_bundle_builder/views/js/front.js',
                ['position' => 'bottom', 'priority' => 200]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Hook: displayHome — featured bundles widget
    // -------------------------------------------------------------------------

    public function hookDisplayHome(array $params): string
    {
        $bundles = PhytoBundle::getActiveBundles((int) $this->context->language->id);

        if (empty($bundles)) {
            return '';
        }

        $this->context->smarty->assign([
            'phyto_bundles'      => $bundles,
            'phyto_builder_base' => $this->context->link->getModuleLink($this->name, 'builder'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/bundle_widget.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: displayLeftColumn / displayRightColumn — sidebar widget
    // -------------------------------------------------------------------------

    public function hookDisplayLeftColumn(array $params): string
    {
        return $this->renderSidebarWidget();
    }

    public function hookDisplayRightColumn(array $params): string
    {
        return $this->renderSidebarWidget();
    }

    private function renderSidebarWidget(): string
    {
        $bundles = PhytoBundle::getActiveBundles((int) $this->context->language->id);

        if (empty($bundles)) {
            return '';
        }

        $this->context->smarty->assign([
            'phyto_bundles'      => $bundles,
            'phyto_builder_base' => $this->context->link->getModuleLink($this->name, 'builder'),
            'phyto_sidebar_mode' => true,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/bundle_widget.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: actionCartSave — validate bundle integrity
    // -------------------------------------------------------------------------

    public function hookActionCartSave(array $params): void
    {
        /** @var Cart $cart */
        $cart = $params['cart'] ?? $this->context->cart;

        if (!$cart || !Validate::isLoadedObject($cart)) {
            return;
        }

        // Check cart for bundle meta stored in cart product customization
        // (the builder front controller stores bundle selections as
        //  a JSON-encoded cart product attribute note)
        $bundleData = $this->context->cookie->__get('phyto_pending_bundle');
        if (!$bundleData) {
            return;
        }

        $data = json_decode($bundleData, true);
        if (!is_array($data) || empty($data['id_bundle'])) {
            return;
        }

        $idBundle = (int) $data['id_bundle'];
        $slots    = PhytoBundle::getSlots($idBundle);

        foreach ($slots as $slot) {
            if (!(int) $slot['required']) {
                continue;
            }
            $idSlot = (int) $slot['id_slot'];
            if (empty($data['selections'][$idSlot])) {
                // Required slot not filled — clear cookie flag (cart was saved incomplete)
                $this->context->cookie->__unset('phyto_pending_bundle');
                return;
            }
        }

        // All required slots filled — clear the flag
        $this->context->cookie->__unset('phyto_pending_bundle');
    }
}
