<?php
/**
 * PhytoLoyaltyTransaction.php
 *
 * ObjectModel representing a loyalty ledger entry.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoLoyaltyTransaction extends ObjectModel
{
    /** @var int */
    public $id_customer;

    /** @var int */
    public $id_order;

    /** @var string earn|redeem|expire|adjust|refund */
    public $type;

    /** @var int positive = earn, negative = redeem/expire */
    public $points;

    /** @var int */
    public $balance_after;

    /** @var string */
    public $note;

    /** @var string */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_loyalty_transaction',
        'primary' => 'id_transaction',
        'fields'  => [
            'id_customer' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'id_order' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'type' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 10,
            ],
            'points' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'balance_after' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => true,
            ],
            'note' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 255,
            ],
            'date_add' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Create a transaction record (does NOT modify the account balance).
     * Use PhytoLoyaltyAccount + this method together inside a DB transaction.
     *
     * @param int    $idCustomer
     * @param int    $idOrder
     * @param string $type
     * @param int    $points
     * @param int    $balanceAfter
     * @param string $note
     * @return bool
     */
    public static function record(
        int $idCustomer,
        int $idOrder,
        string $type,
        int $points,
        int $balanceAfter,
        string $note = ''
    ): bool {
        $tx                = new self();
        $tx->id_customer   = $idCustomer;
        $tx->id_order      = $idOrder;
        $tx->type          = $type;
        $tx->points        = $points;
        $tx->balance_after = $balanceAfter;
        $tx->note          = $note;
        $tx->date_add      = date('Y-m-d H:i:s');

        return (bool) $tx->add();
    }

    /**
     * Paginated transactions for a customer.
     *
     * @param int $idCustomer
     * @param int $page      1-based
     * @param int $pageSize
     * @return array
     */
    public static function getForCustomer(int $idCustomer, int $page = 1, int $pageSize = 20): array
    {
        $offset = ((int) max(1, $page) - 1) * (int) $pageSize;

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction`
             WHERE `id_customer` = ' . (int) $idCustomer . '
             ORDER BY `date_add` DESC
             LIMIT ' . (int) $offset . ', ' . (int) $pageSize
        ) ?: [];
    }

    /**
     * Total rows for a customer (for pagination).
     *
     * @param int $idCustomer
     * @return int
     */
    public static function countForCustomer(int $idCustomer): int
    {
        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction`
             WHERE `id_customer` = ' . (int) $idCustomer
        );
    }

    /**
     * Sum of points earned (positive only) for a specific order.
     *
     * @param int $idOrder
     * @param int $idCustomer
     * @return int
     */
    public static function getEarnedForOrder(int $idOrder, int $idCustomer): int
    {
        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COALESCE(SUM(`points`), 0)
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction`
             WHERE `id_order` = ' . (int) $idOrder . '
               AND `id_customer` = ' . (int) $idCustomer . '
               AND `type` = \'earn\''
        );
    }

    /**
     * Retrieve all transactions with optional filters (admin use).
     *
     * @param array  $filters  ['type' => string, 'date_from' => string, 'date_to' => string, 'id_customer' => int]
     * @param int    $page
     * @param int    $pageSize
     * @return array
     */
    public static function getFiltered(array $filters = [], int $page = 1, int $pageSize = 50): array
    {
        $where  = '1=1';
        $offset = ((int) max(1, $page) - 1) * (int) $pageSize;

        if (!empty($filters['type'])) {
            $where .= ' AND `type` = \'' . pSQL($filters['type']) . '\'';
        }
        if (!empty($filters['id_customer'])) {
            $where .= ' AND `id_customer` = ' . (int) $filters['id_customer'];
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND `date_add` >= \'' . pSQL($filters['date_from']) . '\'';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND `date_add` <= \'' . pSQL($filters['date_to']) . ' 23:59:59\'';
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT t.*, CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name, c.`email`
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction` t
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = t.`id_customer`
             WHERE ' . $where . '
             ORDER BY t.`date_add` DESC
             LIMIT ' . (int) $offset . ', ' . (int) $pageSize
        ) ?: [];
    }

    /**
     * Count filtered transactions.
     *
     * @param array $filters
     * @return int
     */
    public static function countFiltered(array $filters = []): int
    {
        $where = '1=1';
        if (!empty($filters['type'])) {
            $where .= ' AND `type` = \'' . pSQL($filters['type']) . '\'';
        }
        if (!empty($filters['id_customer'])) {
            $where .= ' AND `id_customer` = ' . (int) $filters['id_customer'];
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND `date_add` >= \'' . pSQL($filters['date_from']) . '\'';
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND `date_add` <= \'' . pSQL($filters['date_to']) . ' 23:59:59\'';
        }

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction` WHERE ' . $where
        );
    }
}
