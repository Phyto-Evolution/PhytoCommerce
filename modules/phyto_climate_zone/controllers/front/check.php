<?php
/**
 * Phyto Climate Zone — Check Front Controller
 *
 * Accepts a POST request with id_product and pincode, looks up the pincode
 * prefix in the PHYTO_CLIMATE_MAP configuration, compares against the
 * product's suitable_zones, and returns a JSON result.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Climate_ZoneCheckModuleFrontController extends ModuleFrontController
{
    /** @var bool No layout rendering needed — JSON only. */
    public $ajax = true;

    /**
     * Handle AJAX POST. All logic runs here so the response goes out
     * before PrestaShop tries to render any page template.
     */
    public function postProcess()
    {
        // Ensure we only answer POST requests
        if (!$_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->jsonError('Method not allowed.');
        }

        $idProduct = (int) Tools::getValue('id_product');
        $pincode   = trim((string) Tools::getValue('pincode'));

        // ── Validate inputs ───────────────────────────────────────────────────
        if (!$idProduct) {
            $this->jsonError('Invalid product.');
        }

        if (empty($pincode) || !preg_match('/^\d{6}$/', $pincode)) {
            $this->jsonError('Please enter a valid 6-digit pincode.');
        }

        // ── Load climate map (JSON config) ────────────────────────────────────
        $mapJson = Configuration::get('PHYTO_CLIMATE_MAP');
        $climateMap = $mapJson ? json_decode($mapJson, true) : [];
        if (!is_array($climateMap)) {
            $climateMap = [];
        }

        // ── Determine zone from first 3 digits of pincode ─────────────────────
        $prefix   = substr($pincode, 0, 3);
        $zoneSlug = isset($climateMap[$prefix]) ? $climateMap[$prefix] : null;

        if ($zoneSlug === null) {
            $this->jsonResult([
                'zone'       => null,
                'zone_label' => null,
                'suitable'   => false,
                'message'    => 'We do not have climate data for your pincode area yet.',
            ]);
        }

        // ── Look up zone label ────────────────────────────────────────────────
        $allZones  = Phyto_Climate_Zone::$zones;
        $zoneLabel = isset($allZones[$zoneSlug]) ? $allZones[$zoneSlug] : $zoneSlug;

        // ── Fetch product climate record ──────────────────────────────────────
        $climateRecord = Db::getInstance()->getRow(
            'SELECT `suitable_zones` FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        if (!$climateRecord) {
            // No climate data configured for this product
            $this->jsonResult([
                'zone'       => $zoneSlug,
                'zone_label' => $zoneLabel,
                'suitable'   => true,
                'message'    => 'Your climate zone is ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . '. No specific restrictions are noted for this plant.',
            ]);
        }

        $suitableZones = json_decode($climateRecord['suitable_zones'], true);
        if (!is_array($suitableZones)) {
            $suitableZones = [];
        }

        // ── Determine suitability ─────────────────────────────────────────────
        // If suitable_zones is empty, plant is assumed to grow anywhere.
        if (empty($suitableZones)) {
            $suitable = true;
            $message  = 'Your climate zone is ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . '. This plant can be grown in most climates.';
        } elseif (in_array($zoneSlug, $suitableZones) || in_array('any_indoor', $suitableZones)) {
            $suitable = true;
            $message  = 'Great news! This plant is suitable for your climate zone (' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . ').';
        } else {
            $suitable = false;
            $message  = 'This plant may not thrive in your climate zone (' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . '). Consider growing it indoors with controlled conditions.';
        }

        $this->jsonResult([
            'zone'       => $zoneSlug,
            'zone_label' => $zoneLabel,
            'suitable'   => $suitable,
            'message'    => $message,
        ]);
    }

    /**
     * Emit a JSON result payload and exit.
     *
     * @param array $data
     */
    private function jsonResult(array $data)
    {
        ob_start();
        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Emit a JSON error payload and exit.
     *
     * @param string $message
     */
    private function jsonError($message)
    {
        ob_start();
        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message]);
        exit;
    }

    /**
     * Nothing to render — all output is produced in postProcess().
     */
    public function initContent()
    {
        // Intentionally empty.
    }
}
