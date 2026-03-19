<?php
/**
 * Phyto Plant Collection Widget
 *
 * Allow logged-in buyers to mark purchased plants in their personal collection,
 * add private notes, and optionally share their collection publicly.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Collection_Widget extends Module
{
    public function __construct()
    {
        $this->name          = 'phyto_collection_widget';
        $this->tab           = 'front_office_features';
        $this->version       = '1.0.0';
        $this->author        = 'PhytoCommerce';
        $this->need_instance = 0;
        $this->bootstrap     = true;

        parent::__construct();

        $this->displayName = $this->l('Phyto Plant Collection Widget');
        $this->description = $this->l('Allow customers to track and manage their plant collection from purchased orders.');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    /* ------------------------------------------------------------------
     *  INSTALL / UNINSTALL
     * ------------------------------------------------------------------ */

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayOrderConfirmation')
            && $this->registerHook('displayProductExtraContent')
            && $this->registerHook('displayMyAccountBlock')
            && $this->registerHook('actionProductDelete')
            && $this->installTab()
            && $this->runSql('install');
    }

    public function uninstall()
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    /* ------------------------------------------------------------------
     *  SQL HELPER
     * ------------------------------------------------------------------ */

    private function runSql($filename)
    {
        $path = dirname(__FILE__) . '/sql/' . $filename . '.sql';
        if (!file_exists($path)) {
            return false;
        }

        $sql = str_replace(
            ['PREFIX_', 'ENGINE_TYPE'],
            [_DB_PREFIX_, _MYSQL_ENGINE_],
            file_get_contents($path)
        );

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

    /* ------------------------------------------------------------------
     *  TAB HELPERS
     * ------------------------------------------------------------------ */

    private function installTab()
    {
        $tab             = new Tab();
        $tab->active     = 1;
        $tab->class_name = 'AdminPhytoCollections';
        $tab->module     = $this->name;
        $tab->id_parent  = (int) Tab::getIdFromClassName('AdminCustomers');

        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Plant Collections';
        }

        return $tab->add();
    }

    private function uninstallTab()
    {
        $id = (int) Tab::getIdFromClassName('AdminPhytoCollections');
        if ($id) {
            $tab = new Tab($id);
            return $tab->delete();
        }
        return true;
    }

    /* ------------------------------------------------------------------
     *  MODULE CONFIGURATION PAGE
     * ------------------------------------------------------------------ */

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitPhytoCollConfig')) {
            $allowPublic = (int) Tools::getValue('PHYTO_COLL_ALLOW_PUBLIC');
            Configuration::updateValue('PHYTO_COLL_ALLOW_PUBLIC', $allowPublic);
            $output .= $this->displayConfirmation($this->l('Settings saved.'));
        }

        return $output . $this->renderConfigForm();
    }

    private function renderConfigForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar       = false;
        $helper->table              = $this->table;
        $helper->module             = $this;
        $helper->default_form_language    = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier    = $this->identifier;
        $helper->submit_action = 'submitPhytoCollConfig';
        $helper->currentIndex  = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name
            . '&tab_module=' . $this->tab
            . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigFormArray()]);
    }

    private function getConfigFormArray()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Plant Collection Settings'),
                    'icon'  => 'icon-leaf',
                ],
                'input' => [
                    [
                        'type'    => 'switch',
                        'label'   => $this->l('Allow public collections'),
                        'name'    => 'PHYTO_COLL_ALLOW_PUBLIC',
                        'is_bool' => true,
                        'desc'    => $this->l('When enabled, customers may make their collection visible to anyone via a public URL.'),
                        'values'  => [
                            [
                                'id'    => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id'    => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    private function getConfigFieldsValues()
    {
        return [
            'PHYTO_COLL_ALLOW_PUBLIC' => (int) Configuration::get('PHYTO_COLL_ALLOW_PUBLIC', null, null, null, 1),
        ];
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayOrderConfirmation
     *  Auto-add ordered products to the customer's collection.
     * ------------------------------------------------------------------ */

    public function hookDisplayOrderConfirmation($params)
    {
        /** @var Order $order */
        $order = isset($params['order']) ? $params['order'] : null;
        if (!$order || !Validate::isLoadedObject($order)) {
            return '';
        }

        $idCustomer = (int) $order->id_customer;
        $idOrder    = (int) $order->id;
        $dateAcquired = date('Y-m-d', strtotime($order->date_add));
        $now          = date('Y-m-d H:i:s');

        $products = $order->getProducts();
        if (empty($products)) {
            return '';
        }

        foreach ($products as $product) {
            $idProduct = (int) $product['product_id'];
            if ($idProduct <= 0) {
                continue;
            }

            // Check if item already exists for this customer + product
            $existing = Db::getInstance()->getRow(
                'SELECT `id_item`, `id_order`
                 FROM `' . _DB_PREFIX_ . 'phyto_collection_item`
                 WHERE `id_customer` = ' . $idCustomer . '
                   AND `id_product`  = ' . $idProduct
            );

            if ($existing) {
                // Update to more recent order if applicable
                $existingOrder = new Order((int) $existing['id_order']);
                $existingDate  = Validate::isLoadedObject($existingOrder) ? $existingOrder->date_add : '0000-00-00';
                if ($order->date_add > $existingDate) {
                    Db::getInstance()->update(
                        'phyto_collection_item',
                        [
                            'id_order'  => $idOrder,
                            'date_upd'  => $now,
                        ],
                        '`id_item` = ' . (int) $existing['id_item']
                    );
                }
            } else {
                Db::getInstance()->insert('phyto_collection_item', [
                    'id_customer'   => $idCustomer,
                    'id_product'    => $idProduct,
                    'id_order'      => $idOrder,
                    'personal_note' => '',
                    'is_public'     => 0,
                    'date_acquired' => pSQL($dateAcquired),
                    'date_add'      => $now,
                    'date_upd'      => $now,
                ]);
            }
        }

        return '';
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayProductExtraContent
     *  Show "In your collection" badge if customer owns this product.
     * ------------------------------------------------------------------ */

    public function hookDisplayProductExtraContent($params)
    {
        if (!$this->context->customer->isLogged()) {
            return [];
        }

        $idCustomer = (int) $this->context->customer->id;
        $idProduct  = (int) $params['product']->id;

        $item = Db::getInstance()->getRow(
            'SELECT `id_item`, `date_acquired`
             FROM `' . _DB_PREFIX_ . 'phyto_collection_item`
             WHERE `id_customer` = ' . $idCustomer . '
               AND `id_product`  = ' . $idProduct
        );

        if (!$item) {
            return [];
        }

        $collectionUrl = $this->context->link->getModuleLink(
            $this->name,
            'collection',
            [],
            true
        );

        $this->context->smarty->assign([
            'phyto_coll_date_acquired' => $item['date_acquired'],
            'phyto_coll_collection_url' => $collectionUrl,
        ]);

        $content = $this->display(__FILE__, 'views/templates/hook/product_extra_content.tpl');

        $tab = new PrestaShop\PrestaShop\Core\Product\ProductExtraContent();
        $tab->setTitle($this->l('My Collection'))
            ->setContent($content);

        return [$tab];
    }

    /* ------------------------------------------------------------------
     *  HOOK: displayMyAccountBlock
     *  Render a "My Plant Collection" link in the My Account area.
     * ------------------------------------------------------------------ */

    public function hookDisplayMyAccountBlock($params)
    {
        $collectionUrl = $this->context->link->getModuleLink(
            $this->name,
            'collection',
            [],
            true
        );

        $this->context->smarty->assign([
            'phyto_coll_collection_url' => $collectionUrl,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/my_account_block.tpl');
    }

    /* ------------------------------------------------------------------
     *  HOOK: actionProductDelete
     *  Clean up collection items when a product is deleted.
     * ------------------------------------------------------------------ */

    public function hookActionProductDelete($params)
    {
        $idProduct = isset($params['id_product']) ? (int) $params['id_product'] : 0;
        if ($idProduct <= 0 && isset($params['product'])) {
            $idProduct = (int) $params['product']->id;
        }

        if ($idProduct > 0) {
            Db::getInstance()->delete(
                'phyto_collection_item',
                '`id_product` = ' . $idProduct
            );
        }
    }
}
