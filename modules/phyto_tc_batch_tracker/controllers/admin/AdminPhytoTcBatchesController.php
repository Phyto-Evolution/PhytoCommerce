<?php
/**
 * AdminPhytoTcBatchesController
 *
 * Back-office CRUD for tissue-culture batch records.
 * Uses HelperList for the list view and HelperForm for create/edit.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/classes/PhytoTcBatch.php';

class AdminPhytoTcBatchesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table      = 'phyto_tc_batch';
        $this->identifier = 'id_batch';
        $this->className  = 'PhytoTcBatch';
        $this->lang       = false;
        $this->bootstrap  = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->allow_export = true;

        parent::__construct();

        $this->meta_title = $this->l('TC Batches');

        $this->_select = 'a.`id_batch`';
        $this->_orderBy = 'id_batch';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_batch' => array(
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'orderby' => true,
            ),
            'batch_code' => array(
                'title'  => $this->l('Batch Code'),
                'filter_key' => 'a!batch_code',
                'orderby' => true,
            ),
            'species_name' => array(
                'title'  => $this->l('Species / Clone'),
                'filter_key' => 'a!species_name',
                'orderby' => true,
            ),
            'generation' => array(
                'title'  => $this->l('Generation'),
                'type'   => 'select',
                'list'   => PhytoTcBatch::getGenerationChoices(),
                'filter_key' => 'a!generation',
                'orderby' => true,
            ),
            'date_deflask' => array(
                'title'  => $this->l('Deflask Date'),
                'type'   => 'date',
                'align'  => 'center',
                'orderby' => true,
            ),
            'units_produced' => array(
                'title'  => $this->l('Produced'),
                'align'  => 'center',
                'class'  => 'fixed-width-sm',
                'orderby' => true,
            ),
            'units_remaining' => array(
                'title'  => $this->l('Remaining'),
                'align'  => 'center',
                'class'  => 'fixed-width-sm',
                'orderby' => true,
            ),
            'batch_status' => array(
                'title'  => $this->l('Status'),
                'type'   => 'select',
                'list'   => PhytoTcBatch::getStatusChoices(),
                'filter_key' => 'a!batch_status',
                'orderby' => true,
                'callback' => 'renderStatusBadge',
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

    /**
     * Render a coloured status badge for the list view.
     *
     * @param string $value
     * @param array  $row
     *
     * @return string
     */
    public function renderStatusBadge($value, $row)
    {
        $classes = array(
            'Active'      => 'badge-success',
            'Depleted'    => 'badge-secondary',
            'Quarantined' => 'badge-danger',
            'Archived'    => 'badge-warning',
        );

        $class = isset($classes[$value]) ? $classes[$value] : 'badge-info';

        return '<span class="badge ' . $class . '">' . htmlspecialchars($value) . '</span>';
    }

    /**
     * Render the create/edit form using HelperForm.
     *
     * @return string
     */
    public function renderForm()
    {
        $generationOptions = array();
        foreach (PhytoTcBatch::getGenerationChoices() as $key => $label) {
            $generationOptions[] = array(
                'id'   => $key,
                'name' => $label,
            );
        }

        $statusOptions = array();
        foreach (PhytoTcBatch::getStatusChoices() as $key => $label) {
            $statusOptions[] = array(
                'id'   => $key,
                'name' => $label,
            );
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
                    'hint'     => $this->l('Auto-suggested as YYYYMM-GENUS-SEQ. You may edit it.'),
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
                    'label'   => $this->l('Generation'),
                    'name'    => 'generation',
                    'required' => true,
                    'options' => array(
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
                    'label' => $this->l('Sterility Protocol Used'),
                    'name'  => 'sterility_protocol',
                    'rows'  => 6,
                    'cols'  => 60,
                    'desc'  => $this->l('Describe the sterilization and culture protocol applied to this batch.'),
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Units Produced'),
                    'name'  => 'units_produced',
                    'class' => 'fixed-width-sm',
                    'validation' => 'isUnsignedInt',
                ),
                array(
                    'type'  => 'text',
                    'label' => $this->l('Units Remaining'),
                    'name'  => 'units_remaining',
                    'class' => 'fixed-width-sm',
                    'validation' => 'isUnsignedInt',
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
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Auto-suggest batch code for new records
        if (!$this->object || !$this->object->id) {
            $this->fields_value['batch_code'] = PhytoTcBatch::suggestBatchCode();
        }

        return parent::renderForm();
    }

    /**
     * Set default values for date fields on new records.
     *
     * @return void
     */
    public function initProcess()
    {
        parent::initProcess();
    }

    /**
     * Toolbar title.
     *
     * @return void
     */
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
}
