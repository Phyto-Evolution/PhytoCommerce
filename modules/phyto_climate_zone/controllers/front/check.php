<?php
/**
 * Phyto Climate Zone — Check Front Controller (v2)
 *
 * POST: id_product + pincode (6 digits)
 * Returns JSON with zone code, full zone detail (monthly temp/humidity,
 * frost risk, monsoon months), and suitability verdict.
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
    /** @var bool JSON-only — no layout */
    public $ajax = true;

    public function postProcess()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $pincode   = trim((string) Tools::getValue('pincode'));

        if (!$idProduct) {
            $this->jsonError('Invalid product.');
        }

        if (empty($pincode) || !preg_match('/^\d{6}$/', $pincode)) {
            $this->jsonError('Please enter a valid 6-digit pincode.');
        }

        // ── Load PIN-prefix map ──────────────────────────────────────────────
        $mapJson    = Configuration::get('PHYTO_CLIMATE_MAP');
        $climateMap = ($mapJson) ? json_decode($mapJson, true) : [];
        if (!is_array($climateMap)) {
            $climateMap = [];
        }

        $prefix   = substr($pincode, 0, 3);
        $zoneCode = isset($climateMap[$prefix]) ? $climateMap[$prefix] : null;

        if ($zoneCode === null) {
            $this->jsonResult([
                'zone'      => null,
                'zone_code' => null,
                'zone_data' => null,
                'suitable'  => false,
                'message'   => 'We do not have climate data for your pincode area yet.',
            ]);
        }

        // ── Load full zone data from JSON ────────────────────────────────────
        $allZoneData = Phyto_Climate_Zone::loadZoneData();
        $zoneDetail  = isset($allZoneData[$zoneCode]) ? $allZoneData[$zoneCode] : null;
        $zoneLabel   = ($zoneDetail) ? $zoneDetail['label'] : $zoneCode;

        // ── Fetch product climate record ─────────────────────────────────────
        $record = Db::getInstance()->getRow(
            'SELECT `suitable_zones`, `cannot_tolerate`, `min_temp`, `max_temp`, `outdoor_notes`
             FROM `' . _DB_PREFIX_ . 'phyto_climate_product`
             WHERE `id_product` = ' . $idProduct
        );

        // Build the climate summary to always send back
        $climateSummary = null;
        if ($zoneDetail) {
            $climateSummary = [
                'code'           => $zoneCode,
                'label'          => $zoneDetail['label'],
                'description'    => $zoneDetail['description'],
                'monthly_temp'   => $zoneDetail['monthly_temp'],
                'monthly_humidity' => $zoneDetail['monthly_humidity'],
                'annual_min_temp' => $zoneDetail['annual_min_temp'],
                'annual_max_temp' => $zoneDetail['annual_max_temp'],
                'frost_risk'     => $zoneDetail['frost_risk'],
                'monsoon_months' => $zoneDetail['monsoon_months'],
                'example_cities' => $zoneDetail['example_cities'],
                'months'         => Phyto_Climate_Zone::getMonthLabels(),
            ];
        }

        if (!$record) {
            // No restrictions configured for this product
            $this->jsonResult([
                'zone'      => $zoneCode,
                'zone_code' => $zoneCode,
                'zone_data' => $climateSummary,
                'suitable'  => true,
                'message'   => 'Your area falls in the ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . ' zone. No specific climate restrictions are noted for this plant.',
            ]);
        }

        $suitableZones  = json_decode($record['suitable_zones'], true);
        $cannotTolerate = json_decode($record['cannot_tolerate'], true);
        if (!is_array($suitableZones))  { $suitableZones  = []; }
        if (!is_array($cannotTolerate)) { $cannotTolerate = []; }

        // ── Intolerance warnings ─────────────────────────────────────────────
        $warnings = [];
        if ($zoneDetail) {
            if ($zoneDetail['frost_risk'] && in_array('hard_frost', $cannotTolerate)) {
                $warnings[] = 'This plant cannot tolerate frost, but your zone has frost risk.';
            }
            // High humidity zone (avg > 80%) + direct rain intolerance
            $avgHumidity = array_sum($zoneDetail['monthly_humidity']) / 12;
            if ($avgHumidity > 80 && in_array('direct_rain', $cannotTolerate)) {
                $warnings[] = 'Your zone has high rainfall. This plant needs shelter from direct rain.';
            }
            // Low humidity zone (avg < 50%) + low humidity intolerance
            if ($avgHumidity < 50 && in_array('low_humidity', $cannotTolerate)) {
                $warnings[] = 'Your zone tends to be dry. Supplemental humidity may be needed.';
            }
        }

        // ── Suitability verdict ──────────────────────────────────────────────
        if (empty($suitableZones)) {
            $suitable = true;
            $message  = 'Your area is in the ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . ' zone. This plant can be grown in most climates.';
        } elseif (in_array($zoneCode, $suitableZones) || in_array('any_indoor', $suitableZones)) {
            $suitable = true;
            $message  = 'Great news! This plant is well-suited to the ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . ' zone.';
        } else {
            $suitable = false;
            $message  = 'This plant may not thrive outdoors in the ' . htmlspecialchars($zoneLabel, ENT_QUOTES, 'UTF-8') . ' zone. Consider indoor or controlled growing.';
        }

        $this->jsonResult([
            'zone'           => $zoneCode,
            'zone_code'      => $zoneCode,
            'zone_data'      => $climateSummary,
            'suitable'       => $suitable,
            'message'        => $message,
            'warnings'       => $warnings,
            'outdoor_notes'  => $record['outdoor_notes'] ? htmlspecialchars($record['outdoor_notes'], ENT_QUOTES, 'UTF-8') : null,
            'plant_min_temp' => $record['min_temp'],
            'plant_max_temp' => $record['max_temp'],
        ]);
    }

    private function jsonResult(array $data)
    {
        ob_start();
        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    private function jsonError($message)
    {
        ob_start();
        ob_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => $message]);
        exit;
    }

    public function initContent()
    {
        // Intentionally empty — all output from postProcess().
    }
}
