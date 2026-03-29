<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h2><?php esc_html_e( 'My Loyalty Points', 'phyto-loyalty' ); ?></h2>
<p><?php printf( esc_html__( 'Balance: %d points | Tier: %s | Lifetime earned: %d', 'phyto-loyalty' ), (int) $account->points_balance, esc_html( ucfirst( $account->tier ) ), (int) $account->points_lifetime ); ?></p>
<h3><?php esc_html_e( 'Transaction History', 'phyto-loyalty' ); ?></h3>
<table class="woocommerce-table shop_table">
<thead><tr><th><?php esc_html_e( 'Date', 'phyto-loyalty' ); ?></th><th><?php esc_html_e( 'Type', 'phyto-loyalty' ); ?></th><th><?php esc_html_e( 'Points', 'phyto-loyalty' ); ?></th><th><?php esc_html_e( 'Balance', 'phyto-loyalty' ); ?></th><th><?php esc_html_e( 'Note', 'phyto-loyalty' ); ?></th></tr></thead>
<tbody>
<?php foreach ( $transactions as $tx ) : ?>
<tr>
    <td><?php echo esc_html( $tx->date_add ); ?></td>
    <td><?php echo esc_html( ucfirst( $tx->type ) ); ?></td>
    <td style="color:<?php echo $tx->points > 0 ? 'green' : 'red'; ?>"><?php echo ( $tx->points > 0 ? '+' : '' ) . (int) $tx->points; ?></td>
    <td><?php echo (int) $tx->balance_after; ?></td>
    <td><?php echo esc_html( $tx->note ); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
