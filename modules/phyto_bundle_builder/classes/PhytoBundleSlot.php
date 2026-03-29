<?php
/**
 * PhytoBundleSlot — ObjectModel for slots within a bundle.
 *
 * Each slot represents one "choice" the customer must make, e.g. "Pick a pot".
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoBundleSlot extends ObjectModel
{
    /** @var int */
    public $id_bundle = 0;

    /** @var string */
    public $slot_name = '';

    /** @var string */
    public $slot_type = '';

    /** @var int Category that restricts product choices; 0 = any */
    public $id_category = 0;

    /** @var int */
    public $required = 1;

    /** @var int */
    public $position = 0;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_bundle_slot',
        'primary' => 'id_slot',
        'fields'  => [
            'id_bundle'   => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true],
            'slot_name'   => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 100],
            'slot_type'   => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50],
            'id_category' => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt'],
            'required'    => ['type' => self::TYPE_INT,    'validate' => 'isBool'],
            'position'    => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt'],
        ],
    ];

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Return products eligible for a given slot.
     *
     * When id_category > 0, restrict to that category.
     * An optional search query filters by product name or reference.
     *
     * @param int    $idSlot
     * @param int    $idLang
     * @param string $search
     * @param int    $limit
     *
     * @return array  Each row: id_product, name, price, image_url, reference
     */
    public static function getProductsForSlot(int $idSlot, int $idLang, string $search = '', int $limit = 50): array
    {
        $slot = new self($idSlot);
        if (!Validate::isLoadedObject($slot)) {
            return [];
        }

        $idCategory = (int) $slot->id_category;
        $searchCond = '';

        if ($search !== '') {
            $safe       = pSQL($search);
            $searchCond = ' AND (pl.`name` LIKE \'%' . $safe . '%\' OR p.`reference` LIKE \'%' . $safe . '%\')';
        }

        $categoryCond = '';
        if ($idCategory > 0) {
            $categoryCond = ' INNER JOIN `' . _DB_PREFIX_ . 'category_product` cp
                ON cp.`id_product` = p.`id_product`
                AND cp.`id_category` = ' . $idCategory;
        }

        $idShop = (int) Context::getContext()->shop->id;

        $rows = Db::getInstance()->executeS(
            'SELECT DISTINCT p.`id_product`, pl.`name`, p.`reference`,
                    ps.`price`,
                    i.`id_image`
             FROM `' . _DB_PREFIX_ . 'product` p
             INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
                ON ps.`id_product` = p.`id_product`
                AND ps.`id_shop` = ' . $idShop . '
                AND ps.`active` = 1
             INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON pl.`id_product` = p.`id_product`
                AND pl.`id_lang` = ' . $idLang . '
                AND pl.`id_shop` = ' . $idShop .
            $categoryCond . '
             LEFT JOIN `' . _DB_PREFIX_ . 'image` i
                ON i.`id_product` = p.`id_product`
                AND i.`cover` = 1
             WHERE p.`active` = 1
             ' . $searchCond . '
             ORDER BY pl.`name` ASC
             LIMIT ' . (int) $limit
        ) ?: [];

        $link    = Context::getContext()->link;
        $result  = [];

        foreach ($rows as $row) {
            $imageUrl = '';
            if ($row['id_image']) {
                $imageUrl = $link->getImageLink(
                    'product',
                    $row['id_product'] . '-' . $row['id_image'],
                    ImageType::getFormattedName('small')
                );
            }

            $result[] = [
                'id_product' => (int) $row['id_product'],
                'name'       => $row['name'],
                'reference'  => $row['reference'],
                'price'      => (float) $row['price'],
                'image_url'  => $imageUrl,
            ];
        }

        return $result;
    }
}
