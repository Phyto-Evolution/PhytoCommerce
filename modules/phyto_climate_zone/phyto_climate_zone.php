<?php
/**
 * Phyto Climate Zone Module
 *
 * Shows which plants are suitable for a buyer's climate zone
 * based on offline pincode-prefix lookup.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Climate_Zone extends Module
{
    /** @var array Climate zone definitions */
    public static $zones = [
        'tropical_humid'     => 'Tropical humid (India coastal / South India)',
        'tropical_dry'       => 'Tropical dry (Deccan plateau)',
        'subtropical'        => 'Subtropical (North India plains)',
        'highland_temperate' => 'Highland temperate (Nilgiris / Himalayas)',
        'any_indoor'         => 'Any indoor (controlled environment)',
    ];

    /** @var array Intolerance options */
    public static $intolerances = [
        'hard_frost'    => 'Hard frost',
        'direct_rain'   => 'Direct rain',
        'low_humidity'  => 'Low humidity',
        'alkaline_water' => 'Alkaline water',
    ];

    public function __construct()
    {
        $this->name = 'phyto_climate_zone';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Climate Zone');
        $this->description = $this->l('Shows which plants are suitable for a buyer\'s climate zone based on pincode lookup. Fully offline.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
    }

    /**
     * Module installation
     */
    public function install()
    {
        return parent::install()
            && $this->executeSqlFile('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->installDefaultConfig();
    }

    /**
     * Module uninstallation
     */
    public function uninstall()
    {
        return $this->executeSqlFile('uninstall')
            && Configuration::deleteByName('PHYTO_CLIMATE_MAP')
            && parent::uninstall();
    }

    /**
     * Execute an SQL file from the sql/ directory.
     *
     * @param string $filename File name without .sql extension
     * @return bool
     */
    private function executeSqlFile($filename)
    {
        $path = dirname(__FILE__) . '/sql/' . $filename . '.sql';
        if (!file_exists($path)) {
            return false;
        }

        $sql = file_get_contents($path);
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

    /**
     * Install the default pincode-to-climate-zone mapping.
     *
     * @return bool
     */
    private function installDefaultConfig()
    {
        $defaultMap = json_encode([
            '600' => 'tropical_humid',
            '601' => 'tropical_humid',
            '602' => 'tropical_humid',
            '603' => 'tropical_humid',
            '500' => 'tropical_humid',
            '560' => 'tropical_humid',
            '682' => 'tropical_humid',
            '695' => 'tropical_humid',
            '400' => 'tropical_humid',
            '380' => 'tropical_dry',
            '411' => 'tropical_dry',
            '440' => 'tropical_dry',
            '302' => 'subtropical',
            '110' => 'subtropical',
            '201' => 'subtropical',
            '226' => 'subtropical',
            '208' => 'subtropical',
            '800' => 'subtropical',
            '700' => 'subtropical',
            '781' => 'subtropical',
            '643' => 'highland_temperate',
            '734' => 'highland_temperate',
            '171' => 'highland_temperate',
            '175' => 'highland_temperate',
            '796' => 'highland_temperate',
            '793' => 'highland_temperate',
        ], JSON_PRETTY_PRINT);

        return Configuration::updateValue('PHYTO_CLIMATE_MAP', $defaultMap);
    }

    // -------------------------------------------------------------------------
    // Back-office configuration page
    // -------------------------------------------------------------------------

    /**
     * Module configuration page (Settings > Modules > Configure).
     *
     * @return string HTML output
     */
    public function getContent()
    {
        $output = '';

        // Handle form submission
        if (Tools::isSubmit('submitPhytoClimateConfig')) {
            $mapJson = Tools::getValue('PHYTO_CLIMATE_MAP');
            $decoded = json_decode($mapJson, true);

            if ($decoded === null && $mapJson !== 'null') {
                $output .= $this->displayError($this->l('Invalid JSON. Please check your syntax and try again.'));
            } else {
                Configuration::updateValue('PHYTO_CLIMATE_MAP', $mapJson);
                $output .= $this->displayConfirmation($this->l('Pincode mapping saved successfully.'));
            }
        }

        // Handle "download default mapping" action
        if (Tools::isSubmit('downloadDefaultMap')) {
            $this->downloadJsonMapping();
        }

        $output .= $this->renderConfigForm();

        return $output;
    }

    /**
     * Render the configuration form.
     *
     * @return string
     */
    private function renderConfigForm()
    {
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitPhytoClimateConfig';

        $helper->fields_value = [
            'PHYTO_CLIMATE_MAP' => Configuration::get('PHYTO_CLIMATE_MAP'),
        ];

        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Pincode-to-Climate-Zone Mapping'),
                    'icon'  => 'icon-map-marker',
                ],
                'description' => $this->l('Enter a JSON object mapping 3-digit pincode prefixes to climate zone slugs. Valid zones: tropical_humid, tropical_dry, subtropical, highland_temperate.'),
                'input' => [
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Pincode Mapping (JSON)'),
                        'name'  => 'PHYTO_CLIMATE_MAP',
                        'cols'  => 80,
                        'rows'  => 20,
                        'desc'  => $this->l('Format: { "600": "tropical_humid", "302": "subtropical", ... }'),
                    ],
                ],
                'buttons' => [
                    [
                        'title' => $this->l('Download default mapping'),
                        'icon'  => 'process-icon-download',
                        'name'  => 'downloadDefaultMap',
                        'type'  => 'submit',
                        'class' => 'btn btn-default pull-right',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        return $helper->generateForm([$fields]);
    }

    /**
     * Stream the current JSON mapping as a downloadable file.
     */
    private function downloadJsonMapping()
    {
        $json = Configuration::get('PHYTO_CLIMATE_MAP');
        // Re-encode for pretty output
        $decoded = json_decode($json, true);
        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="phyto_climate_map.json"');
        header('Content-Length: ' . strlen($pretty));
        echo $pretty;
        exit;
    }

    // -------------------------------------------------------------------------
    // Back-office product tab hook
    // -------------------------------------------------------------------------

    /**
     * Hook: displayAdminProductsExtra
     * Renders the climate settings tab on the product edit page.
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];

        // Load existing data
        $data = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        $suitableZones = [];
        $cannotTolerate = [];
        $minTemp = '';
        $maxTemp = '';
        $outdoorNotes = '';

        if ($data) {
            $suitableZones = json_decode($data['suitable_zones'], true) ?: [];
            $cannotTolerate = json_decode($data['cannot_tolerate'], true) ?: [];
            $minTemp = $data['min_temp'];
            $maxTemp = $data['max_temp'];
            $outdoorNotes = $data['outdoor_notes'];
        }

        $ajaxUrl = $this->context->link->getAdminLink('AdminPhytoClimateZone');

        $this->context->smarty->assign([
            'phyto_climate_zones'      => self::$zones,
            'phyto_intolerances'       => self::$intolerances,
            'phyto_selected_zones'     => $suitableZones,
            'phyto_selected_intol'     => $cannotTolerate,
            'phyto_min_temp'           => $minTemp,
            'phyto_max_temp'           => $maxTemp,
            'phyto_outdoor_notes'      => $outdoorNotes,
            'phyto_id_product'         => $idProduct,
            'phyto_climate_ajax_url'   => $ajaxUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    // -------------------------------------------------------------------------
    // Front-office hooks
    // -------------------------------------------------------------------------

    /**
     * Hook: actionFrontControllerSetMedia
     * Enqueue front-office CSS and JS on product pages.
     *
     * @param array $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');
        if ($controller === 'product') {
            $this->context->controller->registerStylesheet(
                'module-phyto-climate-zone-front',
                'modules/' . $this->name . '/views/css/front.css',
                ['media' => 'all', 'priority' => 150]
            );
            $this->context->controller->registerJavascript(
                'module-phyto-climate-zone-js',
                'modules/' . $this->name . '/views/js/climate_check.js',
                ['position' => 'bottom', 'priority' => 150]
            );
        }
    }

    /**
     * Hook: displayProductExtraContent
     * Shows the Climate Suitability widget on the product page.
     *
     * @param array $params
     * @return array Array of PrestaShop\PrestaShop\Core\Product\ProductExtraContent
     */
    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->getId();

        // Check if product has climate data
        $data = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        if (!$data) {
            return [];
        }

        $checkUrl = $this->context->link->getModuleLink(
            $this->name,
            'check',
            [],
            true
        );

        $this->context->smarty->assign([
            'phyto_climate_check_url' => $checkUrl,
            'phyto_climate_id_product' => $idProduct,
        ]);

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $extraContent->setTitle($this->l('Climate Suitability'));
        $extraContent->setContent($content);

        return [$extraContent];
    }
}
