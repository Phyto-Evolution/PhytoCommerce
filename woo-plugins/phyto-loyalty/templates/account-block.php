<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="phyto-loyalty-account-block">
    <?php if ( $account ) : ?>
        <p><?php printf( esc_html__( 'Points Balance: %d | Tier: %s', 'phyto-loyalty' ), (int) $account->points_balance, esc_html( ucfirst( $account->tier ) ) ); ?></p>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'loyalty' ) ); ?>"><?php esc_html_e( 'View my points history', 'phyto-loyalty' ); ?></a>
    <?php else : ?>
        <p><?php esc_html_e( 'No loyalty account yet. Make a purchase to start earning!', 'phyto-loyalty' ); ?></p>
    <?php endif; ?>
</div>
