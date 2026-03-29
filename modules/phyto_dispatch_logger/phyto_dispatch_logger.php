<?php
/**
 * Phyto Dispatch Logger
 *
 * Logs packing conditions per shipment for dispatch evidence.
 * Staff record temperature, humidity, packing method, gel/heat packs
 * and a photo at point of dispatch. Buyers see these conditions on
 * their order detail page. Records serve as evidence for LAG claims.
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   https://opensource.org/licenses/AFL-3.0 AFL-3.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/PhytoDispatchLog.php';

class Phyto_Dispatch_Logger extends Module
{
    /**
     * @var array List of hooks this module registers
     */
    protected $hooks = [
        'displayOrderDetail',
        'displayAdminOrderTabContent',
        'displayAdminOrderTabLink',
        'actionProductDelete',
    ];

    public function __construct()
    {
        $this->name            = 'phyto_dispatch_logger';
        $this->tab             = 'AdminParentOrders';
        $this->version         = '1.0.0';
        $this->author          = 'PhytoCommerce';
        $this->need_instance   = 0;
        $this->bootstrap       = true;

        $this->displayName = $this->l('Phyto Dispatch Logger');
        $this->description = $this->l('Records packing conditions for every shipment at the point of dispatch, including temperature, humidity, packing method, gel and heat packs used, and an optional photo of the packed box. Staff log entries from a dedicated tab inside the back-office order view, and buyers see the dispatch conditions on their order detail page. Records serve as primary evidence when handling Live Arrival Guarantee claims and support accountability across the packing team.');
        $this->confirmUninstall = $this->l(
            'Are you sure you want to uninstall? All dispatch log data will be deleted.'
        );

        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];

        parent::__construct();
    }

    // -------------------------------------------------------------------------
    // Install / Uninstall
    // -------------------------------------------------------------------------

    public function install(): bool
    {
        return parent::install()
            && $this->runSql('install')
            && $this->registerHooks()
            && $this->installTab()
            && $this->createUploadDir();
    }

    public function uninstall(): bool
    {
        return $this->uninstallTab()
            && $this->runSql('uninstall')
            && parent::uninstall();
    }

    // -------------------------------------------------------------------------
    // SQL helpers
    // -------------------------------------------------------------------------

    /**
     * Execute an SQL file bundled with the module.
     *
     * @param string $file 'install' or 'uninstall'
     *
     * @return bool
     */
    protected function runSql(string $file): bool
    {
        $sqlFile = dirname(__FILE__) . '/sql/' . $file . '.sql';

        if (!file_exists($sqlFile)) {
            return false;
        }

        $sql = file_get_contents($sqlFile);

        if (empty($sql)) {
            return true; // nothing to run
        }

        // Replace _PREFIX_ placeholder with real DB prefix
        $sql = str_replace('PREFIX_', _DB_PREFIX_, $sql);
        $sql = str_replace('ENGINE_TYPE', _MYSQL_ENGINE_, $sql);
        $sql = str_replace("\r\n", "\n", $sql);

        // Split on statement delimiter and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            static fn (string $s): bool => !empty($s)
        );

        foreach ($statements as $statement) {
            if (!Db::getInstance()->execute($statement)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Tab helpers
    // -------------------------------------------------------------------------

    /**
     * Create a visible back-office tab under the Orders menu.
     *
     * @return bool
     */
    protected function installTab(): bool
    {
        // Prefer the specific "Orders" tab; fall back to its parent group
        $idParent = (int) Tab::getIdFromClassName('AdminOrders');
        if ($idParent <= 0) {
            $idParent = (int) Tab::getIdFromClassName('AdminParentOrders');
        }

        $tab = new Tab();
        $tab->active       = 1;
        $tab->class_name   = 'AdminPhytoDispatchLog';
        $tab->module       = $this->name;
        $tab->id_parent    = $idParent;
        $tab->icon         = 'local_shipping';

        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[(int) $lang['id_lang']] = 'Dispatch Log';
        }

        return (bool) $tab->add();
    }

    /**
     * Remove the back-office tab created at install.
     *
     * @return bool
     */
    protected function uninstallTab(): bool
    {
        $idTab = (int) Tab::getIdFromClassName('AdminPhytoDispatchLog');
        if ($idTab <= 0) {
            return true; // already gone
        }

        $tab = new Tab($idTab);

        return (bool) $tab->delete();
    }

    // -------------------------------------------------------------------------
    // Upload directory
    // -------------------------------------------------------------------------

    /**
     * Create the directory used for dispatch photos.
     *
     * @return bool
     */
    protected function createUploadDir(): bool
    {
        $dir = PhytoDispatchLog::getPhotoDir();

        if (is_dir($dir)) {
            return true;
        }

        if (!mkdir($dir, 0755, true)) {
            return false;
        }

        // Add an .htaccess that allows only image delivery
        $htaccess = $dir . '.htaccess';
        file_put_contents(
            $htaccess,
            "Options -Indexes\n"
            . "<FilesMatch \"\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$\">\n"
            . "    Order Allow,Deny\n"
            . "    Deny from all\n"
            . "</FilesMatch>\n"
        );

        return true;
    }

    // -------------------------------------------------------------------------
    // Hook registration
    // -------------------------------------------------------------------------

    /**
     * Register all module hooks.
     *
     * @return bool
     */
    protected function registerHooks(): bool
    {
        foreach ($this->hooks as $hook) {
            if (!$this->registerHook($hook)) {
                return false;
            }
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Hook: displayOrderDetail (front-office order confirmation / account)
    // -------------------------------------------------------------------------

    /**
     * Display dispatch conditions to the buyer on their order detail page.
     *
     * @param array $params Hook parameters; expects $params['order'] (Order object)
     *
     * @return string Rendered template HTML or empty string
     */
    public function hookDisplayOrderDetail(array $params): string
    {
        if (empty($params['order'])) {
            return '';
        }

        $idOrder = (int) $params['order']->id;
        $log     = PhytoDispatchLog::getByOrder($idOrder);

        if (empty($log)) {
            return '';
        }

        $photoUrl = '';
        if (!empty($log['photo_filename'])) {
            $photoUrl = $this->context->link->getBaseLink()
                . 'img/phyto_dispatch/'
                . rawurlencode($log['photo_filename']);
        }

        // Format values for display
        $dispatchDate = !empty($log['dispatch_date'])
            ? Tools::displayDate($log['dispatch_date'])
            : '';

        $this->context->smarty->assign([
            'pdl_log'          => $log,
            'pdl_dispatch_date' => $dispatchDate,
            'pdl_photo_url'    => $photoUrl,
            'pdl_module_dir'   => $this->_path,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order_detail.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: displayAdminOrderTabLink (back-office order detail)
    // -------------------------------------------------------------------------

    /**
     * Add a "Dispatch Log" tab link in the back-office order detail view.
     *
     * @param array $params Hook parameters; expects $params['id_order']
     *
     * @return string Rendered HTML
     */
    public function hookDisplayAdminOrderTabLink(array $params): string
    {
        if (empty($params['id_order'])) {
            return '';
        }

        $idOrder = (int) $params['id_order'];
        $log     = PhytoDispatchLog::getByOrder($idOrder);

        $this->context->smarty->assign([
            'pdl_has_log'    => !empty($log),
            'pdl_id_order'   => $idOrder,
            'pdl_admin_link' => $this->getAdminLogLink($idOrder, $log),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/tab_link.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: displayAdminOrderTabContent (back-office order detail)
    // -------------------------------------------------------------------------

    /**
     * Render the dispatch log summary or creation prompt inside the tab panel
     * for the back-office order detail view.
     *
     * @param array $params Hook parameters; expects $params['id_order']
     *
     * @return string Rendered HTML
     */
    public function hookDisplayAdminOrderTabContent(array $params): string
    {
        if (empty($params['id_order'])) {
            return '';
        }

        $idOrder = (int) $params['id_order'];
        $log     = PhytoDispatchLog::getByOrder($idOrder);

        $photoUrl = '';
        if (!empty($log['photo_filename'])) {
            $photoUrl = $this->context->link->getBaseLink()
                . 'img/phyto_dispatch/'
                . rawurlencode($log['photo_filename']);
        }

        $this->context->smarty->assign([
            'pdl_log'        => $log,
            'pdl_photo_url'  => $photoUrl,
            'pdl_id_order'   => $idOrder,
            'pdl_admin_link' => $this->getAdminLogLink($idOrder, $log),
        ]);

        return $this->display(__FILE__, 'views/templates/admin/tab_content.tpl');
    }

    // -------------------------------------------------------------------------
    // Hook: actionProductDelete
    // -------------------------------------------------------------------------

    /**
     * Placeholder hook — kept for future use (e.g. archiving logs when a
     * product associated with an order is deleted).
     *
     * @param array $params Hook parameters
     *
     * @return void
     */
    public function hookActionProductDelete(array $params): void
    {
        // Reserved for future cleanup logic.
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build an admin URL to the dispatch log for the given order.
     * Links to the edit form if a log exists, otherwise to the add form
     * with the order ID pre-filled.
     *
     * @param int        $idOrder
     * @param array|bool $log     Existing log row or falsy if none
     *
     * @return string
     */
    protected function getAdminLogLink(int $idOrder, $log): string
    {
        $controller = 'AdminPhytoDispatchLog';

        if (!empty($log)) {
            return $this->context->link->getAdminLink($controller)
                . '&id_log=' . (int) $log['id_log']
                . '&updatephyto_dispatch_logger';
        }

        return $this->context->link->getAdminLink($controller)
            . '&id_order=' . $idOrder
            . '&addphyto_dispatch_logger';
    }
}
