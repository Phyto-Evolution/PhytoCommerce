<?php
/**
 * Phyto Acclimation Bundler
 *
 * When a TC / deflasked plant is added to cart, auto-suggest acclimation
 * accessories as a dismissable widget below the cart summary.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Acclimation_Bundler extends Module
{
    /** @var array Default configuration values */
    private $configDefaults = [
        'PHYTO_ACCLIM_PRODUCTS' => '',
        'PHYTO_ACCLIM_STAGES'   => '',
        'PHYTO_ACCLIM_TAGS'     => '',
        'PHYTO_ACCLIM_DISCOUNT' => 0,
        'PHYTO_ACCLIM_HEADLINE' => 'Your plant needs an acclimation kit',
        'PHYTO_ACCLIM_MAX_SHOW' => 3,
    ];

    public function __construct()
    {
        $this->name          = 'phyto_acclimation_bundler';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Acclimation Bundler');
        $this->description = $this->l('Automatically suggests acclimation accessories in the cart when a tissue-culture or deflasked plant is added, helping buyers get everything they need in one order. Triggers can be based on Phyto Growth Stage IDs or product tags, and a configurable bundle discount rewards customers who add all kit items at once. The dismissable cart widget shows up to a configurable number of kit products with images and prices.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    /* ------------------------------------------------------------------
     *  INSTALL / UNINSTALL
     * ------------------------------------------------------------------ */

    public function install()
    {
        foreach ($this->configDefaults as $key => $value) {
            if (!Configuration::hasKey($key)) {
                Configuration::updateValue($key, $value);
            }
        }

        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayShoppingCartFooter');
    }

    public function uninstall()
    {
        foreach (array_keys($this->configDefaults) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    /* ------------------------------------------------------------------
     *  BACK OFFICE — Module Configuration (getContent + HelperForm)
     * ------------------------------------------------------------------ */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoAcclimBundler')) {
            $output .= $this->postProcess();
        }

        return $output . $this->renderForm();
    }

    /**
     * Save submitted configuration values.
     *
     * @return string HTML notices
     */
    private function postProcess()
    {
        $errors = [];

        // Comma-separated product IDs — basic validation
        $products = Tools::getValue('PHYTO_ACCLIM_PRODUCTS');
        if (!empty($products) && !preg_match('/^[0-9,\s]+$/', $products)) {
            $errors[] = $this->l('Acclimation kit products must be a comma-separated list of numeric product IDs.');
        }

        $stages = Tools::getValue('PHYTO_ACCLIM_STAGES');
        if (!empty($stages) && !preg_match('/^[0-9,\s]+$/', $stages)) {
            $errors[] = $this->l('Trigger stage IDs must be a comma-separated list of numeric IDs.');
        }

        $discount = (int) Tools::getValue('PHYTO_ACCLIM_DISCOUNT');
        if ($discount < 0 || $discount > 100) {
            $errors[] = $this->l('Bundle discount must be between 0 and 100.');
        }

        $maxShow = (int) Tools::getValue('PHYTO_ACCLIM_MAX_SHOW');
        if ($maxShow < 1) {
            $errors[] = $this->l('Maximum suggestions must be at least 1.');
        }

        if (!empty($errors)) {
            return $this->displayError(implode('<br>', $errors));
        }

        // Normalise whitespace around commas
        Configuration::updateValue('PHYTO_ACCLIM_PRODUCTS', $this->normaliseCsv($products));
        Configuration::updateValue('PHYTO_ACCLIM_STAGES', $this->normaliseCsv($stages));
        Configuration::updateValue('PHYTO_ACCLIM_TAGS', $this->normaliseCsv(Tools::getValue('PHYTO_ACCLIM_TAGS')));
        Configuration::updateValue('PHYTO_ACCLIM_DISCOUNT', $discount);
        Configuration::updateValue('PHYTO_ACCLIM_HEADLINE', Tools::getValue('PHYTO_ACCLIM_HEADLINE'));
        Configuration::updateValue('PHYTO_ACCLIM_MAX_SHOW', $maxShow);

        return $this->displayConfirmation($this->l('Settings saved.'));
    }

    /**
     * Strip extra whitespace from a comma-separated string.
     *
     * @param string $value
     * @return string
     */
    private function normaliseCsv($value)
    {
        if (empty($value)) {
            return '';
        }

        return implode(',', array_map('trim', explode(',', $value)));
    }

    /**
     * Render the HelperForm.
     *
     * @return string HTML
     */
    private function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Acclimation Bundler Settings'),
                    'icon'  => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type'  => 'text',
                        'label' => $this->l('Acclimation kit product IDs'),
                        'name'  => 'PHYTO_ACCLIM_PRODUCTS',
                        'desc'  => $this->l('Comma-separated product IDs that form the acclimation kit (e.g. 42,57,61).'),
                        'class' => 'fixed-width-xxl',
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Trigger stage IDs'),
                        'name'  => 'PHYTO_ACCLIM_STAGES',
                        'desc'  => $this->l('Comma-separated growth-stage IDs from the phyto_growth_stage module that trigger the widget.'),
                        'class' => 'fixed-width-xxl',
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Fallback trigger tags'),
                        'name'  => 'PHYTO_ACCLIM_TAGS',
                        'desc'  => $this->l('Comma-separated product tags used when the growth-stage module is not installed (e.g. TC,deflasked,tissue-culture).'),
                        'class' => 'fixed-width-xxl',
                    ],
                    [
                        'type'    => 'text',
                        'label'   => $this->l('Bundle discount (%)'),
                        'name'    => 'PHYTO_ACCLIM_DISCOUNT',
                        'desc'    => $this->l('Percentage discount applied when customer adds all kit items at once. Set 0 to disable.'),
                        'class'   => 'fixed-width-sm',
                        'suffix'  => '%',
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Widget headline'),
                        'name'  => 'PHYTO_ACCLIM_HEADLINE',
                        'desc'  => $this->l('Headline displayed in the cart widget.'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->l('Max suggestions shown'),
                        'name'  => 'PHYTO_ACCLIM_MAX_SHOW',
                        'desc'  => $this->l('Maximum number of kit products to display in the widget.'),
                        'class' => 'fixed-width-sm',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module          = $this;
        $helper->table           = $this->table;
        $helper->name_controller = $this->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $helper->submit_action   = 'submitPhytoAcclimBundler';

        $helper->fields_value = [
            'PHYTO_ACCLIM_PRODUCTS' => Configuration::get('PHYTO_ACCLIM_PRODUCTS'),
            'PHYTO_ACCLIM_STAGES'   => Configuration::get('PHYTO_ACCLIM_STAGES'),
            'PHYTO_ACCLIM_TAGS'     => Configuration::get('PHYTO_ACCLIM_TAGS'),
            'PHYTO_ACCLIM_DISCOUNT' => Configuration::get('PHYTO_ACCLIM_DISCOUNT'),
            'PHYTO_ACCLIM_HEADLINE' => Configuration::get('PHYTO_ACCLIM_HEADLINE'),
            'PHYTO_ACCLIM_MAX_SHOW' => Configuration::get('PHYTO_ACCLIM_MAX_SHOW'),
        ];

        return $helper->generateForm([$fields_form]);
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayHeader — inject kit product data as JSON
     * ------------------------------------------------------------------ */

    public function hookDisplayHeader($params)
    {
        $controller = $this->context->controller;

        // Only act on the cart / order pages
        if (
            !($controller instanceof CartController)
            && !($controller instanceof OrderController)
        ) {
            return '';
        }

        $kitProductIds = $this->getConfigIdList('PHYTO_ACCLIM_PRODUCTS');
        if (empty($kitProductIds)) {
            return '';
        }

        $idLang     = (int) $this->context->language->id;
        $maxShow    = (int) Configuration::get('PHYTO_ACCLIM_MAX_SHOW');
        $kitItems   = $this->buildKitPayload($kitProductIds, $idLang, $maxShow);

        if (empty($kitItems)) {
            return '';
        }

        // Determine trigger product IDs currently in the cart
        $triggerStages = $this->getConfigIdList('PHYTO_ACCLIM_STAGES');
        $triggerTags   = $this->getConfigList('PHYTO_ACCLIM_TAGS');
        $cartProducts  = $this->getCartProductIds();

        // Build trigger data for JS: cart product IDs with their stage / tag info
        $cartTriggerMap = $this->buildCartTriggerMap($cartProducts, $triggerStages, $triggerTags, $idLang);

        $payload = [
            'kitItems'      => $kitItems,
            'cartTriggers'  => $cartTriggerMap,
            'cartProductIds' => array_values($cartProducts),
            'discount'      => (int) Configuration::get('PHYTO_ACCLIM_DISCOUNT'),
            'headline'      => Configuration::get('PHYTO_ACCLIM_HEADLINE'),
            'addToCartUrl'  => $this->context->link->getPageLink('cart', true, null, ['ajax' => 1, 'action' => 'update']),
            'staticToken'   => Tools::getToken(false),
        ];

        // Register CSS & JS assets
        $controller->registerStylesheet(
            'phyto-acclim-front-css',
            'modules/' . $this->name . '/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );

        $controller->registerJavascript(
            'phyto-acclim-front-js',
            'modules/' . $this->name . '/views/js/acclimation.js',
            ['position' => 'bottom', 'priority' => 150]
        );

        return '<script type="text/javascript">var phytoAcclimData = '
            . json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP)
            . ';</script>';
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayShoppingCartFooter — render the widget container
     * ------------------------------------------------------------------ */

    public function hookDisplayShoppingCartFooter($params)
    {
        $kitProductIds = $this->getConfigIdList('PHYTO_ACCLIM_PRODUCTS');
        if (empty($kitProductIds)) {
            return '';
        }

        $idLang   = (int) $this->context->language->id;
        $maxShow  = (int) Configuration::get('PHYTO_ACCLIM_MAX_SHOW');
        $kitItems = $this->buildKitPayload($kitProductIds, $idLang, $maxShow);

        if (empty($kitItems)) {
            return '';
        }

        $discount = (int) Configuration::get('PHYTO_ACCLIM_DISCOUNT');
        $headline = Configuration::get('PHYTO_ACCLIM_HEADLINE');

        $this->context->smarty->assign([
            'phyto_acclim_kit_items' => $kitItems,
            'phyto_acclim_discount'  => $discount,
            'phyto_acclim_headline'  => $headline,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/cart_widget.tpl');
    }

    /* ------------------------------------------------------------------
     *  HELPER: Build kit product payload for JSON / Smarty
     * ------------------------------------------------------------------ */

    /**
     * Assemble product data for kit items.
     *
     * @param int[] $productIds
     * @param int   $idLang
     * @param int   $maxShow
     *
     * @return array
     */
    private function buildKitPayload(array $productIds, $idLang, $maxShow)
    {
        $items = [];

        foreach ($productIds as $idProduct) {
            if (count($items) >= $maxShow) {
                break;
            }

            $product = new Product($idProduct, false, $idLang);
            if (!Validate::isLoadedObject($product) || !$product->active) {
                continue;
            }

            $cover    = Product::getCover($idProduct);
            $imageUrl = '';
            if ($cover && !empty($cover['id_image'])) {
                $imageUrl = $this->context->link->getImageLink(
                    $product->link_rewrite,
                    $cover['id_image'],
                    'small_default'
                );
            }

            $items[] = [
                'id_product' => (int) $idProduct,
                'name'       => $product->name,
                'price'      => Product::getPriceStatic($idProduct, true),
                'price_fmt'  => Tools::displayPrice(Product::getPriceStatic($idProduct, true)),
                'image_url'  => $imageUrl,
                'url'        => $this->context->link->getProductLink($product),
            ];
        }

        return $items;
    }

    /* ------------------------------------------------------------------
     *  HELPER: Build trigger map for cart products
     * ------------------------------------------------------------------ */

    /**
     * For each cart product, determine whether it matches a trigger stage
     * or trigger tag.
     *
     * @param int[]  $cartProductIds
     * @param int[]  $triggerStages
     * @param string[] $triggerTags
     * @param int    $idLang
     *
     * @return array  [ { id_product, triggered: bool }, ... ]
     */
    private function buildCartTriggerMap(array $cartProductIds, array $triggerStages, array $triggerTags, $idLang)
    {
        $map = [];

        $useGrowthStage = !empty($triggerStages)
            && Module::isInstalled('phyto_growth_stage')
            && Module::isEnabled('phyto_growth_stage');

        foreach ($cartProductIds as $idProduct) {
            $triggered = false;

            // Check growth-stage trigger
            if ($useGrowthStage) {
                $triggered = $this->productMatchesStages((int) $idProduct, $triggerStages);
            }

            // Fallback: check tags if growth-stage module unavailable or not matched
            if (!$triggered && !empty($triggerTags)) {
                $triggered = $this->productMatchesTags((int) $idProduct, $triggerTags, $idLang);
            }

            $map[] = [
                'id_product' => (int) $idProduct,
                'triggered'  => $triggered,
            ];
        }

        return $map;
    }

    /**
     * Check if a product is assigned to any of the trigger stages.
     *
     * @param int   $idProduct
     * @param int[] $stageIds
     *
     * @return bool
     */
    private function productMatchesStages($idProduct, array $stageIds)
    {
        if (empty($stageIds)) {
            return false;
        }

        $stageIn = implode(',', array_map('intval', $stageIds));

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT 1 FROM `' . _DB_PREFIX_ . 'phyto_growth_stage_product`
             WHERE `id_product` = ' . (int) $idProduct . '
             AND `id_stage` IN (' . $stageIn . ')'
        );

        return !empty($row);
    }

    /**
     * Check if a product has any of the trigger tags.
     *
     * @param int      $idProduct
     * @param string[] $tags
     * @param int      $idLang
     *
     * @return bool
     */
    private function productMatchesTags($idProduct, array $tags, $idLang)
    {
        if (empty($tags)) {
            return false;
        }

        $productTags = Tag::getProductTags($idProduct);
        if (empty($productTags) || !isset($productTags[$idLang])) {
            return false;
        }

        $productTagsLower = array_map('mb_strtolower', $productTags[$idLang]);
        foreach ($tags as $tag) {
            if (in_array(mb_strtolower(trim($tag)), $productTagsLower, true)) {
                return true;
            }
        }

        return false;
    }

    /* ------------------------------------------------------------------
     *  HELPER: Retrieve current cart product IDs
     * ------------------------------------------------------------------ */

    /**
     * @return int[]
     */
    private function getCartProductIds()
    {
        $cart = $this->context->cart;
        if (!Validate::isLoadedObject($cart)) {
            return [];
        }

        $products = $cart->getProducts();
        $ids      = [];

        foreach ($products as $p) {
            $ids[] = (int) $p['id_product'];
        }

        return array_unique($ids);
    }

    /* ------------------------------------------------------------------
     *  HELPER: Parse comma-separated config lists
     * ------------------------------------------------------------------ */

    /**
     * Return an array of integers from a comma-separated config value.
     *
     * @param string $key
     * @return int[]
     */
    private function getConfigIdList($key)
    {
        $raw = Configuration::get($key);
        if (empty($raw)) {
            return [];
        }

        return array_filter(array_map('intval', explode(',', $raw)));
    }

    /**
     * Return an array of trimmed strings from a comma-separated config value.
     *
     * @param string $key
     * @return string[]
     */
    private function getConfigList($key)
    {
        $raw = Configuration::get($key);
        if (empty($raw)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $raw)));
    }
}
