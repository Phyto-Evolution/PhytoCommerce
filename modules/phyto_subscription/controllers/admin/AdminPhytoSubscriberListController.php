<?php
/**
 * AdminPhytoSubscriberListController.php
 *
 * Admin controller for viewing and managing customer subscriptions.
 * Supports Pause, Resume and Cancel actions via the Cashfree Manage API.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionCustomer.php';
require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionPlan.php';

class AdminPhytoSubscriberListController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table       = 'phyto_subscription_customer';
        $this->className   = 'PhytoSubscriptionCustomer';
        $this->identifier  = 'id_sub';
        $this->lang        = false;
        $this->bootstrap   = true;
        $this->_orderBy    = 'id_sub';
        $this->_orderWay   = 'DESC';
        $this->deleted     = false;

        parent::__construct();

        $this->module = Module::getInstanceByName('phyto_subscription');

        // JOIN to get customer name and plan name
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)
            LEFT JOIN `' . _DB_PREFIX_ . 'phyto_subscription_plan` p ON (p.`id_plan` = a.`id_plan`)
        ';

        $this->_select = '
            a.*,
            CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name,
            p.`plan_name`
        ';

        $this->fields_list = [
            'id_sub' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'customer_name' => [
                'title'      => $this->l('Customer'),
                'filter_key' => 'c!firstname',
            ],
            'plan_name' => [
                'title'      => $this->l('Plan'),
                'filter_key' => 'p!plan_name',
            ],
            'cashfree_subscription_id' => [
                'title'      => $this->l('Cashfree Subscription ID'),
                'filter_key' => 'a!cashfree_subscription_id',
            ],
            'status' => [
                'title'      => $this->l('Status'),
                'filter_key' => 'a!status',
                'callback'   => 'renderStatus',
            ],
            'start_date' => [
                'title'      => $this->l('Start Date'),
                'type'       => 'date',
                'filter_key' => 'a!start_date',
            ],
            'next_billing_date' => [
                'title'      => $this->l('Next Billing'),
                'type'       => 'date',
                'filter_key' => 'a!next_billing_date',
            ],
        ];

        $this->addRowAction('pause');
        $this->addRowAction('resume');
        $this->addRowAction('cancel');
    }

    // -------------------------------------------------------------------------
    // Status badge helper (callback for fields_list)
    // -------------------------------------------------------------------------

    public function renderStatus($status)
    {
        $badges = [
            'created'   => 'info',
            'active'    => 'success',
            'paused'    => 'warning',
            'cancelled' => 'danger',
            'completed' => 'primary',
        ];
        $badge = isset($badges[$status]) ? $badges[$status] : 'default';
        return '<span class="badge badge-' . $badge . '">' . htmlspecialchars($status) . '</span>';
    }

    // -------------------------------------------------------------------------
    // Row action buttons
    // -------------------------------------------------------------------------

    public function displayPauseLink($token, $id)
    {
        return $this->renderActionButton(
            'pause',
            $id,
            $token,
            'icon-pause',
            $this->l('Pause')
        );
    }

    public function displayResumeLink($token, $id)
    {
        return $this->renderActionButton(
            'resume',
            $id,
            $token,
            'icon-play',
            $this->l('Resume')
        );
    }

    public function displayCancelLink($token, $id)
    {
        return $this->renderActionButton(
            'cancel',
            $id,
            $token,
            'icon-ban-circle',
            $this->l('Cancel'),
            'onclick="return confirm(\'' . $this->l('Cancel this subscription?') . '\');"'
        );
    }

    protected function renderActionButton($action, $id, $token, $icon, $label, $extra = '')
    {
        $url = $this->context->link->getAdminLink('AdminPhytoSubscriberList')
            . '&id_sub=' . (int) $id
            . '&action=' . $action;

        return '<a href="' . htmlspecialchars($url) . '" class="btn btn-default btn-xs" ' . $extra . '>'
            . '<i class="' . $icon . '"></i> ' . $label
            . '</a>';
    }

    // -------------------------------------------------------------------------
    // postProcess — handle pause / resume / cancel
    // -------------------------------------------------------------------------

    public function postProcess()
    {
        $action = Tools::getValue('action');

        if (in_array($action, ['pause', 'resume', 'cancel'])) {
            $idSub = (int) Tools::getValue('id_sub');

            if (!$idSub) {
                $this->errors[] = $this->l('Invalid subscription ID.');
                return parent::postProcess();
            }

            $sub = new PhytoSubscriptionCustomer($idSub);
            if (!Validate::isLoadedObject($sub)) {
                $this->errors[] = $this->l('Subscription not found.');
                return parent::postProcess();
            }

            $result = $this->callManageEndpoint($sub->cashfree_subscription_id, $action);

            if ($result) {
                $newStatus = $this->actionToStatus($action);
                $sub->status   = $newStatus;
                $sub->date_upd = date('Y-m-d H:i:s');
                $sub->save();

                $this->confirmations[] = sprintf(
                    $this->l('Subscription %s has been %sd successfully.'),
                    htmlspecialchars($sub->cashfree_subscription_id),
                    $action
                );
            } else {
                $this->errors[] = sprintf(
                    $this->l('Failed to %s subscription in Cashfree. The DB status was not changed.'),
                    $action
                );
            }
        }

        return parent::postProcess();
    }

    /**
     * Call the Cashfree manage subscription endpoint.
     *
     * Cashfree API: POST /pg/subscriptions/{subscription_id}/manage
     * with body: {"action": "PAUSE" | "RESUME" | "CANCEL"}
     *
     * @param string $cashfreeSubId
     * @param string $action  pause|resume|cancel
     * @return bool
     */
    protected function callManageEndpoint($cashfreeSubId, $action)
    {
        if (empty($cashfreeSubId)) {
            return false;
        }

        $cfAction  = strtoupper($action);
        $endpoint  = '/pg/subscriptions/' . urlencode($cashfreeSubId) . '/manage';
        $payload   = ['action' => $cfAction];

        $result = $this->module->cashfreeRequest('POST', $endpoint, $payload);

        return isset($result['code']) && in_array($result['code'], [200, 201, 204]);
    }

    /**
     * Map front-end action name to DB status.
     *
     * @param string $action
     * @return string
     */
    protected function actionToStatus($action)
    {
        $map = [
            'pause'  => 'paused',
            'resume' => 'active',
            'cancel' => 'cancelled',
        ];
        return isset($map[$action]) ? $map[$action] : 'created';
    }

    // -------------------------------------------------------------------------
    // No form — subscriptions are created through the front office only
    // -------------------------------------------------------------------------

    public function renderForm()
    {
        $this->errors[] = $this->l('Subscriptions cannot be created from the back office.');
        return '';
    }
}
