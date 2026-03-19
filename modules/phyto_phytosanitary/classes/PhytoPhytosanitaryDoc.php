<?php
/**
 * PhytoPhytosanitaryDoc – ObjectModel for phytosanitary regulatory documents.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoPhytosanitaryDoc extends ObjectModel
{
    // -------------------------------------------------------------------------
    // Properties (mirror DB columns)
    // -------------------------------------------------------------------------

    /** @var int Product ID (0 = store-level document) */
    public $id_product;

    /** @var string Document type (e.g. 'phytosanitary_certificate') */
    public $doc_type;

    /** @var string|null Name of the issuing authority */
    public $issuing_authority;

    /** @var string|null Reference / certificate number */
    public $reference_number;

    /** @var string|null ISO date YYYY-MM-DD */
    public $issue_date;

    /** @var string|null ISO date YYYY-MM-DD */
    public $expiry_date;

    /** @var string|null Stored filename (UUID-based) */
    public $filename;

    /** @var int 1 = visible to customers on the front office */
    public $is_public;

    /** @var string|null */
    public $date_add;

    /** @var string|null */
    public $date_upd;

    // -------------------------------------------------------------------------
    // ObjectModel definition
    // -------------------------------------------------------------------------

    /** @var array<string, mixed> */
    public static $definition = [
        'table'   => 'phyto_phytosanitary_doc',
        'primary' => 'id_doc',
        'fields'  => [
            'id_product'        => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt',  'required' => true],
            'doc_type'          => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName',  'required' => true, 'size' => 50],
            'issuing_authority' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName',  'size' => 200],
            'reference_number'  => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName',  'size' => 100],
            'issue_date'        => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'expiry_date'       => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'filename'          => ['type' => self::TYPE_STRING, 'validate' => 'isAnything',     'size' => 255],
            'is_public'         => ['type' => self::TYPE_BOOL,   'validate' => 'isBool',         'required' => true],
            'date_add'          => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd'          => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
        ],
    ];

    // -------------------------------------------------------------------------
    // Static query methods
    // -------------------------------------------------------------------------

    /**
     * Return all documents attached to a product, including store-level docs
     * (id_product = 0).
     *
     * @param int  $idProduct  Product ID
     * @param bool $publicOnly When true only is_public = 1 rows are returned
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getByProduct(int $idProduct, bool $publicOnly = false): array
    {
        $publicFilter = $publicOnly ? ' AND d.`is_public` = 1' : '';

        $sql = '
            SELECT d.*
            FROM `' . _DB_PREFIX_ . 'phyto_phytosanitary_doc` d
            WHERE (d.`id_product` = ' . (int) $idProduct . '
               OR d.`id_product` = 0)
            ' . $publicFilter . '
            ORDER BY d.`expiry_date` ASC, d.`date_add` DESC
        ';

        $result = Db::getInstance()->executeS($sql);

        return is_array($result) ? $result : [];
    }

    /**
     * Return all non-expired documents for every product belonging to an order.
     * "Non-expired" means expiry_date IS NULL or expiry_date >= CURDATE().
     *
     * @param int $idOrder
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getByOrder(int $idOrder): array
    {
        $sql = '
            SELECT DISTINCT d.*
            FROM `' . _DB_PREFIX_ . 'phyto_phytosanitary_doc` d
            INNER JOIN `' . _DB_PREFIX_ . 'order_detail` od
                ON od.`product_id` = d.`id_product`
            WHERE od.`id_order` = ' . (int) $idOrder . '
              AND (d.`expiry_date` IS NULL OR d.`expiry_date` >= CURDATE())
            ORDER BY d.`doc_type` ASC, d.`expiry_date` ASC
        ';

        $result = Db::getInstance()->executeS($sql);

        return is_array($result) ? $result : [];
    }

    /**
     * Absolute path to the upload directory (with trailing slash).
     *
     * @return string
     */
    public static function getUploadDir(): string
    {
        return _PS_ROOT_DIR_ . '/upload/phyto_phytosanitary/';
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Human-readable labels for all supported document types.
     *
     * @return array<string, string>
     */
    public static function getDocTypeLabels(): array
    {
        return [
            'phytosanitary_certificate' => 'Phytosanitary Certificate',
            'import_permit'             => 'Import Permit',
            'quarantine_clearance'      => 'Quarantine Clearance',
            'cites_permit'              => 'CITES Permit',
            'state_movement_permit'     => 'State Movement Permit',
            'other'                     => 'Other',
        ];
    }

    /**
     * Return the human-readable label for a doc_type slug.
     *
     * @param string $slug
     *
     * @return string
     */
    public static function getDocTypeLabel(string $slug): string
    {
        $map = self::getDocTypeLabels();

        return $map[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
    }

    /**
     * Check whether this document is expiring within the given number of days.
     *
     * @param int $days  Default 30 days
     *
     * @return bool
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (empty($this->expiry_date)) {
            return false;
        }

        $expiry = strtotime($this->expiry_date);
        $limit  = strtotime('+' . $days . ' days');

        return $expiry !== false && $expiry <= $limit;
    }

    /**
     * Check whether this document has already expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (empty($this->expiry_date)) {
            return false;
        }

        $expiry = strtotime($this->expiry_date);

        return $expiry !== false && $expiry < strtotime('today');
    }
}
