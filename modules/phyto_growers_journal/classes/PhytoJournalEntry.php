<?php
/**
 * PhytoJournalEntry - ObjectModel for Grower's Journal entries.
 *
 * @author    PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoJournalEntry extends ObjectModel
{
    /** @var int */
    public $id_entry;

    /** @var int */
    public $id_product;

    /** @var int */
    public $id_customer = 0;

    /** @var string */
    public $entry_date;

    /** @var string */
    public $title;

    /** @var string */
    public $body;

    /** @var string */
    public $photo1;

    /** @var string */
    public $photo2;

    /** @var string */
    public $photo3;

    /** @var string */
    public $entry_type = 'Store';

    /** @var int */
    public $approved = 1;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'     => 'phyto_journal_entry',
        'primary'   => 'id_entry',
        'fields'    => array(
            'id_product'  => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true,
            ),
            'id_customer' => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedId',
            ),
            'entry_date'  => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'title'       => array(
                'type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255,
            ),
            'body'        => array(
                'type' => self::TYPE_HTML, 'validate' => 'isCleanHtml',
            ),
            'photo1'      => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255,
            ),
            'photo2'      => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255,
            ),
            'photo3'      => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255,
            ),
            'entry_type'  => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'values' => array('Store', 'Customer', 'Milestone'),
            ),
            'approved'    => array(
                'type' => self::TYPE_BOOL, 'validate' => 'isBool',
            ),
            'date_add'    => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'date_upd'    => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
        ),
    );

    /**
     * Get approved journal entries for a given product, ordered chronologically.
     *
     * @param int $idProduct
     * @param bool $approvedOnly
     * @return array
     */
    public static function getEntriesByProduct($idProduct, $approvedOnly = true)
    {
        $sql = new DbQuery();
        $sql->select('e.*, c.firstname, c.lastname');
        $sql->from('phyto_journal_entry', 'e');
        $sql->leftJoin('customer', 'c', 'c.id_customer = e.id_customer');
        $sql->where('e.id_product = ' . (int) $idProduct);

        if ($approvedOnly) {
            $sql->where('e.approved = 1');
        }

        $sql->orderBy('e.entry_date DESC, e.date_add DESC');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * Check whether a customer already posted for a product within the last N days.
     *
     * @param int $idCustomer
     * @param int $idProduct
     * @param int $days
     * @return bool  true if a recent post exists (rate-limited)
     */
    public static function hasRecentPost($idCustomer, $idProduct, $days = 7)
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('phyto_journal_entry');
        $sql->where('id_customer = ' . (int) $idCustomer);
        $sql->where('id_product = ' . (int) $idProduct);
        $sql->where('date_add >= DATE_SUB(NOW(), INTERVAL ' . (int) $days . ' DAY)');

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
    }

    /**
     * Check whether a customer has purchased a given product.
     *
     * @param int $idCustomer
     * @param int $idProduct
     * @return bool
     */
    public static function customerHasPurchased($idCustomer, $idProduct)
    {
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from('order_detail', 'od');
        $sql->innerJoin('orders', 'o', 'o.id_order = od.id_order');
        $sql->where('o.id_customer = ' . (int) $idCustomer);
        $sql->where('od.product_id = ' . (int) $idProduct);
        $sql->where('o.valid = 1');

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql) > 0;
    }

    /**
     * Return the upload directory path for journal photos.
     *
     * @return string
     */
    public static function getUploadDir()
    {
        return _PS_IMG_DIR_ . 'phyto_journal/';
    }
}
