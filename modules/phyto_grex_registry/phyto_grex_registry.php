<?php
/**
 * Phyto Grex Registry
 *
 * Attach structured scientific/horticultural taxonomy metadata to any product.
 * Displays a taxonomy card on the product page front end.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Grex_Registry extends Module
{
    /** @var array Conservation status options */
    public const CONSERVATION_STATUSES = [
        '' => 'Not Assessed',
        'LC' => 'Least Concern (LC)',
        'NT' => 'Near Threatened (NT)',
        'VU' => 'Vulnerable (VU)',
        'EN' => 'Endangered (EN)',
        'CR' => 'Critically Endangered (CR)',
        'EW' => 'Extinct in the Wild (EW)',
        'EX' => 'Extinct (EX)',
    ];

    public function __construct()
    {
        $this->name = 'phyto_grex_registry';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '8.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Grex Registry');
        $this->description = $this->l('Attach structured scientific and horticultural taxonomy data to any product, including genus, species, subspecies, cultivar, grex name, hybrid formula, mother/father parentage, natural habitat, endemic region, and IUCN conservation status. A "Scientific Profile" tab is displayed on the product page front end, and all fields are editable from within the standard product editor. Designed for orchid, carnivorous plant, and rare species retailers who need verifiable provenance information visible to buyers.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall? All taxonomy data will be permanently deleted.');
    }

    /**
     * Module installation
     */
    public function install()
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('actionProductDelete')
            && $this->installTab();
    }

    /**
     * Module uninstallation
     */
    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    /**
     * Install hidden admin tab for AJAX endpoint
     */
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminPhytoGrexRegistry';
        $tab->module = $this->name;
        $tab->id_parent = -1; // Hidden tab
        $tab->name = [];

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Phyto Grex Registry';
        }

        return $tab->add();
    }

    /**
     * Uninstall hidden admin tab
     */
    private function uninstallTab()
    {
        $id_tab = (int) Tab::getIdFromClassName('AdminPhytoGrexRegistry');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }

        return true;
    }

    /**
     * Execute SQL file with prefix replacement
     *
     * @param string $filename SQL file name without extension
     * @return bool
     */
    private function runSql($filename)
    {
        $filepath = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($filepath)) {
            return false;
        }

        $sql = file_get_contents($filepath);
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
     * Hook: displayAdminProductsExtra
     * Renders the taxonomy tab in the product edit page (back office)
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int) $params['id_product'];
        $data = $this->getGrexData($id_product);

        $this->context->smarty->assign([
            'phyto_grex_data' => $data,
            'phyto_grex_id_product' => $id_product,
            'phyto_grex_ajax_url' => $this->context->link->getAdminLink('AdminPhytoGrexRegistry'),
            'phyto_grex_conservation_statuses' => self::CONSERVATION_STATUSES,
            'phyto_grex_token' => Tools::getAdminTokenLite('AdminPhytoGrexRegistry'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /**
     * Hook: displayProductExtraContent (PrestaShop 8)
     * Renders the taxonomy card on the product page front end
     */
    public function hookDisplayProductExtraContent($params)
    {
        $id_product = (int) $params['product']->getId();
        $data = $this->getGrexData($id_product);

        // Only display if there is meaningful data
        if (!$data || $this->isDataEmpty($data)) {
            return [];
        }

        // Build display fields (only non-empty)
        $fields = $this->buildDisplayFields($data);

        $this->context->smarty->assign([
            'phyto_grex_data' => $data,
            'phyto_grex_fields' => $fields,
            'phyto_grex_conservation_statuses' => self::CONSERVATION_STATUSES,
        ]);

        $this->context->controller->addCSS($this->_path . 'views/css/front.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/front.js');

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $tab = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $tab->setTitle($this->l('Scientific Profile'));
        $tab->setContent($content);

        return [$tab];
    }

    /**
     * Hook: actionProductDelete
     * Clean up taxonomy data when a product is deleted
     */
    public function hookActionProductDelete($params)
    {
        $id_product = (int) $params['id_product'];

        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'phyto_grex_registry`
             WHERE `id_product` = ' . $id_product
        );
    }

    /**
     * Retrieve grex data for a product
     *
     * @param int $id_product
     * @return array|false
     */
    public function getGrexData($id_product)
    {
        return Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_grex_registry`
             WHERE `id_product` = ' . (int) $id_product
        );
    }

    /**
     * Check if all taxonomy fields are empty
     *
     * @param array $data
     * @return bool
     */
    private function isDataEmpty($data)
    {
        $fields = [
            'genus', 'species', 'subspecies', 'cultivar', 'grex_name',
            'hybrid_formula', 'mother', 'father', 'habitat',
            'endemic_region', 'conservation_status', 'notes',
        ];

        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                return false;
            }
        }

        if (!empty($data['icps_registered'])) {
            return false;
        }

        return true;
    }

    /**
     * Build an ordered list of display fields with labels and formatted values
     *
     * @param array $data
     * @return array
     */
    private function buildDisplayFields($data)
    {
        $fields = [];

        if (!empty($data['genus'])) {
            $fields[] = [
                'label' => $this->l('Genus'),
                'value' => '<em>' . htmlspecialchars($data['genus']) . '</em>',
                'raw' => $data['genus'],
            ];
        }

        if (!empty($data['species'])) {
            $fields[] = [
                'label' => $this->l('Species'),
                'value' => '<em>' . htmlspecialchars($data['species']) . '</em>',
                'raw' => $data['species'],
            ];
        }

        if (!empty($data['subspecies'])) {
            $fields[] = [
                'label' => $this->l('Subspecies / Variety'),
                'value' => '<em>' . htmlspecialchars($data['subspecies']) . '</em>',
                'raw' => $data['subspecies'],
            ];
        }

        if (!empty($data['cultivar'])) {
            $fields[] = [
                'label' => $this->l('Cultivar'),
                'value' => '&#8216;' . htmlspecialchars($data['cultivar']) . '&#8217;',
                'raw' => $data['cultivar'],
            ];
        }

        if (!empty($data['grex_name'])) {
            $fields[] = [
                'label' => $this->l('Grex Name'),
                'value' => htmlspecialchars($data['grex_name']),
                'raw' => $data['grex_name'],
            ];
        }

        if (!empty($data['hybrid_formula'])) {
            // Ensure multiplication sign is proper unicode ×
            $formula = htmlspecialchars($data['hybrid_formula']);
            $formula = str_replace(['x ', ' x', ' x '], ["\u{00D7} ", " \u{00D7}", " \u{00D7} "], $formula);
            $fields[] = [
                'label' => $this->l('Hybrid Formula'),
                'value' => '<em>' . $formula . '</em>',
                'raw' => $data['hybrid_formula'],
            ];
        }

        if (!empty($data['mother'])) {
            $fields[] = [
                'label' => $this->l('Mother (♀)'),
                'value' => '<em>' . htmlspecialchars($data['mother']) . '</em>',
                'raw' => $data['mother'],
            ];
        }

        if (!empty($data['father'])) {
            $fields[] = [
                'label' => $this->l('Father (♂)'),
                'value' => '<em>' . htmlspecialchars($data['father']) . '</em>',
                'raw' => $data['father'],
            ];
        }

        if (!empty($data['habitat'])) {
            $fields[] = [
                'label' => $this->l('Natural Habitat'),
                'value' => htmlspecialchars($data['habitat']),
                'raw' => $data['habitat'],
            ];
        }

        if (!empty($data['endemic_region'])) {
            $fields[] = [
                'label' => $this->l('Endemic Region'),
                'value' => htmlspecialchars($data['endemic_region']),
                'raw' => $data['endemic_region'],
            ];
        }

        if (!empty($data['conservation_status'])) {
            $statusLabel = isset(self::CONSERVATION_STATUSES[$data['conservation_status']])
                ? self::CONSERVATION_STATUSES[$data['conservation_status']]
                : $data['conservation_status'];
            $fields[] = [
                'label' => $this->l('Conservation Status'),
                'value' => htmlspecialchars($statusLabel),
                'raw' => $data['conservation_status'],
                'type' => 'conservation',
            ];
        }

        if (!empty($data['notes'])) {
            $fields[] = [
                'label' => $this->l('Taxonomic Notes'),
                'value' => nl2br(htmlspecialchars($data['notes'])),
                'raw' => $data['notes'],
            ];
        }

        return $fields;
    }
}
