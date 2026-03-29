<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="phyto-loyalty-cart-widget">
    <h4><?php esc_html_e( 'Your Loyalty Points', 'phyto-loyalty' ); ?></h4>
    <p><?php printf( esc_html__( 'Available: %d points (worth ₹%.2f)', 'phyto-loyalty' ), (int) $account->points_balance, $account->points_balance * $redeem_rate ); ?></p>
    <div class="phyto-loyalty-redeem-form">
        <input type="number" id="phyto-loyalty-redeem-input" min="1" max="<?php echo esc_attr( $account->points_balance ); ?>" placeholder="<?php esc_attr_e( 'Points to redeem', 'phyto-loyalty' ); ?>">
        <button id="phyto-loyalty-apply-btn" class="button"><?php esc_html_e( 'Apply', 'phyto-loyalty' ); ?></button>
    </div>
    <div id="phyto-loyalty-message"></div>
</div>
