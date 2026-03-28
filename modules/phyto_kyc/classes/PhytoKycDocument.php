<?php
if (!defined('_PS_VERSION_')) { exit; }

class PhytoKycDocument extends ObjectModel
{
    public $id_document;
    public $id_kyc_profile;
    public $id_customer;
    public $kyc_level  = 1;
    public $doc_type;
    public $file_path;
    public $file_name;
    public $mime_type;
    public $date_add;

    public static $definition = [
        'table'   => 'phyto_kyc_document',
        'primary' => 'id_document',
        'fields'  => [
            'id_kyc_profile' => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true],
            'id_customer'    => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true],
            'kyc_level'      => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt'],
            'doc_type'       => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 50],
            'file_path'      => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 500],
            'file_name'      => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 255],
            'mime_type'      => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'size' => 100],
            'date_add'       => ['type' => self::TYPE_DATE,   'validate' => 'isDate'],
        ],
    ];

    public function add($autodate = true, $null_values = false): bool
    {
        $this->date_add = date('Y-m-d H:i:s');
        return parent::add($autodate, $null_values);
    }

    public static function getByProfile(int $idKycProfile, int $level = null): array
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_kyc_document`
                WHERE `id_kyc_profile` = ' . $idKycProfile;
        if ($level !== null) {
            $sql .= ' AND `kyc_level` = ' . (int) $level;
        }
        $sql .= ' ORDER BY `date_add` ASC';
        return Db::getInstance()->executeS($sql) ?: [];
    }
}
