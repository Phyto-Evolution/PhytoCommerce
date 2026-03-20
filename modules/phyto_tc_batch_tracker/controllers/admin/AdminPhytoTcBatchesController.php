<?php
/**
 * AdminPhytoTcBatchesController
 *
 * Back-office CRUD for tissue-culture batch records.
 * v1.1: added parent batch (lineage), contamination log panel,
 *       Print Label row action, low-stock badge in list.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcBatch.php';
require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcContaminationLog.php';

class AdminPhytoTcBatchesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table      = 'phyto_tc_batch';
        $this->identifier = 'id_batch';
        $this->className  = 'PhytoTcBatch';
        $this->lang       = false;
        $this->bootstrap  = true;
        $this->allow_export = true;

        $this->addRowAction('edit');
        $this->addRowAction('printlabel');
        $this->addRowAction('delete');

        parent::__construct();

        $this->meta_title  = $this->l('TC Batches');
        $this->_orderBy    = 'id_batch';
        $this->_orderWay   = 'DESC';

        $threshold = (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10);

        $this->_select = '
            a.`id_batch`,
            IF(a.`units_remaining` <= ' . $threshold . " AND a.`batch_status` = 'Active', 1, 0) AS `is_low_stock`,
            (SELECT COUNT(*) FROM `" . _DB_PREFIX_ . "phyto_tc_contamination_log` cl
             WHERE cl.`id_batch` = a.`id_batch` AND cl.`resolved` = 0) AS `open_incidents`";

        $this->fields_list = array(
            'id_batch' => array(
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'orderby' => true,
            ),
            'batch_code' => array(
                'title'      => $this->l('Batch Code'),
                'filter_key' => 'a!batch_code',
                'orderby'    => true,
            ),
            'species_name' => array(
                'title'      => $this->l('Species / Clone'),
                'filter_key' => 'a!species_name',
                'orderby'    => true,
            ),
            'generation' => array(
                'title'      => $this->l('Generation'),
                'type'       => 'select',
                'list'       => PhytoTcBatch::getGenerationChoices(),
                'filter_key' => 'a!generation',
                'orderby'    => true,
            ),
            'date_deflask' => array(
                'title'  => $this->l('Deflask Date'),
                'type'   => 'date',
                'align'  => 'center',
                'orderby' => true,
            ),
            'units_remaining' => array(
                'title'    => $this->l('Remaining'),
                'align'    => 'center',
                'class'    => 'fixed-width-sm',
                'orderby'  => true,
                'callback' => 'renderUnitsRemaining',
            ),
            'open_incidents' => array(
                'title'    => $this->l('Incidents'),
                'align'    => 'center',
                'class'    => 'fixed-width-xs',
                'orderby'  => false,
                'search'   => false,
                'callback' => 'renderIncidentsBadge',
            ),
            'batch_status' => array(
                'title'      => $this->l('Status'),
                'type'       => 'select',
                'list'       => PhytoTcBatch::getStatusChoices(),
                'filter_key' => 'a!batch_status',
                'orderby'    => true,
                'callback'   => 'renderStatusBadge',
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->l('Delete selected batches?'),
            ),
        );
    }

    // -------------------------------------------------------------------------
    // List callbacks
    // -------------------------------------------------------------------------

    public function renderStatusBadge($value, $row)
    {
        $map = array(
            'Active'      => 'badge-success',
            'Depleted'    => 'badge-secondary',
            'Quarantined' => 'badge-danger',
            'Archived'    => 'badge-warning',
        );
        $class = isset($map[$value]) ? $map[$value] : 'badge-info';

        return '<span class="badge ' . $class . '">' . htmlspecialchars($value) . '</span>';
    }

    public function renderUnitsRemaining($value, $row)
    {
        $threshold = (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10);
        $units     = (int) $value;

        if ((int) $row['is_low_stock']) {
            return '<span class="badge badge-danger" title="' . $this->l('Low stock') . '">'
                . $units . '</span>';
        }

        return $units;
    }

    public function renderIncidentsBadge($value, $row)
    {
        $count = (int) $value;

        if ($count === 0) {
            return '<span class="text-muted">—</span>';
        }

        return '<span class="badge badge-danger">' . $count . '</span>';
    }

    // -------------------------------------------------------------------------
    // Custom row action: Print Label
    // -------------------------------------------------------------------------

    public function displayPrintlabelLink($token, $id, $name = null)
    {
        $printUrl = $this->context->link->getAdminLink('AdminPhytoTcBatches')
            . '&id_batch=' . (int) $id . '&action=printLabel';

        return '<a href="' . $printUrl . '" target="_blank" class="btn btn-default btn-xs"
                   title="' . $this->l('Print Label') . '">
                   <i class="icon-print"></i> ' . $this->l('Print') . '
               </a>';
    }

    // -------------------------------------------------------------------------
    // postProcess: intercept printLabel action
    // -------------------------------------------------------------------------

    public function postProcess()
    {
        if (Tools::getValue('action') === 'printLabel') {
            $idBatch = (int) Tools::getValue('id_batch');
            $batch   = new PhytoTcBatch($idBatch);

            if (!Validate::isLoadedObject($batch)) {
                $this->errors[] = $this->l('Batch not found.');
                parent::postProcess();
                return;
            }

            $lineage   = PhytoTcBatch::getLineageChain($idBatch);
            $printUrl  = $this->context->link->getAdminLink('AdminPhytoTcBatches');

            $this->context->smarty->assign(array(
                'phyto_tc_batch'   => $batch,
                'phyto_tc_lineage' => $lineage,
                'phyto_tc_print_back_url' => $printUrl,
            ));

            // Stream the print-label page directly
            $tplPath = _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/views/templates/admin/print_label.tpl';
            $tpl     = $this->context->smarty->createTemplate($tplPath);
            die($tpl->fetch());
        }

        parent::postProcess();
    }

    // -------------------------------------------------------------------------
    // renderForm: add parent batch + contamination log panel
    // -------------------------------------------------------------------------

    public function renderForm()
    {
        $generationOptions = array();
        foreach (PhytoTcBatch::getGenerationChoices() as $key => $label) {
            $generationOptions[] = array('id' => $key, 'name' => $label);
        }

        $statusOptions = array();
        foreach (PhytoTcBatch::getStatusChoices() as $key => $label) {
            $statusOptions[] = array('id' => $key, 'name' => $label);
        }

        // Parent batch dropdown (exclude current batch to prevent self-reference)
        $currentId      = (int) Tools::getValue('id_batch');
        $allBatches     = PhytoTcBatch::getAllForDropdown();
        $parentOptions  = array(array('id' => 0, 'name' => '— ' . $this->l('None (root batch)') . ' —'));
        foreach ($allBatches as $b) {
            if ((int) $b['id_batch'] !== $currentId) {
                $parentOptions[] = array(
                    'id'   => $b['id_batch'],
                    'name' => $b['batch_code'] . ' — ' . $b['species_name'],
                );
            }
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Batch Record'),
                'icon'  => 'icon-leaf',
            ),
            'input' => array(
                array(
                    'type'     => 'text',
                    'label'    => $this->l('Batch Code'),
                    'name'     => 'batch_code',
                    'required' => true,
                    'size'     => 50,
                    'hint'     => $this->l('Auto-suggested as YYYYMM-GENUS-SEQ.'),
                    'desc'     => $this->l('Unique identifier for this batch.'),
                ),
                array(
                    'type'     => 'text',
                    'label'    => $this->l('Species / Clone Name'),
                    'name'     => 'species_name',
                    'required' => true,
                    'size'     => 200,
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Mother Batch (Parent)'),
                    'name'    => 'parent_id_batch',
                    'desc'    => $this->l('Select the batch this one was propagated from to build the lineage chain.'),
                    'options' => array(
                        'query' => $parentOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Generation'),
                    'name'     => 'generation',
                    'required' => true,
                    'options'  => array(
                        'query' => $generationOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'  => 'date',
                    'label' => $this->l('Initiation Date'),
                    'name'  => 'date_initiation',
                ),
                array(
                    'type'  => 'date',
                    'label' => $this->l('Deflask Date'),
                    'name'  => 'date_deflask',
                ),
                array(
                    'type'  => 'date',
                    'label' => $this->l('Certification Date'),
                    'name'  => 'date_certified',
                ),
                array(
                    'type'  => 'textarea',
                    'label' => $this->l('Sterility Protocol'),
                    'name'  => 'sterility_protocol',
                    'rows'  => 6,
                    'cols'  => 60,
                    'desc'  => $this->l('Describe the sterilization and culture protocol applied.'),
                ),
                array(
                    'type'       => 'text',
                    'label'      => $this->l('Units Produced'),
                    'name'       => 'units_produced',
                    'class'      => 'fixed-width-sm',
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type'       => 'text',
                    'label'      => $this->l('Units Remaining'),
                    'name'       => 'units_remaining',
                    'class'      => 'fixed-width-sm',
                    'validation' => 'isUnsignedInt',
                    'desc'       => $this->l('Decremented automatically on shipment if auto-decrement is enabled.'),
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Batch Status'),
                    'name'    => 'batch_status',
                    'options' => array(
                        'query' => $statusOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'  => 'textarea',
                    'label' => $this->l('Internal Notes'),
                    'name'  => 'notes',
                    'rows'  => 4,
                    'cols'  => 60,
                ),
            ),
            'submit' => array('title' => $this->l('Save')),
        );

        // Auto-suggest batch code for new records
        if (!$this->object || !$this->object->id) {
            $this->fields_value['batch_code']    = PhytoTcBatch::suggestBatchCode();
            $this->fields_value['parent_id_batch'] = 0;
        } else {
            $this->fields_value['parent_id_batch'] = (int) $this->object->parent_id_batch;
        }

        $html = parent::renderForm();

        // Append contamination log panel for existing batches
        if ($this->object && $this->object->id) {
            $html .= $this->renderContaminationPanel((int) $this->object->id);
        }

        return $html;
    }

    // -------------------------------------------------------------------------
    // Contamination log panel (appended below the main form)
    // -------------------------------------------------------------------------

    private function renderContaminationPanel($idBatch)
    {
        $logs       = PhytoTcContaminationLog::getByBatch($idBatch);
        $typeChoices = PhytoTcContaminationLog::getTypeChoices();
        $contamUrl  = $this->context->link->getAdminLink('AdminPhytoTcContamination');

        $this->context->smarty->assign(array(
            'phyto_tc_contam_logs'   => $logs,
            'phyto_tc_contam_types'  => $typeChoices,
            'phyto_tc_id_batch'      => $idBatch,
            'phyto_tc_contam_url'    => $contamUrl,
            'phyto_tc_batches_token' => $this->token,
        ));

        $tplPath = _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/views/templates/admin/contamination_panel.tpl';

        return $this->context->smarty->fetch($tplPath);
    }

    // -------------------------------------------------------------------------
    // Toolbar
    // -------------------------------------------------------------------------

    public function initPageHeaderToolbar()
    {
        if (empty($this->display) || $this->display === 'list') {
            $this->page_header_toolbar_btn['new_batch'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Add New Batch'),
                'icon' => 'process-icon-new',
            );
        }

        parent::initPageHeaderToolbar();
    }

    // -------------------------------------------------------------------------
    // Override delete to cascade contamination logs
    // -------------------------------------------------------------------------

    public function processDelete()
    {
        $idBatch = (int) Tools::getValue($this->identifier);
        PhytoTcContaminationLog::deleteByBatch($idBatch);
        PhytoTcBatch::unlinkProduct(0, 0); // noop — product links have no id_batch FK cascade

        // Remove product links for this batch
        Db::getInstance()->delete('phyto_tc_batch_product', '`id_batch` = ' . $idBatch);

        return parent::processDelete();
    }
}
