<?php
/**
 * Phyto Phytosanitary Module
 *
 * Attach inspection certificates and import permits to products.
 * Auto-includes reference numbers in packing slip PDF.
 *
 * @author    PhytoCommerce
 * @version   1.0.0
 * @license   AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/classes/PhytoPhytosanitaryDoc.php';

class Phyto_Phytosanitary extends Module
{
    /**
     * Module constructor.
     */
    public function __construct()
    {
        $this->name          = 'phyto_phytosanitary';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Phytosanitary');
        $this->description = $this->l('Attaches phytosanitary inspection certificates, import permits, and other regulatory documents to products, with uploaded PDFs available for customers to download on the product page under a "Regulatory Documents" tab. Reference numbers from attached documents are automatically printed on packing slip PDFs, providing a complete compliance paper trail. Essential for sellers of regulated plant material, especially imported species subject to CITES or national phytosanitary requirements.');
        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    /**
     * Module installation.
     *
     * @return bool
     */
    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHooks()
            && $this->installTab()
            && $this->createUploadDir();
    }

    /**
     * Module uninstallation.
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helpers
    // -------------------------------------------------------------------------

    /**
     * Execute an SQL file from the sql/ directory.
     *
     * @param string $filename  'install' or 'uninstall' (without .sql extension)
     *
     * @return bool
     */
    protected function runSql(string $filename): bool
    {
        $file = __DIR__ . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);

        if ($sql === false) {
            return false;
        }

        // Replace PREFIX_ placeholder with the actual DB prefix
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);

        // Split on semicolons and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            static fn (string $s): bool => $s !== ''
        );

        foreach ($statements as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Directory helpers
    // -------------------------------------------------------------------------

    /**
     * Create the upload directory used to store phytosanitary document files.
     *
     * @return bool
     */
    protected function createUploadDir(): bool
    {
        $dir = PhytoPhytosanitaryDoc::getUploadDir();

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                return false;
            }
        }

        // Write an .htaccess that allows only PDF downloads through the
        // front-office download controller – direct directory listing is denied.
        $htaccess = $dir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents(
                $htaccess,
                "Options -Indexes\n" .
                "<FilesMatch \"\\.pdf$\">\n" .
                "    Order allow,deny\n" .
                "    Allow from all\n" .
                "</FilesMatch>\n"
            );
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab helpers
    // -------------------------------------------------------------------------

    /**
     * Install the back-office menu tab.
     *
     * @return bool
     */
    protected function installTab(): bool
    {
        $idParent = (int) Tab::getIdFromClassName('AdminCatalog');

        if ($idParent <= 0) {
            $idParent = (int) Tab::getIdFromClassName('DEFAULT');
        }

        $tab = new Tab();
        $tab->active      = 1;
        $tab->class_name  = 'AdminPhytoPhytosanitary';
        $tab->name        = [];
        $tab->id_parent   = $idParent;
        $tab->module      = $this->name;

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Phytosanitary Docs');
        }

        return $tab->add();
    }

    /**
     * Remove the back-office menu tab.
     *
     * @return bool
     */
    protected function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoPhytosanitary');

        if ($idTab > 0) {
            $tab = new Tab($idTab);

            return $tab->delete();
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Hook registration
    // -------------------------------------------------------------------------

    /**
     * Register all module hooks.
     *
     * @return bool
     */
    protected function registerHooks(): bool
    {
        return $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayPDFInvoice')
            && $this->registerHook('actionProductDelete');
    }

    // -------------------------------------------------------------------------
    // Hook implementations
    // -------------------------------------------------------------------------

    /**
     * displayProductExtraContent – renders the "Regulatory Documents" tab on
     * the product page.
     *
     * @param array<string, mixed> $params
     *
     * @return PrestaShop\PrestaShop\Core\Product\ProductExtraContent|string
     */
    public function hookDisplayProductExtraContent(array $params)
    {
        if (!isset($params['product'])) {
            return '';
        }

        $idProduct = (int) (
            is_array($params['product'])
                ? $params['product']['id']
                : $params['product']->id
        );

        $docs = PhytoPhytosanitaryDoc::getByProduct($idProduct, true);

        if (empty($docs)) {
            return '';
        }

        $uploadUrl = $this->context->link->getBaseLink()
            . 'modules/' . $this->name . '/download.php?file=';

        $this->context->smarty->assign([
            'phyto_docs'       => $docs,
            'phyto_upload_url' => $uploadUrl,
        ]);

        $content = $this->fetch(
            'module:phyto_phytosanitary/views/templates/hook/product_extra_content.tpl'
        );

        if (class_exists('\PrestaShop\PrestaShop\Core\Product\ProductExtraContent')) {
            $extraContent = new \PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $extraContent->setTitle($this->l('Regulatory Documents'));
            $extraContent->setContent($content);

            return $extraContent;
        }

        return $content;
    }

    /**
     * displayPDFInvoice – appends regulatory reference numbers to packing slip.
     *
     * @param array<string, mixed> $params
     *
     * @return string
     */
    public function hookDisplayPDFInvoice(array $params): string
    {
        $idOrder = 0;

        if (isset($params['object'])) {
            $object = $params['object'];

            if (is_object($object) && isset($object->id_order)) {
                $idOrder = (int) $object->id_order;
            } elseif (is_object($object) && isset($object->id)) {
                $idOrder = (int) $object->id;
            }
        }

        if (isset($params['order'])) {
            $order   = $params['order'];
            $idOrder = is_object($order) ? (int) $order->id : (int) $order;
        }

        if ($idOrder <= 0) {
            return '';
        }

        $docs = PhytoPhytosanitaryDoc::getByOrder($idOrder);

        if (empty($docs)) {
            return '';
        }

        $references = array_filter(
            array_column($docs, 'reference_number'),
            static fn ($r): bool => !empty($r)
        );

        if (empty($references)) {
            return '';
        }

        $html  = '<table style="width:100%;margin-top:10px;border-top:1px solid #ccc;font-family:sans-serif;font-size:11px;">';
        $html .= '<tr><td style="padding:4px 0;font-weight:bold;">';
        $html .= htmlspecialchars($this->l('Regulatory Compliance'), ENT_QUOTES, 'UTF-8');
        $html .= ':</td></tr>';
        $html .= '<tr><td style="padding:2px 0;">';
        $html .= htmlspecialchars(implode(', ', $references), ENT_QUOTES, 'UTF-8');
        $html .= '</td></tr>';
        $html .= '</table>';

        return $html;
    }

    /**
     * actionProductDelete – clean up documents when a product is removed.
     *
     * @param array<string, mixed> $params
     *
     * @return void
     */
    public function hookActionProductDelete(array $params): void
    {
        if (!isset($params['product'])) {
            return;
        }

        $product   = $params['product'];
        $idProduct = is_object($product) ? (int) $product->id : (int) $product;

        if ($idProduct <= 0) {
            return;
        }

        // Retrieve docs so we can delete physical files too
        $docs = PhytoPhytosanitaryDoc::getByProduct($idProduct);
        $uploadDir = PhytoPhytosanitaryDoc::getUploadDir();

        foreach ($docs as $doc) {
            if (!empty($doc['filename'])) {
                $filePath = $uploadDir . $doc['filename'];

                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
        }

        Db::getInstance()->delete(
            'phyto_phytosanitary_doc',
            'id_product = ' . (int) $idProduct
        );
    }

    // -------------------------------------------------------------------------
    // Module configuration page (optional, minimal)
    // -------------------------------------------------------------------------

    /**
     * Render a basic configuration page that redirects to the dedicated tab.
     *
     * @return string
     */
    public function getContent(): string
    {
        $link = $this->context->link->getAdminLink('AdminPhytoPhytosanitary');

        Tools::redirectAdmin($link);

        return '';
    }
}
