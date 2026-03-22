<?php
/**
 * AdminPhytoTcContaminationController
 *
 * CRUD controller for contamination incident logs.
 * Accessed as a hidden tab; typically opened from the batch edit form.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcBatch.php';
require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcContaminationLog.php';

class AdminPhytoTcContaminationController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table      = 'phyto_tc_contamination_log';
        $this->identifier = 'id_log';
        $this->className  = 'PhytoTcContaminationLog';
        $this->lang       = false;
        $this->bootstrap  = true;

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->meta_title = $this->l('Contamination Log');
        $this->_orderBy   = 'incident_date';
        $this->_orderWay  = 'DESC';

        // Pre-filter by id_batch if passed in URL
        $idBatch = (int) Tools::getValue('id_batch');
        if ($idBatch) {
            $this->_where = ' AND a.`id_batch` = ' . $idBatch;
        }

        $this->fields_list = array(
            'id_log' => array(
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
            ),
            'id_batch' => array(
                'title'      => $this->l('Batch'),
                'filter_key' => 'a!id_batch',
                'callback'   => 'renderBatchCode',
            ),
            'incident_date' => array(
                'title'  => $this->l('Incident Date'),
                'type'   => 'date',
                'orderby' => true,
            ),
            'type' => array(
                'title'      => $this->l('Type'),
                'type'       => 'select',
                'list'       => PhytoTcContaminationLog::getTypeChoices(),
                'filter_key' => 'a!type',
                'callback'   => 'renderTypeBadge',
            ),
            'affected_units' => array(
                'title'  => $this->l('Affected Units'),
                'align'  => 'center',
                'class'  => 'fixed-width-sm',
            ),
            'resolved' => array(
                'title'      => $this->l('Resolved'),
                'align'      => 'center',
                'type'       => 'bool',
                'filter_key' => 'a!resolved',
                'callback'   => 'renderResolvedBadge',
            ),
        );

        $this->bulk_actions = array(
            'delete' => array(
                'text'    => $this->l('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->l('Delete selected logs?'),
            ),
        );
    }

    // -------------------------------------------------------------------------
    // List callbacks
    // -------------------------------------------------------------------------

    public function renderBatchCode($value, $row)
    {
        $code = Db::getInstance()->getValue(
            'SELECT `batch_code` FROM `' . _DB_PREFIX_ . 'phyto_tc_batch`
             WHERE `id_batch` = ' . (int) $value
        );

        if (!$code) {
            return (int) $value;
        }

        $editUrl = $this->context->link->getAdminLink('AdminPhytoTcBatches')
            . '&id_batch=' . (int) $value . '&updatephyto_tc_batch=1';

        return '<a href="' . $editUrl . '">' . htmlspecialchars($code) . '</a>';
    }

    public function renderTypeBadge($value, $row)
    {
        $map = array(
            'Bacterial' => 'badge-danger',
            'Fungal'    => 'badge-warning',
            'Viral'     => 'badge-danger',
            'Pest'      => 'badge-warning',
            'Unknown'   => 'badge-secondary',
            'Other'     => 'badge-info',
        );
        $class = isset($map[$value]) ? $map[$value] : 'badge-secondary';

        return '<span class="badge ' . $class . '">' . htmlspecialchars($value) . '</span>';
    }

    public function renderResolvedBadge($value, $row)
    {
        return (int) $value
            ? '<span class="badge badge-success">' . $this->l('Yes') . '</span>'
            : '<span class="badge badge-danger">'  . $this->l('No')  . '</span>';
    }

    // -------------------------------------------------------------------------
    // renderForm
    // -------------------------------------------------------------------------

    public function renderForm()
    {
        // Build batch options
        $allBatches   = PhytoTcBatch::getAllForDropdown();
        $batchOptions = array();
        foreach ($allBatches as $b) {
            $batchOptions[] = array(
                'id'   => $b['id_batch'],
                'name' => $b['batch_code'] . ' — ' . $b['species_name'],
            );
        }

        $typeOptions = array();
        foreach (PhytoTcContaminationLog::getTypeChoices() as $key => $label) {
            $typeOptions[] = array('id' => $key, 'name' => $label);
        }

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->l('Contamination Incident'),
                'icon'  => 'icon-warning-sign',
            ),
            'input' => array(
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Batch'),
                    'name'     => 'id_batch',
                    'required' => true,
                    'options'  => array(
                        'query' => $batchOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'     => 'date',
                    'label'    => $this->l('Incident Date'),
                    'name'     => 'incident_date',
                    'required' => true,
                ),
                array(
                    'type'    => 'select',
                    'label'   => $this->l('Contamination Type'),
                    'name'    => 'type',
                    'options' => array(
                        'query' => $typeOptions,
                        'id'    => 'id',
                        'name'  => 'name',
                    ),
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Affected Units'),
                    'name'  => 'affected_units',
                    'class' => 'fixed-width-sm',
                    'desc'  => $this->l('Number of units affected or discarded.'),
                ),
                array(
                    'type'  => 'textarea',
                    'label' => $this->l('Description'),
                    'name'  => 'description',
                    'rows'  => 5,
                    'cols'  => 60,
                    'desc'  => $this->l('Observations, root cause analysis, corrective actions taken.'),
                ),
                array(
                    'type'    => 'switch',
                    'label'   => $this->l('Resolved'),
                    'name'    => 'resolved',
                    'is_bool' => true,
                    'values'  => array(
                        array('id' => 'res_on',  'value' => 1, 'label' => $this->l('Yes')),
                        array('id' => 'res_off', 'value' => 0, 'label' => $this->l('No')),
                    ),
                ),
            ),
            'submit' => array('title' => $this->l('Save')),
        );

        // Pre-select batch when coming from a batch edit page
        if (!$this->object || !$this->object->id) {
            $preSelectBatch = (int) Tools::getValue('id_batch');
            if ($preSelectBatch) {
                $this->fields_value['id_batch'] = $preSelectBatch;
            }
            $this->fields_value['incident_date'] = date('Y-m-d');
            $this->fields_value['resolved']      = 0;
        }

        return parent::renderForm();
    }

    // -------------------------------------------------------------------------
    // Toolbar
    // -------------------------------------------------------------------------

    public function initPageHeaderToolbar()
    {
        if (empty($this->display) || $this->display === 'list') {
            $this->page_header_toolbar_btn['new_log'] = array(
                'href' => self::$currentIndex . '&add' . $this->table . '&token=' . $this->token,
                'desc' => $this->l('Log New Incident'),
                'icon' => 'process-icon-new',
            );
        }

        parent::initPageHeaderToolbar();
    }

    // -------------------------------------------------------------------------
    // AJAX: quick mark-resolved from the batch edit panel
    // -------------------------------------------------------------------------

    public function ajaxProcessResolveLog()
    {
        $idLog = (int) Tools::getValue('id_log');

        if (!$idLog) {
            $this->ajaxDie(json_encode(array('success' => false, 'message' => 'Invalid ID.')));
        }

        $log = new PhytoTcContaminationLog($idLog);
        if (!Validate::isLoadedObject($log)) {
            $this->ajaxDie(json_encode(array('success' => false, 'message' => 'Log not found.')));
        }

        $log->resolved = 1;
        $result = $log->save();

        $this->ajaxDie(json_encode(array(
            'success' => (bool) $result,
            'message' => $result ? $this->l('Marked as resolved.') : $this->l('Save failed.'),
        )));
    }
}
