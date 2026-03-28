<?php
/**
 * AdminPhytoKycController
 *
 * Lists all KYC profiles with Approve / Reject actions.
 * Accessible via Customers → KYC Verification in the admin menu.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_kyc/classes/PhytoKycProfile.php';
require_once _PS_MODULE_DIR_ . 'phyto_kyc/classes/PhytoKycDocument.php';

class AdminPhytoKycController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap    = true;
        $this->table        = 'phyto_kyc_profile';
        $this->className    = 'PhytoKycProfile';
        $this->identifier   = 'id_kyc_profile';
        $this->lang         = false;
        $this->allow_export = true;

        parent::__construct();

        $this->module = Module::getInstanceByName('phyto_kyc');

        $this->fields_list = [
            'id_kyc_profile' => ['title' => 'ID',         'align' => 'center', 'class' => 'fixed-width-xs'],
            'id_customer'    => ['title' => 'Customer ID', 'align' => 'center'],
            'customer_name'  => ['title' => 'Customer',   'filter_key' => 'a!id_customer'],
            'customer_email' => ['title' => 'Email'],
            'pan_number'     => ['title' => 'PAN'],
            'gst_number'     => ['title' => 'GST'],
            'level1_status'  => ['title' => 'L1 Status',  'badge' => 'level1_status'],
            'level2_status'  => ['title' => 'L2 Status',  'badge' => 'level2_status'],
            'date_upd'       => ['title' => 'Last Updated', 'type' => 'datetime'],
        ];

        $this->bulk_actions = [
            'approve_l1' => ['text' => 'Approve L1 (PAN)',    'icon' => 'icon-check'],
            'reject_l1'  => ['text' => 'Reject L1 (PAN)',     'icon' => 'icon-times'],
            'approve_l2' => ['text' => 'Approve L2 (Business)', 'icon' => 'icon-check'],
            'reject_l2'  => ['text' => 'Reject L2 (Business)',  'icon' => 'icon-times'],
        ];
    }

    // =========================================================================
    // List query — join customer table for name + email
    // =========================================================================

    public function getList(
        $idLang,
        $orderBy = null,
        $orderWay = null,
        $start = 0,
        $limit = null,
        $idLangShop = false
    ) {
        $this->_join  = 'LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer` = a.`id_customer`)';
        $this->_select = 'CONCAT(c.`firstname`, " ", c.`lastname`) AS customer_name, c.`email` AS customer_email';

        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);
    }

    // =========================================================================
    // Bulk actions
    // =========================================================================

    protected function processBulkApproveL1(): void
    {
        $this->processBulkStatusUpdate('level1_status', 'Verified', 1);
    }

    protected function processBulkRejectL1(): void
    {
        $this->processBulkStatusUpdate('level1_status', 'Rejected', 0);
    }

    protected function processBulkApproveL2(): void
    {
        $this->processBulkStatusUpdate('level2_status', 'Verified', 2);
    }

    protected function processBulkRejectL2(): void
    {
        $this->processBulkStatusUpdate('level2_status', 'Rejected', 0);
    }

    private function processBulkStatusUpdate(string $field, string $status, int $levelOnApprove): void
    {
        if (empty($this->boxes)) {
            return;
        }

        foreach ($this->boxes as $id) {
            $profile = new PhytoKycProfile((int) $id);
            if (!Validate::isLoadedObject($profile)) {
                continue;
            }

            $profile->$field     = $status;
            $profile->reviewed_by = (int) $this->context->employee->id;

            // Update kyc_level
            if ($status === 'Verified' && $levelOnApprove > 0) {
                $profile->kyc_level = max((int) $profile->kyc_level, $levelOnApprove);
            }

            $profile->update();

            // Notify customer
            if ($status === 'Verified') {
                $customer = new Customer((int) $profile->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    $this->notifyCustomer($customer, $field === 'level1_status' ? 'L1 (PAN)' : 'L2 (Business)', 'approved');
                }
            } elseif ($status === 'Rejected') {
                $customer = new Customer((int) $profile->id_customer);
                if (Validate::isLoadedObject($customer)) {
                    $adminNotes = trim(Tools::getValue('admin_notes', ''));
                    $this->notifyCustomer($customer, $field === 'level1_status' ? 'L1 (PAN)' : 'L2 (Business)', 'rejected', $adminNotes);
                }
            }
        }

        $this->confirmations[] = $this->l('Status updated for selected profiles.');
    }

    // =========================================================================
    // Single-record view with documents
    // =========================================================================

    public function renderView(): string
    {
        $profile = new PhytoKycProfile((int) Tools::getValue('id_kyc_profile'));
        if (!Validate::isLoadedObject($profile)) {
            return '<div class="alert alert-danger">Profile not found.</div>';
        }

        $customer  = new Customer((int) $profile->id_customer);
        $documents = PhytoKycDocument::getByProfile((int) $profile->id);

        $apiResponse1 = $profile->api_response_l1 ? json_decode($profile->api_response_l1, true) : null;
        $apiResponse2 = $profile->api_response_l2 ? json_decode($profile->api_response_l2, true) : null;

        $kycUrl = $this->context->link->getModuleLink('phyto_kyc', 'kyc');

        $html  = '<div class="panel">';
        $html .= '<div class="panel-heading"><h3>KYC Profile #' . (int) $profile->id . '</h3></div>';
        $html .= '<div class="panel-body">';

        // Customer info
        $html .= '<h4>Customer</h4>';
        $html .= '<p><strong>Name:</strong> ' . htmlspecialchars($customer->firstname . ' ' . $customer->lastname) . '<br>';
        $html .= '<strong>Email:</strong> ' . htmlspecialchars($customer->email) . '</p>';

        // L1 Status
        $html .= '<h4>Level 1 — PAN</h4>';
        $html .= '<p><strong>Status:</strong> ' . htmlspecialchars($profile->level1_status) . '<br>';
        $html .= '<strong>PAN:</strong> ' . htmlspecialchars($profile->pan_number ?: '—') . '<br>';
        $html .= '<strong>Name on PAN:</strong> ' . htmlspecialchars($profile->pan_name ?: '—') . '</p>';

        if ($apiResponse1) {
            $html .= '<details><summary>API Response (L1)</summary><pre>' . htmlspecialchars(json_encode($apiResponse1, JSON_PRETTY_PRINT)) . '</pre></details>';
        }

        // L2 Status
        $html .= '<h4>Level 2 — Business/GST</h4>';
        $html .= '<p><strong>Status:</strong> ' . htmlspecialchars($profile->level2_status) . '<br>';
        $html .= '<strong>GST:</strong> ' . htmlspecialchars($profile->gst_number ?: '—') . '<br>';
        $html .= '<strong>Business PAN:</strong> ' . htmlspecialchars($profile->business_pan ?: '—') . '<br>';
        $html .= '<strong>Business Name:</strong> ' . htmlspecialchars($profile->business_name ?: '—') . '</p>';

        if ($apiResponse2) {
            $html .= '<details><summary>API Response (L2)</summary><pre>' . htmlspecialchars(json_encode($apiResponse2, JSON_PRETTY_PRINT)) . '</pre></details>';
        }

        // Documents
        if ($documents) {
            $html .= '<h4>Uploaded Documents</h4><ul>';
            foreach ($documents as $doc) {
                $url   = _PS_BASE_URL_ . '/' . ltrim($doc['file_path'], '/');
                $html .= '<li><a href="' . htmlspecialchars($url) . '" target="_blank">'
                    . htmlspecialchars($doc['file_name']) . '</a>'
                    . ' (' . htmlspecialchars($doc['doc_type']) . ', L' . (int) $doc['kyc_level'] . ')</li>';
            }
            $html .= '</ul>';
        }

        // Admin notes
        $html .= '<h4>Admin Notes</h4>';
        $html .= '<p>' . nl2br(htmlspecialchars($profile->admin_notes ?: '—')) . '</p>';

        // Action buttons
        $baseUrl = $this->context->link->getAdminLink('AdminPhytoKyc')
            . '&id_kyc_profile=' . (int) $profile->id . '&token=' . Tools::getAdminTokenLite('AdminPhytoKyc');

        $html .= '<div class="btn-group mt-3">';
        $html .= '<a href="' . $baseUrl . '&actionApproveL1=1" class="btn btn-success btn-sm">Approve L1</a> ';
        $html .= '<a href="' . $baseUrl . '&actionRejectL1=1"  class="btn btn-danger btn-sm">Reject L1</a> ';
        $html .= '<a href="' . $baseUrl . '&actionApproveL2=1" class="btn btn-success btn-sm">Approve L2</a> ';
        $html .= '<a href="' . $baseUrl . '&actionRejectL2=1"  class="btn btn-danger btn-sm">Reject L2</a>';
        $html .= '</div>';

        $html .= '</div></div>';
        return $html;
    }

    // =========================================================================
    // Single-row quick actions (from list view URL params)
    // =========================================================================

    public function postProcess()
    {
        $idProfile = (int) Tools::getValue('id_kyc_profile');

        if ($idProfile) {
            if (Tools::getValue('actionApproveL1')) {
                $this->boxes = [$idProfile];
                $this->processBulkApproveL1();
            } elseif (Tools::getValue('actionRejectL1')) {
                $this->boxes = [$idProfile];
                $this->processBulkRejectL1();
            } elseif (Tools::getValue('actionApproveL2')) {
                $this->boxes = [$idProfile];
                $this->processBulkApproveL2();
            } elseif (Tools::getValue('actionRejectL2')) {
                $this->boxes = [$idProfile];
                $this->processBulkRejectL2();
            }
        }

        return parent::postProcess();
    }

    // =========================================================================
    // Email helper
    // =========================================================================

    private function notifyCustomer(Customer $customer, string $level, string $action, string $notes = ''): void
    {
        $shopName = Configuration::get('PS_SHOP_NAME');

        if ($action === 'approved') {
            $subject = '[' . $shopName . '] KYC Verification Approved — ' . $level;
            $body    = 'Dear ' . $customer->firstname . ",\n\n"
                . 'Your ' . $level . ' KYC has been approved. You can now log in to see all prices.'
                . "\n\nThank you,\n" . $shopName;
        } else {
            $subject = '[' . $shopName . '] KYC Verification — Action Required';
            $body    = 'Dear ' . $customer->firstname . ",\n\n"
                . 'Your ' . $level . ' KYC submission has been reviewed and could not be approved.'
                . ($notes ? "\n\nNote from our team: " . $notes : '')
                . "\n\nPlease resubmit with correct documents.\n\nThank you,\n" . $shopName;
        }

        Mail::Send(
            (int) $this->context->language->id,
            'contact_form',
            $subject,
            ['{message}' => nl2br(htmlspecialchars($body, ENT_QUOTES))],
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname
        );
    }
}
