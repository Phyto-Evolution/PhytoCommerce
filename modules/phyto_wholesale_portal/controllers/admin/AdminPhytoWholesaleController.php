<?php
/**
 * AdminPhytoWholesaleController — Back-office controller for wholesale applications.
 *
 * Provides a HelperList of all applications and a HelperForm for editing.
 * On status change to 'Approved' the customer is automatically added to the
 * wholesale group.
 *
 * @author    PhytoCommerce
 * @copyright 2026 PhytoCommerce
 * @license   proprietary
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_wholesale_portal/classes/PhytoWholesaleApplication.php';

class AdminPhytoWholesaleController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap   = true;
        $this->table       = 'phyto_wholesale_application';
        $this->className   = 'PhytoWholesaleApplication';
        $this->identifier  = 'id_app';
        $this->lang        = false;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        parent::__construct();

        $this->meta_title = $this->l('Wholesale Applications');

        // ---------------------------------------------------------------
        // List columns
        // ---------------------------------------------------------------
        $this->fields_list = [
            'id_app' => [
                'title'  => $this->l('ID'),
                'class'  => 'fixed-width-xs',
                'align'  => 'center',
            ],
            'business_name' => [
                'title'  => $this->l('Business Name'),
                'filter_key' => 'a!business_name',
            ],
            'customer_name' => [
                'title'      => $this->l('Customer'),
                'filter_key' => 'a!id_customer',
                'havingFilter' => true,
            ],
            'phone' => [
                'title'  => $this->l('Phone'),
            ],
            'status' => [
                'title'   => $this->l('Status'),
                'type'    => 'select',
                'list'    => [
                    'Pending'  => $this->l('Pending'),
                    'Approved' => $this->l('Approved'),
                    'Rejected' => $this->l('Rejected'),
                ],
                'filter_key' => 'a!status',
                'badge_success' => 'Approved',
                'badge_warning' => 'Pending',
                'badge_danger'  => 'Rejected',
                'callback'   => 'renderStatusBadge',
            ],
            'date_add' => [
                'title'  => $this->l('Date Applied'),
                'type'   => 'datetime',
                'filter_key' => 'a!date_add',
            ],
        ];

        // Quick filter buttons
        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected applications?'),
                'icon'    => 'icon-trash',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Custom SELECT to join customer name
    // -------------------------------------------------------------------------

    public function getList(
        $id_lang,
        $order_by = null,
        $order_way = null,
        $start = 0,
        $limit = null,
        $id_lang_shop = false
    ): void {
        // Augment the list query with a customer name column
        $this->_select  = 'CONCAT(c.`firstname`, \' \', c.`lastname`) AS customer_name, c.`email`';
        $this->_join    = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON c.`id_customer` = a.`id_customer`';
        $this->_use_found_rows = true;

        parent::getList($id_lang, $order_by, $order_way, $start, $limit, $id_lang_shop);
    }

    // -------------------------------------------------------------------------
    // Status badge renderer (called from list column callback)
    // -------------------------------------------------------------------------

    public function renderStatusBadge(string $value): string
    {
        $map = [
            'Pending'  => 'warning',
            'Approved' => 'success',
            'Rejected' => 'danger',
        ];
        $type  = $map[$value] ?? 'default';
        $label = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return '<span class="badge badge-' . $type . '">' . $label . '</span>';
    }

    // -------------------------------------------------------------------------
    // Quick approve / reject via row action
    // -------------------------------------------------------------------------

    public function initPageHeaderToolbar(): void
    {
        if (Tools::getValue('id_app') || $this->display === 'edit' || $this->display === 'add') {
            $idApp = (int) Tools::getValue('id_app');
            if ($idApp) {
                $this->page_header_toolbar_btn['approve'] = [
                    'href'  => self::$currentIndex
                        . '&id_app=' . $idApp
                        . '&quick_status=Approved'
                        . '&token=' . $this->token,
                    'desc'  => $this->l('Approve'),
                    'icon'  => 'process-icon-ok',
                    'class' => 'btn-success',
                ];
                $this->page_header_toolbar_btn['reject'] = [
                    'href'  => self::$currentIndex
                        . '&id_app=' . $idApp
                        . '&quick_status=Rejected'
                        . '&token=' . $this->token,
                    'desc'  => $this->l('Reject'),
                    'icon'  => 'process-icon-cancel',
                    'class' => 'btn-danger',
                ];
            }
        }

        parent::initPageHeaderToolbar();
    }

    // -------------------------------------------------------------------------
    // postProcess — handle status changes
    // -------------------------------------------------------------------------

    public function postProcess(): void
    {
        // Quick status change from toolbar or row action
        $quickStatus = Tools::getValue('quick_status');
        $idApp       = (int) Tools::getValue('id_app');

        if ($quickStatus && $idApp && in_array($quickStatus, ['Approved', 'Rejected', 'Pending'], true)) {
            $app = new PhytoWholesaleApplication($idApp);
            if (Validate::isLoadedObject($app)) {
                $app->status   = $quickStatus;
                $app->date_upd = date('Y-m-d H:i:s');
                $app->update();

                if ($quickStatus === 'Approved') {
                    $this->approveCustomer((int) $app->id_customer);
                }

                $this->redirect_after = self::$currentIndex . '&token=' . $this->token;
                $this->confirmations[] = $this->l('Application status updated.');
            }
        }

        parent::postProcess();

        // After parent save — check if newly saved status is Approved
        if (Tools::isSubmit('submitAdd' . $this->table) || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
            $idApp = (int) Tools::getValue('id_app');
            if ($idApp) {
                $app = new PhytoWholesaleApplication($idApp);
                if (Validate::isLoadedObject($app) && $app->status === 'Approved') {
                    $this->approveCustomer((int) $app->id_customer);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Approve customer helper
    // -------------------------------------------------------------------------

    private function approveCustomer(int $idCustomer): void
    {
        if (!$idCustomer) {
            return;
        }

        $wsGroupId = (int) Configuration::get('PHYTO_WHOLESALE_GROUP_ID');
        if (!$wsGroupId) {
            return;
        }

        $customer = new Customer($idCustomer);
        if (!Validate::isLoadedObject($customer)) {
            return;
        }

        $currentGroups = Customer::getGroupsStatic($idCustomer);

        if (!in_array($wsGroupId, $currentGroups, true)) {
            $currentGroups[] = $wsGroupId;
            $customer->updateGroup($currentGroups);
        }
    }

    // -------------------------------------------------------------------------
    // Edit form
    // -------------------------------------------------------------------------

    public function renderForm(): string
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Wholesale Application'),
                'icon'  => 'icon-user-md',
            ],
            'input'  => [
                [
                    'type'     => 'hidden',
                    'name'     => 'id_app',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Customer ID'),
                    'name'     => 'id_customer',
                    'class'    => 'fixed-width-md',
                    'required' => false,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Business Name'),
                    'name'     => 'business_name',
                    'required' => true,
                    'size'     => 200,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('GST Number'),
                    'name'  => 'gst_number',
                    'size'  => 30,
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Address'),
                    'name'  => 'address',
                    'rows'  => 3,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Phone'),
                    'name'  => 'phone',
                    'size'  => 30,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->l('Website'),
                    'name'  => 'website',
                    'size'  => 200,
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Message'),
                    'name'  => 'message',
                    'rows'  => 4,
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('Status'),
                    'name'    => 'status',
                    'options' => [
                        'query' => [
                            ['id' => 'Pending',  'name' => $this->l('Pending')],
                            ['id' => 'Approved', 'name' => $this->l('Approved')],
                            ['id' => 'Rejected', 'name' => $this->l('Rejected')],
                        ],
                        'id'   => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->l('Admin Notes'),
                    'name'  => 'admin_notes',
                    'rows'  => 3,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        return parent::renderForm();
    }
}
