<?php
/**
 * AdminPhytoLoyaltyController.php
 *
 * Admin controller for the Phyto Loyalty programme.
 * Tabs: Overview | Customers | Transactions | Settings
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyAccount.php';
require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyTransaction.php';

class AdminPhytoLoyaltyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap  = true;
        $this->table      = 'phyto_loyalty_account';
        $this->className  = 'PhytoLoyaltyAccount';
        $this->identifier = 'id_loyalty';
        $this->lang       = false;
        $this->_orderBy   = 'id_loyalty';
        $this->_orderWay  = 'DESC';

        parent::__construct();

        $this->module = Module::getInstanceByName('phyto_loyalty');
        $this->meta_title = $this->l('Phyto Loyalty');

        // Default view is overview; tabs are rendered manually
        $this->fields_list = [
            'id_loyalty' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'id_customer' => [
                'title'      => $this->l('Customer ID'),
                'filter_key' => 'a!id_customer',
            ],
            'points_balance' => [
                'title'      => $this->l('Balance'),
                'filter_key' => 'a!points_balance',
                'orderby'    => true,
            ],
            'points_lifetime' => [
                'title'      => $this->l('Lifetime'),
                'filter_key' => 'a!points_lifetime',
                'orderby'    => true,
            ],
            'tier' => [
                'title'      => $this->l('Tier'),
                'filter_key' => 'a!tier',
            ],
            'date_upd' => [
                'title'   => $this->l('Last Activity'),
                'type'    => 'datetime',
                'filter_key' => 'a!date_upd',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Render dispatch
    // -------------------------------------------------------------------------

    public function initContent(): void
    {
        $action = Tools::getValue('action', 'overview');

        switch ($action) {
            case 'customers':
                $this->renderCustomersList();
                break;
            case 'customerDetail':
                $this->renderCustomerDetail();
                break;
            case 'adjustPoints':
                $this->processAdjustPoints();
                break;
            case 'transactions':
                $this->renderTransactions();
                break;
            case 'settings':
                $this->renderSettings();
                break;
            case 'saveSettings':
                $this->processSaveSettings();
                break;
            default:
                $this->renderOverview();
                break;
        }

        parent::initContent();
    }

    // -------------------------------------------------------------------------
    // Tab: Overview
    // -------------------------------------------------------------------------

    protected function renderOverview(): void
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $totalMembers = (int) $db->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`'
        );
        $pointsOutstanding = (int) $db->getValue(
            'SELECT COALESCE(SUM(`points_balance`), 0) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`'
        );
        $pointsRedeemedLifetime = (int) $db->getValue(
            'SELECT COALESCE(SUM(`points_redeemed`), 0) FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`'
        );

        $topEarners = $db->executeS(
            'SELECT a.`id_customer`, a.`points_lifetime`, a.`points_balance`, a.`tier`,
                    CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name, c.`email`
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             ORDER BY a.`points_lifetime` DESC
             LIMIT 10'
        ) ?: [];

        $tierCounts = $db->executeS(
            'SELECT `tier`, COUNT(*) AS cnt
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account`
             GROUP BY `tier`'
        ) ?: [];

        $tierMap = [];
        foreach ($tierCounts as $row) {
            $tierMap[$row['tier']] = (int) $row['cnt'];
        }

        $this->context->smarty->assign([
            'phyto_loyalty_total_members'        => $totalMembers,
            'phyto_loyalty_points_outstanding'   => $pointsOutstanding,
            'phyto_loyalty_points_redeemed_life' => $pointsRedeemedLifetime,
            'phyto_loyalty_top_earners'          => $topEarners,
            'phyto_loyalty_tier_counts'          => $tierMap,
            'phyto_loyalty_tab'                  => 'overview',
            'phyto_loyalty_admin_link'           => $this->context->link->getAdminLink('AdminPhytoLoyalty'),
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phyto_loyalty/views/templates/admin/overview.tpl'
        );
    }

    // -------------------------------------------------------------------------
    // Tab: Customers
    // -------------------------------------------------------------------------

    protected function renderCustomersList(): void
    {
        $search   = pSQL(Tools::getValue('search', ''));
        $page     = max(1, (int) Tools::getValue('page', 1));
        $pageSize = 25;
        $offset   = ($page - 1) * $pageSize;

        $searchWhere = '';
        if ($search !== '') {
            $searchWhere = ' AND (c.`firstname` LIKE \'%' . $search . '%\'
                             OR c.`lastname` LIKE \'%' . $search . '%\'
                             OR c.`email` LIKE \'%' . $search . '%\')';
        }

        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $total = (int) $db->getValue(
            'SELECT COUNT(*)
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             WHERE 1=1' . $searchWhere
        );

        $customers = $db->executeS(
            'SELECT a.*, CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name, c.`email`
             FROM `' . _DB_PREFIX_ . 'phyto_loyalty_account` a
             LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`
             WHERE 1=1' . $searchWhere . '
             ORDER BY a.`points_balance` DESC
             LIMIT ' . $offset . ', ' . $pageSize
        ) ?: [];

        $this->context->smarty->assign([
            'phyto_loyalty_customers'  => $customers,
            'phyto_loyalty_page'       => $page,
            'phyto_loyalty_pages'      => max(1, (int) ceil($total / $pageSize)),
            'phyto_loyalty_total'      => $total,
            'phyto_loyalty_search'     => $search,
            'phyto_loyalty_tab'        => 'customers',
            'phyto_loyalty_admin_link' => $this->context->link->getAdminLink('AdminPhytoLoyalty'),
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phyto_loyalty/views/templates/admin/customers.tpl'
        );
    }

    // -------------------------------------------------------------------------
    // Customer Detail + Adjust
    // -------------------------------------------------------------------------

    protected function renderCustomerDetail(): void
    {
        $idCustomer = (int) Tools::getValue('id_customer', 0);
        if (!$idCustomer) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminPhytoLoyalty') . '&action=customers');
        }

        $account  = PhytoLoyaltyAccount::getByCustomer($idCustomer);
        $customer = new Customer($idCustomer);
        $page     = max(1, (int) Tools::getValue('page', 1));
        $pageSize = 20;

        $total        = PhytoLoyaltyTransaction::countForCustomer($idCustomer);
        $transactions = PhytoLoyaltyTransaction::getForCustomer($idCustomer, $page, $pageSize);

        $this->context->smarty->assign([
            'phyto_loyalty_account'      => $account,
            'phyto_loyalty_customer'     => $customer,
            'phyto_loyalty_transactions' => $transactions,
            'phyto_loyalty_page'         => $page,
            'phyto_loyalty_pages'        => max(1, (int) ceil($total / $pageSize)),
            'phyto_loyalty_total_tx'     => $total,
            'phyto_loyalty_tab'          => 'customers',
            'phyto_loyalty_admin_link'   => $this->context->link->getAdminLink('AdminPhytoLoyalty'),
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phyto_loyalty/views/templates/admin/customer_detail.tpl'
        );
    }

    protected function processAdjustPoints(): void
    {
        $idCustomer = (int) Tools::getValue('id_customer', 0);
        $points     = (int) Tools::getValue('adjust_points', 0);
        $note       = pSQL(Tools::getValue('adjust_note', ''));

        if ($idCustomer && $points !== 0) {
            if ($this->module->adjustPoints($idCustomer, $points, $note)) {
                $this->confirmations[] = $this->l('Points adjusted successfully.');
            } else {
                $this->errors[] = $this->l('Failed to adjust points.');
            }
        }

        // Redirect back to customer detail
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoLoyalty')
            . '&action=customerDetail&id_customer=' . $idCustomer
        );
    }

    // -------------------------------------------------------------------------
    // Tab: Transactions
    // -------------------------------------------------------------------------

    protected function renderTransactions(): void
    {
        $page     = max(1, (int) Tools::getValue('page', 1));
        $pageSize = 30;

        $filters = [
            'type'        => Tools::getValue('filter_type', ''),
            'date_from'   => Tools::getValue('filter_date_from', ''),
            'date_to'     => Tools::getValue('filter_date_to', ''),
            'id_customer' => (int) Tools::getValue('filter_customer', 0),
        ];

        $total        = PhytoLoyaltyTransaction::countFiltered($filters);
        $transactions = PhytoLoyaltyTransaction::getFiltered($filters, $page, $pageSize);

        $this->context->smarty->assign([
            'phyto_loyalty_transactions' => $transactions,
            'phyto_loyalty_page'         => $page,
            'phyto_loyalty_pages'        => max(1, (int) ceil($total / $pageSize)),
            'phyto_loyalty_total_tx'     => $total,
            'phyto_loyalty_filters'      => $filters,
            'phyto_loyalty_tab'          => 'transactions',
            'phyto_loyalty_admin_link'   => $this->context->link->getAdminLink('AdminPhytoLoyalty'),
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phyto_loyalty/views/templates/admin/transactions.tpl'
        );
    }

    // -------------------------------------------------------------------------
    // Tab: Settings
    // -------------------------------------------------------------------------

    protected function renderSettings(): void
    {
        $this->context->smarty->assign([
            'phyto_loyalty_tab'          => 'settings',
            'phyto_loyalty_admin_link'   => $this->context->link->getAdminLink('AdminPhytoLoyalty'),
            'PHYTO_LOYALTY_ENABLED'      => (int) Configuration::get('PHYTO_LOYALTY_ENABLED'),
            'PHYTO_LOYALTY_EARN_RATE'    => Configuration::get('PHYTO_LOYALTY_EARN_RATE'),
            'PHYTO_LOYALTY_REDEEM_RATE'  => Configuration::get('PHYTO_LOYALTY_REDEEM_RATE'),
            'PHYTO_LOYALTY_MIN_REDEEM'   => Configuration::get('PHYTO_LOYALTY_MIN_REDEEM'),
            'PHYTO_LOYALTY_MAX_REDEEM_PCT' => Configuration::get('PHYTO_LOYALTY_MAX_REDEEM_PCT'),
            'PHYTO_LOYALTY_EXPIRY_DAYS'  => Configuration::get('PHYTO_LOYALTY_EXPIRY_DAYS'),
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'phyto_loyalty/views/templates/admin/configure.tpl'
        );
    }

    protected function processSaveSettings(): void
    {
        $fields = [
            'PHYTO_LOYALTY_EARN_RATE'      => 'isFloat',
            'PHYTO_LOYALTY_REDEEM_RATE'    => 'isFloat',
            'PHYTO_LOYALTY_MIN_REDEEM'     => 'isUnsignedInt',
            'PHYTO_LOYALTY_MAX_REDEEM_PCT' => 'isUnsignedInt',
            'PHYTO_LOYALTY_EXPIRY_DAYS'    => 'isUnsignedInt',
        ];

        $hasError = false;
        foreach ($fields as $key => $validator) {
            $value = Tools::getValue($key);
            if (!Validate::$validator($value)) {
                $this->errors[] = sprintf($this->l('Invalid value for %s.'), $key);
                $hasError = true;
            } else {
                Configuration::updateValue($key, (string) $value);
            }
        }

        Configuration::updateValue('PHYTO_LOYALTY_ENABLED', (int) Tools::getValue('PHYTO_LOYALTY_ENABLED'));

        if (!$hasError) {
            $this->confirmations[] = $this->l('Settings saved.');
        }

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminPhytoLoyalty') . '&action=settings'
        );
    }
}
