<?php
if (!defined('_PS_VERSION_')) { exit; }

class PhytoKycProfile extends ObjectModel
{
    public $id_kyc_profile;
    public $id_customer;
    public $kyc_level      = 0;
    public $level1_status  = 'NotStarted';
    public $level2_status  = 'NotStarted';
    public $pan_number;
    public $pan_name;
    public $gst_number;
    public $business_pan;
    public $business_name;
    public $api_response_l1;
    public $api_response_l2;
    public $admin_notes;
    public $reviewed_by;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table'   => 'phyto_kyc_profile',
        'primary' => 'id_kyc_profile',
        'fields'  => [
            'id_customer'     => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true],
            'kyc_level'       => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt'],
            'level1_status'   => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'level2_status'   => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'pan_number'      => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 20],
            'pan_name'        => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 200],
            'gst_number'      => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 20],
            'business_pan'    => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 20],
            'business_name'   => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 200],
            'api_response_l1' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'api_response_l2' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'admin_notes'     => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'reviewed_by'     => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt'],
            'date_add'        => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
            'date_upd'        => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
        ],
    ];

    public function add($autodate = true, $null_values = false): bool
    {
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::add($autodate, $null_values);
    }

    public function update($null_values = false): bool
    {
        $this->date_upd = date('Y-m-d H:i:s');
        return parent::update($null_values);
    }

    public static function getByCustomer(int $idCustomer): ?self
    {
        $id = (int) Db::getInstance()->getValue(
            'SELECT `id_kyc_profile` FROM `' . _DB_PREFIX_ . 'phyto_kyc_profile`
             WHERE `id_customer` = ' . $idCustomer
        );
        if (!$id) return null;
        $obj = new self($id);
        return Validate::isLoadedObject($obj) ? $obj : null;
    }

    public static function getOrCreate(int $idCustomer): self
    {
        $existing = self::getByCustomer($idCustomer);
        if ($existing) return $existing;

        $p = new self();
        $p->id_customer   = $idCustomer;
        $p->level1_status = 'NotStarted';
        $p->level2_status = 'NotStarted';
        $p->add();
        return $p;
    }

    public static function isValidPan(string $pan): bool
    {
        return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper(trim($pan)));
    }

    public static function isValidGst(string $gst): bool
    {
        return (bool) preg_match('/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}Z[A-Z\d]{1}$/', strtoupper(trim($gst)));
    }

    public function getDocuments(int $level = null): array
    {
        require_once __DIR__ . '/PhytoKycDocument.php';
        return PhytoKycDocument::getByProfile((int) $this->id_kyc_profile, $level);
    }
}
