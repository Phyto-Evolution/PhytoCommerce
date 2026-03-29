<?php
/**
 * PhytoBundle — ObjectModel for bundle definitions.
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoBundle extends ObjectModel
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $description = '';

    /** @var string 'percent'|'amount' */
    public $discount_type = 'percent';

    /** @var float */
    public $discount_value = 0.00;

    /** @var int */
    public $active = 1;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'phyto_bundle',
        'primary'   => 'id_bundle',
        'multilang' => true,
        'fields'    => [
            'discount_type'  => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'values' => ['percent', 'amount']],
            'discount_value' => ['type' => self::TYPE_FLOAT,  'validate' => 'isPrice'],
            'active'         => ['type' => self::TYPE_INT,    'validate' => 'isBool'],
            'date_add'       => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd'       => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            // Multilang fields
            'name'           => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'description'    => ['type' => self::TYPE_HTML,   'lang' => true, 'validate' => 'isCleanHtml'],
        ],
    ];

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Return all active bundles for the front-end.
     *
     * @param int $idLang
     *
     * @return array
     */
    public static function getActiveBundles(int $idLang): array
    {
        return Db::getInstance()->executeS(
            'SELECT b.*, bl.`name`, bl.`description`
             FROM `' . _DB_PREFIX_ . 'phyto_bundle` b
             LEFT JOIN `' . _DB_PREFIX_ . 'phyto_bundle_lang` bl
                ON bl.`id_bundle` = b.`id_bundle`
                AND bl.`id_lang` = ' . $idLang . '
             WHERE b.`active` = 1
             ORDER BY b.`id_bundle` ASC'
        ) ?: [];
    }

    /**
     * Return a single bundle row with lang data.
     *
     * @param int $idBundle
     * @param int $idLang
     *
     * @return array|false
     */
    public static function getBundleWithLang(int $idBundle, int $idLang)
    {
        return Db::getInstance()->getRow(
            'SELECT b.*, bl.`name`, bl.`description`
             FROM `' . _DB_PREFIX_ . 'phyto_bundle` b
             LEFT JOIN `' . _DB_PREFIX_ . 'phyto_bundle_lang` bl
                ON bl.`id_bundle` = b.`id_bundle`
                AND bl.`id_lang` = ' . $idLang . '
             WHERE b.`id_bundle` = ' . $idBundle
        );
    }

    /**
     * Get all slots for a bundle, ordered by position.
     *
     * @param int $idBundle
     *
     * @return array
     */
    public static function getSlots(int $idBundle): array
    {
        return Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_bundle_slot`
             WHERE `id_bundle` = ' . $idBundle . '
             ORDER BY `position` ASC, `id_slot` ASC'
        ) ?: [];
    }

    // -------------------------------------------------------------------------
    // Lifecycle overrides
    // -------------------------------------------------------------------------

    public function add($autoDate = true, $nullValues = false): bool
    {
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');

        return parent::add($autoDate, $nullValues);
    }

    public function update($nullValues = false): bool
    {
        $this->date_upd = date('Y-m-d H:i:s');

        return parent::update($nullValues);
    }

    /**
     * Delete bundle and all its slots/lang rows.
     *
     * @return bool
     */
    public function delete(): bool
    {
        Db::getInstance()->delete('phyto_bundle_slot', '`id_bundle` = ' . (int) $this->id);
        Db::getInstance()->delete('phyto_bundle_lang', '`id_bundle` = ' . (int) $this->id);

        return parent::delete();
    }
}
