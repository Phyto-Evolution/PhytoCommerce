<?php
/**
 * Admin controller — Catalog → Seasonal Notifications
 *
 * HelperList of captured notify-me emails. Supports bulk CSV export
 * and a "mark as notified" toggle.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoSeasonalNotifyController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table      = 'phyto_seasonal_notify';
        $this->identifier = 'id_notify';
        $this->className  = ''; // no ObjectModel — raw table
        $this->lang       = false;
        $this->bootstrap  = true;
        $this->list_no_link = true;

        $this->addRowAction(''); // no row actions needed

        $this->_select = 'pl.`name` AS product_name';
        $this->_join   = 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                            ON (pl.`id_product` = a.`id_product`
                                AND pl.`id_lang` = ' . (int) Context::getContext()->language->id . '
                                AND pl.`id_shop` = ' . (int) Context::getContext()->shop->id . ')';

        $this->fields_list = [
            'id_notify' => [
                'title'  => 'ID',
                'align'  => 'center',
                'class'  => 'fixed-width-xs',
                'filter_key' => 'a!id_notify',
            ],
            'product_name' => [
                'title'      => 'Product',
                'filter_key' => 'pl!name',
            ],
            'name' => [
                'title' => 'Name',
            ],
            'email' => [
                'title' => 'Email',
            ],
            'notified' => [
                'title'   => 'Notified',
                'align'   => 'center',
                'active'  => 'notified',
                'type'    => 'bool',
                'class'   => 'fixed-width-sm',
                'filter_key' => 'a!notified',
            ],
            'date_add' => [
                'title' => 'Date',
                'type'  => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        $this->bulk_actions = [
            'export' => [
                'text' => 'Export selected as CSV',
                'icon' => 'icon-download',
            ],
            'marknotified' => [
                'text'    => 'Mark as notified',
                'icon'    => 'icon-check',
                'confirm' => 'Mark selected entries as notified?',
            ],
        ];

        parent::__construct();

        $this->toolbar_title = $this->l('Seasonal Notifications');
    }

    /* ------------------------------------------------------------------
     *  Bulk: Export CSV
     * ------------------------------------------------------------------ */

    protected function processBulkExport()
    {
        $ids = $this->boxes;
        if (empty($ids)) {
            $this->errors[] = $this->l('Please select at least one entry.');
            return;
        }

        $ids = array_map('intval', $ids);

        $rows = Db::getInstance()->executeS(
            'SELECT n.`id_notify`, n.`id_product`, pl.`name` AS product_name,
                    n.`name`, n.`email`, n.`notified`, n.`date_add`
             FROM `' . _DB_PREFIX_ . 'phyto_seasonal_notify` n
             LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON (pl.`id_product` = n.`id_product`
                    AND pl.`id_lang` = ' . (int) $this->context->language->id . '
                    AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')
             WHERE n.`id_notify` IN (' . implode(',', $ids) . ')
             ORDER BY n.`date_add` DESC'
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="seasonal_notifications_' . date('Ymd_His') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Product ID', 'Product', 'Name', 'Email', 'Notified', 'Date']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id_notify'],
                $r['id_product'],
                $r['product_name'],
                $r['name'],
                $r['email'],
                $r['notified'] ? 'Yes' : 'No',
                $r['date_add'],
            ]);
        }

        fclose($out);
        exit;
    }

    /* ------------------------------------------------------------------
     *  Bulk: Mark as notified
     * ------------------------------------------------------------------ */

    protected function processBulkMarknotified()
    {
        $ids = $this->boxes;
        if (empty($ids)) {
            $this->errors[] = $this->l('Please select at least one entry.');
            return;
        }

        $ids = array_map('intval', $ids);

        $ok = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_seasonal_notify`
             SET `notified` = 1
             WHERE `id_notify` IN (' . implode(',', $ids) . ')'
        );

        if ($ok) {
            $this->confirmations[] = $this->l('Selected entries marked as notified.');
        } else {
            $this->errors[] = $this->l('An error occurred while updating records.');
        }
    }

    /* ------------------------------------------------------------------
     *  Toggle notified via row icon
     * ------------------------------------------------------------------ */

    public function processNotifiedphyto_seasonal_notify()
    {
        $id = (int) Tools::getValue('id_notify');
        if ($id) {
            $current = (int) Db::getInstance()->getValue(
                'SELECT `notified` FROM `' . _DB_PREFIX_ . 'phyto_seasonal_notify`
                 WHERE `id_notify` = ' . $id
            );

            Db::getInstance()->update('phyto_seasonal_notify', [
                'notified' => $current ? 0 : 1,
            ], '`id_notify` = ' . $id);
        }

        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }
}
