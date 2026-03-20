<?php
/**
 * AdminPhytoTcCostCalcController
 *
 * Back-office controller for the Phyto TC Cost Calculator module.
 * Renders the interactive calculator form and manages saved estimates.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoTcCostCalcController extends ModuleAdminController
{
    /** @var string DB table name (without prefix) */
    const TABLE = 'phyto_tc_cost_estimate';

    public function __construct()
    {
        parent::__construct();

        $this->bootstrap    = true;
        $this->display      = 'view';
        $this->meta_title   = $this->l('TC Cost Calculator');
    }

    // -------------------------------------------------------------------------
    // Toolbar / page title
    // -------------------------------------------------------------------------

    public function initPageHeaderToolbar(): void
    {
        $this->page_header_toolbar_title = $this->l('TC Cost Calculator');
        $this->page_header_toolbar_btn['save_estimate'] = [
            'href'   => '#phyto-save-section',
            'desc'   => $this->l('Save estimate'),
            'icon'   => 'process-icon-save',
        ];

        parent::initPageHeaderToolbar();
    }

    // -------------------------------------------------------------------------
    // Content initialisation
    // -------------------------------------------------------------------------

    public function initContent(): void
    {
        parent::initContent();

        // Register module assets
        $moduleDir = $this->module->getPathUri();

        $this->context->controller->addCSS($moduleDir . 'views/css/calculator.css');
        $this->context->controller->addJS($moduleDir . 'views/js/calculator.js');

        // Load saved estimates (newest first)
        $savedEstimates = $this->getSavedEstimates();

        // Assign Smarty variables
        $this->context->smarty->assign([
            'module_dir'      => $moduleDir,
            'saved_estimates' => $savedEstimates,
            'form_action'     => $this->context->link->getAdminLink('AdminPhytoTcCostCalc'),
            'token'           => Tools::getAdminTokenLite('AdminPhytoTcCostCalc'),
        ]);

        // Fetch the template and inject into PS BO layout
        $templatePath = _PS_MODULE_DIR_
            . 'phyto_tc_cost_calculator/views/templates/admin/calculator.tpl';

        $this->content = $this->context->smarty->fetch($templatePath);
    }

    // -------------------------------------------------------------------------
    // POST processing
    // -------------------------------------------------------------------------

    public function postProcess(): void
    {
        $action = Tools::getValue('action');

        if ($action === 'save_estimate') {
            $this->processSaveEstimate();
        } elseif ($action === 'delete_estimate') {
            $this->processDeleteEstimate();
        }

        parent::postProcess();
    }

    // -------------------------------------------------------------------------
    // Save estimate
    // -------------------------------------------------------------------------

    protected function processSaveEstimate(): void
    {
        $label      = pSQL(trim((string) Tools::getValue('estimate_label', '')));
        $idBatch    = (int) Tools::getValue('id_batch', 0);
        $inputsJson = Tools::getValue('inputs_json', '{}');
        $resultsJson = Tools::getValue('results_json', '{}');

        // Validate label
        if (empty($label)) {
            $this->errors[] = $this->l('Please enter an estimate label.');
            return;
        }

        // Validate JSON strings
        if (!$this->isValidJson($inputsJson)) {
            $this->errors[] = $this->l('Calculator inputs data is invalid.');
            return;
        }

        if (!$this->isValidJson($resultsJson)) {
            $this->errors[] = $this->l('Calculator results data is invalid.');
            return;
        }

        $inserted = Db::getInstance()->insert(
            self::TABLE,
            [
                'id_batch'       => $idBatch,
                'estimate_label' => $label,
                'inputs_json'    => pSQL($inputsJson, true),
                'results_json'   => pSQL($resultsJson, true),
                'date_add'       => date('Y-m-d H:i:s'),
            ]
        );

        if ($inserted) {
            $this->confirmations[] = $this->l('Estimate saved successfully.');
        } else {
            $this->errors[] = $this->l('Could not save estimate. Please try again.');
        }
    }

    // -------------------------------------------------------------------------
    // Delete estimate
    // -------------------------------------------------------------------------

    protected function processDeleteEstimate(): void
    {
        $idEstimate = (int) Tools::getValue('id_estimate', 0);

        if ($idEstimate <= 0) {
            $this->errors[] = $this->l('Invalid estimate ID.');
            return;
        }

        $deleted = Db::getInstance()->delete(
            self::TABLE,
            'id_estimate = ' . $idEstimate
        );

        if ($deleted) {
            $this->confirmations[] = $this->l('Estimate deleted.');
        } else {
            $this->errors[] = $this->l('Could not delete estimate.');
        }
    }

    // -------------------------------------------------------------------------
    // Data helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve all saved estimates ordered by date descending.
     *
     * @return array
     */
    protected function getSavedEstimates(): array
    {
        $rows = Db::getInstance()->executeS(
            'SELECT `id_estimate`, `id_batch`, `estimate_label`,
                    `inputs_json`, `results_json`, `date_add`
               FROM `' . _DB_PREFIX_ . self::TABLE . '`
           ORDER BY `date_add` DESC'
        );

        if (!is_array($rows)) {
            return [];
        }

        // Decode JSON blobs for convenient use in the template
        foreach ($rows as &$row) {
            $row['inputs']  = json_decode($row['inputs_json'],  true) ?: [];
            $row['results'] = json_decode($row['results_json'], true) ?: [];
        }
        unset($row);

        return $rows;
    }

    /**
     * Quick JSON validity check.
     */
    protected function isValidJson(string $str): bool
    {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
