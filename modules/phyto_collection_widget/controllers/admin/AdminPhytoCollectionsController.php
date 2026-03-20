<?php
/**
 * AdminPhytoCollectionsController
 *
 * Read-only back-office overview of all customer plant collection items.
 * Displays customer name, product name, public status, and acquisition date.
 * No add/edit form — view only.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoCollectionsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'phyto_collection_item';
        $this->className   = 'ObjectModel';
        $this->lang        = false;
        $this->noLink      = true;
        $this->explicitSelect = true;

        parent::__construct();

        $this->page_header_toolbar_title = $this->l('Plant Collections');

        // Columns
        $this->fields_list = [
            'id_item' => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'class'   => 'fixed-width-xs',
            ],
            'customer_name' => [
                'title'   => $this->l('Customer'),
                'filter_key' => 'a!id_customer',
                'havingFilter' => true,
            ],
            'product_name' => [
                'title'   => $this->l('Plant / Product'),
                'havingFilter' => true,
            ],
            'is_public' => [
                'title'   => $this->l('Public'),
                'align'   => 'center',
                'active'  => 'status',
                'type'    => 'bool',
                'class'   => 'fixed-width-sm',
                'orderby' => true,
            ],
            'date_acquired' => [
                'title'   => $this->l('Date Acquired'),
                'type'    => 'date',
                'align'   => 'center',
            ],
            'date_add' => [
                'title'   => $this->l('Added'),
                'type'    => 'datetime',
                'align'   => 'center',
            ],
        ];

        // Disable all modification actions
        $this->addRowAction('details');

        // Default sort
        $this->_defaultOrderBy  = 'date_acquired';
        $this->_defaultOrderWay = 'DESC';
    }

    /**
     * Build the SQL JOIN to pull customer name and product name into the list.
     */
    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false)
    {
        // Build a custom query so we can join customer and product tables
        $this->_join .= '
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu
                ON (cu.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (pl.`id_product` = a.`id_product`
                    AND pl.`id_lang`    = ' . (int) $idLang . '
                    AND pl.`id_shop`    = ' . (int) $this->context->shop->id . ')
        ';

        $this->_select .= '
            CONCAT(cu.`firstname`, \' \', cu.`lastname`) AS customer_name,
            pl.`name` AS product_name
        ';

        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);
    }

    /**
     * Disable the "Add new" button — this is a read-only view.
     */
    public function renderList()
    {
        $this->toolbar_btn = [];
        return parent::renderList();
    }

    /**
     * Disable edit / add form.
     */
    public function renderForm()
    {
        return '';
    }

    /**
     * Override to prevent any POST modifications.
     */
    public function postProcess()
    {
        // Block add/edit/delete actions — read-only controller
        if (Tools::isSubmit('submitAdd' . $this->table)
            || Tools::isSubmit('delete' . $this->table)
            || Tools::isSubmit('deleteAll')) {
            $this->errors[] = $this->l('This section is read-only.');
            return false;
        }

        return parent::postProcess();
    }

    /**
     * Render detail view for a single collection item.
     */
    public function renderDetails()
    {
        $idItem = (int) Tools::getValue($this->identifier);
        if ($idItem <= 0) {
            return '';
        }

        $row = Db::getInstance()->getRow(
            'SELECT a.*,
                    CONCAT(cu.`firstname`, \' \', cu.`lastname`) AS customer_name,
                    cu.`email` AS customer_email,
                    pl.`name` AS product_name
             FROM `' . _DB_PREFIX_ . 'phyto_collection_item` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` cu
                 ON cu.`id_customer` = a.`id_customer`
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                 ON pl.`id_product` = a.`id_product`
                AND pl.`id_lang`    = ' . (int) $this->context->language->id . '
                AND pl.`id_shop`    = ' . (int) $this->context->shop->id . '
             WHERE a.`id_item` = ' . $idItem
        );

        if (!$row) {
            $this->errors[] = $this->l('Collection item not found.');
            return '';
        }

        $this->context->smarty->assign([
            'phyto_coll_item' => $row,
            'back_url'        => $this->context->link->getAdminLink('AdminPhytoCollections'),
        ]);

        return $this->createTemplate('details.tpl')->fetch();
    }
}
