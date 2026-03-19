<?php
/**
 * Hidden admin controller – handles AJAX saves from the product-edit
 * seasonal-availability tab.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoSeasonalProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * Accept AJAX POST and upsert seasonal data for a product.
     */
    public function ajaxProcessSaveSeasonal()
    {
        $idProduct     = (int) Tools::getValue('id_product');
        $shipMonths    = Tools::getValue('ship_months', []);
        $dormMonths    = Tools::getValue('dormancy_months', []);
        $blockPurchase = (int) Tools::getValue('block_purchase');
        $outMsg        = pSQL(Tools::getValue('out_of_season_msg', ''));
        $enableNotify  = (int) Tools::getValue('enable_notify');

        if ($idProduct <= 0) {
            $this->ajaxResponse(false, $this->l('Invalid product ID.'));
        }

        // Sanitise month arrays
        $shipCsv = $this->sanitiseMonths($shipMonths);
        $dormCsv = $this->sanitiseMonths($dormMonths);

        $exists = Db::getInstance()->getValue(
            'SELECT `id_seasonal` FROM `' . _DB_PREFIX_ . 'phyto_seasonal_product`
             WHERE `id_product` = ' . $idProduct
        );

        if ($exists) {
            $ok = Db::getInstance()->update('phyto_seasonal_product', [
                'ship_months'       => $shipCsv,
                'dormancy_months'   => $dormCsv,
                'block_purchase'    => $blockPurchase,
                'out_of_season_msg' => $outMsg,
                'enable_notify'     => $enableNotify,
            ], '`id_product` = ' . $idProduct);
        } else {
            $ok = Db::getInstance()->insert('phyto_seasonal_product', [
                'id_product'        => $idProduct,
                'ship_months'       => $shipCsv,
                'dormancy_months'   => $dormCsv,
                'block_purchase'    => $blockPurchase,
                'out_of_season_msg' => $outMsg,
                'enable_notify'     => $enableNotify,
            ]);
        }

        $this->ajaxResponse($ok, $ok ? $this->l('Seasonal settings saved.') : $this->l('Database error.'));
    }

    /**
     * Filter, deduplicate, sort and return CSV of valid month numbers.
     *
     * @param array|string $input
     * @return string
     */
    private function sanitiseMonths($input)
    {
        if (!is_array($input)) {
            $input = explode(',', (string) $input);
        }

        $months = [];
        foreach ($input as $m) {
            $m = (int) $m;
            if ($m >= 1 && $m <= 12) {
                $months[] = $m;
            }
        }

        $months = array_unique($months);
        sort($months);

        return implode(',', $months);
    }

    /**
     * Send a JSON response and terminate.
     *
     * @param bool   $success
     * @param string $message
     */
    private function ajaxResponse($success, $message)
    {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode([
            'success' => (bool) $success,
            'message' => $message,
        ]));
    }
}
