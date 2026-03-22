<?php
/**
 * Phyto Live Arrival Guarantee — Claim Front Controller
 *
 * Allows a logged-in customer to file a Live Arrival Guarantee claim for
 * a specific order. Handles both:
 *   GET  — render the claim form via Smarty
 *   POST — process form submission, save photos, insert claim record
 *
 * Requirements:
 *  - Customer must be logged in
 *  - Customer must own the order
 *  - Order must have LAG opted in (phyto_lag_order.lag_opted = 1)
 *  - No existing claim for the same order
 *  - Photos: JPEG or PNG, max 2 MB each, up to 3
 *  - Files saved to _PS_IMG_DIR_ . 'phyto_lag/' with UUID filenames
 *  - Inserts into phyto_lag_claim with claim_status='pending'
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Live_ArrivalClaimModuleFrontController extends ModuleFrontController
{
    /** @var bool Require a logged-in customer */
    public $auth = true;

    /** @var bool Redirect guests to login page automatically */
    public $guestAllowed = false;

    /** @var int Maximum photo file size in bytes (2 MB) */
    const MAX_PHOTO_SIZE = 2097152;

    /** @var array Accepted MIME types for photo uploads */
    private $allowedMimes = ['image/jpeg', 'image/png'];

    /** @var array Accepted file extensions (lower-case) */
    private $allowedExtensions = ['jpg', 'jpeg', 'png'];

    /**
     * Handle POST submission (process claim) before content is rendered.
     */
    public function postProcess()
    {
        if (!Tools::isSubmit('submit_claim')) {
            return; // GET request — let initContent() handle it
        }

        $idOrder = (int) Tools::getValue('id_order');
        $errors  = [];

        // ── Validate order ownership ──────────────────────────────────────────
        if (!$idOrder) {
            $this->errors[] = $this->module->l('Invalid order.');
            return;
        }

        $order = new Order($idOrder);
        if (!Validate::isLoadedObject($order)) {
            $this->errors[] = $this->module->l('Order not found.');
            return;
        }

        if ((int) $order->id_customer !== (int) $this->context->customer->id) {
            $this->errors[] = $this->module->l('You are not authorised to file a claim for this order.');
            return;
        }

        // ── Check LAG opt-in record ───────────────────────────────────────────
        $lagRecord = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_lag_order`
             WHERE `id_order` = ' . $idOrder . '
             AND `lag_opted` = 1'
        );

        if (!$lagRecord) {
            $this->errors[] = $this->module->l('Live Arrival Guarantee was not opted in for this order.');
            return;
        }

        // ── Check no existing claim ───────────────────────────────────────────
        $existing = Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_lag_claim`
             WHERE `id_order` = ' . $idOrder
        );

        if ((int) $existing > 0) {
            $this->errors[] = $this->module->l('A claim has already been filed for this order.');
            return;
        }

        // ── Check claim window ────────────────────────────────────────────────
        $claimWindow  = (int) Configuration::get('PHYTO_LAG_CLAIM_WINDOW');
        $orderDate    = new DateTime($order->date_add);
        $now          = new DateTime();
        $daysSince    = (int) $now->diff($orderDate)->days;

        if ($daysSince > $claimWindow) {
            $this->errors[] = $this->module->l('The claim window for this order has expired.');
            return;
        }

        // ── Validate description ──────────────────────────────────────────────
        $description = trim(Tools::getValue('description', ''));
        if (empty($description)) {
            $errors[] = $this->module->l('Please provide a description of the issue.');
        } elseif (Tools::strlen($description) > 4000) {
            $errors[] = $this->module->l('Description must not exceed 4000 characters.');
        }

        // ── Handle photo uploads ──────────────────────────────────────────────
        $uploadDir = _PS_IMG_DIR_ . 'phyto_lag/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $savedPhotos = [];
        $photoKeys   = ['photo_1', 'photo_2', 'photo_3'];

        foreach ($photoKeys as $key) {
            if (!isset($_FILES[$key]) || empty($_FILES[$key]['name'])) {
                $savedPhotos[] = null;
                continue;
            }

            $file = $_FILES[$key];

            // Size check
            if ($file['size'] > self::MAX_PHOTO_SIZE) {
                $errors[] = sprintf(
                    $this->module->l('%s exceeds the maximum file size of 2 MB.'),
                    htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8')
                );
                $savedPhotos[] = null;
                continue;
            }

            // Extension check
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $this->allowedExtensions, true)) {
                $errors[] = sprintf(
                    $this->module->l('%s is not an accepted file type. Use JPEG or PNG.'),
                    htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8')
                );
                $savedPhotos[] = null;
                continue;
            }

            // MIME check via finfo (more reliable than $_FILES['type'])
            if (function_exists('finfo_open')) {
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mimeType, $this->allowedMimes, true)) {
                    $errors[] = sprintf(
                        $this->module->l('%s appears to be an invalid image file.'),
                        htmlspecialchars(basename($file['name']), ENT_QUOTES, 'UTF-8')
                    );
                    $savedPhotos[] = null;
                    continue;
                }
            }

            // Generate UUID-based filename
            $filename  = $this->generateUuid() . '.' . $ext;
            $destPath  = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $errors[] = $this->module->l('Failed to save photo. Please try again.');
                $savedPhotos[] = null;
                continue;
            }

            $savedPhotos[] = $filename;
        }

        // ── If there are validation errors, re-display the form ───────────────
        if (!empty($errors)) {
            // Clean up any photos we already saved
            foreach ($savedPhotos as $fn) {
                if ($fn && file_exists($uploadDir . $fn)) {
                    @unlink($uploadDir . $fn);
                }
            }
            $this->errors = array_merge($this->errors, $errors);
            return;
        }

        // ── Insert claim record ───────────────────────────────────────────────
        $inserted = Db::getInstance()->insert('phyto_lag_claim', [
            'id_order'      => $idOrder,
            'id_customer'   => (int) $this->context->customer->id,
            'description'   => pSQL($description, true),
            'photo_1'       => $savedPhotos[0] ? pSQL($savedPhotos[0]) : '',
            'photo_2'       => $savedPhotos[1] ? pSQL($savedPhotos[1]) : '',
            'photo_3'       => $savedPhotos[2] ? pSQL($savedPhotos[2]) : '',
            'claim_status'  => 'pending',
            'date_add'      => date('Y-m-d H:i:s'),
        ]);

        if (!$inserted) {
            $this->errors[] = $this->module->l('Failed to record your claim. Please try again.');
            return;
        }

        // ── Notify the shop (optional) ────────────────────────────────────────
        $this->sendClaimNotification($idOrder, $description);

        // ── Redirect with success message ─────────────────────────────────────
        $redirectUrl = $this->context->link->getPageLink(
            'order-detail',
            true,
            null,
            'id_order=' . $idOrder
        );

        $this->context->controller->success[] = $this->module->l('Your claim has been submitted successfully. We will review it shortly.');

        Tools::redirect($redirectUrl . '&lag_claim=submitted');
    }

    /**
     * Prepare the claim form for display on GET.
     */
    public function initContent()
    {
        parent::initContent();

        $idOrder = (int) Tools::getValue('id_order');

        // ── Validate the order belongs to this customer ───────────────────────
        if (!$idOrder) {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }

        $order = new Order($idOrder);

        if (!Validate::isLoadedObject($order)
            || (int) $order->id_customer !== (int) $this->context->customer->id
        ) {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }

        // ── Check LAG opt-in ──────────────────────────────────────────────────
        $lagRecord = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'phyto_lag_order`
             WHERE `id_order` = ' . $idOrder . '
             AND `lag_opted` = 1'
        );

        if (!$lagRecord) {
            Tools::redirect($this->context->link->getPageLink('my-account'));
        }

        // ── Check claim window ────────────────────────────────────────────────
        $claimWindow = (int) Configuration::get('PHYTO_LAG_CLAIM_WINDOW');
        $orderDate   = new DateTime($order->date_add);
        $now         = new DateTime();
        $daysSince   = (int) $now->diff($orderDate)->days;
        $canClaim    = ($daysSince <= $claimWindow);

        if (!$canClaim) {
            // Redirect to order detail — window closed
            Tools::redirect(
                $this->context->link->getPageLink('order-detail', true, null, 'id_order=' . $idOrder)
            );
        }

        // ── Check no existing claim ───────────────────────────────────────────
        $existing = Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . 'phyto_lag_claim`
             WHERE `id_order` = ' . $idOrder
        );

        if ((int) $existing > 0) {
            Tools::redirect(
                $this->context->link->getPageLink('order-detail', true, null, 'id_order=' . $idOrder)
            );
        }

        // ── Smarty assignments ────────────────────────────────────────────────
        $claimInstructions = Configuration::get('PHYTO_LAG_CLAIM_INSTR');

        $this->context->smarty->assign([
            'phyto_lag_id_order'       => $idOrder,
            'phyto_lag_order_ref'      => $order->reference,
            'phyto_lag_claim_instr'    => $claimInstructions,
            'phyto_lag_claim_window'   => $claimWindow,
            'phyto_lag_form_action'    => $this->context->link->getModuleLink(
                $this->module->name,
                'claim',
                ['id_order' => $idOrder]
            ),
        ]);

        $this->setTemplate('module:phyto_live_arrival/views/templates/front/claim_form.tpl');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Generate a version-4 UUID string.
     *
     * @return string  e.g. "f47ac10b-58cc-4372-a567-0e02b2c3d479"
     */
    private function generateUuid()
    {
        if (function_exists('random_bytes')) {
            $data    = random_bytes(16);
            $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
            $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }

        // Fallback for older PHP
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Send an email notification to the shop when a claim is filed.
     *
     * @param int    $idOrder
     * @param string $description
     */
    private function sendClaimNotification($idOrder, $description)
    {
        $notifyEmail = Configuration::get('PHYTO_LAG_NOTIFY_EMAIL');
        if (empty($notifyEmail) || !Validate::isEmail($notifyEmail)) {
            return;
        }

        $shopName   = Configuration::get('PS_SHOP_NAME');
        $subject    = '[' . $shopName . '] New LAG Claim — Order #' . $idOrder;
        $body       = 'A Live Arrival Guarantee claim has been filed.' . "\n\n"
            . 'Order ID: ' . $idOrder . "\n"
            . 'Customer ID: ' . (int) $this->context->customer->id . "\n"
            . 'Customer: ' . $this->context->customer->firstname . ' ' . $this->context->customer->lastname . "\n"
            . 'Description: ' . $description . "\n";

        // Use PrestaShop's mail class if the template is not available
        @mail($notifyEmail, $subject, $body, 'From: ' . $shopName . ' <' . Configuration::get('PS_SHOP_EMAIL') . '>');
    }
}
