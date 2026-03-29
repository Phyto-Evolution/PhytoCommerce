<?php
/**
 * Phyto Growers Journal — Main module class.
 *
 * Living grow-log attached to each product. Store posts updates with photos;
 * buyers can also post updates on purchased products. Acts as social proof.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoJournalEntry.php';

class Phyto_Growers_Journal extends Module
{
    /** @var string Configuration key for allowing customer posts */
    const CONFIG_ALLOW_CUSTOMER_POSTS = 'PHYTO_JOURNAL_ALLOW_CUSTOMER_POSTS';

    public function __construct()
    {
        $this->name          = 'phyto_growers_journal';
        $this->tab           = 'administration';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l("Grower's Journal");
        $this->description = $this->l('Creates a chronological grow-log attached to each product page, where staff can post progress updates with photos to show how listed plants are developing. When enabled, verified buyers who have purchased the product can also submit their own journal entries, providing authentic social proof and community engagement. Entry approval is managed from a dedicated back-office controller, and customers can view their own posts from the My Account area.');
        $this->ps_versions_compliancy = array('min' => '8.0.0', 'max' => _PS_VERSION_);
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install()
    {
        if (
            !parent::install()
            || !$this->registerHook('displayAdminProductsExtra')
            || !$this->registerHook('displayProductExtraContent')
            || !$this->registerHook('actionProductDelete')
            || !$this->registerHook('displayMyAccountBlock')
            || !$this->installTab()
            || !$this->runSql('install')
        ) {
            $this->uninstall();
            return false;
        }

        Configuration::updateValue(self::CONFIG_ALLOW_CUSTOMER_POSTS, 0);

        return true;
    }

    public function uninstall()
    {
        $this->uninstallTab();
        $this->runSql('uninstall');
        Configuration::deleteByName(self::CONFIG_ALLOW_CUSTOMER_POSTS);

        return parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // Tab management
    // -------------------------------------------------------------------------

    /**
     * Install a hidden admin tab for AdminPhytoGrowersJournal.
     *
     * @return bool
     */
    protected function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminPhytoGrowersJournal';
        $tab->id_parent  = -1; // hidden
        $tab->module     = $this->name;
        $tab->active     = 1;

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = "Grower's Journal";
        }

        return (bool) $tab->add();
    }

    /**
     * Remove the admin tab registered by this module.
     *
     * @return bool
     */
    protected function uninstallTab()
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoGrowersJournal');
        if ($idTab) {
            $tab = new Tab($idTab);
            return (bool) $tab->delete();
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // SQL helper
    // -------------------------------------------------------------------------

    /**
     * Execute an SQL file from the sql/ directory.
     *
     * @param string $type 'install' or 'uninstall'
     * @return bool
     */
    protected function runSql($type)
    {
        $file = dirname(__FILE__) . '/sql/' . $type . '.sql';
        if (!file_exists($file)) {
            return true;
        }

        $sql = file_get_contents($file);
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);

        // Split on semicolons followed by optional whitespace / newlines
        $statements = preg_split('/;\s*[\r\n]+/', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Configuration page (getContent)
    // -------------------------------------------------------------------------

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoJournalConfig')) {
            $allow = (int) Tools::getValue('allow_customer_posts');
            Configuration::updateValue(self::CONFIG_ALLOW_CUSTOMER_POSTS, $allow);
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderConfigForm();
    }

    /**
     * Build the configuration HelperForm.
     *
     * @return string
     */
    protected function renderConfigForm()
    {
        $fields = array(
            array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('General Settings'),
                        'icon'  => 'icon-cogs',
                    ),
                    'input' => array(
                        array(
                            'type'    => 'switch',
                            'label'   => $this->l('Allow customer posts'),
                            'name'    => 'allow_customer_posts',
                            'is_bool' => true,
                            'desc'    => $this->l(
                                'When enabled, logged-in customers who have purchased a product '
                                . 'can submit journal entries (pending approval).'
                            ),
                            'values'  => array(
                                array('id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')),
                                array('id' => 'active_off', 'value' => 0, 'label' => $this->l('No')),
                            ),
                        ),
                    ),
                    'submit' => array(
                        'title' => $this->l('Save'),
                        'class' => 'btn btn-default pull-right',
                    ),
                ),
            ),
        );

        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $helper->default_form_language    = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'submitPhytoJournalConfig';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['allow_customer_posts'] = (int) Configuration::get(self::CONFIG_ALLOW_CUSTOMER_POSTS);

        return $helper->generateForm($fields);
    }

    // -------------------------------------------------------------------------
    // Admin hooks
    // -------------------------------------------------------------------------

    /**
     * Inject journal entries panel into the product edit page.
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $idProduct = (int) ($params['id_product'] ?? Tools::getValue('id_product'));
        if (!$idProduct) {
            return '';
        }

        $entries   = PhytoJournalEntry::getEntriesByProduct($idProduct, false);
        $adminLink = $this->context->link->getAdminLink('AdminPhytoGrowersJournal')
            . '&id_product_filter=' . $idProduct;

        $this->context->smarty->assign(array(
            'phyto_entries'    => $entries,
            'phyto_admin_link' => $adminLink,
            'phyto_add_link'   => $this->context->link->getAdminLink('AdminPhytoGrowersJournal')
                . '&addphyto_growers_journal=1&id_product=' . $idProduct,
            'id_product'       => $idProduct,
        ));

        return $this->display(__FILE__, 'views/templates/hook/admin_product_tab.tpl');
    }

    /**
     * Handle product deletion — remove associated journal entries.
     *
     * @param array $params
     */
    public function hookActionProductDelete($params)
    {
        $idProduct = (int) ($params['id_product'] ?? 0);
        if (!$idProduct) {
            return;
        }

        Db::getInstance()->delete('phyto_journal_entry', 'id_product = ' . $idProduct);
    }

    // -------------------------------------------------------------------------
    // Front-office hooks
    // -------------------------------------------------------------------------

    /**
     * Display chronological timeline of journal entries on the product page.
     *
     * @param array $params
     * @return PrestaShop\PrestaShop\Core\Product\ProductExtraContent|string
     */
    public function hookDisplayProductExtraContent($params)
    {
        $product   = $params['product'] ?? null;
        $idProduct = $product ? (int) $product->id : 0;
        if (!$idProduct) {
            return '';
        }

        $entries = PhytoJournalEntry::getEntriesByProduct($idProduct, true);
        if (empty($entries)) {
            return '';
        }

        $allowCustomerPosts = (bool) Configuration::get(self::CONFIG_ALLOW_CUSTOMER_POSTS);
        $isLogged           = $this->context->customer->isLogged();
        $canPost            = false;

        if ($allowCustomerPosts && $isLogged) {
            $idCustomer = (int) $this->context->customer->id;
            $canPost    = PhytoJournalEntry::customerHasPurchased($idCustomer, $idProduct)
                && !PhytoJournalEntry::hasRecentPost($idCustomer, $idProduct);
        }

        // Build image base URL for photo thumbnails
        $imgBaseUrl = $this->context->link->getBaseLink() . 'img/phyto_journal/';

        $this->context->smarty->assign(array(
            'phyto_entries'      => $entries,
            'phyto_img_base_url' => $imgBaseUrl,
            'phyto_can_post'     => $canPost,
            'phyto_post_url'     => $this->context->link->getModuleLink(
                $this->name,
                'post',
                array('id_product' => $idProduct)
            ),
            'phyto_id_product'   => $idProduct,
        ));

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        // Register CSS
        $this->context->controller->addCSS(
            $this->_path . 'views/css/front.css',
            'all'
        );

        if (class_exists('PrestaShop\PrestaShop\Core\Product\ProductExtraContent')) {
            $extraContent = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
            $extraContent->setTitle($this->l("Grower's Journal"));
            $extraContent->setContent($content);

            return $extraContent;
        }

        // Fallback for older hook behaviour
        return $content;
    }

    /**
     * Show a link in My Account block to the customer's submitted posts.
     *
     * @param array $params
     * @return string
     */
    public function hookDisplayMyAccountBlock($params)
    {
        if (!$this->context->customer->isLogged()) {
            return '';
        }

        $allowCustomerPosts = (bool) Configuration::get(self::CONFIG_ALLOW_CUSTOMER_POSTS);
        if (!$allowCustomerPosts) {
            return '';
        }

        $this->context->smarty->assign(array(
            'phyto_journal_post_url' => $this->context->link->getModuleLink(
                $this->name,
                'post'
            ),
        ));

        return $this->display(__FILE__, 'views/templates/hook/my_account_block.tpl');
    }
}
