<?php
/**
 * Phyto TC Cost Calculator
 *
 * Internal admin tool for pricing TC (Tissue Culture) batches.
 * Calculates substrate, overhead, and labor costs; derives
 * suggested retail prices at configurable gross-margin targets.
 *
 * @author    PhytoCommerce
 * @version   1.0.0
 * @license   Proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Tc_Cost_Calculator extends Module
{
    public function __construct()
    {
        $this->name            = 'phyto_tc_cost_calculator';
        $this->tab             = 'AdminCatalog';
        $this->version         = '1.0.0';
        $this->author          = 'PhytoCommerce';
        $this->need_instance   = 0;
        $this->bootstrap       = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto TC Cost Calculator');
        $this->description = $this->l(
            'Internal tool for pricing TC batches with cost and margin analysis.'
        );
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->installTab();
    }

    public function uninstall(): bool
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helper
    // -------------------------------------------------------------------------

    /**
     * Execute all statements from a bundled SQL file.
     *
     * @param  string $type  'install' or 'uninstall'
     * @return bool
     */
    protected function runSql(string $type): bool
    {
        $sqlFile = dirname(__FILE__) . '/sql/' . $type . '.sql';

        if (!file_exists($sqlFile)) {
            return false;
        }

        $sql = file_get_contents($sqlFile);

        if (empty(trim($sql))) {
            return true;
        }

        // Replace table prefix placeholder
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        // Split on semicolons to allow multi-statement files
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            static fn(string $s): bool => $s !== ''
        );

        foreach ($statements as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab management
    // -------------------------------------------------------------------------

    /**
     * Register the back-office navigation tab under Catalog.
     */
    protected function installTab(): bool
    {
        $parentId = (int) Tab::getIdFromClassName('AdminCatalog');
        if ($parentId <= 0) {
            $parentId = 2; // Fallback to legacy Catalog parent ID
        }

        $tab               = new Tab();
        $tab->active       = 1;
        $tab->class_name   = 'AdminPhytoTcCostCalc';
        $tab->module       = $this->name;
        $tab->id_parent    = $parentId;
        $tab->icon         = 'science'; // Material icon name shown in PS 8 sidebar

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = $this->l('TC Cost Calculator');
        }

        return (bool) $tab->add();
    }

    /**
     * Remove the back-office navigation tab.
     */
    protected function uninstallTab(): bool
    {
        $tabId = (int) Tab::getIdFromClassName('AdminPhytoTcCostCalc');

        if ($tabId <= 0) {
            return true; // Already gone
        }

        $tab = new Tab($tabId);

        return (bool) $tab->delete();
    }

    // -------------------------------------------------------------------------
    // No hooks registered — back-office only module
    // -------------------------------------------------------------------------
}
