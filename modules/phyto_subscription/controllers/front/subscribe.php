<?php
/**
 * subscribe.php
 *
 * FrontController — initiates a Cashfree subscription for a customer.
 * GET /module/phyto_subscription/subscribe?id_plan=X
 *
 * Flow:
 * 1. Validate customer & plan.
 * 2. Build a unique subscription_id.
 * 3. POST to Cashfree /pg/subscriptions.
 * 4. Save subscription record to DB (status = created).
 * 5. Redirect customer to Cashfree authorisation_details.payment_url.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionPlan.php';
require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionCustomer.php';

class Phyto_SubscriptionSubscribeModuleFrontController extends ModuleFrontController
{
    /** @var bool Authentication required */
    public $auth = true;

    public $authRedirection = 'index.php?controller=authentication&back=';

    public function initContent()
    {
        parent::initContent();

        $idPlan = (int) Tools::getValue('id_plan');

        if (!$idPlan) {
            $this->redirectWithError($this->module->l('No subscription plan specified.'));
            return;
        }

        // Load plan
        try {
            $plan = PhytoSubscriptionPlan::loadById($idPlan);
        } catch (Exception $e) {
            $this->redirectWithError($this->module->l('Subscription plan not found.'));
            return;
        }

        if (!$plan->active) {
            $this->redirectWithError($this->module->l('This subscription plan is not currently available.'));
            return;
        }

        if (empty($plan->cashfree_plan_id)) {
            $this->redirectWithError(
                $this->module->l('This plan has not been configured with a payment provider yet. Please try another plan.')
            );
            return;
        }

        $customer  = $this->context->customer;
        $idCustomer = (int) $customer->id;

        // Build unique subscription ID
        $subscriptionId = 'PHYTO-' . $idCustomer . '-' . $idPlan . '-' . time();

        // Return URL after payment authorisation
        $returnUrl = $this->context->link->getModuleLink(
            'phyto_subscription',
            'callback',
            ['subscription_id' => $subscriptionId]
        );

        // Customer details for Cashfree
        $customerDetails = [
            'customer_id'    => 'CUST-' . $idCustomer,
            'customer_email' => $customer->email,
            'customer_name'  => $customer->firstname . ' ' . $customer->lastname,
            'customer_phone' => $this->getCustomerPhone($idCustomer),
        ];

        // Build Cashfree payload
        $interval = PhytoSubscriptionPlan::toCashfreeInterval($plan->frequency);

        $payload = [
            'subscription_id'     => $subscriptionId,
            'plan_id'             => $plan->cashfree_plan_id,
            'customer_details'    => $customerDetails,
            'return_url'          => $returnUrl,
            'subscription_note'   => 'PhytoCommerce subscription for ' . $plan->plan_name,
            'subscription_meta'   => [
                'notify_url' => $this->context->link->getModuleLink('phyto_subscription', 'webhook'),
            ],
        ];

        // Call Cashfree
        $result = $this->module->cashfreeRequest('POST', '/pg/subscriptions', $payload);

        if (
            !isset($result['code'])
            || !in_array($result['code'], [200, 201])
            || empty($result['body']['authorisation_details']['payment_url'])
        ) {
            $errorMsg = isset($result['body']['message'])
                ? $result['body']['message']
                : $this->module->l('Unknown error');

            PrestaShopLogger::addLog(
                '[PhytoSubscription] Failed to create Cashfree subscription: ' . json_encode($result['body']),
                3,
                null,
                'PhytoSubscription',
                $idCustomer,
                true
            );

            $this->redirectWithError(
                $this->module->l('Could not initiate subscription payment. Please try again or contact support.')
                . ' (' . htmlspecialchars($errorMsg) . ')'
            );
            return;
        }

        // Save to DB
        $sub = new PhytoSubscriptionCustomer();
        $sub->id_customer               = $idCustomer;
        $sub->id_plan                   = $idPlan;
        $sub->cashfree_subscription_id  = $subscriptionId;
        $sub->status                    = 'created';
        $sub->date_add                  = date('Y-m-d H:i:s');
        $sub->date_upd                  = date('Y-m-d H:i:s');
        $sub->save();

        // Redirect to Cashfree payment page
        $paymentUrl = $result['body']['authorisation_details']['payment_url'];
        Tools::redirectLink($paymentUrl);
    }

    /**
     * Fetch a customer's default phone number.
     *
     * @param int $idCustomer
     * @return string
     */
    protected function getCustomerPhone($idCustomer)
    {
        $address = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `phone`, `phone_mobile`
             FROM `' . _DB_PREFIX_ . 'address`
             WHERE `id_customer` = ' . (int) $idCustomer . '
               AND `deleted` = 0
             ORDER BY `id_address` DESC
             LIMIT 1'
        );

        if ($address) {
            return $address['phone_mobile'] ?: $address['phone'] ?: '0000000000';
        }

        return '0000000000';
    }

    /**
     * Redirect to the plans page with an error message stored in the session.
     *
     * @param string $message
     */
    protected function redirectWithError($message)
    {
        $this->context->cookie->phyto_sub_error = $message;
        Tools::redirect($this->context->link->getModuleLink('phyto_subscription', 'plans'));
    }
}
