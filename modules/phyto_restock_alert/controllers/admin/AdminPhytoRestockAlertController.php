<?php
/**
 * AdminPhytoRestockAlertController.php
 *
 * Back-office controller for managing restock alert subscriptions.
 *
 * Features:
 *  - Paginated list of all alert subscriptions
 *  - Filters: product, notified status
 *  - Bulk actions: delete, mark as notified, send manual notification
 *  - Per-row "Send Now" action
 *  - "Send Notifications Now" button for a specific product
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoRestockAlertController extends ModuleAdminController
{
    public function __construct()
    {
        $this->table       = 'phyto_restock_alert';
        $this->className   = 'ObjectModel'; // no dedicated ObjectModel class; raw SQL used
        $this->lang        = false;
        $this->addRowAction('delete');
        $this->addRowAction('sendnow');
        $this->bulk_actions = [
            'delete'        => [
                'text'    => $this->l('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->l('Delete selected alerts?'),
            ],
            'marknotified'  => [
                'text' => $this->l('Mark as notified'),
                'icon' => 'icon-check',
            ],
            'sendnotif'     => [
                'text' => $this->l('Send notification now'),
                'icon' => 'icon-envelope',
            ],
        ];

        $this->fields_list = [
            'id_alert'   => [
                'title'   => $this->l('ID'),
                'align'   => 'center',
                'class'   => 'fixed-width-xs',
            ],
            'email'      => [
                'title'   => $this->l('Email'),
                'filter_key' => 'a!email',
            ],
            'firstname'  => [
                'title'   => $this->l('First Name'),
                'filter_key' => 'a!firstname',
            ],
            'product_name' => [
                'title'      => $this->l('Product'),
                'filter_key' => 'pl!name',
                'havingFilter' => true,
            ],
            'id_product_attribute' => [
                'title'   => $this->l('Combination ID'),
                'align'   => 'center',
                'class'   => 'fixed-width-sm',
                'filter_key' => 'a!id_product_attribute',
            ],
            'date_add'   => [
                'title'   => $this->l('Subscribed On'),
                'type'    => 'datetime',
                'filter_key' => 'a!date_add',
            ],
            'notified'   => [
                'title'   => $this->l('Notified'),
                'align'   => 'center',
                'type'    => 'bool',
                'active'  => 'notified',
                'filter_key' => 'a!notified',
            ],
            'date_notified' => [
                'title'   => $this->l('Notified On'),
                'type'    => 'datetime',
                'filter_key' => 'a!date_notified',
            ],
        ];

        parent::__construct();

        $this->_select = '
            a.*,
            pl.`name` AS product_name
        ';
        $this->_join = '
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                ON pl.`id_product` = a.`id_product`
                AND pl.`id_lang` = ' . (int) $this->context->language->id . '
                AND pl.`id_shop` = ' . (int) $this->context->shop->id . '
        ';
        $this->_defaultOrderBy  = 'id_alert';
        $this->_defaultOrderWay = 'DESC';

        $this->meta_title = $this->l('Restock Alert Subscriptions');
    }

    // -------------------------------------------------------------------------
    // Toolbar
    // -------------------------------------------------------------------------

    public function initToolbarTitle(): void
    {
        parent::initToolbarTitle();
        $this->toolbar_title[] = $this->l('Restock Alert Subscriptions');
    }

    public function initToolbar(): void
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']); // No manual record creation from list
    }

    // -------------------------------------------------------------------------
    // Row actions
    // -------------------------------------------------------------------------

    /**
     * Custom "sendnow" row action — sends notification for a single alert row.
     */
    public function displaySendnowLink(string $token, int $id): string
    {
        $href = self::$currentIndex
            . '&action=sendnow&id_alert=' . $id
            . '&token=' . $token;

        return '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" '
            . 'title="' . $this->l('Send notification now') . '" '
            . 'class="btn btn-default">'
            . '<i class="icon-envelope"></i>'
            . '</a>';
    }

    // -------------------------------------------------------------------------
    // Post-process: row & bulk actions
    // -------------------------------------------------------------------------

    public function postProcess(): void
    {
        // Single row: send notification
        if (Tools::isSubmit('action') && Tools::getValue('action') === 'sendnow') {
            $this->processSendNow((int) Tools::getValue('id_alert'));
        }

        // Single row: toggle notified flag via list toggle (PS convention)
        if (Tools::isSubmit('statusphyto_restock_alert')) {
            $this->processToggleNotified((int) Tools::getValue('id_phyto_restock_alert'));
        }

        // Bulk delete
        if (Tools::isSubmit('submitBulkdelete' . $this->table)) {
            $this->processBulkDelete();
        }

        // Bulk mark notified
        if (Tools::isSubmit('submitBulkmarknotified' . $this->table)) {
            $this->processBulkMarkNotified();
        }

        // Bulk send notifications
        if (Tools::isSubmit('submitBulksendnotif' . $this->table)) {
            $this->processBulkSendNotif();
        }

        parent::postProcess();
    }

    // -------------------------------------------------------------------------
    // Action handlers
    // -------------------------------------------------------------------------

    protected function processSendNow(int $idAlert): void
    {
        if (!$idAlert) {
            $this->errors[] = $this->l('Invalid alert ID.');
            return;
        }

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_restock_alert`
             WHERE `id_alert` = ' . $idAlert
        );

        if (!$row) {
            $this->errors[] = $this->l('Alert not found.');
            return;
        }

        if ($this->sendSingleNotification($row)) {
            $this->confirmations[] = $this->l('Notification sent to') . ' ' . $row['email'];
        } else {
            $this->errors[] = $this->l('Failed to send notification to') . ' ' . $row['email'];
        }
    }

    protected function processToggleNotified(int $idAlert): void
    {
        if (!$idAlert) {
            return;
        }

        $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT `notified` FROM `' . _DB_PREFIX_ . 'phyto_restock_alert`
             WHERE `id_alert` = ' . $idAlert
        );

        if (!$row) {
            return;
        }

        $newVal = $row['notified'] ? 0 : 1;

        Db::getInstance()->update(
            'phyto_restock_alert',
            [
                'notified'      => $newVal,
                'date_notified' => $newVal ? date('Y-m-d H:i:s') : null,
            ],
            '`id_alert` = ' . $idAlert
        );
    }

    protected function processBulkDelete(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) {
            $this->errors[] = $this->l('No alerts selected.');
            return;
        }

        $idList = implode(',', array_map('intval', $ids));
        Db::getInstance()->delete(
            'phyto_restock_alert',
            '`id_alert` IN (' . $idList . ')'
        );

        $this->confirmations[] = $this->l('Selected alerts deleted.');
    }

    protected function processBulkMarkNotified(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) {
            $this->errors[] = $this->l('No alerts selected.');
            return;
        }

        $idList = implode(',', array_map('intval', $ids));
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'phyto_restock_alert`
             SET `notified` = 1, `date_notified` = NOW()
             WHERE `id_alert` IN (' . $idList . ')'
        );

        $this->confirmations[] = $this->l('Selected alerts marked as notified.');
    }

    protected function processBulkSendNotif(): void
    {
        $ids = $this->getSelectedIds();
        if (empty($ids)) {
            $this->errors[] = $this->l('No alerts selected.');
            return;
        }

        $idList = implode(',', array_map('intval', $ids));
        $rows   = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_restock_alert`
             WHERE `id_alert` IN (' . $idList . ')'
        );

        if (!is_array($rows)) {
            return;
        }

        $sent = 0;
        foreach ($rows as $row) {
            if ($this->sendSingleNotification($row)) {
                $sent++;
            }
        }

        $this->confirmations[] = sprintf(
            $this->l('%d notification(s) sent.'),
            $sent
        );
    }

    // -------------------------------------------------------------------------
    // Notification helper
    // -------------------------------------------------------------------------

    protected function sendSingleNotification(array $alert): bool
    {
        $idProduct = (int) $alert['id_product'];
        $product   = new Product($idProduct, false, (int) Configuration::get('PS_LANG_DEFAULT'));

        if (!Validate::isLoadedObject($product)) {
            return false;
        }

        $productLink = $this->context->link->getProductLink(
            $product,
            null,
            null,
            null,
            (int) Configuration::get('PS_LANG_DEFAULT')
        );

        $fromName  = Configuration::get('PHYTO_RESTOCK_FROM_NAME')
            ?: Configuration::get('PS_SHOP_NAME');
        $fromEmail = Configuration::get('PS_SHOP_EMAIL');

        $templateVars = [
            '{firstname}'    => htmlspecialchars((string) $alert['firstname'], ENT_QUOTES, 'UTF-8'),
            '{product_name}' => htmlspecialchars((string) $product->name, ENT_QUOTES, 'UTF-8'),
            '{product_link}' => $productLink,
            '{shop_name}'    => htmlspecialchars(Configuration::get('PS_SHOP_NAME'), ENT_QUOTES, 'UTF-8'),
            '{shop_url}'     => Tools::getShopDomainSsl(true),
        ];

        $sent = Mail::Send(
            (int) Configuration::get('PS_LANG_DEFAULT'),
            'restock_alert',
            Mail::l('Good news! ' . $product->name . ' is back in stock'),
            $templateVars,
            (string) $alert['email'],
            (string) $alert['firstname'] ?: null,
            $fromEmail,
            $fromName,
            null,
            null,
            _PS_MODULE_DIR_ . 'phyto_restock_alert/views/templates/hook/email/',
            false,
            null,
            null
        );

        if ($sent) {
            Db::getInstance()->update(
                'phyto_restock_alert',
                ['notified' => 1, 'date_notified' => date('Y-m-d H:i:s')],
                '`id_alert` = ' . (int) $alert['id_alert']
            );
        }

        return (bool) $sent;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @return int[]
     */
    protected function getSelectedIds(): array
    {
        $raw = Tools::getValue($this->table . 'Box');
        if (!is_array($raw)) {
            return [];
        }

        return array_filter(array_map('intval', $raw));
    }
}
