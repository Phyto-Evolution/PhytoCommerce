<?php
/**
 * AdminPhytoTcBatchProductController
 *
 * Hidden admin controller that handles AJAX requests from the product-edit
 * batch-linking tab. Supports linking, unlinking, and fetching batch details.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcBatch.php';

class AdminPhytoTcBatchProductController extends ModuleAdminController
{
    /** @var bool */
    public $ajax = true;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    /**
     * Route AJAX actions.
     */
    public function initContent()
    {
        parent::initContent();

        // Fallback for non-AJAX calls
        if (!$this->ajax) {
            $this->ajaxProcessDefault();
        }
    }

    /**
     * AJAX: Link a product to a batch.
     */
    public function ajaxProcessLinkBatch()
    {
        $idProduct = (int) Tools::getValue('id_product');
        $idBatch   = (int) Tools::getValue('id_batch');

        if (!$idProduct || !$idBatch) {
            $this->ajaxResponse(false, $this->l('Invalid product or batch ID.'));
            return;
        }

        $batch = new PhytoTcBatch($idBatch);
        if (!Validate::isLoadedObject($batch)) {
            $this->ajaxResponse(false, $this->l('Batch not found.'));
            return;
        }

        $result = PhytoTcBatch::linkProduct($idBatch, $idProduct, 0);

        if ($result) {
            $generations = PhytoTcBatch::getGenerationChoices();
            $batchData = array(
                'id_batch'            => $batch->id,
                'batch_code'          => $batch->batch_code,
                'species_name'        => $batch->species_name,
                'generation'          => $batch->generation,
                'generation_label'    => isset($generations[$batch->generation]) ? $generations[$batch->generation] : $batch->generation,
                'date_deflask'        => $batch->date_deflask ? Tools::displayDate($batch->date_deflask) : '',
                'date_certified'      => $batch->date_certified ? Tools::displayDate($batch->date_certified) : '',
                'sterility_protocol'  => $batch->sterility_protocol,
                'units_remaining'     => $batch->units_remaining,
                'batch_status'        => $batch->batch_status,
            );

            $this->ajaxResponse(true, $this->l('Batch linked successfully.'), $batchData);
        } else {
            $this->ajaxResponse(false, $this->l('Failed to link batch.'));
        }
    }

    /**
     * AJAX: Unlink a product from its batch.
     */
    public function ajaxProcessUnlinkBatch()
    {
        $idProduct = (int) Tools::getValue('id_product');

        if (!$idProduct) {
            $this->ajaxResponse(false, $this->l('Invalid product ID.'));
            return;
        }

        $result = PhytoTcBatch::unlinkProduct($idProduct, 0);

        $this->ajaxResponse($result, $result
            ? $this->l('Batch unlinked successfully.')
            : $this->l('Failed to unlink batch.')
        );
    }

    /**
     * AJAX: Get batch details by ID.
     */
    public function ajaxProcessGetBatch()
    {
        $idBatch = (int) Tools::getValue('id_batch');

        if (!$idBatch) {
            $this->ajaxResponse(false, $this->l('Invalid batch ID.'));
            return;
        }

        $batch = new PhytoTcBatch($idBatch);
        if (!Validate::isLoadedObject($batch)) {
            $this->ajaxResponse(false, $this->l('Batch not found.'));
            return;
        }

        $generations = PhytoTcBatch::getGenerationChoices();

        $batchData = array(
            'id_batch'            => $batch->id,
            'batch_code'          => $batch->batch_code,
            'species_name'        => $batch->species_name,
            'generation'          => $batch->generation,
            'generation_label'    => isset($generations[$batch->generation]) ? $generations[$batch->generation] : $batch->generation,
            'date_initiation'     => $batch->date_initiation ? Tools::displayDate($batch->date_initiation) : '',
            'date_deflask'        => $batch->date_deflask ? Tools::displayDate($batch->date_deflask) : '',
            'date_certified'      => $batch->date_certified ? Tools::displayDate($batch->date_certified) : '',
            'sterility_protocol'  => $batch->sterility_protocol,
            'units_produced'      => $batch->units_produced,
            'units_remaining'     => $batch->units_remaining,
            'batch_status'        => $batch->batch_status,
            'notes'               => $batch->notes,
        );

        $this->ajaxResponse(true, '', $batchData);
    }

    /**
     * AJAX: Suggest a batch code based on species name.
     */
    public function ajaxProcessSuggestCode()
    {
        $speciesName = Tools::getValue('species_name', '');
        $code = PhytoTcBatch::suggestBatchCode($speciesName);

        $this->ajaxResponse(true, '', array('batch_code' => $code));
    }

    /**
     * Send a JSON response.
     *
     * @param bool   $success
     * @param string $message
     * @param array  $data
     */
    private function ajaxResponse($success, $message = '', $data = array())
    {
        header('Content-Type: application/json');
        die(json_encode(array(
            'success' => (bool) $success,
            'message' => $message,
            'data'    => $data,
        )));
    }
}
