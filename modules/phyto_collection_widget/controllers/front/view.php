<?php
/**
 * View FrontController
 *
 * Publicly accessible page showing a customer's shared plant collection.
 * Only displays items where is_public = 1. Personal notes are never shown.
 *
 * URL: /module/phyto_collection_widget/view?customer=<md5(id_customer)>
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Collection_WidgetViewModuleFrontController extends ModuleFrontController
{
    /** @var bool Public page — no authentication required */
    public $auth = false;

    /** @var bool Use SSL */
    public $ssl = true;

    public $display_column_left  = false;
    public $display_column_right = false;

    public function initContent()
    {
        parent::initContent();

        $this->context->controller->registerStylesheet(
            'phyto-coll-front',
            'modules/phyto_collection_widget/views/css/front.css',
            ['media' => 'all', 'priority' => 150]
        );

        // Public collections must be enabled globally
        if (!(bool) Configuration::get('PHYTO_COLL_ALLOW_PUBLIC', null, null, null, true)) {
            $this->errors[] = $this->module->l('Public collections are not enabled on this shop.', 'view');
            $this->setTemplate('module:phyto_collection_widget/views/templates/front/view.tpl');
            return;
        }

        $hash = Tools::getValue('customer', '');

        if (empty($hash) || !preg_match('/^[a-f0-9]{32}$/', $hash)) {
            Tools::redirect($this->context->link->getPageLink('index'));
        }

        // Look up customer by comparing md5(id_customer)
        $customer = $this->findCustomerByHash($hash);

        if (!$customer) {
            $this->errors[] = $this->module->l('Collection not found.', 'view');
            $this->setTemplate('module:phyto_collection_widget/views/templates/front/view.tpl');
            return;
        }

        $idCustomer = (int) $customer['id_customer'];
        $idLang     = (int) $this->context->language->id;
        $idShop     = (int) $this->context->shop->id;

        $items = $this->getPublicItems($idCustomer, $idLang, $idShop);

        // If nothing is public, show an empty state
        $ownerName = htmlspecialchars($customer['firstname'] . ' ' . mb_substr($customer['lastname'], 0, 1) . '.');

        $this->context->smarty->assign([
            'phyto_coll_items'        => $items,
            'phyto_coll_owner_name'   => $ownerName,
            'phyto_coll_customer_hash' => $hash,
        ]);

        $this->setTemplate('module:phyto_collection_widget/views/templates/front/view.tpl');
    }

    /* ------------------------------------------------------------------
     *  Data helpers
     * ------------------------------------------------------------------ */

    /**
     * Find a customer whose MD5 hash of id_customer matches the provided hash.
     * Iterates only active customers to limit exposure.
     *
     * @param string $hash
     * @return array|false
     */
    private function findCustomerByHash($hash)
    {
        // Fetch all active customer IDs in batches and compare hash
        // This avoids exposing all customer data in a single query.
        $limit  = 500;
        $offset = 0;

        do {
            $rows = Db::getInstance()->executeS(
                'SELECT `id_customer`, `firstname`, `lastname`
                 FROM `' . _DB_PREFIX_ . 'customer`
                 WHERE `active` = 1
                   AND `deleted` = 0
                 ORDER BY `id_customer` ASC
                 LIMIT ' . $limit . ' OFFSET ' . $offset
            );

            if (!is_array($rows) || empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                if (md5((string) $row['id_customer']) === $hash) {
                    return $row;
                }
            }

            $offset += $limit;
        } while (count($rows) === $limit);

        return false;
    }

    /**
     * Fetch all public collection items for a customer, with product data.
     * Personal notes are deliberately excluded from this query.
     *
     * @param int $idCustomer
     * @param int $idLang
     * @param int $idShop
     * @return array
     */
    private function getPublicItems($idCustomer, $idLang, $idShop)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT
                a.`id_item`,
                a.`id_product`,
                a.`date_acquired`,
                pl.`name`           AS product_name,
                pl.`link_rewrite`   AS product_rewrite,
                i.`id_image`
             FROM `' . _DB_PREFIX_ . 'phyto_collection_item` a
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                 ON pl.`id_product` = a.`id_product`
                AND pl.`id_lang`    = ' . $idLang . '
                AND pl.`id_shop`    = ' . $idShop . '
             LEFT JOIN `' . _DB_PREFIX_ . 'image` i
                 ON i.`id_product` = a.`id_product`
                AND i.`cover`      = 1
             WHERE a.`id_customer` = ' . $idCustomer . '
               AND a.`is_public`   = 1
             ORDER BY a.`date_acquired` DESC, a.`date_add` DESC'
        );

        if (!is_array($rows)) {
            return [];
        }

        foreach ($rows as &$row) {
            $row['product_url'] = $this->context->link->getProductLink(
                (int) $row['id_product'],
                $row['product_rewrite'],
                null,
                null,
                $idLang,
                $idShop
            );

            if (!empty($row['id_image'])) {
                $row['image_url'] = $this->context->link->getImageLink(
                    $row['product_rewrite'],
                    $row['id_product'] . '-' . $row['id_image'],
                    ImageType::getFormattedName('home')
                );
            } else {
                $row['image_url'] = $this->context->link->getImageLink(
                    $row['product_rewrite'],
                    $this->context->language->iso_code . '-default',
                    ImageType::getFormattedName('home')
                );
            }
        }
        unset($row);

        return $rows;
    }
}
