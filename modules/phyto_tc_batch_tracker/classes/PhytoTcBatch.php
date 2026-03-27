<?php
/**
 * PhytoTcBatch ObjectModel
 *
 * Represents a tissue-culture propagation batch record.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoTcBatch extends ObjectModel
{
    /** @var int */
    public $id_batch;

    /** @var int|null  Mother batch (lineage) */
    public $parent_id_batch;

    /** @var string */
    public $batch_code;

    /** @var string */
    public $species_name;

    /** @var string */
    public $generation;

    /** @var string */
    public $date_initiation;

    /** @var string */
    public $date_deflask;

    /** @var string */
    public $date_certified;

    /** @var string */
    public $sterility_protocol;

    /** @var int */
    public $units_produced = 0;

    /** @var int */
    public $units_remaining = 0;

    /** @var int  1 = low-stock alert already sent; reset to 0 when stock is replenished */
    public $low_stock_alerted = 0;

    /** @var string */
    public $batch_status = 'Active';

    /** @var string */
    public $notes;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table'   => 'phyto_tc_batch',
        'primary' => 'id_batch',
        'fields'  => array(
            'parent_id_batch'     => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'allow_null' => true,
            ),
            'batch_code'          => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50,
            ),
            'species_name'        => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 200,
            ),
            'generation'          => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true,
            ),
            'date_initiation'     => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true,
            ),
            'date_deflask'        => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true,
            ),
            'date_certified'      => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate', 'allow_null' => true,
            ),
            'sterility_protocol'  => array(
                'type' => self::TYPE_HTML, 'validate' => 'isCleanHtml',
            ),
            'units_produced'      => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedInt',
            ),
            'units_remaining'     => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedInt',
            ),
            'low_stock_alerted'   => array(
                'type' => self::TYPE_BOOL, 'validate' => 'isBool',
            ),
            'batch_status'        => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName',
            ),
            'notes'               => array(
                'type' => self::TYPE_HTML, 'validate' => 'isCleanHtml',
            ),
            'date_add'            => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'date_upd'            => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Static lookups
    // -------------------------------------------------------------------------

    public static function getGenerationChoices()
    {
        return array(
            'G0'         => 'G0 - Explant',
            'G1'         => 'G1',
            'G2'         => 'G2',
            'G3+'        => 'G3+',
            'Acclimated' => 'Acclimated',
            'Hardened'   => 'Hardened',
        );
    }

    public static function getStatusChoices()
    {
        return array(
            'Active'      => 'Active',
            'Depleted'    => 'Depleted',
            'Quarantined' => 'Quarantined',
            'Archived'    => 'Archived',
        );
    }

    // -------------------------------------------------------------------------
    // Product linking
    // -------------------------------------------------------------------------

    public static function getBatchByProduct($idProduct, $idProductAttribute = 0)
    {
        $sql = new DbQuery();
        $sql->select('b.*');
        $sql->from('phyto_tc_batch', 'b');
        $sql->innerJoin('phyto_tc_batch_product', 'bp', 'bp.id_batch = b.id_batch');
        $sql->where('bp.id_product = ' . (int) $idProduct);
        $sql->where('bp.id_product_attribute = ' . (int) $idProductAttribute);

        return Db::getInstance()->getRow($sql);
    }

    public static function linkProduct($idBatch, $idProduct, $idProductAttribute = 0)
    {
        self::unlinkProduct($idProduct, $idProductAttribute);

        return Db::getInstance()->insert('phyto_tc_batch_product', array(
            'id_batch'             => (int) $idBatch,
            'id_product'           => (int) $idProduct,
            'id_product_attribute' => (int) $idProductAttribute,
        ));
    }

    public static function unlinkProduct($idProduct, $idProductAttribute = 0)
    {
        return Db::getInstance()->delete(
            'phyto_tc_batch_product',
            'id_product = ' . (int) $idProduct
            . ' AND id_product_attribute = ' . (int) $idProductAttribute
        );
    }

    public static function getAllForDropdown()
    {
        $sql = new DbQuery();
        $sql->select('id_batch, batch_code, species_name, batch_status');
        $sql->from('phyto_tc_batch');
        $sql->orderBy('batch_code ASC');

        return Db::getInstance()->executeS($sql) ?: array();
    }

    // -------------------------------------------------------------------------
    // Inventory auto-decrement
    // -------------------------------------------------------------------------

    /**
     * Atomically decrement units_remaining when an order ships.
     * Clamps to 0; auto-transitions status to Depleted when stock hits zero.
     *
     * @param int $idBatch
     * @param int $qty
     *
     * @return bool
     */
    public static function decrementUnits($idBatch, $qty)
    {
        $idBatch = (int) $idBatch;
        $qty     = (int) $qty;

        if ($idBatch <= 0 || $qty <= 0) {
            return false;
        }

        return (bool) Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_tc_batch`
             SET `units_remaining` = GREATEST(0, `units_remaining` - ' . $qty . '),
                 `batch_status`    = IF(`units_remaining` <= ' . $qty . ", 'Depleted', `batch_status`),
                 `date_upd`        = NOW()
             WHERE `id_batch` = " . $idBatch
        );
    }

    /**
     * Reset low_stock_alerted=0 when units are replenished above threshold.
     *
     * @param int $idBatch
     */
    public static function maybeResetLowStockFlag($idBatch)
    {
        $threshold = (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10);

        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_tc_batch`
             SET `low_stock_alerted` = 0
             WHERE `id_batch` = ' . (int) $idBatch . '
             AND `units_remaining` > ' . $threshold
        );
    }

    // -------------------------------------------------------------------------
    // Low-stock alert
    // -------------------------------------------------------------------------

    /**
     * Send a low-stock alert email if units_remaining has just dropped to or
     * below the configured threshold and an alert has not already been sent.
     *
     * @param int $idBatch
     *
     * @return bool  true if alert email was dispatched
     */
    public static function checkLowStockAlert($idBatch)
    {
        $idBatch   = (int) $idBatch;
        $threshold = (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10);

        $batch = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_tc_batch` WHERE `id_batch` = ' . $idBatch
        );

        if (!$batch
            || (int) $batch['low_stock_alerted'] === 1
            || (int) $batch['units_remaining'] > $threshold
        ) {
            return false;
        }

        $alertEmail = Configuration::get('PHYTO_TC_ALERT_EMAIL') ?: Configuration::get('PS_SHOP_EMAIL');
        $shopName   = Configuration::get('PS_SHOP_NAME');

        $sent = Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            'phyto_tc_low_stock',
            sprintf('[%s] TC Batch Low Stock — %s', $shopName, $batch['batch_code']),
            array(
                '{batch_code}'      => $batch['batch_code'],
                '{species_name}'    => $batch['species_name'],
                '{generation}'      => $batch['generation'],
                '{units_remaining}' => (int) $batch['units_remaining'],
                '{threshold}'       => $threshold,
                '{batch_status}'    => $batch['batch_status'],
                '{shop_name}'       => $shopName,
            ),
            $alertEmail,
            $shopName,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . 'phyto_tc_batch_tracker/mails/'
        );

        // Mark alert sent regardless of mail result to prevent repeated emails
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_tc_batch`
             SET `low_stock_alerted` = 1, `date_upd` = NOW()
             WHERE `id_batch` = ' . $idBatch
        );

        return (bool) $sent;
    }

    /**
     * Return all Active batches at or below the low-stock threshold.
     *
     * @return array
     */
    public static function getLowStockBatches()
    {
        $threshold = (int) Configuration::get('PHYTO_TC_LOW_STOCK_THRESHOLD', null, null, null, 10);

        return Db::getInstance()->executeS(
            "SELECT * FROM `" . _DB_PREFIX_ . "phyto_tc_batch`
             WHERE `units_remaining` <= " . $threshold . "
             AND `batch_status` = 'Active'
             ORDER BY `units_remaining` ASC"
        ) ?: array();
    }

    // -------------------------------------------------------------------------
    // Mother batch / lineage
    // -------------------------------------------------------------------------

    /**
     * Return the full lineage chain from the root ancestor down to $idBatch.
     * Guards against circular references and stops after 10 levels.
     *
     * @param int $idBatch
     *
     * @return array  ordered root → ... → current
     */
    public static function getLineageChain($idBatch)
    {
        $chain   = array();
        $current = (int) $idBatch;
        $seen    = array();
        $depth   = 0;

        while ($current && $depth < 10) {
            if (isset($seen[$current])) {
                break; // circular-reference guard
            }

            $row = Db::getInstance()->getRow(
                'SELECT `id_batch`, `parent_id_batch`, `batch_code`, `species_name`, `generation`
                 FROM `' . _DB_PREFIX_ . 'phyto_tc_batch`
                 WHERE `id_batch` = ' . $current
            );

            if (!$row) {
                break;
            }

            $seen[$current] = true;
            array_unshift($chain, $row); // prepend → chain reads root first

            $current = (int) $row['parent_id_batch'];
            $depth++;
        }

        return $chain;
    }

    /**
     * Return direct children of a batch (batches derived from this one).
     *
     * @param int $idBatch
     *
     * @return array
     */
    public static function getChildren($idBatch)
    {
        return Db::getInstance()->executeS(
            'SELECT `id_batch`, `batch_code`, `species_name`, `generation`, `batch_status`, `units_remaining`
             FROM `' . _DB_PREFIX_ . 'phyto_tc_batch`
             WHERE `parent_id_batch` = ' . (int) $idBatch . '
             ORDER BY `batch_code` ASC'
        ) ?: array();
    }

    // -------------------------------------------------------------------------
    // Batch code suggestion
    // -------------------------------------------------------------------------

    public static function suggestBatchCode($speciesName = '')
    {
        $prefix = date('Ym');
        $genus  = 'BATCH';

        if ($speciesName) {
            $parts = explode(' ', trim($speciesName));
            $g     = strtoupper(substr($parts[0], 0, 4));
            if (strlen($g) >= 2) {
                $genus = $g;
            }
        }

        $base = $prefix . '-' . $genus;

        $last = Db::getInstance()->getValue(
            "SELECT `batch_code` FROM `" . _DB_PREFIX_ . "phyto_tc_batch`
             WHERE `batch_code` LIKE '" . pSQL($base) . "-%'
             ORDER BY `batch_code` DESC"
        );

        if ($last) {
            $parts = explode('-', $last);
            $seq   = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return $base . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
