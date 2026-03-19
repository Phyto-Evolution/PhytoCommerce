<?php
/**
 * AdminPhytoGrowthStagesController — CRUD for global growth-stage definitions.
 *
 * @author    PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_growth_stage/classes/PhytoGrowthStageDef.php';

class AdminPhytoGrowthStagesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table      = 'phyto_growth_stage_def';
        $this->identifier = 'id_stage';
        $this->className  = 'PhytoGrowthStageDef';
        $this->lang       = false;
        $this->bootstrap  = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->_defaultOrderBy    = 'sort_order';
        $this->_defaultOrderWay   = 'ASC';

        parent::__construct();

        $this->meta_title = $this->l('Growth Stages');

        $this->fields_list = [
            'id_stage' => [
                'title'  => $this->l('ID'),
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'orderby' => true,
            ],
            'stage_name' => [
                'title'  => $this->l('Stage Name'),
                'filter_key' => 'a!stage_name',
            ],
            'stage_code' => [
                'title' => $this->l('Code'),
            ],
            'difficulty' => [
                'title'  => $this->l('Difficulty'),
                'type'   => 'select',
                'list'   => [
                    'Beginner'     => $this->l('Beginner'),
                    'Intermediate' => $this->l('Intermediate'),
                    'Advanced'     => $this->l('Advanced'),
                    'Expert'       => $this->l('Expert'),
                ],
                'filter_key'  => 'a!difficulty',
                'filter_type' => 'text',
                'orderby'     => true,
            ],
            'weeks_to_next' => [
                'title' => $this->l('Weeks to Next'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ],
            'sort_order' => [
                'title' => $this->l('Sort Order'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete the selected items?'),
                'icon'    => 'icon-trash',
            ],
        ];
    }

    /**
     * Render the add/edit form using HelperForm.
     *
     * @return string
     */
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Growth Stage'),
                'icon'  => 'icon-leaf',
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Stage Name'),
                    'name'     => 'stage_name',
                    'required' => true,
                    'hint'     => $this->l('e.g. Protocorm, Deflasked, Hardened, Juvenile, Mature Pitcher'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Stage Code'),
                    'name'  => 'stage_code',
                    'hint'  => $this->l('Auto-generated from name if left blank. Must be unique.'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Care Difficulty'),
                    'name'    => 'difficulty',
                    'required' => true,
                    'options' => [
                        'query' => [
                            ['id' => 'Beginner',     'name' => $this->l('Beginner')],
                            ['id' => 'Intermediate', 'name' => $this->l('Intermediate')],
                            ['id' => 'Advanced',     'name' => $this->l('Advanced')],
                            ['id' => 'Expert',       'name' => $this->l('Expert')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Weeks to Next Stage'),
                    'name'  => 'weeks_to_next',
                    'class' => 'fixed-width-sm',
                    'hint'  => $this->l('Estimated weeks until the plant transitions to the next growth stage.'),
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Description'),
                    'name'  => 'description',
                    'cols'  => 60,
                    'rows'  => 6,
                    'hint'  => $this->l('A short description of this growth stage.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Sort Order'),
                    'name'  => 'sort_order',
                    'class' => 'fixed-width-sm',
                    'hint'  => $this->l('Lower numbers appear first in the progression bar.'),
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right',
            ],
        ];

        return parent::renderForm();
    }

    /**
     * Auto-generate stage_code from stage_name before validation.
     */
    public function postProcess()
    {
        if (Tools::isSubmit('submitAddphyto_growth_stage_def') || Tools::isSubmit('submitAddphyto_growth_stage_defAndStay')) {
            $code = Tools::getValue('stage_code');
            $name = Tools::getValue('stage_name');

            if (empty($code) && !empty($name)) {
                $_POST['stage_code'] = PhytoGrowthStageDef::generateCode($name);
            }
        }

        return parent::postProcess();
    }

    /**
     * Clean up product links when a stage is deleted.
     *
     * @param int $idStage
     *
     * @return bool
     */
    protected function afterDelete($object, $oldId)
    {
        PhytoGrowthStageDef::removeAllProductLinks((int) $oldId);

        return true;
    }
}
