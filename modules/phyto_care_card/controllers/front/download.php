<?php
/**
 * Phyto Care Card — Download Front Controller
 *
 * Serves the PDF care card for a product to the browser as a file download.
 * Token is validated before any content is generated.
 *
 * @author    PhytoCommerce
 * @copyright PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Phyto_Care_CardDownloadModuleFrontController extends ModuleFrontController
{
    /** @var bool Disable rendering of the full theme layout */
    public $display_column_left = false;
    public $display_column_right = false;

    /**
     * Bootstrap the controller — token validation and PDF delivery happen here.
     */
    public function init()
    {
        parent::init();

        $idProduct = (int) Tools::getValue('id_product');
        $token     = (string) Tools::getValue('token');

        // ── 1. Basic parameter validation ─────────────────────────────────────
        if (!$idProduct || empty($token)) {
            $this->redirectToHome();
        }

        // ── 2. Token verification ─────────────────────────────────────────────
        $expectedToken = md5($idProduct . _COOKIE_KEY_);
        if (!hash_equals($expectedToken, $token)) {
            $this->redirectToHome();
        }

        // ── 3. Verify product exists ──────────────────────────────────────────
        if (!Product::existsInDatabase($idProduct, 'product')) {
            $this->redirectToHome();
        }

        // ── 4. Generate PDF content via module ────────────────────────────────
        /** @var Phyto_Care_Card $module */
        $module = Module::getInstanceByName('phyto_care_card');

        if (!$module || !($module instanceof Phyto_Care_Card)) {
            $this->redirectToHome();
        }

        $pdfContent = $module->generatePdfContent($idProduct);

        if (!$pdfContent) {
            // No care data configured — redirect to the product page.
            $productLink = $this->context->link->getProductLink($idProduct);
            Tools::redirect($productLink);
        }

        // ── 5. Deliver the file ───────────────────────────────────────────────
        // Clear any previously buffered output to avoid corrupting binary data.
        if (ob_get_level()) {
            ob_end_clean();
        }

        $filename = 'care-card-' . $idProduct . '.pdf';

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: private, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        echo $pdfContent;
        exit;
    }

    /**
     * Fallback redirect when something goes wrong.
     */
    private function redirectToHome()
    {
        Tools::redirect($this->context->link->getPageLink('index'));
    }

    /**
     * Nothing to render — all work is done in init().
     */
    public function initContent()
    {
        // Intentionally empty.
    }
}
