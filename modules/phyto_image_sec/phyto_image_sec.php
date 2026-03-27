<?php
/**
 * PhytoCommerce — Phyto Image Sec
 *
 * Watermarks all product images with the shop logo and blocks image theft
 * via JS-level right-click / drag / keyboard-shortcut protection.
 *
 * v0.2 — adds WebP sibling generation + IPTC copyright metadata embedding.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoImageWatermarker.php';

class Phyto_Image_Sec extends Module
{
    // ──────────────────────────────────────────────────────────────
    //  Constructor
    // ──────────────────────────────────────────────────────────────

    public function __construct()
    {
        $this->name            = 'phyto_image_sec';
        $this->tab             = 'administration';
        $this->version         = '0.2.0';
        $this->author          = 'PhytoCommerce';
        $this->need_instance   = 0;
        $this->bootstrap       = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName    = $this->l('Phyto Image Sec — Image Protection');
        $this->description    = $this->l(
            'Watermarks product images with your shop logo, embeds IPTC copyright metadata, '
            . 'generates compressed WebP siblings, and blocks right-click / drag / download.'
        );
        $this->confirmUninstall = $this->l(
            'Uninstalling will NOT restore already-watermarked images. Continue?'
        );
    }

    // ──────────────────────────────────────────────────────────────
    //  Install / Uninstall
    // ──────────────────────────────────────────────────────────────

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->seedConfig()
            && $this->registerHooks()
            && $this->installTabs();
    }

    public function uninstall(): bool
    {
        return $this->uninstallTabs()
            && $this->runSql('uninstall')
            && $this->deleteConfig()
            && parent::uninstall();
    }

    // ──────────────────────────────────────────────────────────────
    //  Config helpers
    // ──────────────────────────────────────────────────────────────

    private function seedConfig(): bool
    {
        $defaults = [
            'PHYTO_IMGSEC_WATERMARK_ENABLED' => 1,
            'PHYTO_IMGSEC_POSITION'          => 'bottom-right',
            'PHYTO_IMGSEC_OPACITY'           => 60,
            'PHYTO_IMGSEC_SIZE_PCT'          => 25,
            'PHYTO_IMGSEC_PROTECT_ENABLED'   => 1,
            'PHYTO_IMGSEC_WEBP_QUALITY'      => 82,
        ];

        foreach ($defaults as $key => $value) {
            if (!Configuration::updateValue($key, $value)) {
                return false;
            }
        }

        return true;
    }

    private function deleteConfig(): void
    {
        $keys = [
            'PHYTO_IMGSEC_WATERMARK_ENABLED',
            'PHYTO_IMGSEC_POSITION',
            'PHYTO_IMGSEC_OPACITY',
            'PHYTO_IMGSEC_SIZE_PCT',
            'PHYTO_IMGSEC_PROTECT_ENABLED',
            'PHYTO_IMGSEC_WEBP_QUALITY',
        ];

        foreach ($keys as $key) {
            Configuration::deleteByName($key);
        }
    }

    // ──────────────────────────────────────────────────────────────
    //  SQL helper
    // ──────────────────────────────────────────────────────────────

    protected function runSql(string $filename): bool
    {
        $file = dirname(__FILE__) . '/sql/' . $filename . '.sql';

        if (!file_exists($file)) {
            return false;
        }

        $sql = file_get_contents($file);

        if (empty(trim($sql))) {
            return true;
        }

        $sql        = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql        = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);
        $statements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '' || strpos($statement, '--') === 0) {
                continue;
            }

            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook registration
    // ──────────────────────────────────────────────────────────────

    protected function registerHooks(): bool
    {
        $hooks = [
            'actionWatermark',
            'displayHeader',
            'displayBackOfficeHeader',
        ];

        foreach ($hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Tab management
    // ──────────────────────────────────────────────────────────────

    protected function installTabs(): bool
    {
        return $this->installTab('AdminPhytoImageSec', 'Phyto Image Sec', -1);
    }

    protected function installTab(string $className, string $name, $parent): bool
    {
        // Remove any stale tab left over from a failed previous install/reset
        $existingId = (int) Tab::getIdFromClassName($className);
        if ($existingId > 0) {
            (new Tab($existingId))->delete();
        }

        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = $className;
        $tab->module     = $this->name;
        $tab->id_parent  = ($parent === -1) ? -1 : (int) Tab::getIdFromClassName($parent);

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = $name;
        }

        return (bool) $tab->add();
    }

    protected function uninstallTabs(): bool
    {
        $id = (int) Tab::getIdFromClassName('AdminPhytoImageSec');

        if ($id > 0) {
            $tab = new Tab($id);

            return $tab->delete();
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook: actionWatermark
    //  Fires after PS generates a product image thumbnail.
    //  We process all existing sizes for the image on first call only.
    // ──────────────────────────────────────────────────────────────

    public function hookActionWatermark(array $params): void
    {
        if (!Configuration::get('PHYTO_IMGSEC_WATERMARK_ENABLED')) {
            return;
        }

        // Prevent processing the same image more than once per request
        // (PS may fire this hook once per thumbnail size)
        static $processed = [];

        $idImage = (int) ($params['id_image'] ?? 0);

        if (!$idImage || isset($processed[$idImage])) {
            return;
        }

        $processed[$idImage] = true;

        $logoPath = _PS_IMG_DIR_ . Configuration::get('PS_LOGO');

        if (!file_exists($logoPath)) {
            return;
        }

        $watermarker = $this->buildWatermarker($logoPath);
        $this->watermarkAllSizes($watermarker, $idImage);
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook: displayHeader
    //  Injects protection JS+CSS on relevant front-office pages.
    // ──────────────────────────────────────────────────────────────

    public function hookDisplayHeader(array $params): string
    {
        if (!Configuration::get('PHYTO_IMGSEC_PROTECT_ENABLED')) {
            return '';
        }

        $protectedControllers = ['product', 'category', 'search', 'index'];
        $current              = $this->context->controller->php_self ?? '';

        if (!in_array($current, $protectedControllers, true)) {
            return '';
        }

        $this->context->controller->registerStylesheet(
            'phyto-image-sec-front',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 200]
        );

        $this->context->controller->registerJavascript(
            'phyto-image-sec-front',
            'modules/' . $this->name . '/views/js/front.js',
            ['position' => 'bottom', 'priority' => 200]
        );

        return $this->display(__FILE__, 'views/templates/hook/front_protection.tpl');
    }

    // ──────────────────────────────────────────────────────────────
    //  Hook: displayBackOfficeHeader
    //  Injects admin JS+CSS only on this module's config page.
    // ──────────────────────────────────────────────────────────────

    public function hookDisplayBackOfficeHeader(): string
    {
        if (Tools::getValue('configure') !== $this->name) {
            return '';
        }

        $this->context->controller->addCSS($this->_path . 'views/css/admin.css');
        $this->context->controller->addJS($this->_path . 'views/js/admin.js');

        return '';
    }

    // ──────────────────────────────────────────────────────────────
    //  Admin configuration page
    // ──────────────────────────────────────────────────────────────

    public function getContent(): string
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoImageSecConfig')) {
            $output .= $this->processConfigSave();
        }

        $batchUrl = $this->context->link->getAdminLink('AdminPhytoImageSec');
        $output  .= $this->renderBatchPanel($batchUrl);
        $output  .= $this->renderConfigForm();

        return $output;
    }

    private function processConfigSave(): string
    {
        $errors      = [];
        $position    = Tools::getValue('PHYTO_IMGSEC_POSITION');
        $opacity     = (int) Tools::getValue('PHYTO_IMGSEC_OPACITY');
        $size        = (int) Tools::getValue('PHYTO_IMGSEC_SIZE_PCT');
        $webpQuality = (int) Tools::getValue('PHYTO_IMGSEC_WEBP_QUALITY');

        $validPositions = ['center', 'bottom-right', 'bottom-left', 'tiled'];

        if (!in_array($position, $validPositions, true)) {
            $errors[] = $this->l('Invalid watermark position selected.');
        }

        if ($opacity < 0 || $opacity > 100) {
            $errors[] = $this->l('Opacity must be between 0 and 100.');
        }

        if ($size < 5 || $size > 75) {
            $errors[] = $this->l('Watermark size must be between 5% and 75% of image width.');
        }

        if ($webpQuality < 1 || $webpQuality > 100) {
            $errors[] = $this->l('WebP quality must be between 1 and 100.');
        }

        if (!empty($errors)) {
            return implode('', array_map([$this, 'displayError'], $errors));
        }

        Configuration::updateValue(
            'PHYTO_IMGSEC_WATERMARK_ENABLED',
            (int) Tools::getValue('PHYTO_IMGSEC_WATERMARK_ENABLED')
        );
        Configuration::updateValue('PHYTO_IMGSEC_POSITION', $position);
        Configuration::updateValue('PHYTO_IMGSEC_OPACITY', $opacity);
        Configuration::updateValue('PHYTO_IMGSEC_SIZE_PCT', $size);
        Configuration::updateValue('PHYTO_IMGSEC_WEBP_QUALITY', $webpQuality);
        Configuration::updateValue(
            'PHYTO_IMGSEC_PROTECT_ENABLED',
            (int) Tools::getValue('PHYTO_IMGSEC_PROTECT_ENABLED')
        );

        return $this->displayConfirmation($this->l('Settings saved successfully.'));
    }

    private function renderBatchPanel(string $ajaxUrl): string
    {
        return '
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-picture"></i>&nbsp;'
                    . $this->l('Batch Watermark Existing Images')
                . '</div>
            <div class="panel-body">
                <p>'
                    . $this->l(
                        'Apply the watermark to every existing product image in your catalogue. '
                        . 'Large catalogues may take a minute — do not close this page.'
                    )
                . '</p>
                <p class="text-muted"><strong>'
                    . $this->l('Note:')
                    . '</strong> '
                    . $this->l(
                        'This permanently modifies image files on disk. '
                        . 'Disable PrestaShop\'s own Watermark module before running this to avoid double watermarks.'
                    )
                . '</p>
                <button id="phyto-batch-start"
                        class="btn btn-warning"
                        data-ajax-url="' . htmlspecialchars($ajaxUrl, ENT_QUOTES) . '">
                    <i class="icon-play"></i>&nbsp;'
                        . $this->l('Start Batch Watermark')
                . '</button>
                <div id="phyto-batch-progress" style="display:none;margin-top:15px;">
                    <div class="progress">
                        <div id="phyto-batch-bar"
                             class="progress-bar progress-bar-striped active"
                             role="progressbar"
                             style="width:0%;min-width:2em;">0%</div>
                    </div>
                    <p id="phyto-batch-status" class="text-muted" style="margin-top:8px;"></p>
                </div>
            </div>
        </div>';
    }

    private function renderConfigForm(): string
    {
        $fields = [
            [
                'type'    => 'switch',
                'label'   => $this->l('Enable Watermarking'),
                'name'    => 'PHYTO_IMGSEC_WATERMARK_ENABLED',
                'is_bool' => true,
                'desc'    => $this->l('Automatically watermark images when they are uploaded or regenerated.'),
                'values'  => [
                    ['id' => 'wm_on',  'value' => 1, 'label' => $this->l('Enabled')],
                    ['id' => 'wm_off', 'value' => 0, 'label' => $this->l('Disabled')],
                ],
            ],
            [
                'type'    => 'select',
                'label'   => $this->l('Watermark Position'),
                'name'    => 'PHYTO_IMGSEC_POSITION',
                'desc'    => $this->l('Where to place the shop logo on each image.'),
                'options' => [
                    'query' => [
                        ['key' => 'bottom-right', 'name' => $this->l('Bottom Right')],
                        ['key' => 'bottom-left',  'name' => $this->l('Bottom Left')],
                        ['key' => 'center',        'name' => $this->l('Center')],
                        ['key' => 'tiled',         'name' => $this->l('Tiled (repeat across image)')],
                    ],
                    'id'   => 'key',
                    'name' => 'name',
                ],
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('Opacity (%)'),
                'name'     => 'PHYTO_IMGSEC_OPACITY',
                'class'    => 'fixed-width-sm',
                'desc'     => $this->l('Watermark transparency: 0 = invisible, 100 = fully opaque. Recommended: 50–70.'),
                'required' => true,
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('Watermark Size (% of image width)'),
                'name'     => 'PHYTO_IMGSEC_SIZE_PCT',
                'class'    => 'fixed-width-sm',
                'desc'     => $this->l('How large the logo appears relative to the image. Recommended: 20–30.'),
                'required' => true,
            ],
            [
                'type'     => 'text',
                'label'    => $this->l('WebP Quality'),
                'name'     => 'PHYTO_IMGSEC_WEBP_QUALITY',
                'class'    => 'fixed-width-sm',
                'desc'     => $this->l(
                    'Quality for the .webp sibling files generated alongside each image. '
                    . '80–85 gives excellent quality at ~35% smaller file size than JPEG. Range: 1–100.'
                ),
                'required' => true,
            ],
            [
                'type'    => 'switch',
                'label'   => $this->l('Enable JS Image Protection'),
                'name'    => 'PHYTO_IMGSEC_PROTECT_ENABLED',
                'is_bool' => true,
                'desc'    => $this->l(
                    'Disables right-click, drag-to-save, and Ctrl+S on product/category/search pages.'
                ),
                'values'  => [
                    ['id' => 'prot_on',  'value' => 1, 'label' => $this->l('Enabled')],
                    ['id' => 'prot_off', 'value' => 0, 'label' => $this->l('Disabled')],
                ],
            ],
        ];

        $helper                          = new HelperForm();
        $helper->show_toolbar            = false;
        $helper->table                   = $this->table;
        $helper->module                  = $this;
        $helper->default_form_language   = $this->context->language->id;
        $helper->identifier              = $this->identifier;
        $helper->submit_action           = 'submitPhytoImageSecConfig';
        $helper->currentIndex            =
            $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token                   = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value = [
            'PHYTO_IMGSEC_WATERMARK_ENABLED' => (int) Configuration::get('PHYTO_IMGSEC_WATERMARK_ENABLED'),
            'PHYTO_IMGSEC_POSITION'          => Configuration::get('PHYTO_IMGSEC_POSITION'),
            'PHYTO_IMGSEC_OPACITY'           => (int) Configuration::get('PHYTO_IMGSEC_OPACITY'),
            'PHYTO_IMGSEC_SIZE_PCT'          => (int) Configuration::get('PHYTO_IMGSEC_SIZE_PCT'),
            'PHYTO_IMGSEC_WEBP_QUALITY'      => (int) Configuration::get('PHYTO_IMGSEC_WEBP_QUALITY'),
            'PHYTO_IMGSEC_PROTECT_ENABLED'   => (int) Configuration::get('PHYTO_IMGSEC_PROTECT_ENABLED'),
        ];

        return $helper->generateForm([[
            'form' => [
                'legend' => [
                    'title' => $this->l('Image Protection Settings'),
                    'icon'  => 'icon-lock',
                ],
                'input'  => $fields,
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ]]);
    }

    // ──────────────────────────────────────────────────────────────
    //  Watermark helpers (used by both hook and batch controller)
    // ──────────────────────────────────────────────────────────────

    /**
     * Build a watermarker instance from current configuration.
     * Shop name + URL are pulled from PS core config — no extra admin input needed.
     */
    public function buildWatermarker(string $logoPath): PhytoImageWatermarker
    {
        return new PhytoImageWatermarker(
            $logoPath,
            Configuration::get('PHYTO_IMGSEC_POSITION') ?: 'bottom-right',
            (int) (Configuration::get('PHYTO_IMGSEC_OPACITY') ?: 60),
            (int) (Configuration::get('PHYTO_IMGSEC_SIZE_PCT') ?: 25),
            (string) (Configuration::get('PS_SHOP_NAME') ?: ''),
            (string) Tools::getShopDomain(true, true),
            (int) (Configuration::get('PHYTO_IMGSEC_WEBP_QUALITY') ?: 82)
        );
    }

    /**
     * Apply watermark to all thumbnail sizes + original for a given image ID.
     */
    public function watermarkAllSizes(PhytoImageWatermarker $watermarker, int $idImage): void
    {
        $folder   = Image::getImgFolderStatic($idImage);
        $baseDir  = _PS_PROD_IMG_DIR_ . $folder;
        $imgTypes = ImageType::getImagesTypes('products');

        // All thumbnail sizes
        foreach ($imgTypes as $type) {
            foreach (['.jpg', '.webp'] as $ext) {
                $path = $baseDir . $idImage . '-' . $type['name'] . $ext;

                if (file_exists($path)) {
                    $watermarker->apply($path);
                }
            }
        }

        // Original (first match wins)
        foreach (['.jpg', '.png', '.webp'] as $ext) {
            $path = $baseDir . $idImage . $ext;

            if (file_exists($path)) {
                $watermarker->apply($path);
                break;
            }
        }
    }
}
