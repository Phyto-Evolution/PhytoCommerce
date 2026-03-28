<?php
/**
 * Phyto_KycKycModuleFrontController
 *
 * My Account KYC page.
 * GET  /module/phyto_kyc/kyc  → show KYC status + submission form
 * POST /module/phyto_kyc/kyc  → process L1 or L2 submission
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_kyc/classes/PhytoKycProfile.php';
require_once _PS_MODULE_DIR_ . 'phyto_kyc/classes/PhytoKycDocument.php';
require_once _PS_MODULE_DIR_ . 'phyto_kyc/classes/PhytoKycSandboxClient.php';

class Phyto_KycKycModuleFrontController extends ModuleFrontController
{
    public $auth             = true;
    public $authRedirection  = 'my-account';
    public $ssl              = true;

    public function initContent(): void
    {
        parent::initContent();

        if (Tools::isSubmit('submitKycL1')) {
            $this->processL1();
            return;
        }

        if (Tools::isSubmit('submitKycL2')) {
            $this->processL2();
            return;
        }

        $this->displayPage();
    }

    // =========================================================================
    // Display
    // =========================================================================

    private function displayPage(array $errors = [], string $success = ''): void
    {
        $customer   = $this->context->customer;
        $profile    = PhytoKycProfile::getByCustomer((int) $customer->id);
        $requireL1  = (int) Configuration::get('PHYTO_KYC_REQUIRE_L1');
        $requireL2  = (int) Configuration::get('PHYTO_KYC_REQUIRE_L2');

        $l1Status   = $profile ? $profile->level1_status : 'NotStarted';
        $l2Status   = $profile ? $profile->level2_status : 'NotStarted';
        $l1Verified = $l1Status === 'Verified';
        $l2Verified = $l2Status === 'Verified';

        $fullyVerified = Phyto_Kyc::isCustomerVerified((int) $customer->id);

        $this->context->smarty->assign([
            'phyto_kyc_form_url'      => $this->context->link->getModuleLink('phyto_kyc', 'kyc'),
            'phyto_kyc_token'         => Tools::getToken(false),
            'phyto_kyc_errors'        => $errors,
            'phyto_kyc_success'       => $success,
            'phyto_kyc_l1_status'     => $l1Status,
            'phyto_kyc_l2_status'     => $l2Status,
            'phyto_kyc_l1_verified'   => $l1Verified,
            'phyto_kyc_l2_verified'   => $l2Verified,
            'phyto_kyc_fully_verified' => $fullyVerified,
            'phyto_kyc_require_l1'    => $requireL1,
            'phyto_kyc_require_l2'    => $requireL2,
            'phyto_kyc_pan_value'     => '',
        ]);

        $this->setTemplate('module:phyto_kyc/views/templates/front/kyc_account.tpl');
    }

    // =========================================================================
    // Level 1 — PAN verification
    // =========================================================================

    private function processL1(): void
    {
        if (!Tools::checkToken()) {
            $this->displayPage([$this->module->l('Invalid security token. Please refresh and try again.')]);
            return;
        }

        $customer = $this->context->customer;
        $pan      = strtoupper(trim(Tools::getValue('pan_number', '')));

        // Client-side format check
        if (!PhytoKycProfile::isValidPan($pan)) {
            $this->displayPage([$this->module->l('Invalid PAN format. Example: ABCDE1234F')], '');
            return;
        }

        $profile = PhytoKycProfile::getOrCreate((int) $customer->id);

        // Don't re-verify if already verified
        if ($profile->level1_status === 'Verified') {
            $this->displayPage([], $this->module->l('PAN already verified.'));
            return;
        }

        $apiKey = Configuration::get('PHYTO_KYC_SANDBOX_API_KEY');

        if ($apiKey) {
            // Live verification via Sandbox.co.in
            $client = new PhytoKycSandboxClient($apiKey);
            $result = $client->verifyPan($pan);

            if ($result['error']) {
                // API error — fall back to Pending for manual review
                $profile->pan_number     = $pan;
                $profile->level1_status  = 'Pending';
                $profile->api_response_l1 = json_encode(['error' => $result['error']]);
                $profile->update();
                $this->sendAdminNotification($customer, 'L1 (API error — manual review needed): ' . $pan);
                $this->displayPage([], $this->module->l('We could not verify your PAN automatically. Your submission has been queued for manual review.'));
                return;
            }

            if (!$result['valid']) {
                $this->displayPage([$this->module->l('PAN not found or invalid. Please check the number and try again.')], '');
                return;
            }

            // Verified instantly
            $profile->pan_number      = $pan;
            $profile->pan_name        = $result['name'];
            $profile->level1_status   = 'Verified';
            $profile->kyc_level       = max((int) $profile->kyc_level, 1);
            $profile->api_response_l1 = json_encode($result['raw']);
            $profile->update();

            $this->sendCustomerApprovalEmail($customer, 'Level 1 (PAN)');
            $this->displayPage([], $this->module->l('PAN verified successfully! You can now see all prices.'));

        } else {
            // No API key configured — queue for manual review
            $profile->pan_number     = $pan;
            $profile->level1_status  = 'Pending';
            $profile->update();
            $this->sendAdminNotification($customer, 'L1 PAN: ' . $pan);
            $this->displayPage([], $this->module->l('Your PAN has been submitted and is under review. You will be notified within 24 hours.'));
        }
    }

    // =========================================================================
    // Level 2 — GST / Business verification
    // =========================================================================

    private function processL2(): void
    {
        if (!Tools::checkToken()) {
            $this->displayPage([$this->module->l('Invalid security token. Please refresh and try again.')]);
            return;
        }

        $customer    = $this->context->customer;
        $profile     = PhytoKycProfile::getOrCreate((int) $customer->id);
        $errors      = [];

        // Must have passed L1 first
        if ($profile->level1_status !== 'Verified') {
            $this->displayPage([$this->module->l('Please complete Level 1 (PAN) verification first.')]);
            return;
        }

        $gst         = strtoupper(trim(Tools::getValue('gst_number', '')));
        $businessPan = strtoupper(trim(Tools::getValue('business_pan', '')));

        if (empty($gst) && empty($businessPan)) {
            $errors[] = $this->module->l('Please provide a GST number or business PAN.');
        }
        if (!empty($gst) && !PhytoKycProfile::isValidGst($gst)) {
            $errors[] = $this->module->l('Invalid GST number format.');
        }
        if (!empty($businessPan) && !PhytoKycProfile::isValidPan($businessPan)) {
            $errors[] = $this->module->l('Invalid business PAN format.');
        }

        if ($errors) {
            $this->displayPage($errors);
            return;
        }

        // Handle optional file upload
        $docPath = '';
        if (isset($_FILES['kyc_doc']) && $_FILES['kyc_doc']['error'] === UPLOAD_ERR_OK) {
            $docPath = $this->saveUploadedDoc($_FILES['kyc_doc'], (int) $customer->id, 2, $errors);
            if ($errors) {
                $this->displayPage($errors);
                return;
            }
        }

        $apiKey = Configuration::get('PHYTO_KYC_SANDBOX_API_KEY');
        $autoVerified = false;

        if ($apiKey && !empty($gst)) {
            $client = new PhytoKycSandboxClient($apiKey);
            $result = $client->verifyGst($gst);

            if (!$result['error'] && $result['valid']) {
                $profile->gst_number      = $gst;
                $profile->business_name   = $result['business_name'];
                $profile->level2_status   = 'Verified';
                $profile->kyc_level       = max((int) $profile->kyc_level, 2);
                $profile->api_response_l2 = json_encode($result['raw']);
                $autoVerified             = true;
            }
        }

        if (!$autoVerified) {
            $profile->gst_number    = $gst;
            $profile->business_pan  = $businessPan;
            $profile->level2_status = 'Pending';
        }

        $profile->update();

        // Save document record if file was uploaded
        if ($docPath) {
            $doc               = new PhytoKycDocument();
            $doc->id_kyc_profile = (int) $profile->id;
            $doc->id_customer    = (int) $customer->id;
            $doc->kyc_level      = 2;
            $doc->doc_type       = $gst ? 'gst_certificate' : 'business_pan';
            $doc->file_path      = $docPath;
            $doc->file_name      = $_FILES['kyc_doc']['name'];
            $doc->mime_type      = $_FILES['kyc_doc']['type'];
            $doc->add();
        }

        if ($autoVerified) {
            $this->sendCustomerApprovalEmail($customer, 'Level 2 (Business/GST)');
            $this->displayPage([], $this->module->l('Business verification complete! Wholesale pricing is now unlocked.'));
        } else {
            $this->sendAdminNotification($customer, 'L2 GST/Business submission');
            $this->displayPage([], $this->module->l('Your business details have been submitted and are under review.'));
        }
    }

    // =========================================================================
    // File upload helper
    // =========================================================================

    private function saveUploadedDoc(array $file, int $idCustomer, int $level, array &$errors): string
    {
        $allowedMime = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxSize     = 5 * 1024 * 1024; // 5 MB

        if ($file['size'] > $maxSize) {
            $errors[] = $this->module->l('File too large. Maximum size is 5 MB.');
            return '';
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMime, true)) {
            $errors[] = $this->module->l('Only PDF, JPG, and PNG files are accepted.');
            return '';
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dir      = _PS_UPLOAD_DIR_ . 'phyto_kyc/' . $idCustomer . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'kyc_l' . $level . '_' . time() . '_' . Tools::passwdGen(8) . '.' . $ext;
        $dest     = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            $errors[] = $this->module->l('File upload failed. Please try again.');
            return '';
        }

        return 'upload/phyto_kyc/' . $idCustomer . '/' . $filename;
    }

    // =========================================================================
    // Email helpers
    // =========================================================================

    private function sendAdminNotification(Customer $customer, string $details): void
    {
        $shopEmail = Configuration::get('PS_SHOP_EMAIL');
        $shopName  = Configuration::get('PS_SHOP_NAME');
        if (!$shopEmail) {
            return;
        }

        $adminUrl = Context::getContext()->link->getAdminLink('AdminPhytoKyc');
        $subject  = '[' . $shopName . '] KYC Submission — ' . $customer->email;
        $body     = "New KYC submission:\n\n"
            . 'Customer: ' . $customer->firstname . ' ' . $customer->lastname . "\n"
            . 'Email:    ' . $customer->email . "\n"
            . 'Details:  ' . $details . "\n\n"
            . 'Review at: ' . $adminUrl;

        Mail::Send(
            (int) $this->context->language->id,
            'contact_form',
            $subject,
            ['{message}' => nl2br(htmlspecialchars($body, ENT_QUOTES))],
            $shopEmail,
            $shopName
        );
    }

    private function sendCustomerApprovalEmail(Customer $customer, string $level): void
    {
        $shopName = Configuration::get('PS_SHOP_NAME');
        $subject  = '[' . $shopName . '] KYC Verification Approved';
        $body     = 'Dear ' . $customer->firstname . ",\n\n"
            . 'Your ' . $level . ' KYC verification has been approved. '
            . "You can now log in and see all prices.\n\n"
            . 'Thank you,\n' . $shopName;

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
