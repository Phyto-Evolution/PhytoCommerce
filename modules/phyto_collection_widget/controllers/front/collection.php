<?php
/**
 * Collection FrontController
 *
 * Displays the logged-in customer's personal plant collection.
 * Handles AJAX updates for personal_note, is_public toggle, and item removal.
 *
 * URL: /module/phyto_collection_widget/collection
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Collection_WidgetCollectionModuleFrontController extends ModuleFrontController
{
    /** @var bool Require logged-in customer */
    public $auth = true;

    /** @var bool Use SSL */
    public $ssl = true;

    /** @var bool Register stylesheet/js */
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

        $idCustomer   = (int) $this->context->customer->id;
        $allowPublic  = (bool) Configuration::get('PHYTO_COLL_ALLOW_PUBLIC', null, null, null, true);
        $idLang       = (int) $this->context->language->id;
        $idShop       = (int) $this->context->shop->id;

        $items = $this->getCollectionItems($idCustomer, $idLang, $idShop);

        // Public share URL (only meaningful when allow_public is on)
        $publicHash = md5((string) $idCustomer);
        $publicUrl  = $this->context->link->getModuleLink(
            'phyto_collection_widget',
            'view',
            ['customer' => $publicHash],
            true
        );

        // Check if at least one item is marked public
        $hasPublicItems = false;
        foreach ($items as $item) {
            if ((int) $item['is_public'] === 1) {
                $hasPublicItems = true;
                break;
            }
        }

        $this->context->smarty->assign([
            'phyto_coll_items'           => $items,
            'phyto_coll_allow_public'    => $allowPublic,
            'phyto_coll_has_public'      => $hasPublicItems,
            'phyto_coll_public_url'      => $publicUrl,
            'phyto_coll_token'           => Tools::getToken(false),
            'phyto_coll_ajax_url'        => $this->context->link->getModuleLink(
                'phyto_collection_widget',
                'collection',
                [],
                true
            ),
        ]);

        $this->setTemplate('module:phyto_collection_widget/views/templates/front/collection.tpl');
    }

    /**
     * Handle POST requests (AJAX and regular form posts).
     */
    public function postProcess()
    {
        $action     = Tools::getValue('phyto_coll_action');
        $token      = Tools::getValue('token');
        $idCustomer = (int) $this->context->customer->id;

        // CSRF check for all POST actions
        if ($token !== Tools::getToken(false)) {
            if (Tools::getValue('ajax')) {
                $this->ajaxResponse(false, $this->module->l('Invalid security token.', 'collection'));
            }
            $this->errors[] = $this->module->l('Invalid security token.', 'collection');
            return;
        }

        switch ($action) {
            case 'update_note':
                $this->handleUpdateNote($idCustomer);
                break;

            case 'toggle_public':
                $this->handleTogglePublic($idCustomer);
                break;

            case 'remove_item':
                $this->handleRemoveItem($idCustomer);
                break;
        }
    }

    /* ------------------------------------------------------------------
     *  POST action handlers
     * ------------------------------------------------------------------ */

    private function handleUpdateNote($idCustomer)
    {
        $idItem = (int) Tools::getValue('id_item');
        $note   = Tools::getValue('personal_note', '');

        if (!Validate::isCleanHtml($note)) {
            $this->ajaxResponse(false, $this->module->l('Invalid note content.', 'collection'));
            return;
        }

        $ok = $this->updateOwnItem($idCustomer, $idItem, [
            'personal_note' => pSQL($note),
            'date_upd'      => date('Y-m-d H:i:s'),
        ]);

        if (Tools::getValue('ajax')) {
            $this->ajaxResponse($ok, $ok
                ? $this->module->l('Note saved.', 'collection')
                : $this->module->l('Could not save note.', 'collection')
            );
        }
    }

    private function handleTogglePublic($idCustomer)
    {
        $idItem    = (int) Tools::getValue('id_item');
        $allowPublic = (bool) Configuration::get('PHYTO_COLL_ALLOW_PUBLIC', null, null, null, true);

        if (!$allowPublic) {
            $this->ajaxResponse(false, $this->module->l('Public collections are disabled.', 'collection'));
            return;
        }

        // Fetch current value
        $current = Db::getInstance()->getValue(
            'SELECT `is_public`
             FROM `' . _DB_PREFIX_ . 'phyto_collection_item`
             WHERE `id_item`     = ' . $idItem . '
               AND `id_customer` = ' . $idCustomer
        );

        if ($current === false) {
            $this->ajaxResponse(false, $this->module->l('Item not found.', 'collection'));
            return;
        }

        $newValue = ((int) $current === 1) ? 0 : 1;
        $ok = $this->updateOwnItem($idCustomer, $idItem, [
            'is_public' => $newValue,
            'date_upd'  => date('Y-m-d H:i:s'),
        ]);

        if (Tools::getValue('ajax')) {
            $this->ajaxResponse($ok, $ok
                ? $this->module->l('Visibility updated.', 'collection')
                : $this->module->l('Could not update visibility.', 'collection'),
                ['is_public' => $newValue]
            );
        }
    }

    private function handleRemoveItem($idCustomer)
    {
        $idItem = (int) Tools::getValue('id_item');

        $ok = Db::getInstance()->delete(
            'phyto_collection_item',
            '`id_item` = ' . $idItem . ' AND `id_customer` = ' . $idCustomer
        );

        if (Tools::getValue('ajax')) {
            $this->ajaxResponse($ok, $ok
                ? $this->module->l('Item removed from your collection.', 'collection')
                : $this->module->l('Could not remove item.', 'collection')
            );
        }

        // Non-AJAX: redirect back to collection
        Tools::redirect($this->context->link->getModuleLink(
            'phyto_collection_widget',
            'collection',
            [],
            true
        ));
    }

    /* ------------------------------------------------------------------
     *  Data helpers
     * ------------------------------------------------------------------ */

    /**
     * Fetch all collection items for a customer, with product name and thumbnail.
     *
     * @param int $idCustomer
     * @param int $idLang
     * @param int $idShop
     * @return array
     */
    private function getCollectionItems($idCustomer, $idLang, $idShop)
    {
        $rows = Db::getInstance()->executeS(
            'SELECT
                a.`id_item`,
                a.`id_product`,
                a.`id_order`,
                a.`personal_note`,
                a.`is_public`,
                a.`date_acquired`,
                a.`date_add`,
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
             ORDER BY a.`date_acquired` DESC, a.`date_add` DESC'
        );

        if (!is_array($rows)) {
            return [];
        }

        // Append product link and thumbnail URL
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

    /**
     * Update a collection item, verifying ownership.
     *
     * @param int   $idCustomer
     * @param int   $idItem
     * @param array $data
     * @return bool
     */
    private function updateOwnItem($idCustomer, $idItem, array $data)
    {
        if ($idItem <= 0) {
            return false;
        }

        return Db::getInstance()->update(
            'phyto_collection_item',
            $data,
            '`id_item` = ' . $idItem . ' AND `id_customer` = ' . $idCustomer
        );
    }

    /**
     * Emit a JSON response and terminate execution.
     *
     * @param bool   $success
     * @param string $message
     * @param array  $extra
     */
    private function ajaxResponse($success, $message, array $extra = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        die(json_encode(array_merge([
            'success' => (bool) $success,
            'message' => $message,
        ], $extra)));
    }
}
