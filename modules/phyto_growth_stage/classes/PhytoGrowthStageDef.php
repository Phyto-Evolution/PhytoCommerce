<?php
/**
 * PhytoGrowthStageDef - ObjectModel for global growth stage definitions.
 *
 * @author    PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoGrowthStageDef extends ObjectModel
{
    /** @var int */
    public $id_stage;

    /** @var string */
    public $stage_name;

    /** @var string */
    public $stage_code;

    /** @var string */
    public $difficulty;

    /** @var int */
    public $weeks_to_next;

    /** @var string */
    public $description;

    /** @var int */
    public $sort_order;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_growth_stage_def',
        'primary' => 'id_stage',
        'fields'  => [
            'stage_name'    => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 100,
            ],
            'stage_code'    => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isLinkRewrite',
                'required' => true,
                'size'     => 50,
            ],
            'difficulty'    => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'values'   => ['Beginner', 'Intermediate', 'Advanced', 'Expert'],
            ],
            'weeks_to_next' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'description'   => [
                'type'     => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
            ],
            'sort_order'    => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
        ],
    ];

    /**
     * Generate a URL-safe slug from the stage name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function generateCode($name)
    {
        $code = Tools::strtolower(trim($name));
        $code = preg_replace('/[^a-z0-9\s-]/', '', $code);
        $code = preg_replace('/[\s-]+/', '-', $code);

        return Tools::substr($code, 0, 50);
    }

    /**
     * Retrieve all stage definitions ordered by sort_order.
     *
     * @return array
     */
    public static function getAllStages()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_growth_stage_def`
             ORDER BY `sort_order` ASC, `id_stage` ASC'
        );
    }

    /**
     * Retrieve stages assigned to a product (optionally filtered by attribute).
     *
     * @param int      $idProduct
     * @param int|null $idProductAttribute
     *
     * @return array
     */
    public static function getStagesForProduct($idProduct, $idProductAttribute = null)
    {
        $sql = 'SELECT gsd.*, gsp.`id_link`, gsp.`id_product_attribute`, gsp.`weeks_override`
                FROM `' . _DB_PREFIX_ . 'phyto_growth_stage_product` gsp
                LEFT JOIN `' . _DB_PREFIX_ . 'phyto_growth_stage_def` gsd
                    ON gsd.`id_stage` = gsp.`id_stage`
                WHERE gsp.`id_product` = ' . (int) $idProduct;

        if ($idProductAttribute !== null) {
            $sql .= ' AND gsp.`id_product_attribute` = ' . (int) $idProductAttribute;
        }

        $sql .= ' ORDER BY gsd.`sort_order` ASC, gsd.`id_stage` ASC';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Link a product (or combination) to a growth stage.
     *
     * @param int      $idProduct
     * @param int      $idProductAttribute
     * @param int      $idStage
     * @param int|null $weeksOverride
     *
     * @return bool
     */
    public static function assignStageToProduct($idProduct, $idProductAttribute, $idStage, $weeksOverride = null)
    {
        $weeksValue = ($weeksOverride !== null && $weeksOverride !== '')
            ? (int) $weeksOverride
            : 'NULL';

        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'phyto_growth_stage_product`
                (`id_product`, `id_product_attribute`, `id_stage`, `weeks_override`)
                VALUES (' . (int) $idProduct . ', ' . (int) $idProductAttribute . ', ' . (int) $idStage . ', ' . $weeksValue . ')
                ON DUPLICATE KEY UPDATE
                    `id_stage` = ' . (int) $idStage . ',
                    `weeks_override` = ' . $weeksValue;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Remove a product-stage mapping.
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     *
     * @return bool
     */
    public static function removeStageFromProduct($idProduct, $idProductAttribute)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'phyto_growth_stage_product`
             WHERE `id_product` = ' . (int) $idProduct . '
             AND `id_product_attribute` = ' . (int) $idProductAttribute
        );
    }

    /**
     * Remove all stage mappings for a given stage definition.
     *
     * @param int $idStage
     *
     * @return bool
     */
    public static function removeAllProductLinks($idStage)
    {
        return Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'phyto_growth_stage_product`
             WHERE `id_stage` = ' . (int) $idStage
        );
    }

    /**
     * Get the position index (0-based) and total count for a given stage.
     *
     * @param int $idStage
     *
     * @return array ['index' => int, 'total' => int]
     */
    public static function getStagePosition($idStage)
    {
        $stages = self::getAllStages();
        $total  = count($stages);
        $index  = 0;

        foreach ($stages as $i => $stage) {
            if ((int) $stage['id_stage'] === (int) $idStage) {
                $index = $i;
                break;
            }
        }

        return [
            'index' => $index,
            'total' => $total,
        ];
    }
}
