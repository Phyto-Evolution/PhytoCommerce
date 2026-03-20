<?php
/**
 * AdminPhytoSubscriptionController.php
 *
 * Admin controller for managing subscription plans.
 * Allows create/edit/delete of plans and auto-creates them in Cashfree.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionPlan.php';

class AdminPhytoSubscriptionController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table       = 'phyto_subscription_plan';
        $this->className   = 'PhytoSubscriptionPlan';
        $this->identifier  = 'id_plan';
        $this->lang        = false;
        $this->bootstrap   = true;
        $this->_orderBy    = 'id_plan';
        $this->_orderWay   = 'DESC';

        parent::__construct();

        $this->module = Module::getInstanceByName('phyto_subscription');

        $this->fields_list = [
            'id_plan' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'plan_name' => [
                'title'  => $this->l('Plan Name'),
                'filter_key' => 'a!plan_name',
            ],
            'plan_type' => [
                'title'  => $this->l('Type'),
                'filter_key' => 'a!plan_type',
            ],
            'frequency' => [
                'title'  => $this->l('Frequency'),
                'filter_key' => 'a!frequency',
            ],
            'price' => [
                'title'   => $this->l('Price'),
                'type'    => 'price',
                'align'   => 'right',
                'filter_key' => 'a!price',
            ],
            'cashfree_plan_id' => [
                'title'  => $this->l('Cashfree Plan ID'),
                'filter_key' => 'a!cashfree_plan_id',
            ],
            'active' => [
                'title'   => $this->l('Active'),
                'active'  => 'status',
                'type'    => 'bool',
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
                'filter_key' => 'a!active',
            ],
        ];

        $this->addRowAction('edit');
        $this->addRowAction('delete');
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Subscription Plan'),
                'icon'  => 'icon-tags',
            ],
            'input' => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Plan Name'),
                    'name'     => 'plan_name',
                    'required' => true,
                    'col'      => 4,
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Plan Type'),
                    'name'    => 'plan_type',
                    'required'=> true,
                    'options' => [
                        'query' => [
                            ['id' => 'Mystery',       'name' => $this->l('Mystery Box')],
                            ['id' => 'Replenishment', 'name' => $this->l('Replenishment')],
                            ['id' => 'Custom',        'name' => $this->l('Custom')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Billing Frequency'),
                    'name'    => 'frequency',
                    'required'=> true,
                    'options' => [
                        'query' => [
                            ['id' => 'weekly',    'name' => $this->l('Weekly')],
                            ['id' => 'monthly',   'name' => $this->l('Monthly')],
                            ['id' => 'quarterly', 'name' => $this->l('Quarterly')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'      => 'text',
                    'label'     => $this->l('Price'),
                    'name'      => 'price',
                    'required'  => true,
                    'col'       => 2,
                    'suffix'    => $this->context->currency->sign,
                    'desc'      => $this->l('Recurring billing amount.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Max Cycles'),
                    'name'  => 'max_cycles',
                    'col'   => 2,
                    'desc'  => $this->l('0 = unlimited billing cycles.'),
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Description'),
                    'name'  => 'description',
                    'col'   => 6,
                    'rows'  => 4,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Cashfree Plan ID'),
                    'name'  => 'cashfree_plan_id',
                    'col'   => 4,
                    'desc'  => $this->l('Leave blank to auto-create in Cashfree on save.'),
                ],
                [
                    'type'    => 'switch',
                    'label'   => $this->l('Active'),
                    'name'    => 'active',
                    'is_bool' => true,
                    'values'  => [
                        ['id' => 'active_on',  'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $cashfreePlanId = trim(Tools::getValue('cashfree_plan_id'));

            // If no Cashfree plan ID provided, try to create the plan via API
            if (empty($cashfreePlanId)) {
                $planName  = Tools::getValue('plan_name');
                $frequency = Tools::getValue('frequency');
                $price     = (float) Tools::getValue('price');
                $maxCycles = (int) Tools::getValue('max_cycles');

                $interval = PhytoSubscriptionPlan::toCashfreeInterval($frequency);

                $payload = [
                    'plan_id'           => 'PHYTO-' . Tools::str2url($planName) . '-' . time(),
                    'plan_name'         => $planName,
                    'plan_type'         => 'ON_DEMAND',
                    'plan_recurring_amount' => $price,
                    'plan_intervals'    => $interval['intervals'],
                    'plan_interval_type'=> strtoupper($interval['interval_type']),
                    'plan_max_cycles'   => $maxCycles > 0 ? $maxCycles : null,
                ];

                // Remove null values
                $payload = array_filter($payload, function ($v) { return $v !== null; });

                $result = $this->module->cashfreeRequest('POST', '/pg/subscriptions/plans', $payload);

                if (
                    isset($result['code'])
                    && in_array($result['code'], [200, 201])
                    && !empty($result['body']['plan_id'])
                ) {
                    $_POST['cashfree_plan_id'] = $result['body']['plan_id'];
                    $this->confirmations[] = $this->l('Plan successfully created in Cashfree: ')
                        . $result['body']['plan_id'];
                } else {
                    $errorMsg = isset($result['body']['message'])
                        ? $result['body']['message']
                        : $this->l('Unknown error');
                    $this->warnings[] = $this->l(
                        'Could not create plan in Cashfree (will save locally only). Error: '
                    ) . $errorMsg;
                }
            }

            // Set date_add for new records
            $idPlan = (int) Tools::getValue('id_plan');
            if (!$idPlan) {
                $_POST['date_add'] = date('Y-m-d H:i:s');
            }
        }

        return parent::postProcess();
    }
}
