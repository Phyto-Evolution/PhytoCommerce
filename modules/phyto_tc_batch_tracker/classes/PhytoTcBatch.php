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
            'batch_code'          => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50,
            ),
            'species_name'        => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 200,
            ),
            'generation'          => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true,
                'values' => array('G0', 'G1', 'G2', 'G3+', 'Acclimated', 'Hardened'),
            ),
            'date_initiation'     => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'date_deflask'        => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'date_certified'      => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
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
            'batch_status'        => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName',
                'values' => array('Active', 'Depleted', 'Quarantined', 'Archived'),
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

    /**
     * Return the generation choices for dropdowns.
     *
     * @return array
     */
    public static function getGenerationChoices()
    {
        return array(
            'G0'          => 'G0 - Explant',
            'G1'          => 'G1',
            'G2'          => 'G2',
            'G3+'         => 'G3+',
            'Acclimated'  => 'Acclimated',
            'Hardened'    => 'Hardened',
        );
    }

    /**
     * Return the status choices for dropdowns.
     *
     * @return array
     */
    public static function getStatusChoices()
    {
        return array(
            'Active'      => 'Active',
            'Depleted'    => 'Depleted',
            'Quarantined' => 'Quarantined',
            'Archived'    => 'Archived',
        );
    }

    /**
     * Get the batch linked to a given product (and optionally product attribute).
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     *
     * @return array|false
     */
    public static function getBatchByProduct($idProduct, $idProductAttribute = 0)
    {
        $sql = new DbQuery();
        $sql->select('b.*');
        $sql->from('phyto_tc_batch', 'b');
        $sql->innerJoin(
            'phyto_tc_batch_product',
            'bp',
            'bp.id_batch = b.id_batch'
        );
        $sql->where('bp.id_product = ' . (int) $idProduct);
        $sql->where('bp.id_product_attribute = ' . (int) $idProductAttribute);

        return Db::getInstance()->getRow($sql);
    }

    /**
     * Link a product to this batch.
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     *
     * @return bool
     */
    public static function linkProduct($idBatch, $idProduct, $idProductAttribute = 0)
    {
        // Remove any existing link for this product first
        self::unlinkProduct($idProduct, $idProductAttribute);

        return Db::getInstance()->insert('phyto_tc_batch_product', array(
            'id_batch'              => (int) $idBatch,
            'id_product'            => (int) $idProduct,
            'id_product_attribute'  => (int) $idProductAttribute,
        ));
    }

    /**
     * Unlink a product from its batch.
     *
     * @param int $idProduct
     * @param int $idProductAttribute
     *
     * @return bool
     */
    public static function unlinkProduct($idProduct, $idProductAttribute = 0)
    {
        return Db::getInstance()->delete(
            'phyto_tc_batch_product',
            'id_product = ' . (int) $idProduct
            . ' AND id_product_attribute = ' . (int) $idProductAttribute
        );
    }

    /**
     * Get all batches as a simple id => label array (for dropdowns).
     *
     * @return array
     */
    public static function getAllForDropdown()
    {
        $sql = new DbQuery();
        $sql->select('id_batch, batch_code, species_name, batch_status');
        $sql->from('phyto_tc_batch');
        $sql->orderBy('batch_code ASC');

        $rows = Db::getInstance()->executeS($sql);
        if (!$rows) {
            return array();
        }

        return $rows;
    }

    /**
     * Generate a suggested batch code in the format YYYYMM-GENUS-SEQ.
     *
     * @param string $speciesName
     *
     * @return string
     */
    public static function suggestBatchCode($speciesName = '')
    {
        $prefix = date('Ym');
        $genus = 'BATCH';

        if ($speciesName) {
            $parts = explode(' ', trim($speciesName));
            $genus = strtoupper(substr($parts[0], 0, 4));
            if (strlen($genus) < 2) {
                $genus = 'BATCH';
            }
        }

        $base = $prefix . '-' . $genus;

        // Find next sequence number
        $sql = 'SELECT batch_code FROM `' . _DB_PREFIX_ . 'phyto_tc_batch`
                WHERE batch_code LIKE \'' . pSQL($base) . '-%\'
                ORDER BY batch_code DESC LIMIT 1';

        $last = Db::getInstance()->getValue($sql);

        if ($last) {
            $parts = explode('-', $last);
            $seq = (int) end($parts) + 1;
        } else {
            $seq = 1;
        }

        return $base . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }
}
