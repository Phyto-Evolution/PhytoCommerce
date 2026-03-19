<?php
/**
 * Phyto Grex Registry — Admin AJAX Controller
 *
 * Handles save/load of taxonomy data per product via AJAX.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoGrexRegistryController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
        $this->ajax = true;
    }

    /**
     * AJAX: Load grex data for a product
     */
    public function ajaxProcessLoadGrexData()
    {
        $id_product = (int) Tools::getValue('id_product');

        if (!$id_product) {
            $this->ajaxResponse(false, 'Invalid product ID.');
            return;
        }

        $data = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_grex_registry`
             WHERE `id_product` = ' . $id_product
        );

        if (!$data) {
            $data = $this->getDefaultData($id_product);
        }

        $this->ajaxResponse(true, 'Data loaded.', $data);
    }

    /**
     * AJAX: Save grex data for a product
     */
    public function ajaxProcessSaveGrexData()
    {
        $id_product = (int) Tools::getValue('id_product');

        if (!$id_product) {
            $this->ajaxResponse(false, 'Invalid product ID.');
            return;
        }

        $fields = [
            'genus' => 100,
            'species' => 100,
            'subspecies' => 100,
            'cultivar' => 150,
            'grex_name' => 150,
            'hybrid_formula' => 255,
            'mother' => 150,
            'father' => 150,
            'icps_number' => 50,
            'endemic_region' => 200,
            'conservation_status' => 20,
        ];

        $data = [];

        foreach ($fields as $field => $maxLength) {
            $value = Tools::getValue($field, '');
            $data[$field] = pSQL(mb_substr(trim($value), 0, $maxLength));
        }

        // Boolean field
        $data['icps_registered'] = (int) (bool) Tools::getValue('icps_registered', 0);

        // Text fields (no max length enforced at PHP level)
        $data['habitat'] = pSQL(trim(Tools::getValue('habitat', '')));
        $data['notes'] = pSQL(trim(Tools::getValue('notes', '')));

        // Clear ICPS number if not registered
        if (!$data['icps_registered']) {
            $data['icps_number'] = '';
        }

        // Validate conservation status
        $validStatuses = array_keys(Phyto_Grex_Registry::CONSERVATION_STATUSES);
        if (!in_array($data['conservation_status'], $validStatuses)) {
            $data['conservation_status'] = '';
        }

        $now = date('Y-m-d H:i:s');

        // Check if record exists
        $existing = Db::getInstance()->getRow(
            'SELECT `id_grex` FROM `' . _DB_PREFIX_ . 'phyto_grex_registry`
             WHERE `id_product` = ' . $id_product
        );

        if ($existing) {
            // Update
            $setClauses = [];
            foreach ($data as $col => $val) {
                if ($col === 'icps_registered') {
                    $setClauses[] = '`' . $col . '` = ' . (int) $val;
                } else {
                    $setClauses[] = '`' . $col . '` = \'' . $val . '\'';
                }
            }
            $setClauses[] = '`date_upd` = \'' . pSQL($now) . '\'';

            $sql = 'UPDATE `' . _DB_PREFIX_ . 'phyto_grex_registry` SET '
                . implode(', ', $setClauses)
                . ' WHERE `id_product` = ' . $id_product;

            $success = Db::getInstance()->execute($sql);
        } else {
            // Insert
            $columns = ['`id_product`', '`date_add`', '`date_upd`'];
            $values = [$id_product, '\'' . pSQL($now) . '\'', '\'' . pSQL($now) . '\''];

            foreach ($data as $col => $val) {
                $columns[] = '`' . $col . '`';
                if ($col === 'icps_registered') {
                    $values[] = (int) $val;
                } else {
                    $values[] = '\'' . $val . '\'';
                }
            }

            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'phyto_grex_registry` ('
                . implode(', ', $columns) . ') VALUES ('
                . implode(', ', $values) . ')';

            $success = Db::getInstance()->execute($sql);
        }

        if ($success) {
            $this->ajaxResponse(true, 'Taxonomy data saved successfully.');
        } else {
            $this->ajaxResponse(false, 'Failed to save taxonomy data.');
        }
    }

    /**
     * Return default empty data structure
     *
     * @param int $id_product
     * @return array
     */
    private function getDefaultData($id_product)
    {
        return [
            'id_product' => $id_product,
            'genus' => '',
            'species' => '',
            'subspecies' => '',
            'cultivar' => '',
            'grex_name' => '',
            'hybrid_formula' => '',
            'mother' => '',
            'father' => '',
            'icps_registered' => 0,
            'icps_number' => '',
            'habitat' => '',
            'endemic_region' => '',
            'conservation_status' => '',
            'notes' => '',
        ];
    }

    /**
     * Send standardized JSON response
     *
     * @param bool   $success
     * @param string $message
     * @param array  $data
     */
    private function ajaxResponse($success, $message, $data = [])
    {
        header('Content-Type: application/json');
        die(json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ]));
    }
}
