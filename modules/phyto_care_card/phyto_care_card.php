<?php
/**
 * Phyto Care Card Module
 *
 * Auto-generate printable PDF care sheets per product,
 * attached to order confirmation emails.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   MIT
 * @version   1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Care_Card extends Module
{
    /** @var string[] Fields stored in the care card table */
    private $careFields = [
        'light',
        'water_type',
        'water_method',
        'humidity',
        'temperature',
        'media',
        'feed',
        'dormancy',
        'potting',
        'problems',
    ];

    public function __construct()
    {
        $this->name = 'phyto_care_card';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => '8.99.99'];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Care Card');
        $this->description = $this->l('Generates a branded PDF care sheet for each plant product covering light, water type and method, humidity, temperature, growing media, feeding protocol, dormancy instructions, potting tips, and common problems. The PDF is automatically attached to the order confirmation email so every buyer receives customised care guidance the moment they purchase. Uses TCPDF when available, with an HTML fallback, and supports a custom store logo and footer text on every sheet.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall Phyto Care Card? All care card data will be deleted.');
    }

    /* ─── INSTALL / UNINSTALL ─────────────────────────────── */

    public function install()
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('actionEmailSendBefore')
            && $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        return $this->runSql('uninstall')
            && $this->deleteConfig()
            && parent::uninstall();
    }

    /**
     * Execute an SQL file from the sql/ directory.
     *
     * @param string $filename install|uninstall
     * @return bool
     */
    private function runSql($filename)
    {
        $path = dirname(__FILE__) . '/sql/' . $filename . '.sql';
        if (!file_exists($path)) {
            return false;
        }

        $sql = file_get_contents($path);
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
     * Remove module configuration values.
     *
     * @return bool
     */
    private function deleteConfig()
    {
        Configuration::deleteByName('PHYTO_CARE_LOGO_PATH');
        Configuration::deleteByName('PHYTO_CARE_STORE_NAME');
        Configuration::deleteByName('PHYTO_CARE_FOOTER_TEXT');

        return true;
    }

    /* ─── CONFIGURATION PAGE ──────────────────────────────── */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoCareConfig')) {
            $logoPath = Tools::getValue('PHYTO_CARE_LOGO_PATH');
            $storeName = Tools::getValue('PHYTO_CARE_STORE_NAME');
            $footerText = Tools::getValue('PHYTO_CARE_FOOTER_TEXT');

            Configuration::updateValue('PHYTO_CARE_LOGO_PATH', $logoPath);
            Configuration::updateValue('PHYTO_CARE_STORE_NAME', $storeName);
            Configuration::updateValue('PHYTO_CARE_FOOTER_TEXT', $footerText);

            $output .= $this->displayConfirmation($this->l('Settings updated successfully.'));
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Build the HelperForm for the module configuration page.
     *
     * @return string
     */
    private function renderConfigForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Care Card Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Store Logo Path'),
                        'name' => 'PHYTO_CARE_LOGO_PATH',
                        'desc' => $this->l('Absolute or relative path to the logo image used on care card PDFs (e.g. /img/logo.png).'),
                        'size' => 60,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Store Name'),
                        'name' => 'PHYTO_CARE_STORE_NAME',
                        'desc' => $this->l('Store name printed on the care card header.'),
                        'size' => 40,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Footer Text'),
                        'name' => 'PHYTO_CARE_FOOTER_TEXT',
                        'desc' => $this->l('Text displayed at the bottom of the care card PDF.'),
                        'cols' => 60,
                        'rows' => 4,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submitPhytoCareConfig';
        $helper->fields_value = [
            'PHYTO_CARE_LOGO_PATH' => Configuration::get('PHYTO_CARE_LOGO_PATH'),
            'PHYTO_CARE_STORE_NAME' => Configuration::get('PHYTO_CARE_STORE_NAME'),
            'PHYTO_CARE_FOOTER_TEXT' => Configuration::get('PHYTO_CARE_FOOTER_TEXT'),
        ];

        return $helper->generateForm([$fields_form]);
    }

    /* ─── HOOK: BACK OFFICE HEADER (CSS/JS) ───────────────── */

    public function hookDisplayBackOfficeHeader($params)
    {
        if (Tools::getValue('configure') === $this->name
            || Tools::getValue('controller') === 'AdminProducts'
        ) {
            $this->context->controller->addCSS($this->_path . 'views/css/front.css');
        }
    }

    /* ─── HOOK: PRODUCT TAB ───────────────────────────────── */

    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) $params['id_product'];
        if (!$idProduct) {
            return '';
        }

        $careData = $this->getCareData($idProduct);

        $ajaxUrl = $this->context->link->getAdminLink('AdminPhytoCareCard');
        $pdfPreviewUrl = $this->context->link->getModuleLink(
            $this->name,
            'download',
            [
                'id_product' => $idProduct,
                'token' => md5($idProduct . _COOKIE_KEY_),
            ]
        );

        $this->context->smarty->assign([
            'phyto_care_data' => $careData,
            'phyto_care_ajax_url' => $ajaxUrl,
            'phyto_care_id_product' => $idProduct,
            'phyto_care_pdf_preview_url' => $pdfPreviewUrl,
            'phyto_care_csrf_token' => Tools::getToken(false),
            'phyto_light_options' => [
                '' => $this->l('-- Select --'),
                'Full sun' => $this->l('Full sun'),
                'Bright indirect' => $this->l('Bright indirect'),
                'Partial shade' => $this->l('Partial shade'),
                'Low light' => $this->l('Low light'),
            ],
            'phyto_water_type_options' => [
                '' => $this->l('-- Select --'),
                'Distilled only' => $this->l('Distilled only'),
                'Rainwater' => $this->l('Rainwater'),
                'Low-TDS tap' => $this->l('Low-TDS tap'),
                'Any' => $this->l('Any'),
            ],
            'phyto_water_method_options' => [
                '' => $this->l('-- Select --'),
                'Tray method' => $this->l('Tray method'),
                'Top water' => $this->l('Top water'),
                'Mist only' => $this->l('Mist only'),
            ],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /* ─── HOOK: EMAIL ATTACHMENT ──────────────────────────── */

    /**
     * Attach care card PDFs to order confirmation emails.
     *
     * Uses actionEmailSendBefore to modify file_attachment before mail is sent.
     *
     * @param array $params
     */
    public function hookActionEmailSendBefore($params)
    {
        if (!isset($params['template']) || $params['template'] !== 'order_conf') {
            return;
        }

        // Determine the order — we get idOrder from template vars
        $templateVars = isset($params['templateVars']) ? $params['templateVars'] : [];
        $idOrder = 0;

        if (isset($templateVars['{id_order}'])) {
            $idOrder = (int) $templateVars['{id_order}'];
        }

        if (!$idOrder) {
            return;
        }

        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            return;
        }

        $products = $order->getProducts();
        if (empty($products)) {
            return;
        }

        $attachments = [];
        if (isset($params['fileAttachment']) && is_array($params['fileAttachment'])) {
            $attachments = $params['fileAttachment'];
        }

        $tempFiles = [];

        foreach ($products as $product) {
            $idProduct = (int) $product['product_id'];
            $careData = $this->getCareData($idProduct);

            if (empty($careData)) {
                continue;
            }

            $pdfContent = $this->generatePdfContent($idProduct, $careData);
            if (!$pdfContent) {
                continue;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'phyto_care_');
            if ($tempFile === false) {
                continue;
            }

            file_put_contents($tempFile, $pdfContent);
            $tempFiles[] = $tempFile;

            $productName = isset($product['product_name'])
                ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $product['product_name'])
                : 'product_' . $idProduct;

            $attachments[] = [
                'content' => $pdfContent,
                'name' => 'Care_Card_' . $productName . '.pdf',
                'mime' => 'application/pdf',
            ];
        }

        if (!empty($attachments)) {
            $params['fileAttachment'] = $attachments;
        }

        // Clean up temp files
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /* ─── DATA ACCESS ─────────────────────────────────────── */

    /**
     * Retrieve care card data for a product.
     *
     * @param int $idProduct
     * @return array|false
     */
    public function getCareData($idProduct)
    {
        $idProduct = (int) $idProduct;
        if (!$idProduct) {
            return false;
        }

        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_care_card`
             WHERE `id_product` = ' . $idProduct
        );

        return $result ?: false;
    }

    /**
     * Save or update care card data for a product.
     *
     * @param int   $idProduct
     * @param array $data
     * @return bool
     */
    public function saveCareData($idProduct, array $data)
    {
        $idProduct = (int) $idProduct;
        if (!$idProduct) {
            return false;
        }

        $existing = $this->getCareData($idProduct);
        $dbData = ['id_product' => $idProduct, 'date_upd' => date('Y-m-d H:i:s')];

        foreach ($this->careFields as $field) {
            $dbData[$field] = isset($data[$field]) ? pSQL($data[$field], true) : '';
        }

        if ($existing) {
            return Db::getInstance()->update(
                'phyto_care_card',
                $dbData,
                'id_product = ' . $idProduct
            );
        }

        return Db::getInstance()->insert('phyto_care_card', $dbData);
    }

    /* ─── PDF GENERATION ──────────────────────────────────── */

    /**
     * Generate the PDF content (binary string) for a product care card.
     *
     * @param int        $idProduct
     * @param array|null $careData  Pre-fetched data (optional)
     * @return string|false  Raw PDF content or false on failure
     */
    public function generatePdfContent($idProduct, $careData = null)
    {
        $idProduct = (int) $idProduct;
        if (!$careData) {
            $careData = $this->getCareData($idProduct);
        }
        if (empty($careData)) {
            return false;
        }

        $product = new Product($idProduct, false, (int) Configuration::get('PS_LANG_DEFAULT'));
        $productName = is_object($product) && isset($product->name)
            ? (is_array($product->name) ? reset($product->name) : $product->name)
            : 'Product #' . $idProduct;

        $storeName = Configuration::get('PHYTO_CARE_STORE_NAME')
            ?: Configuration::get('PS_SHOP_NAME')
            ?: 'PhytoCommerce';
        $footerText = Configuration::get('PHYTO_CARE_FOOTER_TEXT') ?: '';
        $logoPath = $this->resolveLogoPath();

        // Try to load TCPDF
        $tcpdfClass = $this->loadTcpdf();

        if ($tcpdfClass) {
            return $this->generateTcpdfContent(
                $tcpdfClass,
                $productName,
                $careData,
                $storeName,
                $footerText,
                $logoPath
            );
        }

        // Fallback: generate a simple HTML-wrapped PDF-like document
        return $this->generateHtmlFallbackContent(
            $productName,
            $careData,
            $storeName,
            $footerText,
            $logoPath
        );
    }

    /**
     * Attempt to load TCPDF from known PrestaShop paths.
     *
     * @return string|false  Class name if available, false otherwise
     */
    private function loadTcpdf()
    {
        $paths = [];

        if (defined('_PS_ROOT_DIR_')) {
            $paths[] = _PS_ROOT_DIR_ . '/vendor/tecnickcom/tcpdf/tcpdf.php';
        }

        if (defined('_PS_TOOL_DIR_')) {
            $paths[] = _PS_TOOL_DIR_ . 'tcpdf/tcpdf.php';
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists('TCPDF')) {
                    return 'TCPDF';
                }
            }
        }

        return false;
    }

    /**
     * Generate PDF using TCPDF.
     *
     * @param string $tcpdfClass
     * @param string $productName
     * @param array  $careData
     * @param string $storeName
     * @param string $footerText
     * @param string $logoPath
     * @return string  Raw PDF bytes
     */
    private function generateTcpdfContent(
        $tcpdfClass,
        $productName,
        array $careData,
        $storeName,
        $footerText,
        $logoPath
    ) {
        /** @var TCPDF $pdf */
        $pdf = new $tcpdfClass('P', 'mm', 'A5', true, 'UTF-8', false);

        $pdf->SetCreator($storeName);
        $pdf->SetAuthor($storeName);
        $pdf->SetTitle($this->l('Care Card') . ' - ' . $productName);
        $pdf->SetSubject($this->l('Plant Care Card'));

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);

        $pdf->AddPage();

        // ── Header with logo ───────────────────────────────
        $headerHtml = '<table width="100%" cellpadding="4"><tr>';
        $headerHtml .= '<td width="60%">';
        $headerHtml .= '<h1 style="font-size:16px;color:#2e7d32;">'
            . htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') . '</h1>';
        $headerHtml .= '</td>';
        $headerHtml .= '<td width="40%" align="right">';

        if ($logoPath && file_exists($logoPath)) {
            $headerHtml .= '<img src="' . htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8')
                . '" height="40">';
        }

        $headerHtml .= '</td></tr></table>';
        $pdf->writeHTML($headerHtml, true, false, true, false, '');

        // ── Horizontal rule ────────────────────────────────
        $pdf->writeHTML('<hr style="color:#4caf50;">', true, false, true, false, '');

        // ── Product title ──────────────────────────────────
        $pdf->writeHTML(
            '<h2 style="font-size:14px;color:#1b5e20;">'
            . htmlspecialchars($productName, ENT_QUOTES, 'UTF-8')
            . ' &mdash; ' . $this->l('Care Card') . '</h2>',
            true, false, true, false, ''
        );

        // ── Sections ───────────────────────────────────────
        $sections = $this->buildSections($careData);
        foreach ($sections as $label => $value) {
            if (empty($value)) {
                continue;
            }
            $sectionHtml = '<table width="100%" cellpadding="4" style="margin-bottom:4px;">';
            $sectionHtml .= '<tr><td style="background-color:#e8f5e9;font-weight:bold;font-size:10px;color:#2e7d32;border-bottom:1px solid #a5d6a7;">';
            $sectionHtml .= htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
            $sectionHtml .= '</td></tr>';
            $sectionHtml .= '<tr><td style="font-size:9px;padding:4px 6px;">';
            $sectionHtml .= nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            $sectionHtml .= '</td></tr></table>';
            $pdf->writeHTML($sectionHtml, true, false, true, false, '');
        }

        // ── Footer text ────────────────────────────────────
        if (!empty($footerText)) {
            $pdf->writeHTML('<br>', true, false, true, false, '');
            $pdf->writeHTML(
                '<hr style="color:#4caf50;">',
                true, false, true, false, ''
            );
            $pdf->writeHTML(
                '<p style="font-size:8px;color:#757575;text-align:center;">'
                . nl2br(htmlspecialchars($footerText, ENT_QUOTES, 'UTF-8'))
                . '</p>',
                true, false, true, false, ''
            );
        }

        return $pdf->Output('', 'S');
    }

    /**
     * Fallback: generate HTML content rendered as a downloadable page.
     * This is NOT a real PDF but an HTML document served when TCPDF is unavailable.
     *
     * @param string $productName
     * @param array  $careData
     * @param string $storeName
     * @param string $footerText
     * @param string $logoPath
     * @return string
     */
    private function generateHtmlFallbackContent(
        $productName,
        array $careData,
        $storeName,
        $footerText,
        $logoPath
    ) {
        $sections = $this->buildSections($careData);

        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
        $html .= '<title>' . htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') . ' - Care Card</title>';
        $html .= '<style>';
        $html .= 'body{font-family:Arial,Helvetica,sans-serif;max-width:500px;margin:20px auto;color:#333;font-size:13px;}';
        $html .= '.phyto-care-header{display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #4caf50;padding-bottom:8px;margin-bottom:12px;}';
        $html .= '.phyto-care-header h1{font-size:18px;color:#2e7d32;margin:0;}';
        $html .= '.phyto-care-header img{max-height:50px;}';
        $html .= '.phyto-care-title{font-size:16px;color:#1b5e20;margin:12px 0 8px;}';
        $html .= '.phyto-care-section{margin-bottom:10px;}';
        $html .= '.phyto-care-section-label{background:#e8f5e9;padding:4px 8px;font-weight:bold;font-size:12px;color:#2e7d32;border-bottom:1px solid #a5d6a7;}';
        $html .= '.phyto-care-section-value{padding:6px 8px;font-size:12px;}';
        $html .= '.phyto-care-footer{border-top:1px solid #4caf50;margin-top:16px;padding-top:8px;font-size:10px;color:#757575;text-align:center;}';
        $html .= '@media print{body{margin:0;max-width:100%;}}';
        $html .= '</style></head><body>';

        $html .= '<div class="phyto-care-header">';
        $html .= '<h1>' . htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') . '</h1>';
        if ($logoPath && file_exists($logoPath)) {
            $html .= '<img src="data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) . '" alt="Logo">';
        }
        $html .= '</div>';

        $html .= '<h2 class="phyto-care-title">'
            . htmlspecialchars($productName, ENT_QUOTES, 'UTF-8')
            . ' &mdash; Care Card</h2>';

        foreach ($sections as $label => $value) {
            if (empty($value)) {
                continue;
            }
            $html .= '<div class="phyto-care-section">';
            $html .= '<div class="phyto-care-section-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</div>';
            $html .= '<div class="phyto-care-section-value">' . nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) . '</div>';
            $html .= '</div>';
        }

        if (!empty($footerText)) {
            $html .= '<div class="phyto-care-footer">'
                . nl2br(htmlspecialchars($footerText, ENT_QUOTES, 'UTF-8'))
                . '</div>';
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Build labelled sections array from care data.
     *
     * @param array $careData
     * @return array  label => value
     */
    private function buildSections(array $careData)
    {
        return [
            $this->l('Light Requirement') => isset($careData['light']) ? $careData['light'] : '',
            $this->l('Water Type') => isset($careData['water_type']) ? $careData['water_type'] : '',
            $this->l('Watering Method') => isset($careData['water_method']) ? $careData['water_method'] : '',
            $this->l('Humidity Range') => isset($careData['humidity']) ? $careData['humidity'] : '',
            $this->l('Temperature Range') => isset($careData['temperature']) ? $careData['temperature'] : '',
            $this->l('Soil / Media') => isset($careData['media']) ? $careData['media'] : '',
            $this->l('Feed Protocol') => isset($careData['feed']) ? $careData['feed'] : '',
            $this->l('Dormancy Instructions') => isset($careData['dormancy']) ? $careData['dormancy'] : '',
            $this->l('Potting Tips') => isset($careData['potting']) ? $careData['potting'] : '',
            $this->l('Common Problems') => isset($careData['problems']) ? $careData['problems'] : '',
        ];
    }

    /**
     * Resolve the logo file path from configuration.
     *
     * @return string|false
     */
    private function resolveLogoPath()
    {
        $configPath = Configuration::get('PHYTO_CARE_LOGO_PATH');
        if (empty($configPath)) {
            return false;
        }

        // Absolute path
        if (file_exists($configPath)) {
            return $configPath;
        }

        // Relative to PS root
        if (defined('_PS_ROOT_DIR_') && file_exists(_PS_ROOT_DIR_ . '/' . ltrim($configPath, '/'))) {
            return _PS_ROOT_DIR_ . '/' . ltrim($configPath, '/');
        }

        return false;
    }
}
