<?php
/**
 * Phyto Climate Zone Module
 *
 * Maps products to PCC-IN (PhytoCommerce Climate Code — India) zones.
 * Customers enter their 6-digit pincode to check plant suitability offline.
 * 15 granular India climate zones; 797 PIN prefixes pre-mapped.
 * Zone + monthly climate data loaded from data/india_climate_zones.json.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   proprietary
 * @version   2.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Climate_Zone extends Module
{
    /**
     * Return all 15 PCC-IN zone definitions loaded from JSON.
     * Result is cached per request in a static variable.
     *
     * @return array  slug => label
     */
    public static function getZones()
    {
        static $zones = null;
        if ($zones !== null) {
            return $zones;
        }

        $data = self::loadZoneData();
        $zones = [];
        foreach ($data as $slug => $zone) {
            $zones[$slug] = $zone['label'];
        }
        return $zones;
    }

    /**
     * Return full zone data array (all fields) from JSON.
     * Cached per request.
     *
     * @return array  slug => zone_detail_array
     */
    public static function loadZoneData()
    {
        static $zoneData = null;
        if ($zoneData !== null) {
            return $zoneData;
        }

        $path = dirname(__FILE__) . '/data/india_climate_zones.json';
        if (!file_exists($path)) {
            $zoneData = [];
            return $zoneData;
        }

        $raw = file_get_contents($path);
        $decoded = json_decode($raw, true);
        $zoneData = (is_array($decoded) && isset($decoded['zones'])) ? $decoded['zones'] : [];
        return $zoneData;
    }

    /**
     * Return the months array (Jan–Dec labels).
     *
     * @return string[]
     */
    public static function getMonthLabels()
    {
        $path = dirname(__FILE__) . '/data/india_climate_zones.json';
        if (!file_exists($path)) {
            return ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        }
        $raw = file_get_contents($path);
        $decoded = json_decode($raw, true);
        return (isset($decoded['months'])) ? $decoded['months'] : ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    }

    /** @var array Intolerance options (unchanged) */
    public static $intolerances = [
        'hard_frost'     => 'Hard frost',
        'direct_rain'    => 'Direct rain',
        'low_humidity'   => 'Low humidity',
        'alkaline_water' => 'Alkaline water',
    ];

    public function __construct()
    {
        $this->name = 'phyto_climate_zone';
        $this->tab = 'front_office_features';
        $this->version = '2.0.0';
        $this->author = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Climate Zone');
        $this->description = $this->l('Maps products to 15 PCC-IN climate zones via offline pincode lookup. Covers 797 India PIN prefixes.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
    }

    public function install()
    {
        return parent::install()
            && $this->executeSqlFile('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->installDefaultConfig();
    }

    public function uninstall()
    {
        return $this->executeSqlFile('uninstall')
            && Configuration::deleteByName('PHYTO_CLIMATE_MAP')
            && parent::uninstall();
    }

    /**
     * Execute an SQL file from the sql/ directory.
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

        foreach (preg_split('/;\s*[\r\n]+/', $sql) as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !Db::getInstance()->execute($statement)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Load the generated PIN-prefix map from JSON and store in config.
     * Falls back to a minimal inline map if the JSON file is absent.
     */
    private function installDefaultConfig()
    {
        $mapPath = dirname(__FILE__) . '/data/india_pin_prefix_zone_map.json';
        if (file_exists($mapPath)) {
            $raw     = file_get_contents($mapPath);
            $decoded = json_decode($raw, true);
            $map     = (isset($decoded['map']) && is_array($decoded['map'])) ? $decoded['map'] : [];
        } else {
            // Minimal fallback (26 prefixes, original v1 set) using new zone codes
            $map = [
                '600' => 'PCC-IN-01', '601' => 'PCC-IN-01', '602' => 'PCC-IN-01',
                '603' => 'PCC-IN-01', '500' => 'PCC-IN-04', '560' => 'PCC-IN-03',
                '682' => 'PCC-IN-02', '695' => 'PCC-IN-02', '400' => 'PCC-IN-02',
                '380' => 'PCC-IN-03', '411' => 'PCC-IN-03', '440' => 'PCC-IN-08',
                '302' => 'PCC-IN-05', '110' => 'PCC-IN-05', '201' => 'PCC-IN-05',
                '226' => 'PCC-IN-06', '208' => 'PCC-IN-05', '800' => 'PCC-IN-06',
                '700' => 'PCC-IN-09', '781' => 'PCC-IN-10', '643' => 'PCC-IN-11',
                '734' => 'PCC-IN-12', '171' => 'PCC-IN-12', '175' => 'PCC-IN-12',
                '796' => 'PCC-IN-10', '793' => 'PCC-IN-10',
            ];
        }

        return Configuration::updateValue('PHYTO_CLIMATE_MAP', json_encode($map));
    }

    // -------------------------------------------------------------------------
    // Back-office configuration page
    // -------------------------------------------------------------------------

    public function getContent()
    {
        $output = '';

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

        if (Tools::isSubmit('downloadDefaultMap')) {
            $this->downloadJsonMapping();
        }

        $output .= $this->renderConfigForm();
        return $output;
    }

    private function renderConfigForm()
    {
        $zoneList = '';
        foreach (self::getZones() as $slug => $label) {
            $zoneList .= $slug . ' — ' . $label . "\n";
        }

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitPhytoClimateConfig';

        $helper->fields_value = [
            'PHYTO_CLIMATE_MAP' => Configuration::get('PHYTO_CLIMATE_MAP'),
            'zone_reference'    => '',
        ];

        $fields = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Pincode-to-Climate-Zone Mapping (v2 — 15 PCC-IN zones)'),
                    'icon'  => 'icon-map-marker',
                ],
                'description' => $this->l('JSON mapping 3-digit PIN prefixes to PCC-IN zone codes. Valid codes: PCC-IN-01 through PCC-IN-15. The default map covers 797 Indian PIN prefixes generated by the Python data generator.'),
                'input' => [
                    [
                        'type'  => 'textarea',
                        'label' => $this->l('Pincode Mapping (JSON)'),
                        'name'  => 'PHYTO_CLIMATE_MAP',
                        'cols'  => 80,
                        'rows'  => 20,
                        'desc'  => $this->l('Format: { "600": "PCC-IN-01", "110": "PCC-IN-05", ... }'),
                    ],
                    [
                        'type'  => 'free',
                        'label' => $this->l('Available zone codes'),
                        'name'  => 'zone_reference',
                        'desc'  => nl2br(htmlspecialchars($zoneList, ENT_QUOTES, 'UTF-8')),
                    ],
                ],
                'buttons' => [
                    [
                        'title' => $this->l('Download current mapping'),
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

    private function downloadJsonMapping()
    {
        $json    = Configuration::get('PHYTO_CLIMATE_MAP');
        $decoded = json_decode($json, true);
        $pretty  = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="phyto_climate_map.json"');
        header('Content-Length: ' . strlen($pretty));
        echo $pretty;
        exit;
    }

    // -------------------------------------------------------------------------
    // Back-office product tab hook
    // -------------------------------------------------------------------------

    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];

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
            $suitableZones  = json_decode($data['suitable_zones'], true) ?: [];
            $cannotTolerate = json_decode($data['cannot_tolerate'], true) ?: [];
            $minTemp        = $data['min_temp'];
            $maxTemp        = $data['max_temp'];
            $outdoorNotes   = $data['outdoor_notes'];
        }

        $ajaxUrl = $this->context->link->getAdminLink('AdminPhytoClimateZone');

        $this->context->smarty->assign([
            'phyto_climate_zones'    => self::getZones(),
            'phyto_intolerances'     => self::$intolerances,
            'phyto_selected_zones'   => $suitableZones,
            'phyto_selected_intol'   => $cannotTolerate,
            'phyto_min_temp'         => $minTemp,
            'phyto_max_temp'         => $maxTemp,
            'phyto_outdoor_notes'    => $outdoorNotes,
            'phyto_id_product'       => $idProduct,
            'phyto_climate_ajax_url' => $ajaxUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    // -------------------------------------------------------------------------
    // Front-office hooks
    // -------------------------------------------------------------------------

    public function hookActionFrontControllerSetMedia($params)
    {
        if (Tools::getValue('controller') === 'product') {
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

    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->getId();

        $data = Db::getInstance()->getRow(
            'SELECT id_product FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        if (!$data) {
            return [];
        }

        $checkUrl = $this->context->link->getModuleLink($this->name, 'check', [], true);

        $this->context->smarty->assign([
            'phyto_climate_check_url'  => $checkUrl,
            'phyto_climate_id_product' => $idProduct,
        ]);

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $extraContent->setTitle($this->l('Climate Suitability'));
        $extraContent->setContent($content);

        return [$extraContent];
    }
}
