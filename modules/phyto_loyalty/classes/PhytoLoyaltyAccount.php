<?php
/**
 * PhytoLoyaltyAccount.php
 *
 * ObjectModel representing a customer loyalty account.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoLoyaltyAccount extends ObjectModel
{
    /** @var int */
    public $id_customer;

    /** @var int */
    public $points_balance;

    /** @var int */
    public $points_lifetime;

    /** @var int */
    public $points_redeemed;

    /** @var string seed|sprout|bloom|rare */
    public $tier;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /**
     * Tier thresholds (lifetime points).
     */
    public const TIER_THRESHOLDS = [
        'seed'   => 0,
        'sprout' => 500,
        'bloom'  => 2000,
        'rare'   => 5000,
    ];

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_loyalty_account',
        'primary' => 'id_loyalty',
        'fields'  => [
            'id_customer' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'points_balance' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isInt',
            ],
            'points_lifetime' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'points_redeemed' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'tier' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 10,
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
     * Load account by customer ID, creating one if it does not exist.
     *
     * @param int $idCustomer
     * @return PhytoLoyaltyAccount
     */
    public static function getOrCreate(int $idCustomer): PhytoLoyaltyAccount
    {
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `id_loyalty` FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`
             WHERE `id_customer` = ' . (int) $idCustomer
        );

        if ($row) {
            return new self((int) $row['id_loyalty']);
        }

        $account                  = new self();
        $account->id_customer     = (int) $idCustomer;
        $account->points_balance  = 0;
        $account->points_lifetime = 0;
        $account->points_redeemed = 0;
        $account->tier            = 'seed';
        $account->date_add        = date('Y-m-d H:i:s');
        $account->date_upd        = date('Y-m-d H:i:s');
        $account->add();

        return $account;
    }

    /**
     * Load account by customer ID or return null.
     *
     * @param int $idCustomer
     * @return PhytoLoyaltyAccount|null
     */
    public static function getByCustomer(int $idCustomer): ?PhytoLoyaltyAccount
    {
        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `id_loyalty` FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`
             WHERE `id_customer` = ' . (int) $idCustomer
        );

        if (!$row) {
            return null;
        }

        return new self((int) $row['id_loyalty']);
    }

    /**
     * Recalculate and persist the tier based on lifetime points.
     */
    public function recalculateTier(): void
    {
        $this->tier = self::tierForLifetime((int) $this->points_lifetime);
    }

    /**
     * Return tier string for a given lifetime points value.
     *
     * @param int $lifetime
     * @return string
     */
    public static function tierForLifetime(int $lifetime): string
    {
        if ($lifetime >= self::TIER_THRESHOLDS['rare']) {
            return 'rare';
        }
        if ($lifetime >= self::TIER_THRESHOLDS['bloom']) {
            return 'bloom';
        }
        if ($lifetime >= self::TIER_THRESHOLDS['sprout']) {
            return 'sprout';
        }
        return 'seed';
    }

    /**
     * Return the next tier name and points required to reach it.
     *
     * @return array ['next_tier' => string|null, 'points_needed' => int]
     */
    public function nextTierInfo(): array
    {
        $lifetime = (int) $this->points_lifetime;
        foreach (self::TIER_THRESHOLDS as $tier => $threshold) {
            if ($lifetime < $threshold) {
                return [
                    'next_tier'     => $tier,
                    'points_needed' => $threshold - $lifetime,
                    'threshold'     => $threshold,
                ];
            }
        }
        return ['next_tier' => null, 'points_needed' => 0, 'threshold' => 0];
    }

    /**
     * Return progress percentage towards the next tier.
     *
     * @return int 0-100
     */
    public function tierProgressPct(): int
    {
        $lifetime   = (int) $this->points_lifetime;
        $thresholds = array_values(self::TIER_THRESHOLDS);
        $tiers      = array_keys(self::TIER_THRESHOLDS);
        $currentIdx = array_search($this->tier, $tiers, true);

        if ($this->tier === 'rare' || $currentIdx === false) {
            return 100;
        }

        $lower = $thresholds[$currentIdx];
        $upper = $thresholds[$currentIdx + 1] ?? $lifetime;

        if ($upper <= $lower) {
            return 100;
        }

        return (int) min(100, max(0, round(($lifetime - $lower) / ($upper - $lower) * 100)));
    }

    /**
     * Expire stale points and record a transaction. Returns number of points expired.
     * Called on cron or on each account load if desired.
     *
     * @param int $expiryDays  0 = never
     * @return int
     */
    public function expireStalePoints(int $expiryDays): int
    {
        if ($expiryDays <= 0 || (int) $this->points_balance <= 0) {
            return 0;
        }

        // Find the last activity date (latest transaction)
        $lastActivity = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            'SELECT MAX(`date_add`) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_transaction`
             WHERE `id_customer` = ' . (int) $this->id_customer
        );

        if (!$lastActivity) {
            $lastActivity = $this->date_add;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime('-' . (int) $expiryDays . ' days'));

        if ($lastActivity >= $cutoff) {
            return 0;
        }

        $expiring = (int) $this->points_balance;
        $this->points_balance = 0;
        $this->date_upd       = date('Y-m-d H:i:s');
        $this->update();

        PhytoLoyaltyTransaction::record(
            (int) $this->id_customer,
            0,
            'expire',
            -$expiring,
            0,
            'Points expired after ' . $expiryDays . ' days of inactivity'
        );

        return $expiring;
    }
}
