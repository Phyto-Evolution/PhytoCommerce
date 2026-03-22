<?php
/**
 * AdminPhytoCareCardController
 *
 * Hidden AJAX-only admin controller for the Phyto Care Card module.
 * Handles saving care card data from the product tab.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoCareCardController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->bootstrap = true;
    }

    /**
     * Override init() to intercept AJAX requests before any display logic runs.
     */
    public function init()
    {
        parent::init();

        if ((int) Tools::getValue('phyto_ajax') !== 1) {
            // Not an AJAX call — nothing to display in this hidden controller.
            return;
        }

        $action = Tools::getValue('action');

        switch ($action) {
            case 'save_care':
                $this->ajaxSaveCare();
                break;

            default:
                $this->ajaxError('Unknown action.');
                break;
        }
    }

    /**
     * Handle the save_care AJAX action.
     * Reads care card fields from POST, validates, and delegates to the module.
     */
    private function ajaxSaveCare()
    {
        $idProduct = (int) Tools::getValue('id_product');

        if (!$idProduct) {
            $this->ajaxError('Invalid product ID.');
        }

        // Verify that the product actually exists.
        if (!Product::existsInDatabase($idProduct, 'product')) {
            $this->ajaxError('Product not found.');
        }

        $allowedFields = [
            'light',
            'water_type',
            'water_method',
            'humidity',
            'temperature',
            'media',
            'feed',
            'dormancy',
            'potting',
            'problems',
        ];

        $data = [];
        foreach ($allowedFields as $field) {
            $data[$field] = (string) Tools::getValue($field, '');
        }

        /** @var Phyto_Care_Card $module */
        $module = Module::getInstanceByName('phyto_care_card');

        if (!$module || !($module instanceof Phyto_Care_Card)) {
            $this->ajaxError('Module not available.');
        }

        $saved = $module->saveCareData($idProduct, $data);

        if ($saved) {
            $this->ajaxSuccess(['success' => true]);
        } else {
            $this->ajaxError('Failed to save care card data.');
        }
    }

    /**
     * Output a JSON success response and exit.
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
     * Output a JSON error response and exit.
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
     * Suppress the default view rendering — this is a hidden AJAX controller.
     */
    public function display()
    {
        // Intentionally empty: no HTML is rendered by this controller.
    }

    /**
     * Required by PrestaShop — return an empty string so the BO doesn't
     * try to display a list or form.
     */
    public function renderList()
    {
        return '';
    }
}
