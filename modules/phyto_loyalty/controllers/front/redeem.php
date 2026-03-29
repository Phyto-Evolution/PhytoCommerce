<?php
/**
 * controllers/front/redeem.php
 *
 * AJAX endpoint — redeem loyalty points as a cart rule (voucher).
 *
 * POST params:
 *   points_to_redeem  int
 *   token             string (PS CSRF token)
 *
 * JSON response:
 *   {success, discount_amount, new_balance, voucher_code, error}
 *
 * @author  PhytoCommerce
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyAccount.php';
require_once _PS_MODULE_DIR_ . 'phyto_loyalty/classes/PhytoLoyaltyTransaction.php';

class Phyto_LoyaltyRedeemModuleFrontController extends ModuleFrontController
{
    /** @var bool */
    public $auth = true;

    /** @var bool */
    public $ssl  = true;

    /** @var bool */
    public $ajax = true;

    public function postProcess(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isTokenValid()) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $this->module->l('Invalid token.')]));
        }

        if (!(int) Configuration::get('PHYTO_LOYALTY_ENABLED')) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $this->module->l('Loyalty programme is disabled.')]));
        }

        $idCustomer    = (int) $this->context->customer->id;
        $pointsInput   = (int) Tools::getValue('points_to_redeem', 0);
        $redeemRate    = (float) Configuration::get('PHYTO_LOYALTY_REDEEM_RATE');
        $minRedeem     = (int) Configuration::get('PHYTO_LOYALTY_MIN_REDEEM');
        $maxRedeemPct  = (int) Configuration::get('PHYTO_LOYALTY_MAX_REDEEM_PCT');

        // Basic validation
        if ($pointsInput <= 0) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $this->module->l('Please enter a valid number of points.')]));
        }

        if ($pointsInput < $minRedeem) {
            $this->ajaxDie(json_encode([
                'success' => false,
                'error'   => sprintf($this->module->l('Minimum redemption is %d points.'), $minRedeem),
            ]));
        }

        $account = PhytoLoyaltyAccount::getByCustomer($idCustomer);
        if (!$account) {
            $this->ajaxDie(json_encode(['success' => false, 'error' => $this->module->l('No loyalty account found.')]));
        }

        // Expire stale points first
        $account->expireStalePoints((int) Configuration::get('PHYTO_LOYALTY_EXPIRY_DAYS'));

        if ($pointsInput > (int) $account->points_balance) {
            $this->ajaxDie(json_encode([
                'success' => false,
                'error'   => sprintf($this->module->l('You only have %d points available.'), (int) $account->points_balance),
            ]));
        }

        // Check cart cap
        $cartTotal     = (float) $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $discountAmount = round($pointsInput * $redeemRate, 2);
        $maxDiscount   = round(($maxRedeemPct / 100) * $cartTotal, 2);

        if ($discountAmount > $maxDiscount) {
            $maxAllowedPoints = ($redeemRate > 0) ? (int) floor($maxDiscount / $redeemRate) : 0;
            $this->ajaxDie(json_encode([
                'success' => false,
                'error'   => sprintf(
                    $this->module->l('Maximum discount allowed is ₹%.2f (%d points). Please reduce your redemption.'),
                    $maxDiscount,
                    $maxAllowedPoints
                ),
            ]));
        }

        // Create CartRule atomically
        Db::getInstance()->execute('START TRANSACTION');

        try {
            $voucherCode  = 'LOYALTY-' . strtoupper(Tools::passwdGen(8));
            $cartRule     = new CartRule();
            $cartRule->name          = [(int) Configuration::get('PS_LANG_DEFAULT') => 'Loyalty Redemption'];
            $cartRule->id_customer   = $idCustomer;
            $cartRule->date_from     = date('Y-m-d H:i:s');
            $cartRule->date_to       = date('Y-m-d H:i:s', strtotime('+7 days'));
            $cartRule->description   = 'Loyalty points redemption';
            $cartRule->quantity      = 1;
            $cartRule->quantity_per_user = 1;
            $cartRule->reduction_amount  = $discountAmount;
            $cartRule->reduction_tax     = true;
            $cartRule->reduction_currency = (int) $this->context->currency->id;
            $cartRule->free_shipping     = false;
            $cartRule->active            = 1;
            $cartRule->code              = $voucherCode;

            if (!$cartRule->add()) {
                throw new \RuntimeException('Failed to create CartRule.');
            }

            // Add cart rule to current cart
            $this->context->cart->addCartRule((int) $cartRule->id);

            // Deduct from account
            $newBalance                 = (int) $account->points_balance - $pointsInput;
            $account->points_balance    = $newBalance;
            $account->points_redeemed   = (int) $account->points_redeemed + $pointsInput;
            $account->date_upd          = date('Y-m-d H:i:s');
            $account->update();

            PhytoLoyaltyTransaction::record(
                $idCustomer,
                0,
                'redeem',
                -$pointsInput,
                $newBalance,
                'Redeemed for voucher ' . $voucherCode
            );

            Db::getInstance()->execute('COMMIT');

            $this->ajaxDie(json_encode([
                'success'         => true,
                'discount_amount' => $discountAmount,
                'new_balance'     => $newBalance,
                'voucher_code'    => $voucherCode,
            ]));
        } catch (\Throwable $e) {
            Db::getInstance()->execute('ROLLBACK');
            PrestaShopLogger::addLog(
                '[PhytoLoyalty] redeem failed: ' . $e->getMessage(),
                3, null, 'PhytoLoyalty', $idCustomer, true
            );
            $this->ajaxDie(json_encode(['success' => false, 'error' => $this->module->l('Redemption failed. Please try again.')]));
        }
    }

    protected function ajaxDie(string $value): never
    {
        ob_end_clean();
        echo $value;
        exit;
    }
}
