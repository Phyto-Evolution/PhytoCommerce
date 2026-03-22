<?php
/**
 * PhytoDispatchLog — ObjectModel for dispatch log entries.
 *
 * One log entry per order (enforced via UNIQUE KEY on id_order).
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   https://opensource.org/licenses/AFL-3.0 AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoDispatchLog extends ObjectModel
{
    /** @var int Order ID (foreign key) */
    public $id_order;

    /** @var string|null Dispatch date (YYYY-MM-DD) */
    public $dispatch_date;

    /** @var float|null Ambient temperature in Celsius at packing time */
    public $temp_celsius;

    /** @var int|null Relative humidity percentage at packing time */
    public $humidity_pct;

    /** @var string|null Packing method used */
    public $packing_method;

    /** @var bool Whether a gel pack was included */
    public $gel_pack = false;

    /** @var bool Whether a heat pack was included */
    public $heat_pack = false;

    /** @var int|null Estimated transit days */
    public $transit_days;

    /** @var string|null Name of the staff member who packed the order */
    public $staff_name;

    /** @var string|null Free-text notes */
    public $notes;

    /** @var string|null Filename of the dispatch photo (stored in img/phyto_dispatch/) */
    public $photo_filename;

    /** @var string Record creation datetime */
    public $date_add;

    /** @var string Record last-updated datetime */
    public $date_upd;

    /**
     * ObjectModel definition.
     *
     * @var array
     */
    public static $definition = [
        'table'   => 'phyto_dispatch_log',
        'primary' => 'id_log',
        'fields'  => [
            'id_order' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'dispatch_date' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
                'allow_null' => true,
            ],
            'temp_celsius' => [
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'allow_null' => true,
            ],
            'humidity_pct' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'packing_method' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 100,
                'allow_null' => true,
            ],
            'gel_pack' => [
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'heat_pack' => [
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'transit_days' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'allow_null' => true,
            ],
            'staff_name' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isName',
                'size'     => 100,
                'allow_null' => true,
            ],
            'notes' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isCleanHtml',
                'allow_null' => true,
            ],
            'photo_filename' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isFileName',
                'size'     => 255,
                'allow_null' => true,
            ],
            'date_add' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'date_upd' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Fetch the dispatch log row for a given order.
     *
     * Returns an associative array (all columns) or false when none exists.
     *
     * @param int $idOrder
     *
     * @return array|bool
     */
    public static function getByOrder(int $idOrder)
    {
        if ($idOrder <= 0) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('*')
            ->from('phyto_dispatch_log')
            ->where('`id_order` = ' . (int) $idOrder);

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
    }

    /**
     * Return the absolute filesystem path for dispatch photo storage.
     *
     * The directory is created at module install; this method only computes
     * the path without touching the filesystem.
     *
     * @return string   Path ends with a directory separator
     */
    public static function getPhotoDir(): string
    {
        return _PS_IMG_DIR_ . 'phyto_dispatch' . DIRECTORY_SEPARATOR;
    }

    /**
     * Return the available packing method options.
     *
     * Used both for the admin form select and for display labels.
     *
     * @return array  [ ['id' => <value>, 'name' => <label>], ... ]
     */
    public static function getPackingMethods(): array
    {
        return [
            ['id' => 'Bare-root newspaper',  'name' => 'Bare-root newspaper'],
            ['id' => 'Bark media bag',        'name' => 'Bark media bag'],
            ['id' => 'Humidity box',          'name' => 'Humidity box'],
            ['id' => 'Insulated box',         'name' => 'Insulated box'],
            ['id' => 'Express pouch',         'name' => 'Express pouch'],
        ];
    }

    // -------------------------------------------------------------------------
    // Override: automatic date handling
    // -------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function add($autoDate = true, $nullValues = false): bool
    {
        return parent::add($autoDate, $nullValues);
    }

    /**
     * {@inheritdoc}
     */
    public function update($nullValues = false): bool
    {
        return parent::update($nullValues);
    }
}
