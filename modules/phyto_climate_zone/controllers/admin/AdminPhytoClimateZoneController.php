<?php
/**
 * AdminPhytoClimateZoneController
 *
 * Hidden AJAX-only admin controller for the Phyto Climate Zone module.
 * Handles saving climate suitability data from the product admin tab.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoClimateZoneController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * Intercept every request before display logic runs.
     * If phyto_ajax=1, dispatch to the appropriate action handler and exit.
     */
    public function init()
    {
        parent::init();

        if ((int) Tools::getValue('phyto_ajax') !== 1) {
            // Not an AJAX request — hidden controller has nothing to display.
            return;
        }

        $action = Tools::getValue('action');

        switch ($action) {
            case 'save_climate':
                $this->ajaxSaveClimate();
                break;

            default:
                $this->ajaxError('Unknown action.');
                break;
        }
    }

    /**
     * Handle the save_climate AJAX action.
     *
     * Reads id_product, suitable_zones[], cannot_tolerate[], min_temp,
     * max_temp, outdoor_notes from POST, validates, then upserts into
     * the phyto_climate_product table.
     */
    private function ajaxSaveClimate()
    {
        $idProduct = (int) Tools::getValue('id_product');
        if (!$idProduct) {
            $this->ajaxError('Invalid product ID.');
        }

        if (!Product::existsInDatabase($idProduct, 'product')) {
            $this->ajaxError('Product not found.');
        }

        // --- Suitable zones ---
        $suitableZonesRaw = Tools::getValue('suitable_zones');
        if (!is_array($suitableZonesRaw)) {
            $suitableZonesRaw = [];
        }
        $allowedZones = array_keys(Phyto_Climate_Zone::$zones);
        $suitableZones = array_values(array_intersect($suitableZonesRaw, $allowedZones));

        // --- Cannot tolerate ---
        $cannotTolerateRaw = Tools::getValue('cannot_tolerate');
        if (!is_array($cannotTolerateRaw)) {
            $cannotTolerateRaw = [];
        }
        $allowedIntolerances = array_keys(Phyto_Climate_Zone::$intolerances);
        $cannotTolerate = array_values(array_intersect($cannotTolerateRaw, $allowedIntolerances));

        // --- Temperatures ---
        $minTemp = pSQL(Tools::getValue('min_temp', ''));
        $maxTemp = pSQL(Tools::getValue('max_temp', ''));

        // --- Outdoor notes ---
        $outdoorNotes = pSQL(Tools::getValue('outdoor_notes', ''), true);

        $db = Db::getInstance();

        // Check whether a record already exists for this product.
        $existing = $db->getRow(
            'SELECT `id_climate` FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        $record = [
            'id_product'     => $idProduct,
            'suitable_zones' => pSQL(json_encode($suitableZones)),
            'cannot_tolerate' => pSQL(json_encode($cannotTolerate)),
            'min_temp'       => $minTemp,
            'max_temp'       => $maxTemp,
            'outdoor_notes'  => $outdoorNotes,
        ];

        if ($existing) {
            $saved = $db->update(
                'phyto_climate_product',
                $record,
                'id_product = ' . $idProduct
            );
        } else {
            $saved = $db->insert('phyto_climate_product', $record);
        }

        if ($saved) {
            $this->ajaxSuccess(['success' => true]);
        } else {
            $this->ajaxError('Failed to save climate data.');
        }
    }

    /**
     * Emit a JSON success payload and exit.
     *
     * @param array $data
     */
    private function ajaxSuccess(array $data)
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Emit a JSON error payload and exit.
     *
     * @param string $message
     */
    private function ajaxError($message)
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message]);
        exit;
    }

    /**
     * Suppress standard back-office HTML rendering — AJAX only.
     */
    public function display()
    {
        // Intentionally empty.
    }

    /**
     * Required by PrestaShop — return empty string to suppress list view.
     */
    public function renderList()
    {
        return '';
    }
}
