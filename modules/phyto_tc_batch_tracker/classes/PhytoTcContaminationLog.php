<?php
/**
 * PhytoTcContaminationLog ObjectModel
 *
 * Records contamination incidents against a TC batch.
 *
 * @author  PhytoCommerce
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoTcContaminationLog extends ObjectModel
{
    /** @var int */
    public $id_log;

    /** @var int */
    public $id_batch;

    /** @var string  Date of the observed incident */
    public $incident_date;

    /** @var string  Bacterial | Fungal | Viral | Pest | Unknown | Other */
    public $type = 'Unknown';

    /** @var int */
    public $affected_units = 0;

    /** @var string */
    public $description;

    /** @var int */
    public $resolved = 0;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    public static $definition = array(
        'table'   => 'phyto_tc_contamination_log',
        'primary' => 'id_log',
        'fields'  => array(
            'id_batch'       => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true,
            ),
            'incident_date'  => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true,
            ),
            'type'           => array(
                'type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true,
            ),
            'affected_units' => array(
                'type' => self::TYPE_INT, 'validate' => 'isUnsignedInt',
            ),
            'description'    => array(
                'type' => self::TYPE_HTML, 'validate' => 'isCleanHtml',
            ),
            'resolved'       => array(
                'type' => self::TYPE_BOOL, 'validate' => 'isBool',
            ),
            'date_add'       => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
            'date_upd'       => array(
                'type' => self::TYPE_DATE, 'validate' => 'isDate',
            ),
        ),
    );

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Return all incident type choices.
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        return array(
            'Bacterial' => 'Bacterial',
            'Fungal'    => 'Fungal',
            'Viral'     => 'Viral',
            'Pest'      => 'Pest',
            'Unknown'   => 'Unknown',
            'Other'     => 'Other',
        );
    }

    /**
     * Return all log entries for a given batch, newest first.
     *
     * @param int  $idBatch
     * @param bool $unresolvedOnly
     *
     * @return array
     */
    public static function getByBatch($idBatch, $unresolvedOnly = false)
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_tc_contamination_log`
                WHERE `id_batch` = ' . (int) $idBatch;

        if ($unresolvedOnly) {
            $sql .= ' AND `resolved` = 0';
        }

        $sql .= ' ORDER BY `incident_date` DESC, `id_log` DESC';

        return Db::getInstance()->executeS($sql) ?: array();
    }

    /**
     * Count unresolved incidents for a batch (used for badge in admin list).
     *
     * @param int $idBatch
     *
     * @return int
     */
    public static function countUnresolved($idBatch)
    {
        return (int) Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_tc_contamination_log`
             WHERE `id_batch` = ' . (int) $idBatch . ' AND `resolved` = 0'
        );
    }

    /**
     * Mark all incidents for a batch as resolved.
     *
     * @param int $idBatch
     *
     * @return bool
     */
    public static function resolveAll($idBatch)
    {
        return (bool) Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_tc_contamination_log`
             SET `resolved` = 1, `date_upd` = NOW()
             WHERE `id_batch` = ' . (int) $idBatch
        );
    }

    /**
     * Delete all logs for a batch (called on batch delete).
     *
     * @param int $idBatch
     *
     * @return bool
     */
    public static function deleteByBatch($idBatch)
    {
        return (bool) Db::getInstance()->delete(
            'phyto_tc_contamination_log',
            '`id_batch` = ' . (int) $idBatch
        );
    }
}
