<?php
/**
 * PhytoWholesaleApplication — ObjectModel for wholesale applications.
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoWholesaleApplication extends ObjectModel
{
    /** @var int */
    public $id_app;

    /** @var int */
    public $id_customer = 0;

    /** @var string */
    public $business_name;

    /** @var string */
    public $gst_number;

    /** @var string */
    public $address;

    /** @var string */
    public $phone;

    /** @var string */
    public $website;

    /** @var string */
    public $message;

    /** @var string Pending|Approved|Rejected */
    public $status = 'Pending';

    /** @var string */
    public $admin_notes;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_wholesale_application',
        'primary' => 'id_app',
        'fields'  => [
            'id_customer'   => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedId',  'required' => false],
            'business_name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 200],
            'gst_number'    => ['type' => self::TYPE_STRING, 'validate' => 'isAnything',    'size' => 30],
            'address'       => ['type' => self::TYPE_HTML,   'validate' => 'isCleanHtml'],
            'phone'         => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 30],
            'website'       => ['type' => self::TYPE_STRING, 'validate' => 'isUrl',         'size' => 200],
            'message'       => ['type' => self::TYPE_HTML,   'validate' => 'isCleanHtml'],
            'status'        => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'admin_notes'   => ['type' => self::TYPE_HTML,   'validate' => 'isCleanHtml'],
            'date_add'      => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd'      => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
        ],
    ];

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Get the most recent application for a given customer.
     *
     * @param int $idCustomer
     *
     * @return PhytoWholesaleApplication|false
     */
    public static function getByCustomer(int $idCustomer)
    {
        $idApp = (int) Db::getInstance()->getValue(
            'SELECT `id_app` FROM `' . _DB_PREFIX_ . 'phyto_wholesale_application`
             WHERE `id_customer` = ' . $idCustomer . '
             ORDER BY `date_add` DESC'
        );

        if (!$idApp) {
            return false;
        }

        $app = new self($idApp);

        return Validate::isLoadedObject($app) ? $app : false;
    }

    /**
     * Count applications with status 'Pending'.
     *
     * @return int
     */
    public static function getPendingCount(): int
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_wholesale_application`
             WHERE `status` = \'Pending\''
        );
    }

    /**
     * Return all applications optionally filtered by status, ordered by date descending.
     *
     * @param string|null $status
     *
     * @return array
     */
    public static function getAll(?string $status = null): array
    {
        $where = '';
        if ($status !== null) {
            $where = 'WHERE `status` = \'' . pSQL($status) . '\'';
        }

        return Db::getInstance()->executeS(
            'SELECT a.*, CONCAT(c.firstname, \' \', c.lastname) AS customer_name, c.email
             FROM `' . _DB_PREFIX_ . 'phyto_wholesale_application` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             ' . $where . '
             ORDER BY a.`date_add` DESC'
        );
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
}
