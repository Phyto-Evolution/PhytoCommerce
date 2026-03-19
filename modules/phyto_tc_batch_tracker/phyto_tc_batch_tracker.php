<?php
/**
 * PhytoCommerce TC Batch Tracker
 *
 * Links tissue-culture products to propagation batch records.
 * Buyers see provenance on the front office; admins manage batch-grouped inventory.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoTcBatch.php';

class Phyto_Tc_Batch_Tracker extends Module
{
    public function __construct()
    {
        $this->name          = 'phyto_tc_batch_tracker';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('TC Batch Tracker');
        $this->description = $this->l('Link tissue-culture products to propagation batch records. Buyers see provenance; admin sees batch-grouped inventory.');

        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    /**
     * Module installation.
     *
     * @return bool
     */
    public function install()
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayHeader')
            && $this->installTab();
    }

    /**
     * Module uninstallation.
     *
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    /**
     * Execute an SQL file from the sql/ directory.
     *
     * @param string $filename  install or uninstall (without .sql)
     *
     * @return bool
     */
    private function runSql($filename)
    {
        $file = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        $statements = preg_split('/;\s*[\r\n]+/', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                if (!Db::getInstance()->execute($statement)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Install the admin menu tab under Catalog.
     *
     * @return bool
     */
    private function installTab()
    {
        $tab             = new Tab();
        $tab->class_name = 'AdminPhytoTcBatches';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->icon       = 'science';

        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $tab->name[$lang['id_lang']] = 'TC Batches';
        }

        if (!$tab->add()) {
            return false;
        }

        // Hidden controller tab for AJAX (no menu entry)
        $hiddenTab             = new Tab();
        $hiddenTab->class_name = 'AdminPhytoTcBatchProduct';
        $hiddenTab->module     = $this->name;
        $hiddenTab->id_parent  = -1; // hidden
        $hiddenTab->icon       = '';

        foreach ($languages as $lang) {
            $hiddenTab->name[$lang['id_lang']] = 'TC Batch Product Link';
        }

        return $hiddenTab->add();
    }

    /**
     * Remove admin menu tabs.
     *
     * @return bool
     */
    private function uninstallTab()
    {
        $controllers = array('AdminPhytoTcBatches', 'AdminPhytoTcBatchProduct');

        foreach ($controllers as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);
            if ($idTab) {
                $tab = new Tab($idTab);
                if (!$tab->delete()) {
                    return false;
                }
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Hooks
    // -------------------------------------------------------------------------

    /**
     * Add CSS/JS to the back office header.
     *
     * @param array $params
     */
    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('controller') === 'AdminProducts'
            || Tools::getValue('controller') === 'AdminPhytoTcBatches'
        ) {
            $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
            $this->context->controller->addJS($this->_path . 'views/js/admin.js');
        }
    }

    /**
     * Add CSS to the front office header.
     *
     * @param array $params
     */
    public function hookDisplayHeader($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/front.css');
    }

    /**
     * Display a batch-linking tab on the product edit page (back office).
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];

        $linkedBatch = PhytoTcBatch::getBatchByProduct($idProduct);
        $allBatches  = PhytoTcBatch::getAllForDropdown();

        $ajaxUrl = $this->context->link->getAdminLink('AdminPhytoTcBatchProduct');

        $this->context->smarty->assign(array(
            'phyto_tc_id_product'   => $idProduct,
            'phyto_tc_linked_batch' => $linkedBatch,
            'phyto_tc_all_batches'  => $allBatches,
            'phyto_tc_ajax_url'     => $ajaxUrl,
            'phyto_tc_generations'  => PhytoTcBatch::getGenerationChoices(),
            'phyto_tc_statuses'     => PhytoTcBatch::getStatusChoices(),
        ));

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /**
     * Display batch provenance information on the product page (front office).
     *
     * @param array $params
     *
     * @return array  PrestaShop\PrestaShop\Core\Product\ProductExtraContent[]
     */
    public function hookDisplayProductExtraContent($params)
    {
        $idProduct = (int) $params['product']->getId();
        $batch     = PhytoTcBatch::getBatchByProduct($idProduct);

        if (!$batch) {
            return array();
        }

        $generations = PhytoTcBatch::getGenerationChoices();
        $batch['generation_label'] = isset($generations[$batch['generation']])
            ? $generations[$batch['generation']]
            : $batch['generation'];

        $batch['date_deflask_formatted'] = $batch['date_deflask']
            ? Tools::displayDate($batch['date_deflask'])
            : '';
        $batch['date_certified_formatted'] = $batch['date_certified']
            ? Tools::displayDate($batch['date_certified'])
            : '';

        $this->context->smarty->assign(array(
            'phyto_tc_batch' => $batch,
        ));

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $extraContent->setTitle($this->l('Batch Provenance'));
        $extraContent->setContent($content);

        return array($extraContent);
    }
}
