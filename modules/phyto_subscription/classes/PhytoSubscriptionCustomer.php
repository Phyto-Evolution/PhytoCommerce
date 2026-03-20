<?php
/**
 * PhytoSubscriptionCustomer.php
 *
 * ObjectModel representing a customer's subscription instance.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoSubscriptionCustomer extends ObjectModel
{
    /** @var int */
    public $id_customer;

    /** @var int */
    public $id_plan;

    /** @var string */
    public $cashfree_subscription_id;

    /** @var string created|active|paused|cancelled|completed */
    public $status;

    /** @var string */
    public $start_date;

    /** @var string */
    public $next_billing_date;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_subscription_customer',
        'primary' => 'id_sub',
        'fields'  => [
            'id_customer' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_plan' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'cashfree_subscription_id' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 150,
            ],
            'status' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'values'   => ['created', 'active', 'paused', 'cancelled', 'completed'],
            ],
            'start_date' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
            'next_billing_date' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
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

    /**
     * Get all subscriptions for a customer with plan name.
     *
     * @param int $idCustomer
     * @return array
     */
    public static function getByCustomer($idCustomer)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT s.*, p.`plan_name`, p.`plan_type`, p.`frequency`, p.`price`
             FROM `' . _DB_PREFIX_ . 'phyto_subscription_customer` s
             LEFT JOIN `' . _DB_PREFIX_ . 'phyto_subscription_plan` p ON p.`id_plan` = s.`id_plan`
             WHERE s.`id_customer` = ' . (int) $idCustomer . '
             ORDER BY s.`date_add` DESC'
        );
    }

    /**
     * Get subscription by Cashfree subscription ID.
     *
     * @param string $cashfreeSubId
     * @return array|bool
     */
    public static function getByCashfreeId($cashfreeSubId)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_subscription_customer`
             WHERE `cashfree_subscription_id` = \'' . pSQL($cashfreeSubId) . '\''
        );
    }

    /**
     * Update status by Cashfree subscription ID.
     *
     * @param string $cashfreeSubId
     * @param string $status
     * @return bool
     */
    public static function updateStatusByCashfreeId($cashfreeSubId, $status)
    {
        $allowedStatuses = ['created', 'active', 'paused', 'cancelled', 'completed'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        return Db::getInstance()->update(
            'phyto_subscription_customer',
            [
                'status'   => pSQL($status),
                'date_upd' => date('Y-m-d H:i:s'),
            ],
            '`cashfree_subscription_id` = \'' . pSQL($cashfreeSubId) . '\''
        );
    }

    /**
     * Map a Cashfree subscription status string to our DB enum value.
     *
     * @param string $cfStatus
     * @return string
     */
    public static function mapCashfreeStatus($cfStatus)
    {
        $map = [
            'ACTIVE'     => 'active',
            'INITIALIZED'=> 'created',
            'ON_HOLD'    => 'paused',
            'PAUSED'     => 'paused',
            'CANCELLED'  => 'cancelled',
            'COMPLETED'  => 'completed',
            'EXPIRED'    => 'cancelled',
        ];

        $upper = strtoupper((string) $cfStatus);
        return isset($map[$upper]) ? $map[$upper] : 'created';
    }

    /**
     * Get all subscriptions (admin list) with customer name and plan name.
     *
     * @param int    $limit
     * @param int    $offset
     * @param string $orderBy
     * @param string $orderDir
     * @return array
     */
    public static function getAllWithDetails($limit = 50, $offset = 0, $orderBy = 'id_sub', $orderDir = 'DESC')
    {
        $allowedOrderBy = ['id_sub', 'id_customer', 'status', 'start_date', 'next_billing_date'];
        if (!in_array($orderBy, $allowedOrderBy)) {
            $orderBy = 'id_sub';
        }
        $orderDir = ($orderDir === 'ASC') ? 'ASC' : 'DESC';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT s.*,
                    CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name,
                    p.`plan_name`
             FROM `' . _DB_PREFIX_ . 'phyto_subscription_customer` s
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = s.`id_customer`
             LEFT JOIN `' . _DB_PREFIX_ . 'phyto_subscription_plan` p ON p.`id_plan` = s.`id_plan`
             ORDER BY `' . bqSQL($orderBy) . '` ' . $orderDir . '
             LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset
        );
    }
}
