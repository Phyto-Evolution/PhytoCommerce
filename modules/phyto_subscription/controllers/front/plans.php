<?php
/**
 * plans.php
 *
 * FrontController — public listing of all active subscription plans.
 * URL: /module/phyto_subscription/plans
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_subscription/classes/PhytoSubscriptionPlan.php';

class Phyto_SubscriptionPlansModuleFrontController extends ModuleFrontController
{
    /** @var bool No login required to browse plans */
    public $auth = false;

    public function initContent()
    {
        parent::initContent();

        $plans = PhytoSubscriptionPlan::getActivePlans();

        // Enrich plans with subscribe URLs and formatted prices
        $currency = $this->context->currency;
        foreach ($plans as &$plan) {
            $plan['subscribe_url'] = $this->context->link->getModuleLink(
                'phyto_subscription',
                'subscribe',
                ['id_plan' => (int) $plan['id_plan']]
            );
            $plan['price_formatted'] = Tools::displayPrice(
                (float) $plan['price'],
                $currency
            );
        }
        unset($plan);

        $this->context->smarty->assign([
            'phyto_plans'       => $plans,
            'phyto_page_title'  => $this->module->l('Subscription Plans'),
            'is_logged_in'      => (bool) $this->context->customer->isLogged(),
            'login_url'         => $this->context->link->getPageLink(
                'authentication',
                true,
                null,
                ['back' => urlencode($this->context->link->getModuleLink('phyto_subscription', 'plans'))]
            ),
        ]);

        $this->setTemplate('module:phyto_subscription/views/templates/front/plans.tpl');
    }

    /**
     * Breadcrumb support.
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->module->l('Subscription Plans'),
            'url'   => $this->context->link->getModuleLink('phyto_subscription', 'plans'),
        ];
        return $breadcrumb;
    }
}
