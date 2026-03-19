<?php
/**
 * AdminPhytoGrowthStageProductController — Hidden AJAX controller
 * for managing product ↔ growth-stage assignments from the product edit page.
 *
 * @author    PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_growth_stage/classes/PhytoGrowthStageDef.php';

class AdminPhytoGrowthStageProductController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * Handle AJAX requests.
     */
    public function ajaxProcessSaveStageMapping()
    {
        $idProduct          = (int) Tools::getValue('id_product');
        $idProductAttribute = (int) Tools::getValue('id_product_attribute');
        $idStage            = (int) Tools::getValue('id_stage');
        $weeksOverride      = Tools::getValue('weeks_override');

        if (!$idProduct) {
            $this->ajaxResponse(false, $this->l('Invalid product ID.'));
            return;
        }

        // id_stage = 0 means "remove mapping"
        if (!$idStage) {
            $result = PhytoGrowthStageDef::removeStageFromProduct($idProduct, $idProductAttribute);
            $this->ajaxResponse($result, $result ? $this->l('Stage mapping removed.') : $this->l('Error removing mapping.'));
            return;
        }

        $weeksOverrideValue = ($weeksOverride !== '' && $weeksOverride !== null)
            ? (int) $weeksOverride
            : null;

        $result = PhytoGrowthStageDef::assignStageToProduct(
            $idProduct,
            $idProductAttribute,
            $idStage,
            $weeksOverrideValue
        );

        $this->ajaxResponse($result, $result ? $this->l('Stage mapping saved.') : $this->l('Error saving mapping.'));
    }

    /**
     * Handle AJAX request to fetch current mappings for a product.
     */
    public function ajaxProcessGetMappings()
    {
        $idProduct = (int) Tools::getValue('id_product');

        if (!$idProduct) {
            $this->ajaxResponse(false, $this->l('Invalid product ID.'));
            return;
        }

        $mappings = PhytoGrowthStageDef::getStagesForProduct($idProduct);

        $this->ajaxDie(json_encode([
            'success'  => true,
            'mappings' => $mappings,
        ]));
    }

    /**
     * Standard AJAX processing entry point.
     */
    public function postProcess()
    {
        if (Tools::isSubmit('ajax')) {
            $action = Tools::getValue('action');

            if ($action === 'saveStageMapping') {
                $this->ajaxProcessSaveStageMapping();
            } elseif ($action === 'getMappings') {
                $this->ajaxProcessGetMappings();
            }
        }

        return parent::postProcess();
    }

    /**
     * Send a JSON AJAX response and terminate.
     *
     * @param bool   $success
     * @param string $message
     */
    private function ajaxResponse($success, $message)
    {
        $this->ajaxDie(json_encode([
            'success' => (bool) $success,
            'message' => $message,
        ]));
    }
}
