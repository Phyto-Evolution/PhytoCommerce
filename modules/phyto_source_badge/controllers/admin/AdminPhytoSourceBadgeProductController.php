<?php
/**
 * PhytoCommerce — AdminPhytoSourceBadgeProductController
 *
 * Hidden AJAX controller for saving product ↔ badge assignments.
 * This controller has id_parent = -1 (not shown in the menu).
 *
 * Expected POST parameters
 * ────────────────────────
 *   action          string   'save'
 *   id_product      int      Product ID
 *   id_badge[]      int[]    Selected badge IDs
 *   permit_ref[]    string[] Corresponding permit references (may be empty string)
 *   origin_country[]string[] Corresponding origin countries (may be empty string)
 *
 * Response
 * ────────
 *   JSON  {success: true}   on success
 *   JSON  {success: false, error: "..."}  on error
 *
 * @author    PhytoCommerce
 * @copyright 2024 PhytoCommerce
 * @license   MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminPhytoSourceBadgeProductController extends ModuleAdminController
{
    /** @var bool  Require bootstrap markup (not really used — AJAX only). */
    protected $bootstrap = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Force JSON output
        $this->content_type = 'application/json';
    }

    // ──────────────────────────────────────────────────────────────
    //  Request dispatch
    // ──────────────────────────────────────────────────────────────

    /**
     * Main entry point.  Only the 'save' action is supported.
     *
     * All other requests receive a 405 Method Not Allowed response.
     *
     * @return void
     */
    public function initContent(): void
    {
        $action = Tools::getValue('action');

        if ($action === 'save') {
            $this->processSaveBadges();
        } else {
            $this->ajaxRender(json_encode([
                'success' => false,
                'error'   => 'Unknown action.',
            ]));
        }

        exit; // AJAX controllers must not render a full page
    }

    // ──────────────────────────────────────────────────────────────
    //  Save handler
    // ──────────────────────────────────────────────────────────────

    /**
     * Delete all existing assignments for the product and re-insert the
     * submitted ones.  Returns JSON {success: true} or {success: false, error}.
     *
     * @return void
     */
    protected function processSaveBadges(): void
    {
        // ── Validate CSRF token ──────────────────────────────────
        if (!$this->validateToken()) {
            $this->jsonError('Invalid or missing security token.');
            return;
        }

        // ── Validate input ───────────────────────────────────────
        $idProduct = (int) Tools::getValue('id_product', 0);
        if ($idProduct <= 0) {
            $this->jsonError('Invalid product ID.');
            return;
        }

        $badgeIds      = Tools::getValue('id_badge', []);
        $permitRefs    = Tools::getValue('permit_ref', []);
        $originCountries = Tools::getValue('origin_country', []);

        if (!is_array($badgeIds)) {
            $badgeIds = [];
        }

        // ── Database transaction ─────────────────────────────────
        $db = Db::getInstance();

        // Delete existing assignments for this product
        if (!$db->delete('phyto_source_badge_product', 'id_product = ' . $idProduct)) {
            $this->jsonError('Database error while deleting existing assignments.');
            return;
        }

        // Re-insert submitted assignments
        foreach ($badgeIds as $index => $idBadge) {
            $idBadge = (int) $idBadge;
            if ($idBadge <= 0) {
                continue;
            }

            $permitRef     = isset($permitRefs[$index])
                ? pSQL(trim((string) $permitRefs[$index]))
                : '';
            $originCountry = isset($originCountries[$index])
                ? pSQL(trim((string) $originCountries[$index]))
                : '';

            $inserted = $db->insert('phyto_source_badge_product', [
                'id_product'     => $idProduct,
                'id_badge'       => $idBadge,
                'permit_ref'     => $permitRef,
                'origin_country' => $originCountry,
            ]);

            if (!$inserted) {
                $this->jsonError('Database error while inserting badge assignment (badge ID ' . $idBadge . ').');
                return;
            }
        }

        // ── Success ──────────────────────────────────────────────
        $this->ajaxRender(json_encode(['success' => true]));
    }

    // ──────────────────────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────────────────────

    /**
     * Validate the PrestaShop admin token sent with the AJAX request.
     *
     * Accepts the token either as a POST field 'token' or as a query-string
     * parameter 'token'.
     *
     * @return bool
     */
    protected function validateToken(): bool
    {
        $token = Tools::getValue('token', '');

        return $token === Tools::getAdminTokenLite('AdminPhytoSourceBadgeProduct');
    }

    /**
     * Emit a JSON error response and terminate.
     *
     * @param string $message
     *
     * @return void
     */
    protected function jsonError(string $message): void
    {
        $this->ajaxRender(json_encode([
            'success' => false,
            'error'   => $message,
        ]));
    }

    /**
     * Output JSON directly and terminate the response.
     *
     * Overrides the parent ajaxRender to ensure the correct Content-Type
     * header is sent and no extra HTML is appended.
     *
     * @param string $value
     *
     * @return void
     */
    public function ajaxRender($value = null, $controller = null, $method = null): void
    {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo $value;
        exit;
    }
}
