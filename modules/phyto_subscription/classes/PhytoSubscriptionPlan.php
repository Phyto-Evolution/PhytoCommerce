<?php
/**
 * PhytoSubscriptionPlan.php
 *
 * ObjectModel representing a subscription plan.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class PhytoSubscriptionPlan extends ObjectModel
{
    /** @var string */
    public $plan_name;

    /** @var string Mystery|Replenishment|Custom */
    public $plan_type;

    /** @var string weekly|monthly|quarterly */
    public $frequency;

    /** @var float */
    public $price;

    /** @var int */
    public $max_cycles;

    /** @var string */
    public $description;

    /** @var string */
    public $cashfree_plan_id;

    /** @var bool */
    public $active;

    /** @var string */
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'phyto_subscription_plan',
        'primary' => 'id_plan',
        'fields'  => [
            'plan_name' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size'     => 200,
            ],
            'plan_type' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'values'   => ['Mystery', 'Replenishment', 'Custom'],
            ],
            'frequency' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'values'   => ['weekly', 'monthly', 'quarterly'],
            ],
            'price' => [
                'type'     => self::TYPE_FLOAT,
                'validate' => 'isPrice',
                'required' => true,
            ],
            'max_cycles' => [
                'type'     => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
            ],
            'description' => [
                'type'     => self::TYPE_HTML,
                'validate' => 'isCleanHtml',
            ],
            'cashfree_plan_id' => [
                'type'     => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size'     => 100,
            ],
            'active' => [
                'type'     => self::TYPE_BOOL,
                'validate' => 'isBool',
            ],
            'date_add' => [
                'type'     => self::TYPE_DATE,
                'validate' => 'isDate',
            ],
        ],
    ];

    /**
     * Return all active plans.
     *
     * @return array
     */
    public static function getActivePlans()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_subscription_plan`
             WHERE `active` = 1
             ORDER BY `plan_name` ASC'
        );
    }

    /**
     * Return a plan by its Cashfree plan ID.
     *
     * @param string $cashfreePlanId
     * @return array|bool
     */
    public static function getByCoashfreeId($cashfreePlanId)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_subscription_plan`
             WHERE `cashfree_plan_id` = \'' . pSQL($cashfreePlanId) . '\''
        );
    }

    /**
     * Map frequency to Cashfree interval_type values.
     *
     * @param string $frequency
     * @return array ['interval_type' => string, 'intervals' => int]
     */
    public static function toCashfreeInterval($frequency)
    {
        switch ($frequency) {
            case 'weekly':
                return ['interval_type' => 'week', 'intervals' => 1];
            case 'quarterly':
                return ['interval_type' => 'month', 'intervals' => 3];
            case 'monthly':
            default:
                return ['interval_type' => 'month', 'intervals' => 1];
        }
    }

    /**
     * Convenience: load and return plan as object (throws if not found).
     *
     * @param int $idPlan
     * @return PhytoSubscriptionPlan
     * @throws PrestaShopException
     */
    public static function loadById($idPlan)
    {
        $plan = new self((int) $idPlan);
        if (!Validate::isLoadedObject($plan)) {
            throw new PrestaShopException('Plan #' . (int) $idPlan . ' not found.');
        }
        return $plan;
    }
}
