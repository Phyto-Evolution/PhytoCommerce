<?php
/**
 * controllers/front/account.php
 *
 * Front controller — My Loyalty Points page.
 * Shows balance, tier, tier progress, and paginated transaction history.
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyAccount.php';
require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyTransaction.php';

class Phyto_LoyaltyAccountModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = true;  // requires login

    /** @var bool */
    public $ssl = true;

    public function initContent(): void
    {
        parent::initContent();

        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            Tools::redirect('index.php?controller=my-account');
        }

        $idCustomer = (int) $this->context->customer->id;
        $account    = PhytoLoyaltyAccount::getOrCreate($idCustomer);

        // Expire stale points
        $expiryDays = (int) Configuration::get('PHYTO_LOYALTY_EXPIRY_DAYS');
        $account->expireStalePoints($expiryDays);

        // Pagination
        $page     = max(1, (int) Tools::getValue('page', 1));
        $pageSize = 15;
        $total    = PhytoLoyaltyTransaction::countForCustomer($idCustomer);
        $pages    = max(1, (int) ceil($total / $pageSize));
        $page     = min($page, $pages);

        $transactions = PhytoLoyaltyTransaction::getForCustomer($idCustomer, $page, $pageSize);

        // Tier data
        $tierLabels = [
            'seed'   => $this->module->l('Seed'),
            'sprout' => $this->module->l('Sprout'),
            'bloom'  => $this->module->l('Bloom'),
            'rare'   => $this->module->l('Rare'),
        ];
        $nextTierInfo = $account->nextTierInfo();
        $progressPct  = $account->tierProgressPct();

        $this->context->smarty->assign([
            'phyto_loyalty_account'      => $account,
            'phyto_loyalty_balance'      => (int) $account->points_balance,
            'phyto_loyalty_lifetime'     => (int) $account->points_lifetime,
            'phyto_loyalty_redeemed'     => (int) $account->points_redeemed,
            'phyto_loyalty_tier'         => $account->tier,
            'phyto_loyalty_tier_label'   => $tierLabels[$account->tier] ?? ucfirst($account->tier),
            'phyto_loyalty_tier_labels'  => $tierLabels,
            'phyto_loyalty_next_tier'    => $nextTierInfo['next_tier'],
            'phyto_loyalty_points_to_next' => $nextTierInfo['points_needed'],
            'phyto_loyalty_progress_pct' => $progressPct,
            'phyto_loyalty_transactions' => $transactions,
            'phyto_loyalty_page'         => $page,
            'phyto_loyalty_pages'        => $pages,
            'phyto_loyalty_total_tx'     => $total,
            'phyto_loyalty_redeem_rate'  => (float) Configuration::get('PHYTO_LOYALTY_REDEEM_RATE'),
            'phyto_loyalty_earn_rate'    => (float) Configuration::get('PHYTO_LOYALTY_EARN_RATE'),
        ]);

        $this->setTemplate('module:phyto_loyalty/views/templates/front/account.tpl');
    }

    public function getBreadcrumbLinks(): array
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->trans('My account', [], 'Shop.Theme.Customeraccount'),
            'url'   => $this->context->link->getPageLink('my-account'),
        ];
        $breadcrumb['links'][] = [
            'title' => $this->module->l('My Loyalty Points'),
            'url'   => $this->context->link->getModuleLink('phyto_loyalty', 'account'),
        ];

        return $breadcrumb;
    }
}
